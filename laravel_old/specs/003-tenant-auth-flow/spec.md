# Feature Specification: Tenant Authentication Flow

**Feature Branch**: `003-tenant-auth-flow`

**Created**: 2026-05-31

**Status**: Draft

**Input**: User description: "Implementar fluxo de autenticação no tenant (multi-tenant) — Login, Forgot Password e Reset Password dentro do escopo do tenant, reutilizando integralmente a UI, componentes, validações e comportamento do fluxo já existente no tenant central. Sem registro/signup. Sem alterar o comportamento do tenant central."

## Overview

The platform is multi-tenant: each school (tenant) is reached on its own hostname and is fully data-isolated from every other tenant and from the central context. A complete authentication flow — sign in, forgot password, reset password — already exists and works in the **central** context. This feature delivers the **same flow, scoped to a tenant**, so that a user belonging to a specific school can sign in on that school's hostname, recover access to their account, and sign out — with an experience that is visually and behaviorally identical to the central flow and without changing the central flow in any way.

Account creation (registration/sign-up) is explicitly **out of scope**. Tenant users are assumed to already exist within their tenant (provisioned through other means).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - A tenant user signs in and out on their tenant hostname (Priority: P1)

A user who belongs to a specific school opens that school's hostname (e.g. `escola-um.geffin.local`) and is presented with a sign-in screen that looks and behaves exactly like the central one. They enter their email and password, are authenticated against **their own tenant's** user accounts, and land in the authenticated area for that tenant. They can sign out, which ends their session and returns them to the tenant sign-in screen. A user who does not belong to that tenant cannot sign in there, even with otherwise valid credentials from a different tenant.

**Why this priority**: Sign-in is the entry point to everything tenant-scoped. Without it no tenant user can reach any tenant feature. It is the foundational, independently shippable slice and delivers value on its own (gated access per tenant).

**Independent Test**: On an active tenant's hostname, submit valid credentials of a user that belongs to that tenant → access is granted and a tenant-scoped session is established. Submit credentials of a user that belongs to a *different* tenant → access is denied with the standard "invalid credentials" feedback. Sign out → session ends and the sign-in screen is shown again.

**Acceptance Scenarios**:

1. **Given** an active tenant hostname and a user account that belongs to that tenant, **When** the user submits correct email and password on the tenant sign-in screen, **Then** they are authenticated within that tenant's context, a tenant-scoped session is persisted, and they are redirected to the tenant's authenticated landing area.
2. **Given** the tenant sign-in screen, **When** the user submits an email/password that does not match any account in that tenant, **Then** authentication is rejected with a generic "invalid credentials" message that does not reveal whether the email exists.
3. **Given** valid credentials belonging to *Tenant A*, **When** they are submitted on *Tenant B*'s hostname, **Then** authentication fails and no session is established (no cross-tenant authentication).
4. **Given** an authenticated tenant user, **When** they sign out, **Then** their session is invalidated, the session token is rotated, and they are returned to the tenant sign-in screen.
5. **Given** an authenticated tenant user, **When** they navigate to a tenant route that requires authentication, **Then** access is allowed only while the session is valid and only within the same tenant context.
6. **Given** an unauthenticated visitor, **When** they request a tenant route that requires authentication, **Then** they are redirected to that tenant's sign-in screen.

---

### User Story 2 - A tenant user requests a password reset (Forgot Password) (Priority: P2)

A tenant user who has forgotten their password opens the "forgot password" screen on their tenant hostname — identical to the central one — and submits their email. The system, scoped to that tenant, sends a password-reset email whose link points back to the **same tenant's** hostname. Regardless of whether the email corresponds to a real account in that tenant, the user sees the same neutral success feedback, so the screen cannot be used to discover which emails are registered.

**Why this priority**: Account recovery is essential for real-world usage but depends on sign-in (US1) being in place. It is independently testable and shippable after US1.

**Independent Test**: On a tenant hostname, submit a known tenant email on the forgot-password screen → a reset email is sent and its link targets that tenant's hostname. Submit an unknown email → the same success feedback is shown and no email/link is produced for a non-existent account. Confirm timing and messaging do not differ enough to reveal account existence.

**Acceptance Scenarios**:

