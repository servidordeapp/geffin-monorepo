---

description: "Task list for Password Reset by Email feature implementation"
---

# Tasks: Password Reset by Email ("Esqueci minha senha")

**Input**: Design documents from `/specs/001-password-reset-email/`

**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/ ✅, quickstart.md ✅

**Tests**: REQUIRED. Plan Constitution Check (`plan.md` §Constitution Check, line 55) states "TDD (Boost: tests before implementation) PASS" and `research.md` Decision 10 mandates "(a) write failing Pest test, (b) make it pass" for every implementation slice. Test files are listed in `research.md` Decision 10.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Different file, no dependency on incomplete tasks in same phase — safe to run in parallel
- **[Story]**: User-story tag (US1 = request reset link, US2 = set new password); omitted for Setup / Foundational / Polish phases
- All paths are relative to `laravel/` (the working directory, per `plan.md` §Project Structure)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Bring the local stack into a state where the feature can be developed and tested. No production code yet.

- [ ] T001 Verify `make up` + `make up-workers` start the stack and that `make shell` enters the api-laravel container (per `quickstart.md` §1). If `mailpit` is not exposed on `http://localhost:8025`, document the actual URL in `quickstart.md`.
- [ ] T002 Confirm `php artisan config:show auth.passwords.users.expire` returns `60` (matches FR-006 without a config change, per `research.md` Decision 1). If not, edit `config/auth.php` to set `expire => 60`.
- [ ] T003 [P] Confirm `config/cache.php` default store works for `RateLimiter` (per `research.md` Decision 6). No code change unless missing; only verify.
- [ ] T004 [P] Confirm `config/session.php` `driver => 'database'` and that the `sessions` table exists with a `user_id` column (per `research.md` Decision 5). No code change unless missing; only verify.

**Checkpoint**: Stack is up, queue worker running, broker config matches spec, cache + sessions verified. Implementation can begin.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Build the pieces that both user stories depend on: the audit table & model, the shared rate-limiter, the shared service scaffold, and the localization file skeletons. No story-specific behavior yet.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T005 Create migration `database/migrations/2026_05_23_000000_create_password_reset_audit_events_table.php` with the schema in `data-model.md` §3 (columns `id`, `event_type`, `user_id` nullable FK→users on delete set null, `email_hash` char(64), `ip_address` string(45) nullable, `user_agent` string(255) nullable, `outcome` string(20), `reason` string(80) nullable, `created_at` indexed). Add the three composite indexes listed there. Use `php artisan make:migration --no-interaction create_password_reset_audit_events_table` to scaffold, then edit. Disable `updated_at` (override `up()` with `$table->timestamp('created_at')` only).
- [ ] T006 [P] Create Eloquent model `app/Models/PasswordResetAuditEvent.php` exactly as `data-model.md` §3 "Eloquent Model" prescribes: `const UPDATED_AT = null`, fillable via `#[Fillable]`, `belongsTo(User::class)`, static `emailHash(string $email): string` helper. Use `php artisan make:model PasswordResetAuditEvent --no-interaction`.
- [ ] T007 [P] Create factory `database/factories/PasswordResetAuditEventFactory.php` with sensible defaults for each enum (`event_type='requested'`, `outcome='accepted'`, random IPv4, plausible UA) and state helpers `requested()`, `emailSent()`, `linkOpened()`, `passwordChanged()`, `tokenRejected(string $reason)`, `requestThrottled()`. Use `php artisan make:factory PasswordResetAuditEventFactory --no-interaction --model=PasswordResetAuditEvent`.
- [ ] T008 [P] Create unit test `tests/Unit/Auth/PasswordResetAuditEventTest.php` per `research.md` Decision 10 row 6. Use `php artisan make:test --pest --unit Auth/PasswordResetAuditEventTest --no-interaction`. Cover: (a) `emailHash()` is deterministic + lowercases + trims, (b) row has no `updated_at` even after `touch()`/`save()`, (c) `user_id` accepts null, (d) `event_type` accepts each of the six enum values. Test MUST FAIL until T006 lands.
- [ ] T009 [P] Create `app/Services/Auth/PasswordResetRateLimiter.php` per `research.md` Decision 6: two named limiters (`password-reset:email:{sha1(email)}` 5/h, `password-reset:ip:{ip}` 20/h). Public method signature `hitOrFail(?string $email, string $ip): bool` returning `true` when under both limits, `false` when either is exceeded (does NOT throw — per `livewire-components.md` §1 action step 2 "the wrapper does not throw"). Use `php artisan make:class Services/Auth/PasswordResetRateLimiter --no-interaction`.
- [ ] T010 Create `app/Services/Auth/PasswordResetService.php` SCAFFOLD ONLY (no method bodies yet — the US1 / US2 phases fill them in). Class with three public methods: `request(string $email, \Illuminate\Http\Request $request): void`, `recordLinkOpened(string $email, \Illuminate\Http\Request $request): void`, `reset(string $email, string $token, string $password, \Illuminate\Http\Request $request): string` (returns the broker status constant). Constructor accepts `PasswordResetRateLimiter` via promoted property (per PHP rules in `laravel/CLAUDE.md`). Add `// TODO(status)` marker in a private `resolveAccount()` helper that returns the `User` from `users.email` case-insensitive (per `data-model.md` §1). Use `php artisan make:class Services/Auth/PasswordResetService --no-interaction`.
- [ ] T011 [P] Create lang file skeletons `lang/pt_BR/passwords.php` and `lang/en/passwords.php` with the five required keys (`sent`, `reset`, `throttled`, `token`, `user`) per `livewire-components.md` §Localization keys touched. PT-BR strings exactly as the table prescribes; EN strings are direct equivalents. No `lang/pt_BR/auth.php` strings yet — those are added per-story.
- [ ] T012 [P] Add base `lang/pt_BR/auth.php` and `lang/en/auth.php` if missing (Laravel ships `lang/en/auth.php` only on `--locale=en`; confirm with `ls lang/`). Do NOT add `forgot.*` / `reset.*` / `mail.*` keys yet — those land in their respective stories.
- [ ] T013 Register `App\Services\Auth\PasswordResetRateLimiter` and `App\Services\Auth\PasswordResetService` as singletons in `app/Providers/AppServiceProvider.php` (`bind` or `singleton`; singletons are fine because the limiter has no per-request state — counters live in the cache store). Confirm via `php artisan tinker --execute 'app(App\Services\Auth\PasswordResetService::class);'`.

