# Phase 0 Research — Password Reset by Email

All open questions raised by `spec.md` (assumptions referenced existing platform capabilities) and by `plan.md` (technical context) are resolved here. No `NEEDS CLARIFICATION` markers remain in `plan.md` after this document.

---

## Decision 1 — Token issuance, hashing, expiry, single-use

**Decision**: Use Laravel's first-party `Illuminate\Support\Facades\Password` broker (the `users` broker already configured in `config/auth.php`) for token issuance, persistence, expiry, and consumption. Do not roll our own.

**Rationale**:

- The broker already stores `bcrypt(token)` in `password_reset_tokens` (the plain token is sent in the URL only) — satisfies FR-004's "non-reversible representation".
- `auth.passwords.users.expire = 60` already matches FR-006's 60-minute requirement; no config change needed.
- `Password::reset()` deletes the row on success — single-use is structural, not a check we have to remember to write (FR-006 second clause).
- `Password::sendResetLink()` already enforces the configured `throttle` (60 s by default), but that is a coarse same-row throttle; we replace/supplement it with our own per-email + per-IP `RateLimiter` (see Decision 6) because the broker's throttle does not satisfy FR-011's per-IP axis.

**Alternatives considered**:

- *Custom token table + custom service*: rejected — re-implements code that ships with the framework, increases attack surface, and forfeits the framework's test coverage of the broker.
- *Stateless signed URLs (JWT-style)*: rejected — FR-007 mandates invalidating *previously issued* outstanding tokens when a new one is requested. Stateless signed tokens cannot be revoked without an additional revocation list, which is just a worse version of the table the broker already uses.

---

## Decision 2 — Invalidating previous outstanding tokens (FR-007)

**Decision**: Before calling `Password::broker()->sendResetLink()`, explicitly `DB::table('password_reset_tokens')->where('email', $email)->delete()`.

**Rationale**:

- The broker keys `password_reset_tokens` by email (it is the primary key), so a new `createToken()` call already overwrites the previous row for that email. However it does so via `updateOrInsert`, and the deletion-first form makes the intent explicit and survives any future framework refactor that might switch to multi-row tokens.
- A single statement; runs in the same transaction as the audit-event write.

**Alternatives considered**:

- *Trust the broker's overwrite semantics implicitly*: rejected — too dependent on undocumented internals; one Laravel minor-version change could quietly break FR-007.

---

## Decision 3 — Account-enumeration-safe responses (FR-003, SC-005)

**Decision**: The request controller always returns the same neutral PT-BR message ("Se uma conta existir para este e-mail, você receberá instruções em instantes.") regardless of `Password::sendResetLink()`'s status return. The notification is **always dispatched to a queue** (`ShouldQueue`), so the controller's response time does not depend on whether mail was sent. The not-found branch additionally calls `usleep()` to floor at a constant ~10 ms of synthetic work before responding, smoothing out the difference between (a) one DB read + one DB write + one queue push and (b) one DB read.

**Rationale**:

- SC-005 requires p95 difference under 50 ms. Without the floor, the not-found branch is measurably faster because it skips the queue push.
- Queueing the notification removes the SMTP-handshake variance entirely from the user-facing request.
- The neutral message string is identical and the HTTP status (200) is identical on both branches; only the audit row differs (visible only server-side).

**Alternatives considered**:

- *Always perform a dummy bcrypt to equalize hashing time*: rejected — the broker doesn't bcrypt on the not-found path; the slow operation we actually need to equalize is the queue-push, and a `usleep` cap is simpler and sufficient.
- *Use 202 Accepted on both branches*: rejected — Livewire actions don't surface HTTP status to the user; the user-visible signal is the rendered confirmation, which is already identical.

---

## Decision 4 — Email delivery (FR-005, SC-002)

**Decision**: A single `App\Notifications\PasswordResetRequested` class implementing `Illuminate\Contracts\Queue\ShouldQueue` and using `Queueable`. The notification's `toMail()` returns a `MailMessage` whose markdown view is `mail.auth.password-reset` (a Blade view in `resources/views/mail/auth/password-reset.blade.php`) — branded, PT-BR by default, English fallback when `$notifiable->locale === 'en'`. The reset URL embedded in the mail is built via `URL::temporarySignedRoute('password.reset', now()->addMinutes(60), ['token' => $token, 'email' => $email])` so URL tampering is detected at the HTTP layer (FR-015 edge case "tampered link → invalid link error").

**Rationale**:

