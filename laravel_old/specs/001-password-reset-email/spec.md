# Feature Specification: Password Reset by Email ("Esqueci minha senha")

**Feature Branch**: `001-password-reset-email`

**Created**: 2026-05-23

**Status**: Draft

**Input**: User description: "Feature to 'Esqueci minha senha' on login screen. Sending by email."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Request password reset link from login screen (Priority: P1)

A user who cannot remember their password opens the login screen, taps the "Esqueci minha senha" (Forgot my password) link, enters the email address tied to their account, and submits the request. The system confirms the request was received and, if the address matches an active account, sends an email containing a secure link to set a new password.

**Why this priority**: Without this step the user has no way to start recovery; it is the entry point of the whole flow and unblocks every locked-out user from completing login.

**Independent Test**: Submit the form with a known active email; confirm a generic "if the address exists you will receive an email" confirmation is shown, and that a reset email is delivered to the inbox containing a valid reset link.

**Acceptance Scenarios**:

1. **Given** a user on the login screen with a registered, active email, **When** they click "Esqueci minha senha", enter their email and submit, **Then** the system displays a neutral confirmation message and sends a reset email containing a unique, time-limited link to that address.
2. **Given** a user submits an email that does not exist or belongs to a deactivated account, **When** they submit the form, **Then** the system displays the same neutral confirmation message and does NOT send any email, so account existence cannot be inferred.
3. **Given** a user submits an invalid email format, **When** they submit the form, **Then** the system shows an inline validation error and does not call the reset workflow.

---

### User Story 2 - Choose a new password from the reset link (Priority: P1)

A user who received the reset email opens the link on the same or another device, enters and confirms a new password that meets the system's password rules, and submits it. The system updates the credential, invalidates the reset link, ends any existing authenticated sessions for that account, and returns the user to the login screen with a success message so they can sign in with the new password.

**Why this priority**: The first story is wasted effort if the user cannot finish setting a new password; this story closes the loop and restores account access.

**Independent Test**: Open a freshly issued reset link, submit a valid new password, and confirm login succeeds with the new password while the old password is rejected and the same link cannot be reused.

**Acceptance Scenarios**:

1. **Given** a valid, unused, unexpired reset link, **When** the user opens it and submits a new password meeting all password rules, **Then** the password is updated, the link is consumed, all existing sessions for that user are revoked, and the user is redirected to the login screen with a success confirmation.
2. **Given** an expired reset link, **When** the user opens it, **Then** the system shows a clear "link expired" message and offers to request a new reset email instead of presenting the password form.
3. **Given** a reset link that has already been used, **When** the user opens it again, **Then** the system shows a "link already used" message and offers to request a new reset email.
4. **Given** the user is on the reset form, **When** they submit a password that fails policy or does not match the confirmation field, **Then** the system shows inline validation errors and does not change the stored password.

---

### Edge Cases