**Checkpoint**: Audit table + model + factory exist, rate-limiter exists, service scaffold exists, lang files exist. `php artisan migrate` is green. `php artisan test --compact --filter=PasswordResetAuditEventTest` is green. Both user stories can now begin.

---

## Phase 3: User Story 1 — Request password reset link from login screen (Priority: P1) 🎯 MVP

**Goal**: User on the login screen taps "Esqueci minha senha", enters their email, submits, and receives a queued reset email (or nothing, with an identical neutral confirmation, if the email is unknown).

**Independent Test**: Per `spec.md` US1 Independent Test — "Submit the form with a known active email; confirm a generic 'if the address exists you will receive an email' confirmation is shown, and that a reset email is delivered to the inbox containing a valid reset link."

### Tests for User Story 1 (WRITE FIRST, MUST FAIL BEFORE IMPLEMENTATION) ⚠️

- [ ] T014 [P] [US1] Create `tests/Feature/Auth/ForgotPasswordRequestTest.php` per `research.md` Decision 10 row 1. Use `php artisan make:test --pest Auth/ForgotPasswordRequestTest --no-interaction`. Cover (each as its own `it()`): (a) FR-001 link visible on `/login` and points to `route('password.request')`, (b) FR-002 invalid email format shows inline `@error('email')` and writes no audit row, (c) FR-003 known + unknown email return identical `$status` string and component DOM, (d) FR-004 known email triggers `Notification::assertSentTo($user, PasswordResetRequested::class)` exactly once and creates a `password_reset_tokens` row with `bcrypt($token)`, (e) FR-007 second request for same email deletes the prior `password_reset_tokens` row (assert count = 1), (f) one `requested:accepted` audit row per submission (with `user_id` on found, null on not-found). Use `Notification::fake()` and `RefreshDatabase`. ALL ASSERTIONS MUST FAIL initially.
- [ ] T015 [P] [US1] Create `tests/Feature/Auth/PasswordResetEnumerationTest.php` per `research.md` Decision 10 row 3 and SC-005. Loops N=50 each branch (known vs unknown email), measures wall-clock per `Livewire::test()->call('requestReset')`, asserts `p95(unknown) - p95(known) < 50ms` AND `p95(known) - p95(unknown) < 50ms` (symmetric). Use `Queue::fake()` to confirm both branches reach a queue-push code path / floor sleep equally (per `research.md` Decision 3). MUST FAIL initially.
- [ ] T016 [P] [US1] Create `tests/Feature/Auth/PasswordResetRateLimitTest.php` per `research.md` Decision 10 row 4 and FR-011. Cover: (a) 6th request for same email in same hour returns the throttled status string and writes a `request_throttled:throttled` audit row, (b) 21st request from same IP across different emails returns throttled and writes the same audit row, (c) advancing the clock past the hour resets the limiter (use `travel(now()->addHour())`). MUST FAIL initially.

