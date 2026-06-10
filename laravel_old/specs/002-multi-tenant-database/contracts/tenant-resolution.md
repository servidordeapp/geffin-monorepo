# Contract: Tenant Resolution & Access Block

**Feature**: `002-multi-tenant-database`

Applies to every request whose host is NOT a central domain. Middleware order on
the tenant route group:

```
1. BlockDeletedTenant            (custom, this feature)  ← runs first
2. InitializeTenancyByDomain     (stancl)
3. PreventAccessFromCentralDomains (stancl)
```

`BlockDeletedTenant` looks up the request host in `domains` joined to `tenants`
**including trashed** (`withTrashed()`), then:

| Host condition | Action | Status | Tenant DB touched? |
|----------------|--------|--------|--------------------|
| domain row exists, tenant **active** | pass to `InitializeTenancyByDomain` | 200 (app) | yes (normal) |
| domain row exists, tenant **trashed** | short-circuit "contact your manager" | **403** | **no** |
| **no** domain row for host | short-circuit "tenant not found" | **404** | no |

Guarantees:
- For a trashed tenant, **no tenancy initialization and no tenant-DB query** occur
  (SC-003): the gate returns before middleware #2.
- 403 vs 404 are visually and semantically distinct (FR-008, FR-009) so BFFs /
  mobile clients branch on status code.
- Credentials are ignored for trashed tenants: a valid prior cookie/token on that
  host still yields 403 (FR-008 sc3, edge case).
- Precedence: the 403 applies to ALL routes on a trashed host (root, login, any
  API/protected route, would-be public pages) (FR-008 sc2, edge case).

## 403 — tenant unavailable (soft-deleted)

HTML (`!expectsJson`): branded view, localized pt-BR copy from
`config/tenancy_block.php` (overridable, FR-016), e.g.
> "Este ambiente está temporariamente indisponível. Entre em contato com o seu
> responsável para regularizar a situação."

JSON (`expectsJson`):
```json
{ "error": "tenant_unavailable",
  "message": "<localized contact-your-manager copy>" }
```
HTTP **403**.

## 404 — tenant not found (never provisioned / typo)

HTML: distinct "ambiente não encontrado" page.
JSON:
```json
{ "error": "tenant_not_found",
  "message": "<localized not-found copy>" }
```
HTTP **404**.

## Configurable copy

`config/tenancy_block.php` keys (each backed by a pt-BR lang line, editable
without deploy):
```
'unavailable_message' => env-overridable string,
'not_found_message'   => env-overridable string,
'status_unavailable'  => 403,
'status_not_found'    => 404,
```