- Repeated requests for the same email within a short window MUST be rate-limited so the inbox is not flooded and the system cannot be used to spam third parties.
- If a user requests a reset multiple times, only the most recently issued link is valid; older outstanding links are invalidated on issuance.
- A user who completes a password reset MUST be signed out of all active sessions and devices.
- If email delivery fails (bounce, soft-fail), the user-visible confirmation message remains neutral; failures are logged for operational follow-up but never surfaced as account existence hints.
- Tokens MUST NOT be re-usable after expiration, after use, or after the account is deactivated/locked.
- If the user's account is locked, suspended, or pending deletion, the reset request is silently ignored (no email sent) while still showing the neutral confirmation message.
- Links that have been tampered with (modified or truncated) MUST be rejected with a generic "invalid link" error and offer the option to request a new email.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The login screen MUST display a clearly visible "Esqueci minha senha" affordance that takes the user to a request form.
- **FR-002**: The request form MUST accept a single email address, validate format client- and server-side, and submit it for processing.
- **FR-003**: After submission, the system MUST always display the same neutral confirmation message ("If an account exists for this email, you will receive instructions shortly.") regardless of whether the email matches an account, to prevent account enumeration.
- **FR-004**: When the email matches an active account, the system MUST generate a single-use, cryptographically random, time-limited reset token bound to that account, persist a non-reversible representation of it, and send a reset email to the registered address.
- **FR-005**: The reset email MUST contain a link that includes the token, be branded for the product, be localized to the user's language (Portuguese as primary), and clearly state the expiration window and that the message can be ignored if not requested.
- **FR-006**: Reset tokens MUST expire 60 minutes after issuance and MUST be consumed on first successful password change.
- **FR-007**: When a new reset is requested for the same account, any previously issued outstanding tokens for that account MUST be invalidated.
- **FR-008**: The reset link MUST land on a form that asks for a new password and a confirmation, and MUST enforce the platform's existing password policy (length and complexity requirements published in the policy).
- **FR-009**: On successful password change, the system MUST update the stored credential using a current, salted password-hashing scheme, mark the token as used, terminate all active authenticated sessions for that user across web and mobile clients, and write an audit event.
- **FR-010**: After successful reset, the user MUST be redirected to the login screen with a confirmation message and MUST be required to authenticate with the new password.
- **FR-011**: The request endpoint MUST be rate-limited per IP and per email (default: at most 5 requests per email per hour and 20 per IP per hour) and MUST present a generic "try again later" message when the limit is exceeded.
- **FR-012**: Every reset request, every email dispatched, every successful reset, and every failed token usage MUST produce an audit log entry capturing the user identifier, timestamp, originating IP, and outcome, with sensitive material (tokens, passwords) excluded from logs.
- **FR-013**: The feature MUST be available to every user type whose primary login uses email and password (guardians and school staff users that exist in the authoritative user store).
- **FR-014**: The reset workflow MUST work end-to-end on web and on mobile clients, sharing the same backend rules.
- **FR-015**: Tokens MUST be rejected with a clear, distinct user-facing message when they are expired, already used, malformed, or bound to an account that is no longer active.

### Key Entities *(include if feature involves data)*

- **User Account**: The authoritative person record whose credentials are being reset. Identified by email; has status (active, suspended, deactivated); only active accounts can complete a reset.
- **Password Reset Token**: A one-time, time-limited proof of email ownership bound to a single User Account, with attributes: opaque token value (only its hashed form is stored), issued-at, expires-at, consumed-at, requester IP, status (valid, expired, consumed, invalidated).
- **Password Reset Audit Event**: An append-only record describing a reset-related action (requested, email sent, link opened, password changed, token rejected) with timestamp, account reference, source IP, and outcome.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A first-time user can complete the full flow (request email → open link → submit new password → sign in) in under 3 minutes, end-to-end, including reading the email.
- **SC-002**: At least 95% of reset emails are delivered to the recipient inbox (not spam, not bounced) within 60 seconds of the user submitting the request, under normal operating load.
- **SC-003**: At least 90% of users who request a reset successfully sign in with the new password within 24 hours of the request (measured as completed-reset-to-request ratio).
- **SC-004**: Support tickets categorized as "cannot log in / forgot password" decrease by at least 40% within 60 days of release compared to the prior 60 days.
- **SC-005**: Zero account-enumeration regressions: an external observer cannot distinguish between a registered and unregistered email submission by response body, response status, or response timing (difference under 50ms at the 95th percentile).
- **SC-006**: 100% of successful password resets revoke all previously active sessions for the affected user within 5 seconds of the password change.
- **SC-007**: 100% of password reset actions (request, dispatch, consumption, rejection) are represented in the audit log and recoverable for at least 12 months.

## Assumptions

- The primary user-facing language is Portuguese (Brazil); English is a secondary fallback only if the user's profile is explicitly English.
- The platform already maintains an authoritative user store keyed by email with an "active/suspended/deactivated" status; this feature reads and updates that store rather than introducing a new one.
- Transactional email delivery is already available to the platform (deliverability, branded sender, SPF/DKIM); no new email provider is being procured as part of this feature.
- The platform already has a documented password policy (minimum length, complexity, deny-list); this feature enforces it but does not redefine it.
- The platform already has a session/token revocation capability for authenticated sessions; this feature invokes it on successful password change rather than building it.
- Multi-factor authentication, social/SSO logins, and account-recovery via SMS or security questions are **out of scope** for this feature; only email-based password reset for email/password accounts is in scope.
- The login screen in scope is the standard email/password login surfaced by both the school and guardian frontends; bespoke kiosk or third-party embedded surfaces are out of scope.
- Audit log storage and retention infrastructure already exists; this feature emits structured events into it rather than creating a new sink.