### Implementation for User Story 1

- [ ] T017 [US1] Add `forgot.*` and `mail.reset.subject` keys to `lang/pt_BR/auth.php` and `lang/en/auth.php` per `livewire-components.md` §Localization keys touched (rows `forgot.title`, `forgot.subtitle`, `forgot.submit`, `forgot.back_to_login`) and `notification-mail.md` §Subject contract (`mail.reset.subject`).
- [ ] T018 [P] [US1] Create `app/Notifications/PasswordResetRequested.php` exactly as `contracts/notification-mail.md` §Class prescribes: `implements ShouldQueue`, `use Queueable`, constructor `public string $token`, `via()` returns `['mail']`, `toMail()` builds `URL::temporarySignedRoute('password.reset', now()->addMinutes(60), ['token' => $this->token, 'email' => $notifiable->email])` and a `MailMessage` rendering `mail.auth.password-reset` with `$url`, `$expiresInMinutes = 60`, `$recipientName = $notifiable->name`. Set `public int $tries = 3; public array $backoff = [10, 60, 300];`. Add `// TODO(bounce)` per Decision 7. Use `php artisan make:notification PasswordResetRequested --no-interaction`.
- [ ] T019 [P] [US1] Create Blade mail template `resources/views/mail/auth/password-reset.blade.php` per `contracts/notification-mail.md` §Mail content contract — markdown mail with the 7 numbered requirements (greeting with `$recipientName`, reason sentence, action button to `$url` labelled "Criar nova senha", expiration sentence "Este link expira em {{ $expiresInMinutes }} minutos.", ignore-if-not-requested sentence, brand sign-off, no separate plain-text view). Localize all strings via `__()` so EN fallback works.
- [ ] T020 [US1] Implement `PasswordResetService::request()` in `app/Services/Auth/PasswordResetService.php` per `contracts/http-routes.md` §Livewire action `forgotPassword` step 3. Always: (i) write `requested:accepted` audit row via `PasswordResetAuditEvent::create([...])` with `user_id` if found else null, `email_hash` via `PasswordResetAuditEvent::emailHash($email)`, `ip_address = $request->ip()`, `user_agent = substr($request->userAgent() ?? '', 0, 255)`. (ii) If user found: `DB::table('password_reset_tokens')->where('email', $email)->delete()` (per `research.md` Decision 2 — explicit invalidation, FR-007); then `Password::broker()->sendResetLink(['email' => $email])` — this dispatches the queued `PasswordResetRequested` notification with the freshly generated token. (iii) If not found: `usleep(10_000)` constant-time floor (per `research.md` Decision 3, SC-005). Use `Notification::locale($notifiable->preferred_locale ?? 'pt_BR')`-style locale propagation per `research.md` Decision 4 (notification reads `$notifiable->preferred_locale ?? 'pt_BR'` since the column does not exist today — leave a `// TODO(i18n)` marker).
- [ ] T021 [US1] Create Livewire SFC `resources/views/components/auth/⚡forgot-password.blade.php` per `contracts/livewire-components.md` §1. Sibling reference: `resources/views/components/auth/⚡login.blade.php` — match the file shape exactly (`new #[Layout('layouts.guest')] class extends Component { ... };`, `#[Validate(...)]` attributes). Public properties `$email = ''` with `#[Validate('required|email:rfc|max:254')]` and `?string $status = null`. Action `requestReset()` runs: `$this->validate()` → `app(PasswordResetRateLimiter::class)->hitOrFail($this->email, request()->ip())` (if `false`: set `$this->status = __('passwords.throttled')`, write `request_throttled:throttled` audit row via service, then `return`) → `app(PasswordResetService::class)->request($this->email, request())` → `$this->status = __('passwords.sent')` → `$this->reset('email')`. Template per `contracts/livewire-components.md` §Template requirements: `wire:submit="requestReset"`, `wire:model="email"`, `autofocus`, `novalidate`, inline `@error('email')`, `@if($status)` status block above form, submit button uses `wire:loading.attr="disabled"`, back link to `route('login')`.
- [ ] T022 [US1] Add route `Route::livewire('/password/forgot', 'auth.forgot-password')->name('password.request');` inside the existing `Route::middleware('guest')->group(...)` block in `routes/web.php` per `contracts/http-routes.md` Route table row 1.
- [ ] T023 [US1] Wire the "Esqueci minha senha" anchor on the existing login template at `resources/views/components/auth/⚡login.blade.php` (currently a `<a href="#">` placeholder at the line indicated by `contracts/livewire-components.md` §Login screen wiring). Replace with `<a href="{{ route('password.request') }}" wire:navigate ...>Esqueci minha senha</a>` preserving existing classes / `t-caption`.
- [ ] T024 [US1] Run `php artisan test --compact --filter=ForgotPasswordRequestTest --filter=PasswordResetRateLimitTest --filter=PasswordResetEnumerationTest`. ALL THREE MUST PASS. If `PasswordResetEnumerationTest` is flaky, increase N from 50 to 100 in T015 and re-run. Then `vendor/bin/pint --dirty --format agent`.

