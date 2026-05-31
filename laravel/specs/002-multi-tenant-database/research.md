# Phase 0 Research: Multi-Tenant Multi-Database Foundation

**Feature**: `002-multi-tenant-database` | **Date**: 2026-05-24

This document resolves every open decision required before design. The application
already has `stancl/tenancy` v3 installed, `php artisan tenancy:install` executed,
and `App\Providers\TenancyServiceProvider` registered in `bootstrap/providers.php`.
Default stubs are present: `config/tenancy.php`, the `tenants` / `domains`
migrations, `routes/tenant.php`, and an empty `database/migrations/tenant/` directory.

---

## D1. Tenant database engine

- **Decision**: One **MySQL 8.4** database per tenant on the same server as the
  central database, provisioned by `Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager`.
  Tests run against **sqlite** with one file per tenant (`SQLiteDatabaseManager`).
- **Rationale**: The actual runtime defined in `infra/docker/docker-compose.yml`
  is `mysql:8.4` (`DB_CONNECTION=mysql`, host `mysql`, db `geffin`). The spec
  assumption text said "PostgreSQL", but no Postgres service exists anywhere in
  infra and changing the datastore is out of scope for a tenancy-wiring feature.
  The stancl MySQL manager already supports one-database-per-tenant cleanly.
- **Consequence**: The spec assumption line is corrected from PostgreSQL to MySQL
  (same PR, specs stay the source of truth). The MySQL connection user must hold
  the `CREATE`/`DROP DATABASE` privilege for provisioning; documented in quickstart.
- **Alternatives considered**:
  - *PostgreSQL (match spec literally)* — rejected: would require introducing a
    Postgres container, migrating the existing single-DB app off MySQL, and re-doing
    feature 001's storage. Large, unrelated blast radius.
  - *PostgreSQL schema-per-tenant* (`PostgreSQLSchemaManager`) — rejected: spec
    mandates a dedicated isolated **database** per tenant, not a shared-DB schema.

## D2. Central connection & tenant connection template

- **Decision**: Keep `database.central_connection => env('DB_CONNECTION')`
  (resolves to `mysql` in docker, `sqlite` in tests). Leave
  `template_tenant_connection => null` so the dynamic tenant connection is cloned
  from the central connection with only the database name swapped.
- **Rationale**: stancl's `DatabaseTenancyBootstrapper` clones the central
  connection for each tenant; cloning keeps credentials/host identical and only
  varies the `database` name (`prefix + tenant_id`, i.e. `tenant<uuid>`). No extra
  connection definitions needed.
- **Alternatives**: a hand-written `tenant` template connection — rejected as
  redundant; the reserved name `tenant` is also disallowed by the package.

## D3. Custom Tenant model with real columns + soft deletes

- **Decision**: Introduce `App\Modules\Tenancy\Models\Tenant` extending
  `Stancl\Tenancy\Database\Models\Tenant`, adding Laravel `SoftDeletes` and real
  columns `slug`, `name`, `status`, `deleted_at`. Point `config/tenancy.php`
  `tenant_model` at it. Override `getCustomColumns()` (from the package's
  VirtualColumn/`HasDataColumn` trait) to list `id`, `slug`, `name`, `status`,
  `deleted_at` so they become first-class columns; everything else still falls
  through to the `data` JSON column.
- **Rationale**: The base model stores all non-listed attributes in a single
  `data` JSON column (verified in `vendor/stancl/tenancy/.../HasDataColumn.php` →
  `Stancl\VirtualColumn\VirtualColumn`). Real columns are required to enforce
  DB-level **uniqueness** on `slug` (FR-005) and to filter by `status`/`deleted_at`.
  `deleted_at` MUST be a custom column or `SoftDeletes` writes would be swallowed
  by the JSON encoder.
- **Alternatives**: keep everything in `data` JSON — rejected: cannot put a unique
  index on a JSON path portably across MySQL/sqlite, and querying status/trashed
  becomes awkward.

## D4. Preserve the tenant database on delete (THE critical wiring change)

- **Decision**: Remove `Jobs\DeleteDatabase` from the `Events\TenantDeleted`
  pipeline in `App\Providers\TenancyServiceProvider`. Keep `CreateDatabase` +
  `MigrateDatabase` on `TenantCreated`.
