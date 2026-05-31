# Implementation Plan: Password Reset by Email ("Esqueci minha senha")

**Branch**: `001-password-reset-email` | **Date**: 2026-05-23 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-password-reset-email/spec.md`

## Summary

Add an end-to-end email-based password reset flow ("Esqueci minha senha") to the existing Livewire login surface. The flow has four user-facing screens: (1) login screen affordance, (2) request form (email only), (3) reset form (new password + confirmation, reached via emailed link), (4) success/error states. The technical approach reuses Laravel's first-party `Password` broker for token issuance, hashing, expiry, and consumption, and layers four feature-specific concerns on top of it: account-enumeration-safe responses, queued branded PT-BR notification, multi-axis rate limiting (per-email + per-IP), and a dedicated `password_reset_audit_events` append-only table that records every request/dispatch/consumption/rejection. On successful reset the flow purges all of the user's persisted database sessions, hashes the new password via the existing `hashed` cast, and writes the audit event. All four user-facing screens are Livewire 4 single-file components placed under `resources/views/components/auth/`, matching the convention established by the existing `⚡login.blade.php` component.

## Technical Context

**Language/Version**: PHP 8.5 (composer.json requires `^8.3`)

**Primary Dependencies**: Laravel 13.8, Livewire 4.3, Laravel Notifications (built-in), `Illuminate\Auth\Passwords\PasswordBroker`, `Illuminate\Support\Facades\RateLimiter`

**Storage**: PostgreSQL (production) / SQLite (local default). Three tables involved: existing `users`, existing `password_reset_tokens` (Laravel default schema, stores hashed token keyed by email), new `password_reset_audit_events` (this feature).

**Testing**: Pest 4 with `RefreshDatabase`. Feature tests for the two Livewire SFCs (request form, reset form) using `Livewire::test()`. Unit tests for the audit logger and the rate-limiter wrapper. Notification assertion via `Notification::fake()`. Mail content assertion via the notification's `toMail` payload.

**Target Platform**: Linux server (Docker — `infra/docker/`), nginx → php-fpm; Livewire-rendered HTML consumed by web (school + guardian) and by the React Native guardian mobile client (which renders the same web reset URL inside an in-app browser per FR-014).

**Project Type**: Web application (server-rendered, Livewire SFC). No separate frontend bundle for this feature.

**Performance Goals**: SC-005 requires response-time delta between "email exists" and "email does not exist" under 50 ms at p95. Achieved by always enqueueing the notification job (never sending inline) and always taking the same code path through the controller, plus a constant-time sleep floor on the not-found branch.

**Constraints**:

- Token lifetime: 60 minutes (FR-006; matches Laravel default `auth.passwords.users.expire = 60`).
- Rate limits: 5 requests per email per hour, 20 per IP per hour (FR-011).
- Notification MUST be queued (SC-002: 95% delivered ≤60s and SC-005: no timing leak).
- All sessions for the user MUST be revoked within 5s of password change (SC-006). Implemented by deleting all rows in `sessions` where `user_id = ?` after `Auth::logoutOtherDevices()` (which rotates the remember token).
- Primary locale PT-BR (`APP_LOCALE` will need to be set to `pt_BR` for this flow's strings; email + UI strings live in `lang/pt_BR/`).
- Audit retention: 12 months minimum (SC-007). Enforced operationally; this feature only writes the rows.

**Scale/Scope**: Two new Livewire SFCs, one new notification class, one new audit model + migration, one new rate-limiter helper, one new web routes block (two routes: `password.request` POST endpoint via Livewire action, `password.reset` GET with signed token, `password.update` POST via Livewire action). Estimated ~600 LOC excluding tests, ~400 LOC of tests.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

The project constitution at `.specify/memory/constitution.md` is currently a placeholder template (all `[PRINCIPLE_*]` tokens unfilled). In its absence the authoritative rules are the eight principles in the repo root `CLAUDE.md` plus the Laravel Boost rules in `laravel/CLAUDE.md`. This plan is checked against those:

| Rule | Status | Evidence |
|---|---|---|
| Separation of Concerns | PASS | Auth concern stays inside the Livewire SFCs and a dedicated `PasswordResetService`; no spillover into other domains. |
| Event-Driven First (cross-domain) | N/A | Password reset is platform/auth, not a cross-domain mutation. No external module is read or written; no RabbitMQ event is required. |
| Financial Consistency | N/A | No financial mutation. |
| AI as Observer | N/A | No AI involvement. |
| BFF Pattern | PASS-WITH-NOTE | The current `laravel/` skeleton is the API core itself; no BFF exists yet in the working directory. The reset *web pages* are rendered by Laravel/Livewire because that is the only running surface today. When the BFFs come online, the reset URL hosted by Laravel remains the canonical surface (per common practice for password-reset deep links); BFFs proxy the initial request form but the token-bearing URL points at Laravel directly so that the signed-token verification stays on the side that owns the credential store. |
| Stateless Services | PASS | All state in `users`, `password_reset_tokens`, `sessions`, `password_reset_audit_events`. No in-memory state across requests. |
| Idempotency | PASS | Re-submitting the request form for the same email within the rate-limit window is a no-op beyond audit logging; token consumption is single-use enforced by the broker. |
| Auditability | PASS | Every state transition emits an audit row (see data-model). |
| Module Isolation | PASS-WITH-NOTE | The Modules/ tree described in `CLAUDE.md` does not yet exist in `app/`. Auth is cross-cutting and would belong to `Modules/Shared/` once that tree is created; in the meantime the feature lives in flat Laravel directories (`app/Livewire/Auth`, `app/Notifications`, `app/Models`, `app/Services/Auth`) and is structured so it can be moved into `app/Modules/Shared/Auth/` later without changing namespaces inside the feature (only the root namespace prefix). |
| TDD (Boost: tests before implementation) | PASS | Phase 2 tasks will list the Pest tests first; implementation tasks follow per the SpecKit workflow. |
| Livewire 4 SFC convention | PASS | New components placed under `resources/views/components/auth/` with the `⚡` filename prefix matching the existing `⚡login.blade.php`. |
| Pint formatting | PASS | `vendor/bin/pint --dirty --format agent` will be run at the end of every implementation slice. |

No violations require entry in **Complexity Tracking**.

## Project Structure

### Documentation (this feature)

```text
specs/001-password-reset-email/
├── plan.md              # this file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   ├── http-routes.md       # route table + request/response shapes
│   ├── livewire-components.md  # public properties + actions of the two SFCs
│   └── notification-mail.md    # email subject + body contract (PT-BR + EN)
└── checklists/
    └── requirements.md  # already present, generated earlier
