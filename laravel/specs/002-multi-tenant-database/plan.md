# Implementation Plan: Multi-Tenant Multi-Database Foundation

**Branch**: `002-multi-tenant-database` | **Date**: 2026-05-24 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/002-multi-tenant-database/spec.md`

## Summary

Turn the single-database Laravel API into a hostname-routed, database-per-tenant
system using the already-installed `stancl/tenancy` v3. A **central** context owns
tenants, domains, central administrators, and a tenant audit log; each tenant gets
an **isolated MySQL database** created and migrated on provisioning. Central admins
CRUD tenants from a Livewire UI. **Delete is soft**: the tenant database is
preserved and any request to a soft-deleted tenant's hostname is short-circuited
(before any tenant-DB query) with a localized **403 "contact your manager"**
response, kept distinct from a **404 "tenant not found"**. Restore re-enables the
tenant by flipping metadata and reusing the preserved database.

Two non-default wiring changes carry the design: (1) **remove `Jobs\DeleteDatabase`
from the `TenantDeleted` pipeline** so soft-delete never drops a database, and (2) a
**`BlockDeletedTenant` middleware** ordered before tenancy initialization that
resolves the host including trashed tenants to produce the 403/404 split.

## Technical Context

**Language/Version**: PHP 8.3+ (composer `^8.3`), Laravel 13

**Primary Dependencies**: `stancl/tenancy` ^3.10 (multi-DB tenancy), Livewire ^4.3
(admin UI), Pest ^4 (tests), Larastan ^3, Pint ^1

**Storage**: MySQL 8.4 — central DB `geffin` + one DB per tenant (`tenant<uuid>`),
provisioned by `MySQLDatabaseManager`. Tests: sqlite, one file per tenant.

**Testing**: Pest (Feature + Unit), `RefreshDatabase` for central; tenant DBs
created per-test via the provisioning pipeline.

**Target Platform**: Linux server (docker compose; `geffin-mysql` service)

**Project Type**: Web service (Laravel modular monolith) — central admin module
added under `app/Modules/Tenancy/`.

**Performance Goals**: provision <60s (SC-001); 403 block <500ms p95 with zero
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
| Simplicity / YAGNI | PASS | Hard-delete, per-tenant DB servers, tenant auth, queued provisioning all deferred. One boolean for admin role, not a new table. |
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
└── tasks.md             # Phase 2 — created by /speckit-tasks (NOT here)
```

### Source Code (repository root = `laravel/`)

```text
app/
├── Models/
│   └── User.php                          # + is_central_admin (central admins)
├── Providers/
│   └── TenancyServiceProvider.php        # EDIT: drop Jobs\DeleteDatabase from TenantDeleted
└── Modules/
    └── Tenancy/                          # NEW central-context module
        ├── Models/
        │   ├── Tenant.php                # extends stancl Tenant + SoftDeletes + custom cols
        │   ├── Domain.php                # optional subclass (withTrashed joins)
        │   └── TenantAuditEvent.php
        ├── Enums/
        │   ├── TenantStatus.php
        │   └── TenantAuditAction.php
        ├── Http/
        │   ├── Middleware/
        │   │   └── BlockDeletedTenant.php   # 403 trashed / 404 missing, pre-tenancy
        │   └── Requests/
        │       ├── StoreTenantRequest.php
        │       └── UpdateTenantRequest.php
        ├── Livewire/
        │   └── Tenants/{Index,Create,Edit}.php (+ blade views)
        ├── Services/
        │   ├── TenantProvisioningService.php  # atomic create + rollback
        │   ├── TenantService.php              # update/soft-delete/restore
        │   └── TenantAuditLogger.php
        ├── Policies/
        │   └── TenantPolicy.php               # manage-tenants gate
        ├── Providers/
        │   └── TenancyModuleServiceProvider.php  # routes, policy, bindings
        └── routes.php                         # central /admin/inquilinos routes

bootstrap/providers.php                   # EDIT: register TenancyModuleServiceProvider

config/
├── tenancy.php                           # EDIT: tenant_model → App\Modules\Tenancy\Models\Tenant
└── tenancy_block.php                     # NEW: configurable block copy + status codes

routes/
└── tenant.php                            # EDIT: prepend BlockDeletedTenant to group

lang/pt_BR/tenancy.php                    # NEW: localized block messages

database/
├── migrations/                           # CENTRAL
│   ├── 2019_09_15_000010_create_tenants_table.php  # EDIT: + slug/name/status/softDeletes
│   ├── 2019_09_15_000020_create_domains_table.php  # (unique domain already)
│   ├── XXXX_add_is_central_admin_to_users_table.php       # NEW
│   └── XXXX_create_tenant_audit_events_table.php           # NEW
└── migrations/tenant/                    # TENANT (per-tenant DB)
    └── XXXX_create_users_table.php       # NEW baseline tenant users schema

tests/
├── Feature/Tenancy/
│   ├── TenantProvisioningTest.php        # create+migrate+rollback, audit
│   ├── TenantIsolationTest.php           # SC-002 marker A invisible in B
│   ├── SoftDeleteBlockTest.php           # 403 trashed / 404 missing / no DB query
│   ├── RestoreTenantTest.php
│   └── TenantAdminAuthorizationTest.php  # is_central_admin gate, context isolation
└── Unit/Tenancy/
    └── TenantModelTest.php               # custom columns, soft-delete, slug uniqueness
```

**Structure Decision**: Central tenant management lives in a dedicated
`app/Modules/Tenancy/` module (per the locked decision), establishing the
modular-monolith convention from `CLAUDE.md`. It is **central infrastructure**, not
one of the six tenant-scoped business modules, and imports none of them — so module
isolation holds. The existing flat `app/Models/User.php` stays (central admins);
tenant-scoped schema lives only under `database/migrations/tenant/`.

## Complexity Tracking

No constitution/architecture violations. Section intentionally empty.

## Phase 2 note

`/speckit-tasks` will derive the ordered, TDD-first task list from this plan,
`data-model.md`, and `contracts/`. Suggested ordering: central schema + custom
Tenant model → provisioning + isolation → soft-delete wiring + block middleware →
restore → admin UI/authz → audit log → bulk migrate. User stories US1 and US2 are
both P1 and are the MVP slice; US3/US4 follow.
