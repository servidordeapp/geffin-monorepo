---
description: "Task list for Multi-Tenant Multi-Database Foundation"
---

# Tasks: Multi-Tenant Multi-Database Foundation

**Input**: Design documents from `specs/002-multi-tenant-database/`

**Prerequisites**: plan.md, spec.md, research.md (D1–D12), data-model.md, contracts/ (tenant-admin-routes.md, tenant-resolution.md)

**Tests**: INCLUDED. TDD is mandatory per the plan's Constitution Check (every behavior gets a failing Pest test before code). Each story's tests are written first and MUST fail before implementation.

**Organization**: Tasks are grouped by user story. Repository root = `laravel/`.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependency on an incomplete task)
- **[Story]**: US1 / US2 / US3 / US4 (Setup, Foundational, Polish carry no story label)
- Exact file paths are included in every task

## Stack

PHP 8.3+, Laravel 13, `stancl/tenancy` ^3.10, Livewire ^4, Pest ^4, Larastan ^3, Pint ^1. Runtime DB: MySQL 8.4 (central `geffin` + one `tenant<uuid>` per tenant). Test DB: sqlite, one file per tenant (`SQLiteDatabaseManager`).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Module skeleton and static configuration that later phases build on.

- [ ] T001 [P] Create the Tenancy module directory tree under `app/Modules/Tenancy/` (`Models/`, `Enums/`, `Http/Middleware/`, `Http/Requests/`, `Livewire/Tenants/`, `Services/`, `Policies/`, `Providers/`) and the test directories `tests/Feature/Tenancy/` and `tests/Unit/Tenancy/` (use a `.gitkeep` where a dir would otherwise be empty)
- [ ] T002 [P] Create `config/tenancy_block.php` with keys `unavailable_message`, `not_found_message`, `status_unavailable` (403), `status_not_found` (404), each env-overridable and defaulting to the pt-BR lang lines (per contracts/tenant-resolution.md "Configurable copy")
- [ ] T003 [P] Create `lang/pt_BR/tenancy.php` with the localized "contact your manager" (unavailable) and "tenant not found" copy referenced by `config/tenancy_block.php` (FR-016, D11)
- [ ] T004 [P] Configure the test environment for per-tenant sqlite in `phpunit.xml` (`DB_CONNECTION=sqlite`) and confirm `config/tenancy.php` `database.managers` maps `sqlite => Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager` and that the tenant sqlite files land in a writable path (D1)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Central schema, custom models, enums, audit logging, authorization, and stancl wiring. Every user story depends on this phase.

**⚠️ CRITICAL**: No user-story work may begin until this phase is complete.

