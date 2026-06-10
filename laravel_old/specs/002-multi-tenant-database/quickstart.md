# Quickstart: Multi-Tenant Multi-Database Foundation

**Feature**: `002-multi-tenant-database`

## Prerequisites

- Docker stack up (`make up`) — MySQL 8.4 service `geffin-mysql`.
- The MySQL connection user can `CREATE`/`DROP DATABASE` (needed to provision
  tenant databases). For local docker the `geffin` user has rights on the local
  server; in managed envs grant `CREATE`/`DROP` on the tenant DB name pattern.
- `central_domains` in `config/tenancy.php` include the host you use for the admin
  UI (default `127.0.0.1`, `localhost`). Tenant hosts (e.g. `escola-um.geffin.local`)
  must resolve to the app — add to `/etc/hosts` for local testing.

## One-time wiring (this feature)

1. Point the package at the custom model — `config/tenancy.php`:
   `'tenant_model' => App\Modules\Tenancy\Models\Tenant::class`.
2. In `App\Providers\TenancyServiceProvider`, the `Events\TenantDeleted` pipeline
   no longer contains `Jobs\DeleteDatabase` (tenant DB is preserved on delete).
3. Register `App\Modules\Tenancy\Providers\TenancyModuleServiceProvider` in
   `bootstrap/providers.php`.
4. Run central migrations: `make migrate` (adds tenant columns, `is_central_admin`,
   `tenant_audit_events`).
5. Seed a central admin (`is_central_admin = true`) to access the admin UI.

## Create a tenant

Via UI: sign in as central admin → `/admin/inquilinos/criar` → submit name, slug,
hostname. Or via tinker:
```php
$t = App\Modules\Tenancy\Models\Tenant::create(['name'=>'Escola Um','slug'=>'escola-um']);
$t->domains()->create(['domain'=>'escola-um.geffin.local']);
```
The `TenantCreated` pipeline creates DB `tenant<uuid>` and runs
`database/migrations/tenant/*` into it.

## Bulk-migrate tenants (later schema changes)

```
php artisan tenants:migrate          # all ACTIVE tenants (trashed skipped)
```

## Soft-delete & verify the block

```
# UI: /admin/inquilinos → Delete → confirm
curl -i http://escola-um.geffin.local/        # → 403 "contact your manager"
curl -i -H 'Accept: application/json' http://escola-um.geffin.local/api/anything
# → 403 {"error":"tenant_unavailable", ...}; tenant DB still exists, no query run
curl -i http://inexistente.geffin.local/       # → 404 "tenant not found" (distinct)
```

## Restore

```
# UI: /admin/inquilinos?incluir_excluidos=1 → Restore
curl -i http://escola-um.geffin.local/         # → 200, normal app, data intact
```

## Tests

```
php artisan test --compact
```
Key suites to expect (TDD, written first):
- Provisioning creates an isolated DB + migrates it; rollback on failure.
- **Isolation**: marker row written in tenant A is never visible from tenant B
  (SC-002).
- Soft-delete preserves the DB and returns 403 contact-manager on the host with no
  tenant-DB query; 404 for unknown host.
- Restore returns the tenant to a working state with data intact.
- `is_central_admin` gate blocks non-admins; tenant context cannot hit admin routes.
- Every CRUD action writes a `tenant_audit_events` row.

> Test DB: sqlite, one file per tenant (`SQLiteDatabaseManager`). No MySQL needed
> for the suite.