- **Rationale**: The base Tenant model maps the Eloquent `deleted` event to
  `Events\TenantDeleted` (verified at `vendor/stancl/tenancy/.../Models/Tenant.php`
  line 60). With `SoftDeletes`, a soft delete STILL fires `deleted`, so the default
  wiring would **drop the tenant database** on a soft delete — directly violating
  FR-007 and SC-007. Detaching `DeleteDatabase` makes both soft- and force-delete
  leave the database intact. Hard deletion of databases is explicitly out of scope
  (spec assumptions) and will be designed in a future story.
- **Alternatives**:
  - *Conditionally skip the job when trashed* — rejected: more moving parts; we
    have no in-scope path that should ever drop a tenant DB, so unconditional
    removal is simpler (YAGNI) and safer.

## D5. Distinguishing "soft-deleted" (403) from "not found" (404) at routing time

- **Decision**: Add a custom middleware `App\Modules\Tenancy\Http\Middleware\BlockDeletedTenant`
  that runs **before** `InitializeTenancyByDomain` on the tenant route group. It
  resolves the request host against the `domains` table including trashed tenants
  (`->withTrashed()`):
  - host has a domain row whose tenant is **trashed** → short-circuit with **403**
    + localized "contact your manager" response. **No tenancy is initialized and no
    tenant-DB connection is opened** (satisfies SC-003).
  - host has a domain row whose tenant is **active** → pass through; stancl's
    `InitializeTenancyByDomain` initializes tenancy normally.
  - host has **no** domain row → respond **404** "tenant not found" (distinct copy).
- **Rationale**: stancl resolves the tenant via the `Domain` → `Tenant`
  relationship. A `SoftDeletes` global scope on `Tenant` would make a trashed
  tenant resolve to *nothing*, collapsing the 403 and 404 cases into a single 404
  and losing the distinction FR-008/FR-009 require. A dedicated pre-tenancy gate
  keeps one source of truth, guarantees zero tenant-DB queries for trashed tenants,
  and lets us serve both HTML and JSON.
- **Content negotiation**: branch on `$request->expectsJson()`. JSON body shape and
  HTTP codes are fixed (403 / 404) so BFFs and mobile clients can react (FR-009).
- **Alternatives**:
  - *Override stancl's domain resolver to include trashed, then check in a listener*
    — rejected: deeper coupling to package internals; the middleware approach needs
    no resolver override.
  - *Let the framework 404 then rewrite* — rejected: by the time stancl throws, we
    cannot cleanly tell "trashed" from "never existed" without re-querying anyway.

## D6. Atomic provisioning + rollback

- **Decision**: A `TenantProvisioningService` wraps creation in a central DB
  transaction: create the `Tenant` row + at least one `Domain` row, then let the
  synchronous `TenantCreated` pipeline (`CreateDatabase`, `MigrateDatabase`,
  `shouldBeQueued(false)`) run. On any failure: roll back the central rows, drop a
  partially-created tenant database if one exists, free the hostname, write a
  `provision_failed` audit event, and surface the underlying error to the admin.
- **Rationale**: FR-004 requires create-or-nothing from the admin's perspective and
  SC-001 a <60s synchronous result. Keeping the pipeline synchronous (v1) gives an
  immediate success/failure. DB-level unique constraints on `slug` and `domain`
  make concurrent same-identifier creates fail for all but one caller (edge case in
  spec; FR-005).
- **Alternatives**: queued provisioning — deferred: adds async status tracking UX
  not needed at current scale; can switch `shouldBeQueued(true)` later.

## D7. Central-administrator authorization & context isolation

- **Decision**: Mark central admins with a boolean `is_central_admin` on the
  existing central `users` table. Authorize tenant management with a
  `manage-tenants` Gate / `TenantPolicy` requiring `is_central_admin`. Tenant-
  management routes are registered only in the **central** web context
  (`routes/web.php` / module `routes.php`, no tenancy-init middleware); the tenant
  route group never includes them.
