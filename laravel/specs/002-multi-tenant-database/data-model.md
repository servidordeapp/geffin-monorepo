# Phase 1 Data Model: Multi-Tenant Multi-Database Foundation

**Feature**: `002-multi-tenant-database` | **Date**: 2026-05-24

Two physical contexts:

- **Central database** (`mysql`, db `geffin`): tenants, domains, central
  administrators, tenant audit log, framework infra (cache/jobs).
- **Tenant database** (one per tenant, `tenant<uuid>`): tenant-scoped business
  schema (baseline: tenant `users`).

---

## Central context

### Tenant  (`tenants`)

Custom model `App\Modules\Tenancy\Models\Tenant` extends
`Stancl\Tenancy\Database\Models\Tenant`, adds `SoftDeletes`.

| Column | Type | Notes |
|--------|------|-------|
| `id` | string (UUID) PK | from `UUIDGenerator`; drives tenant DB name `tenant<id>` |
| `slug` | string | **unique across all rows incl. trashed**; immutable identity |
| `name` | string | display name; editable (US3) |
| `status` | string enum | `active` \| `inactive` (mirrors trashed state for queries) |
| `data` | json nullable | stancl VirtualColumn overflow for non-custom attributes |
| `created_at` / `updated_at` | timestamp | |
| `deleted_at` | timestamp nullable | soft-delete marker; **custom column** (not in `data`) |

`getCustomColumns()` returns `['id','slug','name','status','deleted_at']`.

**Constraints / rules**
- `slug` UNIQUE (DB index) — enforced even against soft-deleted rows (FR-005).
- `slug` format: lowercase `[a-z0-9-]`, validated in Form Request.
- Soft delete only via the standard flow; `forceDelete` guarded (SC-007).
- Relationships: `hasMany(Domain)`, `hasMany(TenantAuditEvent)`.

**State transitions**
```
(none) --create--> active
active --soft delete--> inactive(trashed: deleted_at set, DB preserved)
inactive --restore--> active
```
No `active --hard delete-->` path in scope.

### Domain  (`domains`)

`Stancl\Tenancy\Database\Models\Domain` (package default; custom subclass only if
needed for `withTrashed` joins).

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `domain` | string | **UNIQUE across all tenants incl. soft-deleted** (FR-005) |
| `tenant_id` | string FK → tenants.id | cascade per package default |
| `created_at`/`updated_at` | timestamp | |

**Rules**
- A tenant MUST have ≥1 domain (enforced in provisioning service).
- Domain rows are **kept** when the tenant is soft-deleted → the unique index
  naturally blocks reuse of a hostname bound to a trashed tenant (FR-007, edge case).

### User  (`users`) — central administrators

Existing `App\Models\User`; add one column.

| Column | Type | Notes |
|--------|------|-------|
| (existing) | | name, email (unique), password, … |
| `is_central_admin` | boolean default `false` | gate for `manage-tenants` (FR-003) |

**Rules**
- Only `is_central_admin = true` users pass the `manage-tenants` gate.
- Lives only in the central DB; never confused with tenant users (FR-014).

### TenantAuditEvent  (`tenant_audit_events`) — append-only

New model `App\Modules\Tenancy\Models\TenantAuditEvent`.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint PK | |
| `tenant_id` | string nullable | FK-ish ref (nullable for `provision_failed` before row exists) |
| `actor_id` | bigint nullable | central user id; null for system actions |
| `action` | string enum | `created`\|`updated`\|`soft_deleted`\|`restored`\|`migrated`\|`provision_failed` |
| `outcome` | string enum | `success`\|`failure` |
| `metadata` | json nullable | non-sensitive context (e.g. error class). **No secrets/creds** |
| `created_at` | timestamp | |

**Rules**
- Append-only (no update/delete in app code).
- Retained ≥12 months (no pruning in v1) (SC-005).

---

## Tenant context (per tenant DB)

### User  (`users`) — tenant/school users (baseline schema only)

Created by `database/migrations/tenant/..._create_users_table.php`. Standard
Laravel users columns (id, name, email unique, password, timestamps). Auth flows
for tenant users are **out of scope** for this feature; this establishes the schema
and gives the isolation test (SC-002) a real table to write/read a marker row.

---

## Enums

- `App\Modules\Tenancy\Enums\TenantStatus`: `Active`, `Inactive` (TitleCase keys).
- `App\Modules\Tenancy\Enums\TenantAuditAction`: `Created`, `Updated`,
  `SoftDeleted`, `Restored`, `Migrated`, `ProvisionFailed`.

## Validation (Form Requests)

| Field | Rule |
|-------|------|
| `name` | required, string, max 255 |
| `slug` | required, lowercase, regex `^[a-z0-9-]+$`, **unique:tenants,slug** incl. trashed |
| `domain` | required, valid host, **unique:domains,domain** (no soft-delete exclusion) |

Update request: `slug` and `domain` changes that collide with any existing row
(including trashed) are rejected (US3 scenario 3); `name` is freely editable.