- Queued notification keeps the request-form action under 50 ms (Decision 3).
- `URL::temporarySignedRoute` adds an HMAC signature on top of the broker's bcrypt-hashed token, defending against URL tampering before the broker is even consulted.
- Markdown mail template inherits Laravel's responsive default and is overridable per-product later without touching the notification class.
- Localization: `Notification::locale($notifiable->preferred_locale ?? 'pt_BR')` is set in the service before `notify()` so PT-BR is the default and per-user override is supported (matches the spec's "primary language Portuguese" assumption).

**Alternatives considered**:

- *Custom `Mailable` instead of `Notification`*: rejected — `Notification` is the framework's documented path for "I want this user to receive a message" and gives us queueing + localization + future channel additions (SMS, push) for free.
- *Synchronous mail send*: rejected by SC-005 (timing leak).

---

## Decision 5 — Session revocation on successful reset (FR-009, SC-006)

**Decision**: Inside the `PasswordResetService::handleReset()` method, after `Password::reset()` returns `Password::PASSWORD_RESET`:

1. `$user->forceFill(['remember_token' => Str::random(60)])->save();` — invalidates the "remember me" cookie on every device (Laravel rotates it on `logoutOtherDevices`, we do it directly because the user is not authenticated in this request).
2. `DB::table('sessions')->where('user_id', $user->id)->delete();` — the `sessions` table (from the bundled migration; session driver is `database` per `config/session.php` default in fresh skeleton) is the canonical store of every active web session for this user.
3. Mobile/native clients that hold a Sanctum personal-access-token will need their tokens revoked too once Sanctum is added; **this is out of scope today because no Sanctum tokens table exists yet**. A `// TODO(sanctum)` comment in `PasswordResetService` documents this exact gap so it is not forgotten when mobile auth lands.

**Rationale**:

- These two writes plus the remember-token rotation cover every authentication credential that exists in the codebase today. SC-006's "100% of sessions revoked within 5s" is satisfied because both operations happen synchronously inside the password-update request itself (no queue, no async).
- The remember-token rotation is required because `DB::delete('sessions')` does not invalidate the remember-me cookie — that cookie reauthenticates against the `users.remember_token` column.

**Alternatives considered**:

- *Use `Auth::logoutOtherDevices($password)`*: rejected — that helper requires the user to be currently authenticated in the request, which they are not on the reset flow.
- *Mark sessions invalid via a flag column*: rejected — `sessions` table has no such column and adding one is more invasive than `DELETE`.

---

## Decision 6 — Rate limiting (FR-011)

**Decision**: A thin `PasswordResetRateLimiter` wrapper over `Illuminate\Support\Facades\RateLimiter` with two named limiters:

- `password-reset:email:{sha1($email)}` — 5 requests / 1 h (`RateLimiter::for('password-reset-email', fn ($job) => Limit::perHour(5)->by($job->email))`).
- `password-reset:ip:{$request->ip()}` — 20 requests / 1 h.

Both are checked on the `password.email` POST and on the `password.update` POST (re-trying tokens). If either is exceeded the Livewire action returns a generic "Tente novamente em alguns minutos." string and increments an `audit_event` row of type `request_throttled`.

**Rationale**:

- Two independent limiters are cheaper to reason about than one composite limiter and let us tune them separately if abuse patterns shift.
- Email is hashed in the limiter key so the cache store never holds plaintext addresses keyed by themselves.
- The `RateLimiter` facade backs onto the cache store (`config/cache.php` default = database in the current skeleton); no new infrastructure needed.

**Alternatives considered**:

- *Laravel's built-in `throttle:` middleware*: rejected — it can only key by request IP or `Auth::id()`, neither of which gives us a per-email axis for unauthenticated traffic.
- *Custom DB-backed counter table*: rejected — duplicates `RateLimiter`'s cache store with worse semantics (no atomic increment, no auto-expiry).

---

## Decision 7 — Audit log (FR-012, SC-007)

**Decision**: New table `password_reset_audit_events` (append-only by convention — no `Model::updated` listener, only `create()`). Schema:

| Column | Type | Nullable | Note |
|---|---|---|---|
| `id` | bigint PK auto | — | — |
| `event_type` | string(40) | no | enum: `requested`, `email_sent`, `link_opened`, `password_changed`, `token_rejected`, `request_throttled` |
| `user_id` | bigint nullable, FK→users(id) on delete set null | yes | nullable because some events (request for unknown email) have no resolved user |
| `email_hash` | char(64) | no | sha256 of lowercased email; lets us correlate without storing plaintext PII |
| `ip_address` | string(45) | yes | IPv6-wide; nullable for CLI/test |
| `user_agent` | string(255) | yes | truncated |
| `outcome` | string(20) | no | enum: `accepted`, `rejected`, `delivered`, `failed`, `throttled` |
| `reason` | string(80) | yes | when outcome=rejected, machine-readable code: `expired`, `consumed`, `invalid`, `tampered`, `account_inactive` |
| `created_at` | timestamp | no | indexed; partitionable by month later |

No `updated_at` — these rows are immutable. Sensitive material (token plaintext, hashes, new password) is never logged.

**Rationale**:

- The repo `CLAUDE.md` assumes a shared audit infrastructure but none exists in the current monorepo; building a dedicated table for password-reset audit events is the minimum useful step and is forward-compatible with later forwarding to a generic audit sink (the migration adds the columns a generic sink will need).
- `email_hash` instead of plaintext satisfies the spec's "sensitive material excluded from logs" — even an attacker with read access to this table cannot enumerate accounts.
- 12-month retention (SC-007) is operational; the table is small enough (~6 rows per real reset, ~1 row per attack-probe) that no partitioning is needed at expected scale; a `created_at` index supports a future periodic prune job.

**Alternatives considered**:

- *Write JSON events to the `log` channel only*: rejected — SC-007 requires "recoverable for at least 12 months" and log files rotate; structured rows in PostgreSQL are queryable for support tickets.
- *Generic `audit_events` table now*: rejected — premature; one feature does not justify designing a polymorphic audit schema. The migration is small enough to backport into a generic table later.

---

## Decision 8 — Localization

**Decision**: Primary locale `pt_BR`. Files:

- `lang/pt_BR/passwords.php` — overrides Laravel broker strings (`sent`, `throttled`, `user`, `token`, `reset`).
- `lang/pt_BR/auth.php` — extends existing file with reset-flow UI strings.
- `lang/en/passwords.php` + `lang/en/auth.php` — English fallback (covers spec's "English secondary fallback only if user profile is explicitly English").

The notification reads the recipient's locale from a future `users.preferred_locale` column if present; otherwise it defaults to `pt_BR` via `Notification::locale('pt_BR')`. The column does not exist today and is **not added by this feature** — adding it is a separate i18n cross-cut. Until then every user gets PT-BR.

**Rationale**: PT-BR is the assumed product locale (spec Assumptions). Adding the user-language column would balloon scope.

**Alternatives considered**:

- *Browser `Accept-Language` header for the request form, user attribute for the email*: rejected for now — the form is rendered via Livewire and would need session-scoped locale switching that does not exist yet. Single locale today, room to grow.

---

## Decision 9 — Livewire 4 component shape

**Decision**: Single-file components (SFC) placed under `resources/views/components/auth/`, file names `⚡forgot-password.blade.php` and `⚡reset-password.blade.php`, both using `#[Layout('layouts.guest')]` and `#[Validate(...)]` attributes — matching the existing `⚡login.blade.php` exactly. Routes are registered as `Route::livewire('/password/forgot', 'auth.forgot-password')->name('password.request')` etc., matching the existing `Route::livewire('/login', 'auth.login')->name('login')` pattern.

**Rationale**: Project convention — diverging would require a justification this feature does not have.

**Alternatives considered**:

- *Class-backed `App\Livewire\Auth\*` components*: rejected — inconsistent with existing `⚡login.blade.php`.
- *Plain Blade form + standard controller*: rejected — same reason; the login screen sets the precedent.

---

## Decision 10 — Test strategy (TDD per CLAUDE.md)

**Decision**: Each Phase-2 task pair is "(a) write failing Pest test, (b) make it pass". Test breakdown:

| Test file | Covers | Type |
|---|---|---|
| `tests/Feature/Auth/ForgotPasswordRequestTest.php` | FR-001/002/003/004/007, SC-005 (response parity) | Feature, `Livewire::test` |
| `tests/Feature/Auth/ResetPasswordTest.php` | FR-008/009/010/015 | Feature, `Livewire::test` |
| `tests/Feature/Auth/PasswordResetEnumerationTest.php` | SC-005 timing parity (under 50 ms diff over N samples) | Feature |
| `tests/Feature/Auth/PasswordResetRateLimitTest.php` | FR-011, audit row `request_throttled` | Feature |
| `tests/Unit/Auth/PasswordResetServiceTest.php` | Service-level orchestration, session purge, remember-token rotation | Unit |
| `tests/Unit/Auth/PasswordResetAuditEventTest.php` | Audit model: appended-only, email-hashed, schema | Unit |

`Notification::fake()` asserts `PasswordResetRequested` is queued and addressed correctly. `Queue::fake()` is used in the enumeration test to confirm the not-found branch *also* takes a queue-push code path under the timing floor.

**Rationale**: Mirrors `pest-testing` skill guidance (feature tests preferred, browser tests not required for back-end + Livewire-server-rendered flows).

**Alternatives considered**:

- *Browser test (Pest 4 visit/click)*: deferred — adds a Playwright dependency the project does not have configured yet and Livewire feature tests already cover the rendered DOM via `Livewire::test()->assertSee()`.
