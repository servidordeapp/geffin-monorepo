# Implementation Plan: Multi-Tenant Multi-Database Foundation

**Branch**: `002-multi-tenant-database` | **Date**: 2026-05-24 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/002-multi-tenant-database/spec.md`

## Summary

Turn the single-database Laravel API into a hostname-routed, database-per-tenant
system using the already-installed `stancl/tenancy` v3. A **central** context owns
tenants, domains, central administrators, and a tenant audit log; each tenant gets
an **isolated MySQL database** created and migrated on provisioning. Central admins
CRUD tenants from a Livewire UI. **Provisioning is asynchronous**: the central
tenant row + hostname are persisted synchronously (so uniqueness errors surface
immediately), and database creation + tenant migrations are dispatched to the
queue worker (`QUEUE_CONNECTION=database`, container `geffin-worker`). The tenant
starts in `pending` status, flips to `active` on success, or to `failed` (with
cleanup) on failure. **Delete is soft**: the tenant database is preserved and any
request to a soft-deleted tenant's hostname is short-circuited (before any
tenant-DB query) with a localized **403 "contact your manager"** response, kept
distinct from a **404 "tenant not found"** and from a **503 "provisioning in
progress / failed"** for pending/failed tenants. Restore re-enables the tenant by
flipping metadata and reusing the preserved database.

Three non-default wiring changes carry the design: (1) **remove `Jobs\DeleteDatabase`
from the `TenantDeleted` pipeline** so soft-delete never drops a database; (2) a
**`BlockDeletedTenant` middleware** ordered before tenancy initialization that
resolves the host including trashed tenants and produces the 403/404/503 split;
(3) **empty the `TenantCreated` JobPipeline** and move `CreateDatabase` +
`MigrateDatabase` invocation into a dedicated queued job
(`App\Modules\Tenancy\Jobs\ProvisionTenantDatabase`) so provisioning is truly
asynchronous and the HTTP request is no longer blocked by `CREATE DATABASE`.

## Technical Context

**Language/Version**: PHP 8.3+ (composer `^8.3`), Laravel 13

**Primary Dependencies**: `stancl/tenancy` ^3.10 (multi-DB tenancy), Livewire ^4.3
(admin UI), Pest ^4 (tests), Larastan ^3, Pint ^1

**Storage**: MySQL 8.4 — central DB `geffin` + one DB per tenant (`tenant<uuid>`),
provisioned by `MySQLDatabaseManager`. Tests: sqlite, one file per tenant.

**Queue**: `QUEUE_CONNECTION=database`; queue worker runs in the `geffin-worker`
docker compose service (already on `up`). Tests run with `QUEUE_CONNECTION=sync`
so jobs execute inline; the new `Queue::fake()`-based test asserts the dispatch
contract explicitly.

**Testing**: Pest (Feature + Unit), `RefreshDatabase` for central; tenant DBs
created per-test via the provisioning pipeline.

**Target Platform**: Linux server (docker compose; `geffin-mysql` service)

**Project Type**: Web service (Laravel modular monolith) — central admin module
added under `app/Modules/Tenancy/`.

**Performance Goals**: synchronous provisioning leg (central row + hostname +
queue dispatch) <2s (SC-001); end-to-end provisioning including async
database creation + migration <60s (SC-001); 403 block <500ms p95 with zero
tenant-DB queries (SC-003); restore <5s (SC-004); migrate ≥50 tenants <5min (SC-006).

**Constraints**: tenant isolation = 0 cross-tenant leakage (SC-002); soft-delete
never drops data; no hard-delete path in scope; `slug`/`domain` unique incl.
trashed; pt-BR copy configurable without deploy (FR-016).

**Scale/Scope**: dozens→low-hundreds of tenants on a shared MySQL server (v1).
Per-tenant DB servers, hard deletion, and tenant-user auth are out of scope.

## Constitution Check

*GATE: must pass before Phase 0 and re-checked after Phase 1.*

> `.specify/memory/constitution.md` is still the unfilled template (placeholder
> principles). Absent ratified principles, this plan is gated against the repo's
> authoritative architectural rules in `CLAUDE.md` / `GEMINI.md` (9 architectural
> principles) and the TDD mandate.

| Gate | Status | Notes |
|------|--------|-------|
| TDD-first (red→green) | PASS | Every behavior gets a failing Pest test before code (see tasks). |
| Module isolation | PASS | New `Tenancy` module is **central infrastructure**; it imports no business module's internals. Business modules don't exist yet. |
| Event-driven cross-domain | PASS | Provisioning rides stancl tenant lifecycle events; no synchronous cross-module business calls introduced. |
| Financial consistency / Auditability | PASS | `tenant_audit_events` records every tenant CRUD action; no secrets logged (FR-013/SC-005). |
| Idempotency | PASS | Provisioning rolls back on failure; restore and `tenants:migrate` are idempotent. |
| Stateless services | PASS | All state in MySQL; no in-memory tenant state. |
| Simplicity / YAGNI | PASS | Hard-delete, per-tenant DB servers, tenant auth deferred. Queued provisioning is in scope (one job, one status flip; failure path consolidates rollback inside the job). One boolean for admin role, not a new table. |
| BFF / hostname forwarding | PASS (doc-only) | Spec documents that BFFs forward the original host; no BFF code changed here. |

No violations → Complexity Tracking is empty.

**Post-Phase-1 re-check**: design introduces no new framework, no extra project,
no repository abstraction. Custom Tenant model + one middleware + one service layer
are the minimum to satisfy FR-004/007/008/009. Gate still PASS.

## Project Structure

### Documentation (this feature)

```text
specs/002-multi-tenant-database/
├── plan.md              # This file
├── research.md          # Phase 0 — decisions D1–D12
├── data-model.md        # Phase 1 — entities & rules
├── quickstart.md        # Phase 1 — run/verify guide
├── contracts/
│   ├── tenant-admin-routes.md     # central CRUD route contract
│   └── tenant-resolution.md       # host-resolution & 403/404 block contract
├── checklists/
│   └── requirements.md  # spec quality checklist
└── tasks.md             # Phase 2 — created by /speckit-tasks (NOT here)
```

### Source Code (repository root = `laravel/`)

> **As built.** The tree below reflects what was actually implemented, which
> diverges from the original sketch in three ways: (1) the single `TenantService`
> was split into one service per operation (`TenantProvisioningService`,
> `SoftDeleteTenantService`, `UpdateTenantService`, `RestoreTenantService`) sharing
> a `Concerns\ResolvesActorId` trait; (2) write/show actions are **single-action
> invokable controllers** under `Http/Controllers/Tenants/`, while only the
> list/create/edit screens are Livewire components; (3) an extra
> `OnlyCentralDomains` middleware fences the admin routes to the central host(s),
> and Blade views live under `resources/views/tenancy/` (not beside the Livewire
> classes).

```text
app/
├── Models/
│   └── User.php                          # + is_central_admin (central admins)
├── Providers/
│   └── TenancyServiceProvider.php        # EDIT: drop Jobs\DeleteDatabase from TenantDeleted AND empty TenantCreated pipeline (async job handles it)
└── Modules/
    └── Tenancy/                          # NEW central-context module
        ├── Models/
        │   ├── Tenant.php                # extends stancl Tenant + SoftDeletes + custom cols; forceDelete() guarded
        │   ├── Domain.php                # subclass; tenant() relation uses withTrashed()
        │   └── TenantAuditEvent.php      # append-only audit row (UPDATED_AT = null)
        ├── Enums/
        │   ├── TenantStatusEnum.php          # Pending, Active, Inactive, Failed
        │   └── TenantAuditActionEnum.php     # Created, ProvisionQueued, Updated, SoftDeleted, Restored, Migrated, ProvisionFailed
        ├── Http/
        │   ├── Controllers/
        │   │   └── Tenants/              # single-action invokable controllers
        │   │       ├── StoreTenantController.php
        │   │       ├── ShowTenantController.php
        │   │       ├── UpdateTenantController.php
        │   │       ├── DestroyTenantController.php
        │   │       └── RestoreTenantController.php
        │   ├── Middleware/
        │   │   ├── BlockDeletedTenant.php   # 403 trashed / 404 missing / 503 pending|failed, pre-tenancy
        │   │   └── OnlyCentralDomains.php   # fences /admin/inquilinos to central host(s); 404 otherwise
        │   └── Requests/
        │       ├── StoreTenantRequest.php
        │       └── UpdateTenantRequest.php
        ├── Jobs/
        │   └── ProvisionTenantDatabase.php   # queued job: createDatabase + migrate + flip status; failure → cleanup + audit (no re-throw)
        ├── Livewire/
        │   └── Tenants/{Index,Create,Edit}.php   # list/create/edit screens (views in resources/views/tenancy/tenants/)
        ├── Services/
        │   ├── Concerns/
        │   │   └── ResolvesActorId.php        # shared trait: resolve the acting central admin id
        │   ├── TenantProvisioningService.php  # sync row+domain in transaction; dispatch queued provisioning job
        │   ├── SoftDeleteTenantService.php    # soft-delete + status=inactive + audit; rejects central/tenant-context
        │   ├── UpdateTenantService.php        # persist name; reject identity collisions; audit
        │   ├── RestoreTenantService.php       # restore + status=active; reuse DB; audit
        │   └── TenantAuditLogger.php
        ├── Policies/
        │   └── TenantPolicy.php               # manage-tenants gate
        ├── Providers/
        │   └── TenancyModuleServiceProvider.php  # routes, policy, bindings
        └── routes.php                         # central /admin/inquilinos routes