**Checkpoint**: US1 is independently shippable. A user can submit the request form and (when their email matches an account) receive the queued email. Unknown emails get the same neutral confirmation. Rate limit + audit log + enumeration defense are all enforced. US2 is not blocked on anything in US1 and can be developed in parallel by a second developer.

---

## Phase 4: User Story 2 — Choose a new password from the reset link (Priority: P1)

**Goal**: User clicks the emailed reset link, lands on the new-password form, submits a policy-compliant password, has all sessions revoked, and is redirected to login with a success message.

**Independent Test**: Per `spec.md` US2 Independent Test — "Open a freshly issued reset link, submit a valid new password, and confirm login succeeds with the new password while the old password is rejected and the same link cannot be reused."

### Tests for User Story 2 (WRITE FIRST, MUST FAIL BEFORE IMPLEMENTATION) ⚠️

- [ ] T025 [P] [US2] Create `tests/Feature/Auth/ResetPasswordTest.php` per `research.md` Decision 10 row 2. Use `php artisan make:test --pest Auth/ResetPasswordTest --no-interaction`. Cover (each as its own `it()`): (a) FR-008 password failing policy (`abc`) shows inline `@error('password')` and writes no audit row, (b) FR-009 successful submit hashes & persists the new password via `users.password` rehash assertion (`Hash::check('NewPass1234ABC', $user->fresh()->password)`), rotates `users.remember_token` to a new 60-char string, deletes all `sessions` where `user_id = $user->id` (seed two session rows first), and writes a `password_changed:accepted` audit row, (c) FR-010 successful submit redirects to `route('login')` and flashes `__('passwords.reset')`, (d) FR-015 reused token returns `Password::INVALID_TOKEN`, writes `token_rejected:rejected:invalid` audit row, sets `$tokenError`, does NOT redirect, (e) FR-015 expired token (insert row with `created_at = now()->subMinutes(61)`) same handling, (f) FR-015 tampered signature (mangle the `signature` query param) returns HTTP 403 before the component runs (use `$this->get($tamperedUrl)->assertForbidden()`). Use `RefreshDatabase`. ALL ASSERTIONS MUST FAIL initially.
- [ ] T026 [P] [US2] Create unit test `tests/Unit/Auth/PasswordResetServiceTest.php` per `research.md` Decision 10 row 5. Cover `PasswordResetService::reset()`: (a) broker `PASSWORD_RESET` path triggers exactly: password rehash, `remember_token` rotation, `DELETE FROM sessions WHERE user_id = ?`, audit row write (use `DB::spy()` / `Notification::fake()` / `PasswordResetAuditEvent` count assertions), (b) broker `INVALID_TOKEN` path writes `token_rejected:rejected:invalid` audit row and does NOT touch users, sessions, remember_token, (c) `recordLinkOpened()` writes `link_opened:accepted` audit row with hashed email. MUST FAIL initially.

