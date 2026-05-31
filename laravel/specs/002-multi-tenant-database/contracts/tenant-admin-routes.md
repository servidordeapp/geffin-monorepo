# Contract: Central Tenant-Management Routes

**Feature**: `002-multi-tenant-database`

Central context only (central domains: `127.0.0.1`, `localhost`, + admin host).
All routes require: authenticated session + `is_central_admin` (gate
`manage-tenants`). Non-admins → 403; guests → redirect to `login`.
URL segments Portuguese; route names English (project convention).

These routes are NEVER registered in the tenant route group (FR-014).

| Method | URL (segment) | Route name | Purpose | Story |
|--------|---------------|------------|---------|-------|
| GET | `/admin/inquilinos` | `tenants.index` | List active tenants; `?incluir_excluidos=1` includes trashed; search `?q=` by name/slug/domain | US3, FR-011 |
| GET | `/admin/inquilinos/criar` | `tenants.create` | Create form | US1 |
| POST | `/admin/inquilinos` | `tenants.store` | Provision tenant (atomic) | US1, FR-004 |
| GET | `/admin/inquilinos/{tenant}` | `tenants.show` | Detail (slug, domains, dates, status) | US3 |
| GET | `/admin/inquilinos/{tenant}/editar` | `tenants.edit` | Edit form | US3 |
| PATCH | `/admin/inquilinos/{tenant}` | `tenants.update` | Update display name (identity fields rejected on collision) | US3 |
| DELETE | `/admin/inquilinos/{tenant}` | `tenants.destroy` | **Soft-delete** (DB preserved) | US2, FR-007 |
| POST | `/admin/inquilinos/{tenant}/restaurar` | `tenants.restore` | Restore trashed tenant | US4, FR-010 |

## store (POST /admin/inquilinos)

Request:
```json
{ "name": "Escola Um", "slug": "escola-um", "domain": "escola-um.geffin.local" }
```
Validation: see data-model. On success (201 / redirect to `tenants.show`):
```json
{ "id": "<uuid>", "name": "Escola Um", "slug": "escola-um",
  "status": "active", "domains": ["escola-um.geffin.local"], "created_at": "..." }
```
Behaviour:
- Central transaction creates Tenant + Domain; synchronous `TenantCreated`
  pipeline creates + migrates the tenant DB.
- Any step fails → rollback central rows, drop partial DB, free hostname, write
  `provision_failed` audit event, return 422 with the underlying error message
  (no secrets). Tenant never appears "active". (FR-004, edge cases)
- Duplicate `slug`/`domain` (incl. trashed) → 422 field error; concurrent
  duplicate → unique-constraint violation surfaced as the same 422 (FR-005).

## update (PATCH /admin/inquilinos/{tenant})

- `name` editable; persists with no migration, no DB/hostname change (US3 sc2).
- Attempt to change `slug` or `domain` to one used by any tenant (incl. trashed)
  → 422 field error, existing values unchanged (US3 sc3).

## destroy (DELETE /admin/inquilinos/{tenant})

- Soft-delete: sets `deleted_at`, `status=inactive`; tenant DB **preserved**;
  domain rows **preserved** (hostname stays reserved). Writes `soft_deleted` audit.
- Rejected if target is the central context or invoked from a tenant context
  (edge case) → 403/422.
- Returns 200/redirect; tenant disappears from default list, visible under
  `?incluir_excluidos=1` with a Restore affordance.

## restore (POST /admin/inquilinos/{tenant}/restaurar)

- Clears `deleted_at`, `status=active`; reuses existing DB (no migrate) and domain
  rows; writes `restored` audit. Returns 200/redirect.

## Errors (shared)

| Status | When |
|--------|------|
| 302 → login | unauthenticated |
| 403 | authenticated, not `is_central_admin` |
| 404 | tenant id not found in central |
| 422 | validation / provisioning failure (message body, no secrets) |

## Audit (FR-013)

Every store/update/destroy/restore writes one `tenant_audit_events` row
(actor_id, tenant_id, action, outcome, timestamp).