```

### Source Code (repository root)

The working directory is `/home/bdsoliveira/coding/geffin-monorepo2/laravel/` (the API core; per the repo `CLAUDE.md` this will eventually live at `apps/api-laravel/` but the move is out of scope here).

```text
laravel/
├── app/
│   ├── Livewire/
│   │   └── Auth/                 # NEW — class-backed Livewire components (only if not pure SFC)
│   ├── Notifications/
│   │   └── PasswordResetRequested.php   # NEW — queued, branded, PT-BR
│   ├── Models/
│   │   ├── User.php                     # (existing)
│   │   └── PasswordResetAuditEvent.php  # NEW
│   └── Services/
│       └── Auth/
│           ├── PasswordResetService.php # NEW — orchestrates broker + audit + session revoke
│           └── PasswordResetRateLimiter.php # NEW — per-email + per-IP RateLimiter wrapper
├── database/
│   ├── migrations/
│   │   └── 2026_05_23_000000_create_password_reset_audit_events_table.php  # NEW
│   └── factories/
│       └── PasswordResetAuditEventFactory.php  # NEW
├── lang/
│   ├── pt_BR/
│   │   ├── auth.php                     # NEW or extended — reset strings
│   │   └── passwords.php                # NEW — broker strings localized
│   └── en/
│       └── (same files as fallback)
├── resources/
│   └── views/
│       ├── components/
│       │   └── auth/
│       │       ├── ⚡login.blade.php             # (existing) — add forgot-password link wiring
│       │       ├── ⚡forgot-password.blade.php   # NEW — request form
│       │       └── ⚡reset-password.blade.php    # NEW — set-new-password form
│       └── mail/
│           └── auth/
│               └── password-reset.blade.php     # NEW — branded mail template
├── routes/
│   └── web.php                          # extended: 3 new routes
└── tests/
    ├── Feature/
    │   └── Auth/
    │       ├── ForgotPasswordRequestTest.php    # NEW
    │       ├── ResetPasswordTest.php            # NEW
    │       ├── PasswordResetEnumerationTest.php # NEW — SC-005 timing/response parity
    │       └── PasswordResetRateLimitTest.php   # NEW
    └── Unit/
        └── Auth/
            ├── PasswordResetServiceTest.php     # NEW
            └── PasswordResetAuditEventTest.php  # NEW
```

**Structure Decision**: Flat Laravel layout (no `app/Modules/` yet) because that directory tree does not exist in the current skeleton and creating it just for one cross-cutting auth feature would be premature. Namespaces are chosen so that a later move into `app/Modules/Shared/Auth/` is a search-and-replace of `App\Services\Auth\` → `App\Modules\Shared\Auth\Services\` (and equivalent for `Notifications`, `Models`, `Livewire`). The Livewire SFCs follow the existing `resources/views/components/auth/⚡*.blade.php` convention established by the in-repo `⚡login.blade.php`.

## Complexity Tracking

> No constitution violations; this section intentionally empty.