- [ ] T005 [P] Create enum `App\Modules\Tenancy\Enums\TenantStatus` (`Active`, `Inactive`; TitleCase keys, string-backed) in `app/Modules/Tenancy/Enums/TenantStatus.php`
- [ ] T006 [P] Create enum `App\Modules\Tenancy\Enums\TenantAuditAction` (`Created`, `Updated`, `SoftDeleted`, `Restored`, `Migrated`, `ProvisionFailed`) in `app/Modules/Tenancy/Enums/TenantAuditAction.php`
- [ ] T007 [P] EDIT `database/migrations/2019_09_15_000010_create_tenants_table.php`: add real columns `slug` (string, **unique** incl. trashed), `name` (string), `status` (string), and `softDeletes()` (`deleted_at`) — keep the existing `id`/`data` columns (data-model "Tenant")
- [ ] T008 [P] Create migration `database/migrations/XXXX_add_is_central_admin_to_users_table.php` adding `is_central_admin` boolean default `false` to the central `users` table (D7)
- [ ] T009 [P] Create migration `database/migrations/XXXX_create_tenant_audit_events_table.php` (`id`, `tenant_id` string nullable, `actor_id` bigint nullable, `action` string, `outcome` string, `metadata` json nullable, `created_at`; no `updated_at`) (D10, data-model "TenantAuditEvent")
- [ ] T010 [P] Create baseline tenant migration `database/migrations/tenant/XXXX_create_users_table.php` (`id`, `name`, `email` unique, `password`, timestamps) — gives the isolation test a real table to write a marker row (D8)
- [ ] T011 [P] Verify `database/migrations/2019_09_15_000020_create_domains_table.php` enforces `unique('domain')`; edit only if the unique index is missing (FR-005)
- [ ] T012 [P] [Unit, TDD — write first, must FAIL] `tests/Unit/Tenancy/TenantModelTest.php`: asserts `slug`/`name`/`status`/`deleted_at` persist as real columns (not in `data` JSON), `SoftDeletes` sets `deleted_at`, and `slug` uniqueness is enforced including against trashed rows
- [ ] T013 Create model `App\Modules\Tenancy\Models\Tenant` in `app/Modules/Tenancy/Models/Tenant.php` extending `Stancl\Tenancy\Database\Models\Tenant`, using `SoftDeletes`, overriding `getCustomColumns()` to return `['id','slug','name','status','deleted_at']`, with `hasMany(Domain)` and `hasMany(TenantAuditEvent)` and a `newFactory()` hook — makes T012 pass (depends on T007, T012)
- [ ] T014 [P] Create model `App\Modules\Tenancy\Models\Domain` in `app/Modules/Tenancy/Models/Domain.php` extending `Stancl\Tenancy\Database\Models\Domain` with a `tenant()` relation that uses `withTrashed()` (so the block middleware can resolve a trashed tenant's host) (D5)
- [ ] T015 [P] Create append-only model `App\Modules\Tenancy\Models\TenantAuditEvent` in `app/Modules/Tenancy/Models/TenantAuditEvent.php` (`$fillable` actor/tenant/action/outcome/metadata, `metadata` cast to array, `UPDATED_AT = null`) (data-model "TenantAuditEvent")
- [ ] T016 Create `database/factories/TenantFactory.php` (unique `slug`, `name`, `status=active`) and point `Tenant::newFactory()` at it (depends on T013)
- [ ] T017 [P] EDIT `app/Models/User.php` to add `is_central_admin` to `$fillable` and cast it `boolean`; add a `centralAdmin()` state to `database/factories/UserFactory.php` (D7)
- [ ] T018 Create `App\Modules\Tenancy\Services\TenantAuditLogger` in `app/Modules/Tenancy/Services/TenantAuditLogger.php` writing one `tenant_audit_events` row (actor_id, tenant_id, action, outcome, non-sensitive metadata) — never logs credentials/secrets (FR-013, depends on T006, T009, T015)
- [ ] T019 Create `App\Modules\Tenancy\Policies\TenantPolicy` in `app/Modules/Tenancy/Policies/TenantPolicy.php` and a `manage-tenants` gate requiring `is_central_admin` (FR-003, depends on T017)
- [ ] T020 EDIT `config/tenancy.php`: set `tenant_model => App\Modules\Tenancy\Models\Tenant::class` and `domain_model => App\Modules\Tenancy\Models\Domain::class` (D3, depends on T013, T014)
- [ ] T021 Create `App\Modules\Tenancy\Providers\TenancyModuleServiceProvider` in `app/Modules/Tenancy/Providers/TenancyModuleServiceProvider.php` (loads `app/Modules/Tenancy/routes.php` as central web routes, registers `TenantPolicy` + `manage-tenants` gate); create the empty `app/Modules/Tenancy/routes.php` (depends on T019, T013, T014)
- [ ] T022 EDIT `bootstrap/providers.php` to register `App\Modules\Tenancy\Providers\TenancyModuleServiceProvider::class` (depends on T021)

**Checkpoint**: Central schema migrates, custom Tenant/Domain/Audit models resolve, `manage-tenants` gate exists, module provider is registered. User stories can begin.

---

## Phase 3: User Story 1 - Central administrator provisions a new tenant (Priority: P1) 🎯 MVP

**Goal**: A central admin creates a tenant (name + unique slug + unique hostname); the system provisions an isolated database, migrates it, binds the hostname, and serves that hostname from the tenant's own data — atomically, with rollback on failure.

**Independent Test**: From the admin UI, create a tenant on `escola-um.geffin.local`; confirm a new DB is created + migrated, the central record is `active`, the hostname resolves to the tenant context, and a request returns that tenant's data and never another tenant's.

### Tests for User Story 1 (write FIRST, must FAIL) ⚠️

- [ ] T023 [P] [US1] `tests/Feature/Tenancy/TenantProvisioningTest.php`: store creates Tenant + Domain, creates and migrates the tenant DB, writes a `created` audit row; on a forced failure it rolls back the central rows, drops any partial tenant DB, frees the hostname, writes a `provision_failed` audit row, and surfaces the error (422); duplicate `slug`/`domain` incl. trashed → 422 (FR-004, FR-005, edge cases)
- [ ] T024 [P] [US1] `tests/Feature/Tenancy/TenantIsolationTest.php`: provision tenants A and B, write a marker `users` row inside A, assert it is never visible from B's context (SC-002)
- [ ] T025 [P] [US1] `tests/Feature/Tenancy/TenantAdminAuthorizationTest.php`: guest → redirect to `login`; authenticated non-admin → 403; `is_central_admin` user → allowed; a tenant-context request can never reach the central admin routes (FR-003, FR-014)

### Implementation for User Story 1

- [ ] T026 [P] [US1] Create `App\Modules\Tenancy\Http\Requests\StoreTenantRequest` in `app/Modules/Tenancy/Http/Requests/StoreTenantRequest.php` (`name` required string max 255; `slug` required lowercase regex `^[a-z0-9-]+$` `unique:tenants,slug` incl. trashed; `domain` required valid host `unique:domains,domain`) (data-model "Validation")
- [ ] T027 [US1] Create `App\Modules\Tenancy\Services\TenantProvisioningService` in `app/Modules/Tenancy/Services/TenantProvisioningService.php`: wrap create in a central DB transaction (Tenant + ≥1 Domain), run the synchronous `TenantCreated` pipeline (CreateDatabase + MigrateDatabase), and on any failure roll back rows, drop a partial tenant DB, free the hostname, and write a `provision_failed` audit; write a `created` audit on success (FR-004, D6; depends on T013, T018, T016)
- [ ] T028 [US1] Add `GET /admin/inquilinos/criar` (`tenants.create`) and `POST /admin/inquilinos` (`tenants.store`) to `app/Modules/Tenancy/routes.php`, both behind `auth` + `can:manage-tenants` (contracts/tenant-admin-routes.md; depends on T027, T019)
- [ ] T029 [US1] Create Livewire `App\Modules\Tenancy\Livewire\Tenants\Create` in `app/Modules/Tenancy/Livewire/Tenants/Create.php` + its blade view (form for name/slug/domain that validates via StoreTenantRequest rules and calls `TenantProvisioningService`) (D12; depends on T027, T026)

**Checkpoint**: A central admin can provision an isolated, migrated tenant; isolation and authorization tests pass.

---

## Phase 4: User Story 2 - Soft-delete a tenant and block access with a "contact manager" message (Priority: P1) 🎯 MVP

**Goal**: Soft-deleting a tenant preserves its database and hostname but blocks every request to that host with a localized 403 "contact your manager" response (HTML + JSON), distinct from a 404 for an unknown host — with zero tenant-DB queries for the blocked host.

**Independent Test**: Soft-delete an active tenant from the central context; from a clean session open the tenant's hostname and confirm a 403 "contact your manager" message (not the app, not a sign-in form), that the tenant DB still exists with data intact, and that an unknown host returns a distinct 404.

### Tests for User Story 2 (write FIRST, must FAIL) ⚠️

- [ ] T030 [P] [US2] `tests/Feature/Tenancy/SoftDeleteBlockTest.php`: after soft-delete, every route on the host returns 403 with the contact-manager copy (assert both HTML and JSON shapes), no tenant-DB query runs, the tenant DB and domain rows are preserved, a stale cookie/token is ignored (still 403); an unknown host returns 404 `tenant_not_found`; soft-deleting the central tenant or deleting from a tenant context is rejected (FR-007, FR-008, FR-009, SC-003, SC-007, edge cases)

### Implementation for User Story 2

- [ ] T031 [P] [US2] EDIT `app/Providers/TenancyServiceProvider.php`: remove `Jobs\DeleteDatabase` from the `Events\TenantDeleted` pipeline so neither soft- nor force-delete drops a tenant database (THE critical wiring change, D4)
- [ ] T032 [P] [US2] Create middleware `App\Modules\Tenancy\Http\Middleware\BlockDeletedTenant` in `app/Modules/Tenancy/Http/Middleware/BlockDeletedTenant.php`: look up the request host in `domains` joined to `tenants` `withTrashed()`; active → `next()`; trashed → short-circuit 403 (no tenancy init, no tenant-DB query); no row → 404; branch HTML vs JSON on `expectsJson()`, using `config/tenancy_block.php` copy (D5, contracts/tenant-resolution.md; depends on T014, T002, T003)
- [ ] T033 [P] [US2] Create blade views `resources/views/tenancy/blocked.blade.php` (branded 403 contact-manager) and `resources/views/tenancy/not-found.blade.php` (distinct 404) reading the configurable/localized copy (FR-016)
- [ ] T034 [US2] EDIT `routes/tenant.php` to prepend `BlockDeletedTenant` before `InitializeTenancyByDomain` (then `PreventAccessFromCentralDomains`) on the tenant route group (contracts/tenant-resolution.md order; depends on T032)
- [ ] T035 [P] [US2] Create `App\Modules\Tenancy\Services\TenantService` in `app/Modules/Tenancy/Services/TenantService.php` with `softDelete()`: set `deleted_at` + `status=inactive`, preserve DB and domain rows, write a `soft_deleted` audit; reject if the target is the central tenant or the call originates from a tenant context (FR-007, edge case; depends on T013, T018)
- [ ] T036 [P] [US2] Guard hard-delete in `app/Modules/Tenancy/Models/Tenant.php` by overriding `forceDelete()` to throw (no accidental hard-delete path), and assert the guard in `SoftDeleteBlockTest` (SC-007; depends on T013)
- [ ] T037 [US2] Add `DELETE /admin/inquilinos/{tenant}` (`tenants.destroy`) to `app/Modules/Tenancy/routes.php` behind `auth` + `can:manage-tenants`, calling `TenantService::softDelete` (contracts/tenant-admin-routes.md; depends on T035)

**Checkpoint**: MVP complete — provision (US1) + soft-delete-and-block (US2) both work and are independently testable.

---

## Phase 5: User Story 3 - Central administrator lists and updates tenants (Priority: P2)

**Goal**: A central admin lists/searches/filters tenants, opens a detail view, and edits non-identity attributes (display name) without disrupting the tenant's DB or hostname binding; identity-field collisions are rejected.

**Independent Test**: With three tenants (two active, one trashed), open the management screen and verify list, search by name, filter by status, open a detail view, edit the display name (persisted), and confirm routing is undisturbed.

### Tests for User Story 3 (write FIRST, must FAIL) ⚠️

- [ ] T038 [P] [US3] `tests/Feature/Tenancy/TenantManagementTest.php`: index shows active tenants by default, `?incluir_excluidos=1` includes trashed, `?q=` searches name/slug/domain; updating `name` persists with no migration and no host/DB change; updating `slug` or `domain` to a value used by any tenant incl. trashed → 422 with existing values unchanged (FR-011, US3 scenarios 1–3)

### Implementation for User Story 3

- [ ] T039 [P] [US3] Create `App\Modules\Tenancy\Http\Requests\UpdateTenantRequest` in `app/Modules/Tenancy/Http/Requests/UpdateTenantRequest.php` (`name` editable; `slug`/`domain` collisions incl. trashed rejected) (data-model "Validation")
- [ ] T040 [US3] Add `update()` to `App\Modules\Tenancy\Services\TenantService` (`app/Modules/Tenancy/Services/TenantService.php`): persist `name`, reject identity-field collisions, trigger no migration and no host/DB change, write an `updated` audit (US3 scenario 2; depends on T035, T013)
- [ ] T041 [US3] Add `GET /admin/inquilinos` (`tenants.index`), `GET /admin/inquilinos/{tenant}` (`tenants.show`), `GET /admin/inquilinos/{tenant}/editar` (`tenants.edit`), `PATCH /admin/inquilinos/{tenant}` (`tenants.update`) to `app/Modules/Tenancy/routes.php` behind `auth` + `can:manage-tenants` (contracts/tenant-admin-routes.md; depends on T040)
- [ ] T042 [US3] Create Livewire `App\Modules\Tenancy\Livewire\Tenants\Index` in `app/Modules/Tenancy/Livewire/Tenants/Index.php` + blade: paginated active list, `q` search, `incluir_excluidos` filter, and a Delete affordance wired to `tenants.destroy` (FR-011; depends on T040, T035)
- [ ] T043 [P] [US3] Create Livewire `App\Modules\Tenancy\Livewire\Tenants\Edit` in `app/Modules/Tenancy/Livewire/Tenants/Edit.php` + an edit/show blade view (edit display name via UpdateTenantRequest rules) (depends on T040, T039)

**Checkpoint**: Tenants are listable, searchable, filterable, and editable without breaking routing.

---

## Phase 6: User Story 4 - Restore a soft-deleted tenant (Priority: P3)

**Goal**: A central admin restores a trashed tenant — clears `deleted_at`, flips status to active, re-enables hostname routing, and reuses the preserved database with no re-migration and no data loss.

**Independent Test**: Take a trashed tenant from US2, click Restore, then open the tenant hostname and confirm the normal app is served and pre-deletion data is intact.

### Tests for User Story 4 (write FIRST, must FAIL) ⚠️

- [ ] T044 [P] [US4] `tests/Feature/Tenancy/RestoreTenantTest.php`: restoring clears `deleted_at`, sets `status=active`, the host serves the normal app again (no 403), all pre-delete data is present, no migration re-runs, and a `restored` audit row is written (FR-010, SC-004, US4 scenarios 1–2)

### Implementation for User Story 4

- [ ] T045 [US4] Add `restore()` to `App\Modules\Tenancy\Services\TenantService` (`app/Modules/Tenancy/Services/TenantService.php`): `withTrashed()->restore()`, set `status=active`, reuse existing DB and domain rows (no migrate), write a `restored` audit (FR-010, D9; depends on T035, T013)
- [ ] T046 [US4] Add `POST /admin/inquilinos/{tenant}/restaurar` (`tenants.restore`) to `app/Modules/Tenancy/routes.php` behind `auth` + `can:manage-tenants`, calling `TenantService::restore` (contracts/tenant-admin-routes.md; depends on T045)
- [ ] T047 [US4] Add a Restore affordance for trashed rows to Livewire `App\Modules\Tenancy\Livewire\Tenants\Index` (`app/Modules/Tenancy/Livewire/Tenants/Index.php`) wired to `tenants.restore` (depends on T042, T045)

**Checkpoint**: Full tenant lifecycle (provision → list/update → soft-delete/block → restore) works end-to-end.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Bulk-migration guarantee, static analysis, formatting, and end-to-end validation.

- [ ] T048 [P] `tests/Feature/Tenancy/BulkMigrateTest.php`: `php artisan tenants:migrate` applies a new tenant migration across all active tenants and skips trashed ones by default (FR-012, SC-006)
- [ ] T049 [P] Run `vendor/bin/pint --dirty --format agent` and fix any style findings across the new/edited files
- [ ] T050 [P] Run Larastan (`vendor/bin/phpstan analyse`) over `app/Modules/Tenancy/` and clear any new errors
- [ ] T051 Execute `specs/002-multi-tenant-database/quickstart.md` end-to-end (provision → isolate → soft-delete/block → restore) and confirm each step behaves as documented

---

## Dependencies & Execution Order

### Phase dependencies

- **Setup (Phase 1)**: no dependencies — start immediately.
- **Foundational (Phase 2)**: depends on Setup — **blocks all user stories**.
- **User Stories (Phases 3–6)**: all depend on Foundational. US1 and US2 are both P1 and form the MVP. US3 (P2) and US4 (P4) follow. US4's restore is verified against a tenant soft-deleted by US2's path; US3's Index UI hosts the Delete (US2) and Restore (US4) affordances.
- **Polish (Phase 7)**: depends on the desired stories being complete.

### Critical wiring dependencies

- T013 (Tenant model) depends on T007 (columns) and T012 (failing unit test).
- T020 (config pointers) depends on T013 + T014; T021 (module provider) depends on T019 + T013 + T014; T022 (bootstrap) depends on T021.
- T031 (drop `Jobs\DeleteDatabase`) is what makes soft-delete preserve the DB — must land before T030 can pass.
- T032 (BlockDeletedTenant) + T034 (route order) produce the 403/404 split; both must precede a green T030.

### Shared-file (sequential) tasks — do NOT parallelize these with each other

- `app/Modules/Tenancy/routes.php`: T021 (create) → T028 (US1) → T037 (US2) → T041 (US3) → T046 (US4).
- `app/Modules/Tenancy/Services/TenantService.php`: T035 (US2) → T040 (US3) → T045 (US4).
- `app/Modules/Tenancy/Models/Tenant.php`: T013 (create) → T036 (US2 forceDelete guard).
- `app/Modules/Tenancy/Livewire/Tenants/Index.php`: T042 (US3) → T047 (US4).

### Within each user story

- Tests are written first and MUST fail before implementation (TDD).
- Models → services → routes → Livewire UI.

---

## Parallel Opportunities

- **Setup**: T001, T002, T003, T004 all run in parallel.
- **Foundational**: T005, T006, T007, T008, T009, T010, T011, T012 in parallel (enums, migrations, unit test). After T013: T014, T015, T017 in parallel.
- **US1 tests**: T023, T024, T025 in parallel. T026 in parallel with them.
- **US2**: T031, T032, T033, T035, T036 are different files and can run in parallel after T030 fails; T034 and T037 follow their dependencies.
- **US3**: T039 parallel with the T038 test; T043 parallel with T042 after T040.
- **Polish**: T048, T049, T050 in parallel; T051 last.

### Parallel example — User Story 1

```bash
# Write all three failing tests together (TDD red):
Task: "TenantProvisioningTest in tests/Feature/Tenancy/TenantProvisioningTest.php"
Task: "TenantIsolationTest in tests/Feature/Tenancy/TenantIsolationTest.php"
Task: "TenantAdminAuthorizationTest in tests/Feature/Tenancy/TenantAdminAuthorizationTest.php"

# StoreTenantRequest can be built alongside the tests:
Task: "StoreTenantRequest in app/Modules/Tenancy/Http/Requests/StoreTenantRequest.php"
```

---

## Implementation Strategy

### MVP first (User Stories 1 + 2 — both P1)

1. Phase 1: Setup.
2. Phase 2: Foundational (CRITICAL — blocks all stories).
3. Phase 3: US1 — provision. **Validate** isolation + authorization.
4. Phase 4: US2 — soft-delete + block. **Validate** the 403/404 split and DB preservation.
5. The platform can ship and operate manually with the MVP while US3/US4 follow.

### Incremental delivery

MVP (US1 + US2) → add US3 (list/update) → add US4 (restore) → Polish. Each story is independently testable and adds value without breaking the previous ones.

---

## Notes

- TDD is mandatory here (plan Constitution Check) — confirm every test fails before writing implementation.
- `[P]` = different files, no incomplete dependency.
- Run `vendor/bin/pint --dirty --format agent` after edits (T049) per repo convention.
- URL segments are Portuguese (`/admin/inquilinos`, `criar`, `editar`, `restaurar`); route names are English (`tenants.*`) per project convention.
- No secrets/credentials in audit metadata (FR-013, SC-005).