### Implementation for User Story 2

- [ ] T027 [US2] Add `reset.*` keys to `lang/pt_BR/auth.php` and `lang/en/auth.php` per `contracts/livewire-components.md` §Localization keys touched (rows `reset.title`, `reset.subtitle`, `reset.password_label`, `reset.password_confirmation_label`, `reset.submit`, `reset.request_new_link`).
- [ ] T028 [US2] Implement `PasswordResetService::recordLinkOpened()` in `app/Services/Auth/PasswordResetService.php` per `contracts/livewire-components.md` §2 mount step 1. Resolve user from email (case-insensitive); write `link_opened:accepted` audit row with `user_id` (nullable) + `email_hash`. No other side effects.
- [ ] T029 [US2] Implement `PasswordResetService::reset()` in `app/Services/Auth/PasswordResetService.php` per `contracts/http-routes.md` §Livewire action `updatePassword` step 2 and `research.md` Decision 5. Wrap a single `DB::transaction()`: call `Password::broker()->reset(['email' => $email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token], function (User $user, string $plain) { $user->forceFill(['password' => $plain])->save(); })` — the model's `hashed` cast rehashes inside the callback (per `data-model.md` §1). On `Password::PASSWORD_RESET`: (i) `$user->forceFill(['remember_token' => Str::random(60)])->save();`, (ii) `DB::table('sessions')->where('user_id', $user->id)->delete();`, (iii) write `password_changed:accepted` audit row. Add `// TODO(sanctum)` comment per `research.md` Decision 5 step 3. On `Password::INVALID_TOKEN` or `Password::INVALID_USER`: write `token_rejected:rejected:invalid` audit row (reason `invalid`). On `Password::RESET_THROTTLED`: write `request_throttled:throttled`. Return the broker status string.
- [ ] T030 [US2] Create Livewire SFC `resources/views/components/auth/⚡reset-password.blade.php` per `contracts/livewire-components.md` §2. Sibling reference: `⚡login.blade.php` + the `⚡forgot-password.blade.php` written in T021. Public properties `$token, $email, $password, $password_confirmation, ?string $tokenError = null` (use `password_confirmation` snake_case to match the broker's `confirmed` rule convention per the contract's "Field mapping for `confirmed`" note). `rules()` method per contract exactly: `password` uses `Illuminate\Validation\Rules\Password::min(12)->letters()->numbers()`. `mount(string $token)` sets `$this->token`, `$this->email = (string) request()->query('email', '')`, calls `app(PasswordResetService::class)->recordLinkOpened($this->email, request())`. Action `updatePassword()` runs: `$this->validate()` → `app(PasswordResetRateLimiter::class)->hitOrFail(null, request()->ip())` (IP-only — per contract; on false set `$tokenError = __('passwords.throttled')` and return) → `$status = app(PasswordResetService::class)->reset($this->email, $this->token, $this->password, request())` → if `Password::PASSWORD_RESET`: `session()->flash('status', __('passwords.reset')); return $this->redirect(route('login'), navigate: true);` else: `$this->tokenError = __('passwords.token');`. Template per contract: hidden inputs for `token` + `email`, both password inputs `type="password" autocomplete="new-password"`, inline `@error('password')` + `@error('password_confirmation')`, `@if($tokenError)` block with "Pedir novo link" anchor to `route('password.request')`.
- [ ] T031 [US2] Add route `Route::livewire('/password/reset/{token}', 'auth.reset-password')->middleware('signed')->name('password.reset');` inside the existing `Route::middleware('guest')->group(...)` block in `routes/web.php` per `contracts/http-routes.md` Route table row 2. Order matters: place AFTER the `/password/forgot` route from T022.
- [ ] T032 [US2] Run `php artisan test --compact --filter=ResetPasswordTest --filter=PasswordResetServiceTest`. BOTH MUST PASS. Then `vendor/bin/pint --dirty --format agent`.

