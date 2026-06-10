# HTTP Routes Contract — Password Reset by Email

All routes live in `routes/web.php` inside the existing `Route::middleware('guest')->group(...)` block.

## Route table

| Method | Path | Name | Handler | Middleware | Purpose |
|---|---|---|---|---|---|
| GET | `/password/forgot` | `password.request` | Livewire SFC `auth.forgot-password` | `guest` | Render the email-entry form |
| GET | `/password/reset/{token}` | `password.reset` | Livewire SFC `auth.reset-password` | `guest`, `signed` | Render the set-new-password form |
| POST (Livewire action) | `/livewire/update` → `forgotPassword` action | — | inside `auth.forgot-password` component | `guest`, throttle via in-action `PasswordResetRateLimiter` | Submit email to request reset |
| POST (Livewire action) | `/livewire/update` → `updatePassword` action | — | inside `auth.reset-password` component | `guest`, throttle via in-action `PasswordResetRateLimiter` | Submit new password |

The POST endpoints are *Livewire component actions*, not HTTP routes. They are reached through the standard `/livewire/update` endpoint Livewire 4 publishes; the contract for *the action itself* is captured in `livewire-components.md`.

## GET `/password/forgot`

**Query params**: none.

**Response**: HTML, status 200. Renders `layouts.guest` with the `auth.forgot-password` Livewire component embedded. The component starts with an empty `$email` field.

**No side effects.**

## GET `/password/reset/{token}`

**Query params**:

- `email` (required, string) — the email address the token was issued for.
- `signature` (required, string) — added by `URL::temporarySignedRoute`; verified by the `signed` middleware before the controller runs. If the signature is missing or invalid, Laravel returns 403 and a generic "Link inválido ou adulterado." page (custom view in `resources/views/errors/403.blade.php` is **not** in scope here; the default Laravel 403 page is acceptable for v1).

**Path params**:

- `token` (required, string) — the plaintext token (the broker bcrypts it on the way in for storage; the URL carries plaintext).

**Behavior of the SFC's `mount()`**:

1. Compute `email_hash` for the `email` param and log `link_opened:accepted` audit event.
2. Pre-fill the form's hidden `$token` and `$email` properties.
3. Do **not** verify the token here — verification happens on form submission so that opening a tampered link writes one consistent `token_rejected:rejected:tampered` audit row only at the submission step.

**Response**: HTML 200 with the new-password form, OR HTTP 403 if signature is invalid.

## Livewire action `forgotPassword` (component `auth.forgot-password`)

**Input properties**:

- `email: string` — required, valid email format, max 254 chars (RFC 5321).

**Validation**: `['email' => 'required|email:rfc,dns|max:254']`. DNS check is optional in dev; enforce only when `app()->isProduction()`. If validation fails, no audit row is written and no state changes — the form just re-renders with inline errors (FR-002 client-side AND server-side: Livewire's `#[Validate]` runs both).

**Side effects (always run, in order)**:

1. Per-IP rate-limit check (20 / hour). On hit: audit `request_throttled:throttled` (no user_id), show neutral "Tente novamente em alguns minutos." string, return.
2. Per-email rate-limit check (5 / hour, key = `sha1(email)`). Same handling on hit.
3. `PasswordResetService::request($email, $request)` is called. This service ALWAYS:
   - Looks up the user (`User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first()`).
   - Writes a `requested:accepted` audit row (with `user_id` if found, else null).
   - If user is found: deletes any existing `password_reset_tokens` row for that email, calls `Password::broker()->sendResetLink(['email' => $email])` which generates the token, persists `bcrypt(token)`, and dispatches the queued `PasswordResetRequested` notification.
   - If user is NOT found: `usleep(10_000)` (10 ms floor) — no token, no notification.
4. Both branches set the same neutral message on the component: `$this->status = __('passwords.sent');` (PT-BR string: "Se uma conta existir para este e-mail, você receberá instruções em instantes.").
5. Both branches clear `$email` to prevent accidental double-submission.

**Response (component state after action)**: `$status` populated, `$email` cleared, no redirect.

## Livewire action `updatePassword` (component `auth.reset-password`)

**Input properties**:

- `token: string` — hidden, set on `mount()`.
- `email: string` — hidden, set on `mount()`.
- `password: string` — required, min 12 chars, must include letter and digit (Laravel's `Password::min(12)->letters()->numbers()`), max 255.
- `passwordConfirmation: string` — required, must equal `password`.

**Validation**: `['password' => ['required', 'confirmed', Password::min(12)->letters()->numbers(), 'max:255'], 'email' => 'required|email', 'token' => 'required|string']`. Validation failure does **not** consume the token (rule: validation runs before broker).

**Side effects (in order)**:

1. Per-IP rate-limit check (20 / hour) on same limiter as the request endpoint — defends against brute-forcing tokens. On hit: audit `request_throttled:throttled`, show generic message, return.
2. Call `PasswordResetService::reset(['email' => $email, 'password' => $password, 'password_confirmation' => $passwordConfirmation, 'token' => $token])`, which wraps `Password::broker()->reset(...)` and:
   - On `Password::PASSWORD_RESET`:
     - Hashes & persists the new password (via the `User` model's `hashed` cast inside the broker's callback).
     - Rotates `users.remember_token` to a fresh `Str::random(60)`.
     - Deletes every row in `sessions` where `user_id = $user->id`.
     - Writes `password_changed:accepted` audit row.
     - Returns success — the component redirects to `route('login')` with a flash session message `__('passwords.reset')` ("Senha atualizada. Faça login com a nova senha.").
   - On `Password::INVALID_TOKEN` / `Password::INVALID_USER`:
     - Writes `token_rejected:rejected:{invalid|expired|consumed}` (reason derived from broker status — see status mapping below).
     - Sets `$this->addError('email', __('passwords.token'))` to surface "Link inválido ou expirado. Solicite um novo e-mail."
     - Does **not** redirect; offers a "Pedir novo link" button that links back to `route('password.request')`.

### Broker status → audit reason mapping

| Broker status constant | Audit `event_type:outcome:reason` | User-facing string |
|---|---|---|
| `Password::PASSWORD_RESET` | `password_changed:accepted` | "Senha atualizada." (redirect to login) |
| `Password::INVALID_TOKEN` | `token_rejected:rejected:invalid` | "Link inválido ou expirado. Solicite um novo e-mail." |
| `Password::INVALID_USER` | `token_rejected:rejected:invalid` | (same) |
| `Password::RESET_THROTTLED` | `request_throttled:throttled` | "Tente novamente em alguns minutos." |

Laravel's broker does not distinguish "expired" from "invalid" in its return; the spec's FR-015 distinct-message requirement is satisfied by the `signed` middleware (which fires earlier and yields a different page for tampered URLs) combined with the broker's not-found-or-expired message. If the platform later needs to distinguish "expired" specifically, the service can pre-read the `password_reset_tokens.created_at` and compare to `now()->subMinutes(60)` to flip the audit reason to `expired`.

## Error response shapes

All user-facing strings come from `lang/pt_BR/passwords.php` and `lang/pt_BR/auth.php`. No JSON error envelope is defined because every endpoint here returns HTML (the request originates from a Livewire form, not from an API client).

## Out-of-scope routes

- Public REST API for password reset — not in scope; mobile clients hit the same Livewire endpoints inside an in-app browser.
- Password-change-while-authenticated (no email link, just current-password challenge) — out of scope per spec.