bootstrap/providers.php                   # EDIT: register TenancyModuleServiceProvider

config/
├── tenancy.php                           # EDIT: tenant_model + domain_model → App\Modules\Tenancy\Models\*
└── tenancy_block.php                     # NEW: configurable block copy + status codes (unavailable 403, not_found 404, provisioning 503)

routes/
└── tenant.php                            # EDIT: prepend BlockDeletedTenant to group

lang/pt_BR/tenancy.php                    # NEW: localized block + provisioning + status messages

resources/views/tenancy/                  # NEW central-context Blade views
├── blocked.blade.php                     # 403 contact-manager
├── not-found.blade.php                   # 404 tenant not found
├── provisioning.blade.php               # 503 pending/failed (meta-refresh while pending)
└── tenants/{index,create,edit,show}.blade.php   # admin CRUD screens

database/
├── migrations/                           # CENTRAL
│   ├── 2019_09_15_000010_create_tenants_table.php  # EDIT: + slug/name/status/softDeletes
│   ├── 2019_09_15_000020_create_domains_table.php  # (unique domain already)
│   ├── 2026_05_24_213250_add_is_central_admin_to_users_table.php       # NEW
│   └── 2026_05_24_213314_create_tenant_audit_events_table.php          # NEW
├── migrations/tenant/                    # TENANT (per-tenant DB)
│   ├── 2026_05_24_213323_create_users_table.php     # NEW baseline tenant users schema
│   └── 2026_05_24_213324_create_sessions_table.php  # NEW per-tenant sessions
└── factories/
    ├── TenantFactory.php                 # afterCreating provisions DB; pending()/failed() states
    └── DomainFactory.php                 # NEW

