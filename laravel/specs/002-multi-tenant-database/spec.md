# Feature Specification: Multi-Tenant Multi-Database Foundation

**Feature Branch**: `002-multi-tenant-database`

**Created**: 2026-05-24

**Status**: Completed

**Input**: User description: "Add multi tenant multidatabase to project, using stancl/tenancy package. It is already installed on this projetc, also the command 'php artisan tenancy:install' was exacuted and App\\Providers\\TenancyServiceProvider::class was added to bootstrap/providers.php. The central tenant will be responsable to make CRUD operations for tenants. The delete operation should uses softdeletes, so when a user try to access a deleted tenant, a message should appears instructing the user to contact your manager to solve this."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Central administrator provisions a new tenant (Priority: P1)

A platform administrator working in the central (non-tenant) context registers a new school in the system by providing the school's display name, a unique slug, and the public hostname it will be reached on. The system creates a brand new isolated database for that school, runs the tenant-scoped migrations into it, links the hostname to the tenant, and from that moment on any request arriving at that hostname is served with the school's own data, fully separated from every other tenant.

**Why this priority**: Without this story no tenant exists; every other capability (sign-in, billing, students, canteen, anything tenant-scoped) is blocked. It is the foundational slice that turns the application from single-database into multi-tenant.

**Independent Test**: From the central admin UI/API, create a tenant with hostname `escola-um.geffin.local`; confirm a new database is created and migrated, the tenant record is persisted in the central database, the hostname resolves to the tenant context, and a request to that hostname returns data from the new tenant's database (and not from any other tenant's).

**Acceptance Scenarios**:

1. **Given** an authenticated central administrator on the tenants management screen, **When** they submit a valid tenant creation form (unique slug, unique hostname, display name), **Then** the system creates the tenant record in the central database, provisions a dedicated tenant database, runs the tenant migrations into it, associates the hostname with the tenant, and shows the new tenant in the list with status "active".
2. **Given** the tenants management screen is open, **When** an administrator submits a slug or hostname that is already in use by any tenant (including soft-deleted ones), **Then** the form is rejected with a clear field-level error and no database or tenant record is created.
3. **Given** an active tenant `escola-um` has been provisioned with hostname `escola-um.geffin.local`, **When** any caller makes a request to that hostname, **Then** the application resolves the tenant context automatically, all tenant-scoped data reads and writes happen against the tenant's own database, and central-database data remains unreachable from that request.
4. **Given** an unauthenticated user or a non-central-admin user, **When** they attempt to reach the tenant management endpoints, **Then** the system denies the action with the standard authorization error and no tenant is created or modified.

---

### User Story 2 - Soft-delete a tenant and block access with a "contact manager" message (Priority: P1)

A platform administrator decides that a school must stop using the system (non-payment, contract ended, school request, etc.). From the tenants management screen the administrator soft-deletes the tenant. The tenant's database is preserved and the tenant record is kept (flagged as deleted). From that moment on, any user — administrator of the school, teacher, guardian, student — who tries to reach the tenant via its hostname is shown a clear, branded message telling them the tenant is unavailable and instructing them to contact their manager to resolve the situation. No tenant data is exposed and no sign-in form is rendered for that tenant.

**Why this priority**: This is the explicit constraint stated by the requester. Without it, deleting a tenant either destroys data (unacceptable for a financial system that must remain auditable) or silently 404s end-users (no actionable guidance). This story enforces the chosen "preserve data, block access, guide the user" policy.

**Independent Test**: From the central admin context, soft-delete an active tenant. Then, from a clean browser session, open the tenant's hostname; confirm the page does NOT load the normal tenant home page or sign-in form, that a clear "tenant unavailable — contact your manager" message is shown instead, and that the underlying tenant database still exists with all its data intact.

**Acceptance Scenarios**:

1. **Given** an active tenant in the tenants list, **When** the central administrator clicks "Delete" and confirms the action, **Then** the tenant is marked as soft-deleted (deletion timestamp recorded), it is removed from the default "active tenants" list, the hostname remains reserved (cannot be reused while soft-deleted), and the underlying tenant database is preserved.
2. **Given** a tenant that has been soft-deleted, **When** any visitor opens any URL under that tenant's hostname (root, sign-in page, any protected route, any API endpoint), **Then** the response shows a clear, localized "this tenant is currently unavailable, please contact your manager" message; no tenant-scoped data is queried; no authentication form is shown.
3. **Given** a tenant that has been soft-deleted, **When** an authenticated request (e.g., with a previously valid session cookie or bearer token) is sent to any endpoint on that hostname, **Then** the session is not honored on that tenant, no tenant data is returned, and the same "contact your manager" response is served.
4. **Given** the central administrator views the tenants management screen with the "show deleted" filter on, **When** the page renders, **Then** the soft-deleted tenant is visible with its deletion date and a "Restore" affordance, while non-admin contexts never see this listing.