**Checkpoint**: US2 is independently shippable. A user with a freshly issued reset link can set a new password, gets all sessions revoked, is redirected to login. Expired / used / tampered links surface the correct user-facing message and the correct audit row. Combined with US1 the full end-to-end flow works.

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Validate the feature against the spec's success criteria end-to-end and ensure formatting / style compliance before merging.

- [ ] T033 [P] Run the full quickstart walkthrough in `quickstart.md` §3 manually (request reset for seeded `teste@geffin.local`, click the email link in Mailpit, set new password `NewSecurePass123!`, log in with new password, confirm old password fails). Tick every row of the §4 verification table. Any mismatch is a bug in the corresponding implementation task — fix at the source, do not patch in polish.
- [ ] T034 [P] Run the full test suite: `php artisan test --compact`. Whole suite MUST be green (no regressions in pre-existing tests).
- [ ] T035 [P] Run `vendor/bin/pint --dirty --format agent` from the laravel/ directory. Fix anything Pint touches by re-staging.
- [ ] T036 [P] Run `vendor/bin/phpstan analyse --memory-limit=1G` (Larastan v3 is installed per `laravel/CLAUDE.md` §Foundational Context). Address any new errors introduced by this feature; pre-existing errors are out of scope.
- [ ] T037 SC-006 sanity check: log in as a seeded user in two browsers, complete the reset in a third browser, verify both original sessions are bounced to `/login` on next request and `select count(*) from sessions where user_id = ?` returns 0. (Covered by automated test T025 case (b) but worth one manual confirmation before declaring done.)
- [ ] T038 SC-007 retention sanity check: `php artisan tinker --execute 'echo App\Models\PasswordResetAuditEvent::count();'` ≥ 4 after a happy-path walk; document the SQL query for support tickets in `quickstart.md` §4 row "FR-012" (already drafted there — confirm wording is still accurate after implementation).

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational. Independent of US2.
- **User Story 2 (Phase 4)**: Depends on Foundational. Independent of US1 (note: in production the flow is US1 → US2, but they can be implemented in either order — the broker-issued token US2 consumes can be hand-inserted via `DB::table('password_reset_tokens')->insert(...)` in tests until US1 lands).
- **Polish (Phase 5)**: Depends on US1 AND US2 both complete.

### User Story Dependencies

- **US1 (P1)**: Can start after Foundational. No story dependencies.
- **US2 (P1)**: Can start after Foundational. No story dependency on US1 (see note above).

### Within Each User Story

- Tests (T014–T016 for US1; T025–T026 for US2) MUST be written FIRST and MUST FAIL before any implementation task runs (per `research.md` Decision 10 and `laravel/CLAUDE.md` Boost/Pest rules).
- Implementation order within a story: lang strings → notification & mail (US1 only) → service method → Livewire SFC → route → wiring/glue → run tests.
- The Pint format task at the end of each story is non-skippable (per `laravel/CLAUDE.md` §pint/core rules).

### Parallel Opportunities

- All Setup tasks marked [P] (T003, T004) can run in parallel after T001.
- Within Foundational, T006/T007/T008/T009/T011/T012 are all `[P]` (different files, no read-after-write between them). T005 must precede T008 (test asserts the table exists). T010 must follow T005, T006, T009 (service references all three). T013 must follow T009 and T010.
- Within US1, the three test tasks T014/T015/T016 are `[P]` (different files). The notification T018 and the mail template T019 are `[P]` against each other. T021 (component) must follow T017 (lang strings used by template), T018 (notification dispatched by service), and T020 (service method called by component).
- Within US2, T025/T026 are `[P]`. T028 and T029 modify the same file (`PasswordResetService.php`) — they are sequential. T030 must follow T027, T028, T029.
- Polish tasks T033/T034/T035/T036 are `[P]` (different observables).
- **Cross-story parallelism**: after Phase 2 checkpoint, a second developer can take Phase 4 in parallel with Phase 3 — the files touched do not overlap except `PasswordResetService.php` (US1 adds `request()`, US2 adds `recordLinkOpened()` + `reset()`); coordinate via separate commits or a brief rebase.