tests/
├── Feature/Tenancy/
│   ├── TenantProvisioningTest.php        # async dispatch contract + inline-sync happy path, rollback, audit
│   ├── TenantIsolationTest.php           # SC-002 marker A invisible in B
│   ├── SoftDeleteBlockTest.php           # 403 trashed / 404 missing / no DB query
│   ├── BlockDeletedTenantTest.php        # middleware 403/404/503 split
│   ├── RestoreTenantTest.php
│   ├── TenantManagementTest.php          # index/search/filter + update (US3)
│   ├── TenantAdminAuthorizationTest.php  # is_central_admin gate, context isolation
│   ├── TenantRoutesTest.php              # route wiring + OnlyCentralDomains fence
│   ├── BulkMigrateTest.php               # tenants:migrate over active, skips trashed
│   └── Livewire/{CreateTest,EditTest}.php
└── Unit/Tenancy/
    ├── TenantModelTest.php               # custom columns, soft-delete, slug uniqueness
    ├── DomainModelTest.php               # withTrashed() relation
    └── TenantPolicyTest.php              # manage-tenants gate
```

**Structure Decision**: Central tenant management lives in a dedicated
`app/Modules/Tenancy/` module (per the locked decision), establishing the
modular-monolith convention from `CLAUDE.md`. It is **central infrastructure**, not
one of the six tenant-scoped business modules, and imports none of them — so module
isolation holds. The existing flat `app/Models/User.php` stays (central admins);
tenant-scoped schema lives only under `database/migrations/tenant/`. The
mutating/read actions are thin single-action controllers that delegate to the
per-operation services; the per-operation service split (instead of one
`TenantService`) keeps each lifecycle action — provision, update, soft-delete,
restore — in its own class.

## Complexity Tracking

No constitution/architecture violations. Section intentionally empty.

## Phase 2 note

`/speckit-tasks` will derive the ordered, TDD-first task list from this plan,
`data-model.md`, and `contracts/`. Suggested ordering: central schema + custom
Tenant model → provisioning + isolation → soft-delete wiring + block middleware →
restore → admin UI/authz → audit log → bulk migrate. User stories US1 and US2 are
both P1 and are the MVP slice; US3/US4 follow.