---

### User Story 3 - Central administrator lists and updates tenants (Priority: P2)

A platform administrator opens the tenants management screen and sees every tenant in the system, can search and filter them, can open a tenant to see its details (slug, hostname(s), creation date, status, soft-deletion date if applicable), and can edit non-identity attributes such as display name without breaking the tenant's database or hostname binding.

**Why this priority**: Necessary for ongoing operations but not required to validate the multi-tenant foundation. The platform can ship Story 1 + Story 2 and operate manually for a short window while Story 3 follows. Sequenced after the foundational slices.

**Independent Test**: With at least three tenants provisioned (two active, one soft-deleted), open the management screen and verify list, search by name, filter by status, open a detail view, edit the display name, and confirm the edit is persisted without disrupting tenant routing.

**Acceptance Scenarios**:

1. **Given** several tenants exist (active and soft-deleted), **When** the administrator opens the management screen, **Then** they see a paginated list of active tenants by default, can toggle a filter to include soft-deleted ones, and can search by display name, slug, or hostname.
2. **Given** an active tenant detail view, **When** the administrator updates the display name and saves, **Then** the change is persisted on the central tenant record, no tenant migration is triggered, and the hostname and database binding remain unchanged.
3. **Given** an active tenant detail view, **When** the administrator attempts to change the tenant's slug or its hostname to one already used by another tenant (including soft-deleted), **Then** the change is rejected with a clear field-level error and the existing values remain.

---

### User Story 4 - Restore a soft-deleted tenant (Priority: P3)

A central administrator restores a soft-deleted tenant when the underlying issue (e.g., overdue payment) is resolved. The tenant's database, data, and hostname are reactivated; the "contact your manager" block is removed; users can sign in again and use the tenant normally.

**Why this priority**: Closes the lifecycle but is recoverable manually in the short term (an administrator can run a script) if the UI is delayed. Lower urgency than create and block-on-delete.

**Independent Test**: Take a soft-deleted tenant from Story 2, click "Restore" in the central admin screen, then open the tenant's hostname and confirm the normal application is served and existing tenant data is intact.

**Acceptance Scenarios**:

1. **Given** a soft-deleted tenant, **When** the central administrator clicks "Restore" and confirms, **Then** the deletion timestamp is cleared, the tenant returns to "active" status, the hostname resolves to the tenant context again, and the tenant's preserved database is reused as-is.
2. **Given** a restored tenant, **When** a user opens the tenant hostname, **Then** the normal application is served (no "contact your manager" message), and all data that existed before the soft-delete is available unchanged.

---

### Edge Cases

