# Quickstart — Password Reset by Email

How to bring this feature up locally, exercise the happy path end-to-end, and verify each acceptance criterion. Assumes you have the monorepo's `make up` stack running.

## 1. Bring the stack up

From repo root:

```bash
make up                 # postgres, redis, rabbitmq, minio, nginx, php-fpm, mailpit, etc.
make up-workers         # add the queue:listen worker — REQUIRED, because the notification is queued
make shell              # bash into the api-laravel container
```

Inside the container:

```bash
php artisan migrate     # applies the new password_reset_audit_events migration
php artisan config:show mail.from  # sanity-check the sender
```

The `make fresh` target re-runs migrations from scratch + seeds. Use it if you want a clean slate.

## 2. Seed a test user

```bash
php artisan tinker --execute 'App\Models\User::factory()->create(["email" => "teste@geffin.local", "password" => "OldPass1234ABC"]);'
```

(`password` field will be re-hashed automatically by the `hashed` cast.)

## 3. Walk the happy path manually

1. Open `http://localhost` (the nginx default site routes to Laravel).
2. Click "Esqueci minha senha" on the login screen.
3. On `/password/forgot`, enter `teste@geffin.local` and submit. You should see the neutral confirmation message and the email field cleared.
4. Open Mailpit at the URL printed by `make minio-ui`-style helper (mailpit's default is `http://localhost:8025`). You should see a "Redefinição de senha — Geffin" message with a "Criar nova senha" button.
5. Click the button. Browser lands on `/password/reset/{token}?email=teste@geffin.local&signature=...` and shows the new-password form.
6. Enter `NewSecurePass123!` in both fields and submit.
7. You are redirected to `/login` with a green status: "Senha atualizada. Faça login com a nova senha."
8. Log in with the new password — succeeds. Try the old password — fails.

## 4. Verify each acceptance criterion

| Spec ref | How to verify |
|---|---|
| FR-001 (affordance visible) | The "Esqueci minha senha" link on `/login` is now a real anchor pointing at `route('password.request')`. |
| FR-002 (validation client + server) | Submit the request form with `not-an-email`. Livewire renders inline error; no audit row written. |
| FR-003 (neutral response, no enumeration) | Submit with `nobody@example.com` (unseeded). UI shows identical confirmation; mailpit is empty; one `requested:accepted` audit row exists with `user_id IS NULL`. |
| FR-004 (single-use, hashed, time-limited) | After step 6, `select * from password_reset_tokens` returns 0 rows for `teste@geffin.local`. |
| FR-005 (branded, localized, mentions expiry + ignore) | Inspect mailpit: subject is PT-BR, body has the 60-minute notice and the "ignore this email" sentence. |
| FR-006 (60-min expiry) | `php artisan tinker --execute 'DB::table("password_reset_tokens")->insert(["email"=>"teste@geffin.local","token"=>bcrypt("xx"),"created_at"=>now()->subMinutes(61)]);'` then visit a reset URL using the token `xx` (and a freshly-signed URL via `URL::temporarySignedRoute`); broker should reject as invalid/expired and an `token_rejected:rejected:invalid` audit row appears. |
| FR-007 (new request invalidates prior) | Request reset twice in succession; verify only one row in `password_reset_tokens` for that email and that the first emailed token no longer works. |
| FR-008 (password policy enforced) | Try `abc` as the new password; inline error "deve ter pelo menos 12 caracteres". |
| FR-009 (sessions revoked + audit written) | Log in in two browsers as `teste@geffin.local`. Do a reset from a third browser. Both original sessions return to `/login` on next request. `select count(*) from sessions where user_id = ?` returns 0. A `password_changed:accepted` audit row exists. |
| FR-010 (redirect to login w/ confirmation) | Step 7 confirms. |
| FR-011 (rate limit) | Hit the request form six times in a row for `teste@geffin.local` from the same IP. Sixth attempt returns the throttled message and writes a `request_throttled:throttled` audit row. |
| FR-012 (audit log captures everything, no secrets) | `select event_type, outcome, reason from password_reset_audit_events order by id desc limit 20;` — verify the expected sequence. Verify no column contains a token or password. |
| FR-013 (works for every email/password user) | Seed users with different roles (when role column exists later); for now any seeded user works. |
| FR-014 (web + mobile) | The reset URL is a normal HTTPS URL; opening it in the iOS simulator's Safari or in React Native's in-app browser renders the same form. |
| FR-015 (distinct rejections) | Test cases: (a) tamper the signature param in the URL → Laravel's `signed` middleware returns 403. (b) Open a reset URL after using it → broker returns `INVALID_TOKEN` → component shows "Link inválido ou expirado". |
| SC-005 (under 50ms enumeration delta) | Run `tests/Feature/Auth/PasswordResetEnumerationTest.php` which loops N=50 each branch and asserts p95 delta < 50 ms. |
| SC-006 (sessions revoked within 5s) | The test asserts `Bus::dispatchedAfterResponse` is empty and that the `DELETE` happens synchronously inside the same request. |
| SC-007 (audit rows persisted) | `select count(*) from password_reset_audit_events` after the happy-path walk returns ≥ 4 rows. |

## 5. Run the test suite

```bash
php artisan test --compact --filter=Auth   # only this feature's tests
php artisan test --compact                 # full suite — should remain green
vendor/bin/pint --dirty --format agent     # auto-format anything we edited
```

## 6. Known gaps to address in follow-up features

- **`users.status` column** (active/suspended/deactivated) does not exist; we treat every user as active. `PasswordResetService::resolveAccount()` has the only hook that will need updating.
- **`users.preferred_locale` column** does not exist; every user gets PT-BR.
- **Mail bounce webhook**: no provider configured; bounces are not surfaced into `password_reset_audit_events` as `email_sent:failed` yet.
- **Sanctum personal-access-tokens**: not yet installed; when added, `PasswordResetService::reset()` must also revoke `personal_access_tokens` rows for the user.
- **`audit_events` cross-cutting table**: this feature ships a dedicated table; consolidation into a generic sink is a separate ticket.

These are captured as `// TODO(...)` comments in the relevant files so they are visible to grep.