1. **Given** the tenant forgot-password screen and an email that belongs to a user in that tenant, **When** the user submits it, **Then** a reset email is generated and delivered, and the reset link in that email points to the **current tenant's** hostname.
2. **Given** the tenant forgot-password screen and an email that does **not** belong to any user in that tenant, **When** the user submits it, **Then** the user sees the same neutral success message and no reset link is created (no user enumeration).
3. **Given** repeated forgot-password submissions from the same email or origin, **When** the configured rate threshold is exceeded, **Then** further attempts are throttled with feedback consistent with the central flow.
4. **Given** a user of *Tenant A*, **When** a reset is requested for an email that exists only in *Tenant B*, **Then** no reset link valid for *Tenant A* is produced (tenants do not leak each other's accounts).

---

### User Story 3 - A tenant user resets their password from the email link (Reset Password) (Priority: P3)

A tenant user clicks the reset link from the email, which opens the reset-password screen on their tenant hostname — identical to the central one. With a valid, unexpired token they choose a new password and confirm it; the password is updated for their tenant account and they are redirected to the tenant sign-in screen (or signed in, matching central behavior) where the new password works. An invalid, expired, or wrong-tenant token shows a clear error and does not change any password.

**Why this priority**: Completes the recovery loop. It depends on US2 producing the link and on US1 for the resulting sign-in, so it is delivered last while still being independently verifiable.

**Independent Test**: Using a valid token issued for a tenant account, open the reset screen on that tenant's hostname, set a new password, and confirm the user can subsequently sign in with it. Repeat with an expired/invalid/foreign-tenant token and confirm the password is unchanged and a clear error is shown.

**Acceptance Scenarios**:

1. **Given** a valid, unexpired reset link opened on the correct tenant hostname, **When** the user submits a new password meeting the password policy and a matching confirmation, **Then** the account password is updated and the user is redirected per the central flow's behavior and can sign in with the new password.
2. **Given** a reset link whose token is expired, already used, or malformed, **When** the user opens the reset screen and submits, **Then** the request is rejected with a clear error and no password is changed.
3. **Given** a reset token issued for a user in *Tenant A*, **When** it is used against *Tenant B*'s hostname, **Then** it is treated as invalid and no password is changed in either tenant.
4. **Given** a new password that fails the password policy or whose confirmation does not match, **When** it is submitted, **Then** validation errors are shown matching the central flow and no change is made.

---

### Edge Cases

- **Deleted / unavailable tenant**: When a tenant is soft-deleted or otherwise unavailable, its sign-in, forgot-password, and reset-password screens MUST NOT be served as normal; the existing "tenant unavailable — contact your manager" handling applies instead (consistent with the multi-tenant foundation).
- **Already authenticated user revisiting auth screens**: A user with a live tenant session who opens the tenant sign-in / forgot / reset screens is handled the same way the central flow handles it (e.g. redirected away from guest-only screens).
- **Reset link opened on the wrong hostname**: A link generated for one tenant but opened on the central domain or another tenant is treated as invalid.
- **Expired or tampered reset link**: Signature/expiry failures yield a clear error, never a partial or silent password change.
- **Concurrent sessions across tenants**: A person who legitimately has accounts in two tenants can hold independent, isolated sessions per tenant hostname without one affecting the other.
- **Throttling feedback**: Rate-limit responses must not become an oracle for account existence.

## Requirements *(mandatory)*

### Functional Requirements

**Tenant context & isolation**

- **FR-001**: The system MUST serve the sign-in, forgot-password, and reset-password screens within a tenant's context when accessed on that tenant's hostname, resolving the active tenant from the hostname.
- **FR-002**: The system MUST authenticate a user only against the account store of the tenant resolved from the current request, and MUST NOT authenticate a user against any other tenant's or the central context's accounts.
- **FR-003**: The system MUST keep each tenant's authentication session isolated, so a session established on one tenant grants no access to another tenant or to the central context.
- **FR-004**: The system MUST NOT expose, confirm, or leak the existence of accounts belonging to other tenants through any of the three screens or their feedback/timing.
- **FR-005**: The system MUST apply the existing "tenant unavailable" handling to all three auth screens when the tenant is soft-deleted or otherwise blocked, rather than rendering the normal auth screens.

**Login**

- **FR-006**: Users MUST be able to sign in on the tenant hostname using email and password.
- **FR-007**: The system MUST validate submitted credentials and reject invalid ones with a generic message that does not reveal whether the email exists.
- **FR-008**: The system MUST persist an authenticated session for the tenant user upon successful sign-in.
- **FR-009**: Users MUST be able to sign out, which MUST invalidate the session and rotate the session token.
- **FR-010**: The system MUST redirect unauthenticated users to the tenant sign-in screen when they request a protected tenant route, and MUST redirect/handle already-authenticated users away from guest-only auth screens.

**Forgot password**

- **FR-011**: Users MUST be able to request a password reset by submitting their email on the tenant forgot-password screen.
- **FR-012**: The system MUST generate a reset token and send a reset email only for an email that corresponds to a real account in the current tenant.
- **FR-013**: The reset link contained in the email MUST point to the **current tenant's** hostname so it opens the correct tenant context.
- **FR-014**: The system MUST show the same neutral success feedback whether or not the submitted email corresponds to a real account (anti-enumeration), matching the central flow.
- **FR-015**: The system MUST apply rate limiting to reset requests consistent with the central flow's thresholds and feedback.

**Reset password**

- **FR-016**: Users MUST be able to set a new password using a valid, unexpired reset link opened on the correct tenant hostname.
- **FR-017**: The system MUST validate the reset token's authenticity, expiry, and tenant scope, and MUST reject tokens that are expired, already used, malformed, or issued for a different tenant.
- **FR-018**: The system MUST enforce the same password policy and confirmation rules as the central flow.
- **FR-019**: On a successful reset, the system MUST update the tenant account's password and redirect the user consistent with the central flow, such that the user can authenticate with the new password.
- **FR-020**: The system MUST NOT change any password when token or password validation fails.

**Reuse & consistency**

- **FR-021**: The tenant auth screens MUST reuse the existing central auth presentation and interaction (layout, components, inputs, validation messages, loading/error states, responsiveness) so the tenant experience is visually and behaviorally identical, without duplicating UI code.
- **FR-022**: The feature MUST NOT alter the behavior or appearance of the existing central authentication flow (no regression in the central context).
- **FR-023**: The system MUST record password-reset/auth security events for the tenant flow consistent with how the central flow records them, preserving auditability.

### Key Entities *(include if feature involves data)*

- **Tenant**: An isolated school context resolved by hostname; owns its own user account store and is fully separated from other tenants and the central context. (Already exists from the multi-tenant foundation.)
- **Tenant User Account**: A credential-bearing account that exists within a single tenant; the only accounts a tenant sign-in may authenticate against. Identified by email within the tenant.
- **Password Reset Token**: A time-limited, single-tenant credential that authorizes a password change; valid only within the tenant that issued it and only until it expires or is used.
- **Authentication Session**: The persisted state proving a tenant user is signed in; scoped to one tenant and ended on sign-out.
- **Auth Security Event**: An audit record of authentication / password-reset activity (e.g. reset requested, link opened, reset completed) used for traceability.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of sign-in attempts using credentials valid for the current tenant succeed; 100% of attempts using credentials that belong only to a different tenant fail.
- **SC-002**: A tenant user can complete sign-in in under 1 minute and reach the authenticated area on the first valid attempt.
- **SC-003**: For every successful forgot-password request, the reset email's link opens the reset screen on the same tenant hostname 100% of the time.
- **SC-004**: Forgot-password responses for existing vs. non-existing emails are indistinguishable to the user (same message; no timing-based oracle), verified across repeated trials.
- **SC-005**: 100% of valid reset links allow the user to set a new password and subsequently sign in with it; 100% of expired/invalid/wrong-tenant links are rejected with no password change.
- **SC-006**: No reset token issued for one tenant is ever accepted by another tenant (0 cross-tenant token acceptances).
- **SC-007**: The central authentication flow shows zero regressions — its screens, messages, and behavior are unchanged after this feature ships.
- **SC-008**: The three tenant auth screens are visually and behaviorally identical to their central counterparts across the supported responsive breakpoints (no per-screen divergence).
- **SC-009**: Rate limiting blocks abusive reset/sign-in attempts at the same thresholds as the central flow.

## Assumptions

- The platform uses a **database-per-tenant** model: each tenant owns its own user and session storage, so tenant authentication is naturally isolated and "no cross-tenant authentication" is enforced structurally rather than by a tenant-id filter. (Confirmed by the existing multi-tenant foundation.)
- **Tenant users already exist** within their tenant (provisioned via tenant setup or other flows). This feature does not create accounts; registration/sign-up is out of scope.
- Each tenant is reached on its **own hostname/subdomain**, and the active tenant is resolved from that hostname.
- The **central authentication flow is the reference design**; the tenant flow must match it and reuse its presentation/behavior rather than reimplement it.
- The existing **password policy, anti-enumeration, rate-limiting, and audit-logging** behavior of the central flow are the intended behavior for the tenant flow as well.
- Email delivery is already configured for the platform; tenant reset emails use the same delivery mechanism, differing only in the tenant-scoped link.
- The existing **"tenant unavailable / contact your manager"** handling for soft-deleted tenants also governs access to the tenant auth screens.
- Out of scope: registration/sign-up, social/SSO login, multi-factor authentication, and any change to central-context behavior.