- A user opens a hostname that does not correspond to any tenant (typo, never-provisioned subdomain): the system MUST respond with a clear "tenant not found" message distinguishable from the "tenant unavailable — contact your manager" message used for soft-deleted tenants, so support can diagnose the difference.
- A user holds a long-lived token or cookie issued by an active tenant that is later soft-deleted: every subsequent request on that hostname MUST be blocked with the "contact your manager" message regardless of credentials presented.
- A tenant database creation fails mid-provisioning (disk full, permission, migration error): the system MUST NOT leave a half-created tenant in an "active" state; the central tenant record either rolls back (freeing the slug + hostname for re-use) or is flagged as `failed` and surfaced to the administrator with the underlying error captured in the audit log. While the asynchronous provisioning job is still in flight the tenant remains in `pending` status and any request to its hostname MUST be short-circuited with a 503 "provisioning in progress" response — never the normal app and never a partial 5xx leaking the broken state.
- Two central administrators try to create a tenant with the same slug or hostname at the same time: only one creation succeeds; the other is rejected with the uniqueness error rather than producing duplicate or orphan databases.
- An administrator attempts to soft-delete the central tenant itself, or to soft-delete a tenant from within a tenant context rather than the central context: the system MUST reject the action.
- A request arrives with a hostname whose tenant is soft-deleted and the requested route would otherwise be a public marketing page: the "contact your manager" message still takes precedence; no tenant content is served from a soft-deleted tenant.
- Tenant migrations evolve over time: when migrations are added later, every active tenant database MUST be migratable from the central context without affecting routing or causing partial-state outages.
- A hostname previously bound to a soft-deleted tenant MUST NOT be assignable to a new tenant while the original tenant remains soft-deleted, to prevent confusion and data leak across tenants.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST operate in two contexts — a **central context** that owns global metadata (tenants, hostnames, central administrators) and a **tenant context** that owns one isolated database per tenant — and MUST automatically switch between them based on the incoming request's hostname.
- **FR-002**: Each tenant MUST have its own dedicated database, fully isolated from every other tenant and from the central database; no tenant request MUST be able to read or write data belonging to another tenant or to the central context.
- **FR-003**: The central context MUST expose CRUD operations on tenants (create, list/read, update, soft-delete, restore) and MUST scope these operations to users with the "central administrator" role; non-central users MUST receive an authorization error.
- **FR-004**: Creating a tenant MUST persist the central tenant record and at least one unique hostname binding synchronously (so identifier-uniqueness errors surface immediately to the administrator), and MUST then perform the database provisioning + tenant migrations asynchronously via the queue. While the asynchronous step is in flight, the tenant MUST be visible in the central admin UI with a clearly non-active status (e.g., "provisioning"); when the asynchronous step completes the tenant MUST be flipped to "active"; if any asynchronous step fails, the tenant MUST NOT remain in an "active" state, MUST be flagged with a failure status (or rolled back), and the failure MUST be surfaced to the administrator (UI badge + audit log).
- **FR-005**: Each tenant MUST have a unique slug and at least one unique public hostname; uniqueness MUST be enforced across all tenants including soft-deleted ones, so identifiers cannot be silently reassigned.
- **FR-006**: The system MUST identify the active tenant for an incoming request by matching the request's hostname to a registered tenant hostname; if no hostname match exists, the request MUST be served from the central context (for central admin URLs) or rejected with a "tenant not found" message (for any other hostname).
- **FR-007**: Tenant deletion MUST be a soft-delete: the central tenant record MUST be marked deleted with a timestamp, the tenant database and its data MUST be preserved untouched, and the hostname binding MUST be preserved but flagged as unavailable.
- **FR-008**: When a request targets a hostname bound to a soft-deleted tenant, the system MUST short-circuit the request before any tenant-database query, MUST NOT render the normal application, MUST NOT render a sign-in form, and MUST respond with a clear, localized message instructing the user to contact their manager to resolve the situation. The same message MUST be returned for both web (HTML) and API (JSON) callers, differing only in content type.
- **FR-009**: The "tenant unavailable — contact your manager" response MUST be visually distinct from a "tenant not found" response and MUST use a stable HTTP status code that downstream BFFs and mobile clients can react to (default 403 Forbidden for soft-deleted tenants; 404 Not Found for missing tenants; 503 Service Unavailable for tenants whose database has not yet finished provisioning or whose provisioning failed) so client apps can show the right UX.
- **FR-010**: A soft-deleted tenant MUST be restorable from the central admin context; restoring MUST clear the deletion timestamp, re-enable hostname routing, and reuse the existing tenant database without re-running migrations or recreating data.
- **FR-011**: The central admin tenants list MUST default to showing only active tenants and MUST offer an explicit "include deleted" filter so soft-deleted tenants can be reviewed and restored.
- **FR-012**: Tenant migrations (schema changes applied inside each tenant database) MUST be runnable in bulk against all active tenants from the central context, and MUST be skippable for soft-deleted tenants by default.
- **FR-013**: Every tenant CRUD action (create, update, soft-delete, restore) MUST produce an audit log entry in the central context capturing the actor, timestamp, tenant id, action, and outcome; sensitive material (e.g., database credentials, secrets) MUST NOT appear in logs.
- **FR-014**: The system MUST prevent operations that would corrupt isolation: tenant context requests MUST NOT be able to access tenant-management endpoints; central context requests MUST NOT be able to read tenant-specific business data without explicitly initializing a tenant context.
- **FR-015**: Existing tenant-aware modules (Financial, Billing, Contracts, Students, Canteen, Commerce) MUST continue to function unchanged once a tenant context is initialized; their migrations MUST be classified as tenant migrations and live in the tenant database, not the central one.
- **FR-016**: The "contact your manager" message MUST be localized to the platform's primary language (Portuguese, Brazil) and MUST be configurable as platform copy so the wording can be changed without a code deploy.

### Key Entities *(include if feature involves data)*