- **Rationale**: FR-003/FR-014. The existing `User` + login/password-reset
  (feature 001) is the central-admin auth surface (confirmed product decision).
  Tenant-scoped requests physically cannot reach central-only routes because they
  are not in the tenant route group, and central requests never touch tenant data
  without explicit `tenancy()->initialize()`.
- **Alternatives**: a separate `central_admins` table — rejected: the existing
  `users` table already lives in the central DB and is the natural home; a boolean
  flag is the minimal change (YAGNI).

## D8. Tenant baseline migrations & bulk migrate

- **Decision**: Add a baseline tenant migration creating a tenant-scoped `users`
  table (school users — schema only; tenant auth flows are out of scope here) under
  `database/migrations/tenant/`. Bulk migrations run via `php artisan tenants:migrate`
  (already pathed to `database/migrations/tenant` in `config/tenancy.php`).
- **Rationale**: FR-012/FR-015. `tenants:migrate` iterates `Tenant::all()`, which
  with the `SoftDeletes` global scope excludes trashed tenants automatically, so
  soft-deleted tenants are skipped by default. A real tenant table also gives the
  isolation test (SC-002) a concrete place to write a marker row in tenant A and
  prove it is invisible in tenant B.
- **Alternatives**: empty tenant schema + throwaway test table — rejected: a real
  `users` baseline is the schema every future business module will assume anyway.

## D9. Restore

- **Decision**: `TenantService::restore()` calls `withTrashed()->restore()` to clear
  `deleted_at`, flips `status` back to `active`, and writes a `restored` audit event.
  No re-migration, no data recreation; the preserved database and existing domain
  rows are reused as-is.
- **Rationale**: FR-010/SC-004. Because D4 never dropped the DB and D5 kept the
  domain rows, restore is a metadata flip — fast (<5s) and lossless.

## D10. Audit log

- **Decision**: New central, append-only `tenant_audit_events` table +
  `TenantAuditEvent` model. Every create/update/soft-delete/restore/migration/
  provision-failure writes a row capturing actor id, tenant id, action, outcome,
  timestamp, and a non-sensitive metadata JSON. Credentials/secrets are never logged.
- **Rationale**: FR-013/SC-005 (retrievable ≥12 months → no auto-prune in v1).
- **Alternatives**: reuse Laravel's generic log files — rejected: not queryable or
  retention-guaranteed per the constitution's auditability principle.

## D11. Localized, configurable block copy

- **Decision**: Store the "contact your manager" and "tenant not found" copy in a
  pt-BR translation file with a thin `config/tenancy_block.php` override layer so
  operators can change wording (or switch to "contact the platform team") without a
  code deploy. Primary locale pt-BR.
- **Rationale**: FR-016. Lang file gives localization; config override gives
  deploy-free editability.

## D12. Admin UI surface

- **Decision**: Build the central CRUD UI with **Livewire v4** (project standard;
  `routes/web.php` already uses `Route::livewire(...)`). The `BlockDeletedTenant`
  middleware additionally serves JSON for API/BFF callers. URL segments in
  Portuguese, route names in English (project convention) — e.g.
  `/admin/inquilinos` with names `tenants.index|create|store|edit|update|destroy|restore`.
- **Rationale**: Consistency with feature 001 and the installed stack; no new
  frontend dependency.

---

## Resolved unknowns summary

| Topic | Resolution |
|-------|-----------|
| DB engine | MySQL 8.4 (sqlite-file-per-tenant in tests); spec assumption corrected |
| Tenant model | Custom `App\Modules\Tenancy\Models\Tenant` + SoftDeletes + real columns |
| Delete safety | Remove `Jobs\DeleteDatabase` from `TenantDeleted` pipeline |
| 403 vs 404 | `BlockDeletedTenant` pre-tenancy middleware, `withTrashed` host lookup |
| Provisioning | Synchronous pipeline wrapped in transaction with rollback + audit |
| Authz | `is_central_admin` flag + `manage-tenants` gate; central-only routes |
| Tenant schema | Baseline tenant `users` migration; `tenants:migrate` skips trashed |
| Copy | pt-BR lang file + `config/tenancy_block.php` override |
| UI | Livewire v4, Portuguese URL segments / English route names |

No `NEEDS CLARIFICATION` items remain.