---

## Parallel Example: User Story 1 Tests

```bash
# Launch all three US1 tests in parallel (different files, no shared state):
Task: "Create tests/Feature/Auth/ForgotPasswordRequestTest.php per research.md Decision 10 row 1"
Task: "Create tests/Feature/Auth/PasswordResetEnumerationTest.php per research.md Decision 10 row 3"
Task: "Create tests/Feature/Auth/PasswordResetRateLimitTest.php per research.md Decision 10 row 4"
```

## Parallel Example: Foundational Phase

```bash
# After T005 (migration) lands, these six are all independent files:
Task: "Create app/Models/PasswordResetAuditEvent.php per data-model.md §3"
Task: "Create database/factories/PasswordResetAuditEventFactory.php with the six state helpers"
Task: "Create tests/Unit/Auth/PasswordResetAuditEventTest.php"
Task: "Create app/Services/Auth/PasswordResetRateLimiter.php"
Task: "Create lang/pt_BR/passwords.php and lang/en/passwords.php with the five keys"
Task: "Ensure lang/pt_BR/auth.php and lang/en/auth.php exist (base files only)"
```

---

## Implementation Strategy

### MVP First (US1 only is NOT viable)

Unlike a typical feature, US1 alone is not user-shippable: the spec's US1 produces an email containing a link that goes nowhere until US2 renders the form. The MVP slice is **US1 + US2 together**, in that order or in parallel:

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational (CRITICAL — blocks both stories).
3. Complete Phase 3: US1 — request form + email.
4. Complete Phase 4: US2 — reset form + session purge.
5. Complete Phase 5: Polish — manual quickstart, full test run, Pint, Larastan.
6. Deploy.

### Incremental Delivery Within the Stories

Even though both stories must land together to be user-shippable, each story is independently testable: US1's tests assert email is queued and audit rows are written without ever needing the reset form to render. US2's tests can hand-insert a token row to exercise the reset flow without ever going through the request form. This is what lets two developers work the stories in parallel.

### Parallel Team Strategy

1. Whole team: Phase 1 + Phase 2 together (one person can comfortably do it; ~½ day).
2. Once Foundational checkpoint passes:
   - Dev A: Phase 3 (US1).
   - Dev B: Phase 4 (US2).
3. Both devs together: Phase 5 (Polish).

---

## Notes

- `[P]` = different files, no dependencies on incomplete tasks in same phase.
- `[Story]` label maps each task to the US it advances (US1 / US2). Setup, Foundational, Polish carry no story label.
- Every test task MUST FAIL when first written. Run it once with `php artisan test --compact --filter=<NewTest>` before writing the corresponding implementation task — if it passes immediately, the test is testing the wrong thing.
- Audit rows are facts, not state. Never `update()` a `PasswordResetAuditEvent` row; always `create()` a new one.
- Sensitive material (plaintext token, plaintext password, password hash) NEVER appears in audit rows or logs. The audit row stores `email_hash`, not `email`.
- Pint is mandatory at the end of each story phase (per `laravel/CLAUDE.md` §pint/core rules). Do NOT skip with `--no-verify` or `--no-edit`.
- All Livewire components are single-file (SFC) under `resources/views/components/auth/` with the `⚡` filename prefix, per the existing `⚡login.blade.php` convention (and `research.md` Decision 9).
- The `users.preferred_locale` column does NOT exist today — the notification reads it as `$notifiable->preferred_locale ?? 'pt_BR'`. Leaving a `// TODO(i18n)` marker per Decision 8 is non-negotiable.
- The `users.status` column does NOT exist today — every user is treated as active. The `// TODO(status)` marker in `PasswordResetService::resolveAccount()` is non-negotiable (per `data-model.md` §1 account-status note).
- Sanctum personal-access-token revocation is out of scope — `// TODO(sanctum)` marker in `PasswordResetService::reset()` is non-negotiable (per `research.md` Decision 5 step 3).