- **Tenant**: An isolated customer (a school). Attributes: unique identifier, unique slug, display name, status (pending / active / inactive / failed / soft-deleted), creation timestamp, soft-deletion timestamp (nullable), reference to its dedicated database. Lives in the central database. The `pending` status applies while the asynchronous database provisioning job is queued or running; `failed` applies when that job exhausted its options without producing a usable database. Soft-delete only; never hard-deleted via the standard flow.
- **Tenant Domain**: A public hostname bound to a tenant. Attributes: hostname (unique across all tenants including soft-deleted), tenant reference. A tenant MUST have at least one. Lives in the central database.
- **Tenant Database**: A dedicated database holding all tenant-scoped data (users of that school, students, payments, contracts, etc.). Identified by a name derived from the tenant identifier. Created at tenant provisioning, preserved through soft-delete, reused on restore.
- **Central Administrator**: A user with the authority to manage tenants. Lives in the central database (not in any tenant database) and MUST NOT be confused with tenant-scoped users.
- **Tenant Audit Event**: Append-only record of central-level tenant lifecycle actions (created, updated, soft-deleted, restored, migration applied, provisioning failed) with timestamp, actor, tenant reference, action, and outcome.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A central administrator can create a brand new tenant — including database creation, tenant migrations, and hostname binding — in under 60 seconds end-to-end through the admin interface, with no manual database steps. The synchronous portion (central row + hostname + queue dispatch) MUST complete in under 2 seconds; the asynchronous database provisioning runs on the queue and MUST flip the tenant from `pending` to `active` within the remaining 58-second budget under normal load.
- **SC-002**: Once a tenant is provisioned, any request to that tenant's hostname is served from the correct tenant's data with zero cross-tenant data leakage; an automated isolation test that writes a marker row in tenant A and reads tenant B MUST never see tenant A's marker, across 100% of test runs.
- **SC-003**: When a tenant is soft-deleted, 100% of requests to that tenant's hostname (root URL, any nested URL, any API endpoint, with or without credentials) return the "contact your manager" response within 500 ms at the 95th percentile, with no tenant database query executed.
- **SC-004**: Restoring a soft-deleted tenant brings it back to a fully working state — its hostname serves the normal application and all pre-deletion data is visible — within 5 seconds of confirming the restore action.
- **SC-005**: 100% of tenant CRUD actions are represented in the central audit log and retrievable for at least 12 months.
- **SC-006**: A platform with at least 50 active tenants can run a new tenant-scoped migration across every active tenant from a single central command in under 5 minutes total, without manual per-tenant steps.
- **SC-007**: No central administrator can accidentally hard-delete a tenant through the standard UI/API: every "delete" path leads to a soft-delete; recovery (restore) is always possible without data restoration from backups.

## Assumptions

- The application uses the `stancl/tenancy` package for multi-tenancy. The package has already been installed, `php artisan tenancy:install` has been executed, and `App\Providers\TenancyServiceProvider::class` is registered in `bootstrap/providers.php`. This feature configures and wires it; it does not re-install or replace it.
- Tenants are identified by **hostname** (subdomain or fully-qualified domain) rather than by URL path or HTTP header. Subdomains like `<slug>.geffin.com` are the default; custom domains may be added later through the same domain table.
- Only a "central administrator" role can manage tenants. This role lives in the central database. Tenant-scoped users (school staff, guardians) MUST NOT have any tenant-management capability.
- "Contact your manager" refers to the tenant's own administrator/responsible person at the school level — i.e., the school's contract manager — not the platform vendor's support team. The wording is configurable as platform copy in case operations later prefer "contact the platform team" framing.
- Existing domain modules (Financial, Billing, Contracts, Students, Canteen, Commerce) and any new ones SHOULD treat their data as tenant-scoped by default; their migrations are tenant migrations. The Shared module remains cross-cutting but contains no tenant-bound business data.
- BFF services (bff-school, bff-guardian) and the api-gateway continue to forward the original hostname to the API Core so the tenant resolution stays correct end-to-end; this feature documents that requirement but does not modify the BFF code.
- Tenant database provisioning uses the same database server (MySQL 8.4, as defined in `infra/docker/docker-compose.yml`) as the central database with one database per tenant; per-tenant database servers are out of scope for v1. (Corrected from an earlier "PostgreSQL" assumption during planning — no PostgreSQL service exists in infra; see `plan.md` decision D1.)
- Hard-deletion (permanent removal of a tenant and its database) is explicitly out of scope for this feature and will be designed separately if/when a legal-retention story requires it.
- Backups, encryption-at-rest, and disaster recovery for tenant databases are governed by the platform's existing data-protection policies and are not redefined here.
