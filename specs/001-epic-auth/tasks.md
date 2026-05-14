# Tasks: Epic Auth — Autenticação

**Input**: Design documents from `specs/001-epic-auth/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/auth-api.yml ✅, quickstart.md ✅

**Organization**: Tasks grouped by user story. Multi-service: API Core (Laravel/Pest), BFF Guardian (NestJS), BFF School (NestJS), guardian-web (Next.js), school-web (Next.js), guardian-mobile (Expo/React Native).

**TDD mandatory** (constitution Principle II): test tasks written and confirmed failing before implementation tasks.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable — different files, no incomplete dependencies
- **[Story]**: User story label (US1, US2, US3, US4)
- Exact file paths in all task descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: API Core foundation — Sanctum, module scaffolding, guard config, rate limiters

- [X] T001 Install Laravel Sanctum and publish vendor files in `apps/api-laravel/` (`composer require laravel/sanctum` + `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`)
- [X] T002 [P] Create Auth module directory structure in `apps/api-laravel/app/Modules/Auth/` (Controllers/, Notifications/, Listeners/, Requests/, routes.php, Providers/)
- [X] T003 [P] Create Administration module directory structure in `apps/api-laravel/app/Modules/Administration/` (Models/, Events/, Providers/)
- [X] T004 Create `apps/api-laravel/app/Modules/Auth/Providers/AuthServiceProvider.php` and register in `apps/api-laravel/config/app.php`
- [X] T005 [P] Create `apps/api-laravel/app/Modules/Administration/Providers/AdministrationServiceProvider.php` and register in `apps/api-laravel/config/app.php`
- [X] T006 Update `apps/api-laravel/config/auth.php` with guardian + admin guards, providers (eloquent), and password brokers (`guardian_password_resets` 60 min, `admin_password_resets` 60 min)
- [X] T007 Register Guardian and Admin rate limiters in `apps/api-laravel/app/Providers/AppServiceProvider.php` (Redis-backed: `login.guardian` + `login.admin` at 5/15 min by email+IP; `resend.guardian` + `resend.admin` at 1/min by user ID)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Migrations, models, domain events, seeder. ALL tasks complete before any user story.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T008 Create migration `apps/api-laravel/database/migrations/Students/xxxx_create_guardians_table.php` (uuid PK via `gen_random_uuid()`, name varchar(255), email varchar(255) unique, email_verified_at timestamp nullable, password varchar(255), active bool default true, timestamps)
- [X] T009 [P] Create migration `apps/api-laravel/database/migrations/Administration/xxxx_create_school_admins_table.php` (identical schema to guardians)
- [X] T010 [P] Create migration `apps/api-laravel/database/migrations/Auth/xxxx_create_guardian_password_resets_table.php` (email varchar PK, token varchar(255), created_at timestamp)
- [X] T011 [P] Create migration `apps/api-laravel/database/migrations/Auth/xxxx_create_admin_password_resets_table.php` (same schema as guardian_password_resets)
- [X] T012 Create `apps/api-laravel/app/Modules/Students/Models/Guardian.php` implementing `MustVerifyEmail` and `HasApiTokens` (Sanctum), with `active` field, `$casts` for uuid and timestamps
- [X] T013 [P] Create `apps/api-laravel/app/Modules/Administration/Models/SchoolAdmin.php` implementing `MustVerifyEmail` and `HasApiTokens`, same pattern as Guardian
- [X] T014 Create `apps/api-laravel/app/Modules/Students/Events/GuardianCreated.php` domain event (carries Guardian model)
- [X] T015 [P] Create `apps/api-laravel/app/Modules/Administration/Events/SchoolAdminCreated.php` domain event (carries SchoolAdmin model)
- [X] T016 Register migration paths from module subdirectories in respective ServiceProviders using `loadMigrationsFrom()` (Students, Administration, Auth)
- [X] T017 Create `apps/api-laravel/database/seeders/AuthSeeder.php` with: verified guardian (`guardian@test.com` / `password`, `email_verified_at = now()`), unverified guardian (`unverified@test.com` / `password`, `email_verified_at = null`), verified admin (`admin@test.com` / `password`)

**Checkpoint**: `make migrate` succeeds — 5 new tables created. `make artisan "db:seed --class=AuthSeeder"` creates 3 test users.

---

## Phase 3: User Story 1 — Login do Responsável (P1) 🎯 MVP

**Goal**: Guardian login/logout on guardian-web (Next.js) and guardian-mobile (Expo) via bff-guardian proxy. Rate limiting after 5 failed attempts. Unverified email blocks login.

**Independent Test**: `guardian@test.com` / `password` → 200 + token. `unverified@test.com` → 403 `EMAIL_NOT_VERIFIED`. 5 bad passwords → 429 `TOO_MANY_ATTEMPTS`. Logout → 200. Revoked token reuse → 401.

### Tests for User Story 1 (TDD — write first, confirm failure before T020)

- [X] T018 [US1] Write `apps/api-laravel/tests/Feature/Modules/Auth/GuardianLoginTest.php` covering: successful login returns `{ data: { token, user } }` (200), invalid password returns `INVALID_CREDENTIALS` (401), unverified email returns `EMAIL_NOT_VERIFIED` (403), inactive account returns `ACCOUNT_INACTIVE` (403), 6th attempt returns `TOO_MANY_ATTEMPTS` (429), authenticated logout returns 200, unauthenticated logout returns 401
- [X] T019 [P] [US1] Write `apps/api-laravel/tests/Unit/Modules/Auth/RateLimiterTest.php` covering: limiter key format (`email|ip`), threshold 5, decay 15 min, resend key (`user:{id}`), resend threshold 1/min

### API Implementation — User Story 1

- [X] T020 [US1] Implement login + logout in `apps/api-laravel/app/Modules/Auth/Controllers/GuardianAuthController.php` (guard: `Auth::guard('guardian')`, check `active` + `hasVerifiedEmail()` before token issue, throttle via `RateLimiter`, return `{ data: { token, user } }`)
- [X] T021 [US1] Create `apps/api-laravel/app/Modules/Auth/Requests/GuardianLoginRequest.php` (email: required email format max 255; password: required string)
- [X] T022 [US1] Register guardian login + logout routes in `apps/api-laravel/app/Modules/Auth/routes.php` (`POST /api/v1/guardian/auth/login` unauthenticated, `POST /api/v1/guardian/auth/logout` behind `auth:guardian`)

### BFF Guardian — User Story 1

- [X] T023 [US1] Bootstrap bff-guardian NestJS app in `apps/bff-guardian/` (`package.json` with `@nestjs/core` `@nestjs/common` `@nestjs/axios` `axios`; `src/main.ts`; `src/app.module.ts`)
- [X] T024 [P] [US1] Implement bff-guardian auth proxy in `apps/bff-guardian/src/auth/` (`auth.controller.ts` POST /auth/login + /auth/logout, `auth.service.ts` forwarding to `http://api:8000/api/v1/guardian/auth/*`, `auth.module.ts` with HttpModule)

### Frontend Guardian Web — User Story 1

- [X] T025 [US1] Bootstrap guardian-web Next.js app in `frontends/guardian-web/` (`package.json`, `next.config.ts`, `src/app/layout.tsx`, `src/app/page.tsx`, `tsconfig.json`)
- [X] T026 [P] [US1] Implement `frontends/guardian-web/src/app/login/page.tsx` (email + password form, POST to bff-guardian `/auth/login`, store token in `localStorage`, redirect to `/dashboard`, show `EMAIL_NOT_VERIFIED` with resend link on 403)

### Frontend Guardian Mobile — User Story 1

- [X] T027 [US1] Bootstrap guardian-mobile Expo app in `frontends/guardian-mobile/` (`package.json`, `app.json`, `App.tsx`, install `expo-secure-store`)
- [X] T028 [P] [US1] Implement `frontends/guardian-mobile/src/screens/LoginScreen.tsx` (email + password fields, POST to bff-guardian, store token via `SecureStore.setItemAsync('auth_token', token)`, navigate to Dashboard)

**Checkpoint**: GuardianLoginTest + RateLimiterTest pass. Smoke: curl guardian login (quickstart step 5), guardian-web login page renders.

---

## Phase 4: User Story 2 — Login do Administrador Escolar (P1)

**Goal**: SchoolAdmin login/logout on school-web (Next.js) via bff-school proxy. Same rate-limiting and email-verification rules.

**Independent Test**: `admin@test.com` / `password` → 200 + token. Unverified admin → 403. 5 bad logins → 429. Logout → 200.

### Tests for User Story 2 (TDD — write first, confirm failure before T030)

- [X] T029 [US2] Write `apps/api-laravel/tests/Feature/Modules/Auth/AdminLoginTest.php` covering: successful login (200), invalid credentials (401), unverified email (403), inactive account (403), rate limit lockout (429), successful logout (200)

### API Implementation — User Story 2

- [X] T030 [US2] Implement login + logout in `apps/api-laravel/app/Modules/Auth/Controllers/AdminAuthController.php` (guard: `Auth::guard('admin')`, same pattern as GuardianAuthController)
- [X] T031 [US2] Create `apps/api-laravel/app/Modules/Auth/Requests/AdminLoginRequest.php` (same validation as GuardianLoginRequest)
- [X] T032 [US2] Register admin login + logout routes in `apps/api-laravel/app/Modules/Auth/routes.php` (`POST /api/v1/admin/auth/login`, `POST /api/v1/admin/auth/logout` behind `auth:admin`)

### BFF School — User Story 2

- [X] T033 [US2] Bootstrap bff-school NestJS app in `apps/bff-school/` (`package.json`, `src/main.ts`, `src/app.module.ts`)
- [X] T034 [P] [US2] Implement bff-school auth proxy in `apps/bff-school/src/auth/` (`auth.controller.ts`, `auth.service.ts` forwarding to `http://api:8000/api/v1/admin/auth/*`, `auth.module.ts`)

### Frontend School Web — User Story 2

- [X] T035 [US2] Bootstrap school-web Next.js app in `frontends/school-web/` (`package.json`, `next.config.ts`, `src/app/layout.tsx`)
- [X] T036 [P] [US2] Implement `frontends/school-web/src/app/login/page.tsx` (login form, POST to bff-school `/auth/login`, store token in `localStorage`, redirect to `/dashboard`)

**Checkpoint**: AdminLoginTest pass. Smoke: curl admin login (quickstart step 6), school-web login page renders.

---

## Phase 5: User Story 3 — Confirmação de E-mail (P2)

**Goal**: Guardian and SchoolAdmin receive email verification link on creation (144h TTL, signed URL). Resend rate-limited 1/min. Login blocked until verified.

**Independent Test**: Unverified guardian → login 403. Mailpit email → click link → 200. Login → 200. Click again → 400 `EMAIL_ALREADY_VERIFIED`. Resend twice in 1 min → 429.

### Tests for User Story 3 (TDD — write first, confirm failure before T038)

- [X] T037 [US3] Write `apps/api-laravel/tests/Feature/Modules/Auth/EmailVerificationTest.php` covering: `GuardianCreated` triggers verification email, valid signed URL verifies (200), expired link returns `LINK_EXPIRED` (400), already-verified returns `EMAIL_ALREADY_VERIFIED` (400), resend sends email (200), resend when verified returns 400, second resend within 1 min returns 429, `SchoolAdminCreated` admin equivalents

### API Implementation — User Story 3

- [X] T038 [US3] Create `apps/api-laravel/app/Modules/Auth/Notifications/GuardianEmailVerificationNotification.php` (extend `Illuminate\Auth\Notifications\VerifyEmail`, override `verificationUrl()` with `URL::temporarySignedRoute()` at 144h TTL to guardian verify endpoint)
- [X] T039 [P] [US3] Create `apps/api-laravel/app/Modules/Auth/Notifications/AdminEmailVerificationNotification.php` (same pattern, admin verify endpoint)
- [X] T040 [US3] Create `apps/api-laravel/app/Modules/Auth/Listeners/SendGuardianEmailVerification.php` (handles `GuardianCreated`, calls `$event->guardian->notify(new GuardianEmailVerificationNotification())`)
- [X] T041 [P] [US3] Create `apps/api-laravel/app/Modules/Auth/Listeners/SendAdminEmailVerification.php` (handles `SchoolAdminCreated`)
- [X] T042 [US3] Register event→listener bindings in `apps/api-laravel/app/Modules/Auth/Providers/AuthServiceProvider.php` (`GuardianCreated` → `SendGuardianEmailVerification`, `SchoolAdminCreated` → `SendAdminEmailVerification`)
- [X] T043 [US3] Add `verifyEmail` + `resendVerification` to `apps/api-laravel/app/Modules/Auth/Controllers/GuardianAuthController.php` (`verifyEmail`: validate signed URL, mark verified; `resendVerification`: throttle `resend.guardian`, check not verified, re-notify)
- [X] T044 [P] [US3] Add `verifyEmail` + `resendVerification` to `apps/api-laravel/app/Modules/Auth/Controllers/AdminAuthController.php` (same with `admin` guard + `resend.admin` limiter)
- [X] T045 [US3] Register verify-email + resend routes in `apps/api-laravel/app/Modules/Auth/routes.php` (`GET /api/v1/guardian/auth/verify-email/{id}/{hash}` signed middleware, `POST /api/v1/guardian/auth/resend-verification` behind `auth:guardian`; admin equivalents)

### Frontend — User Story 3

- [X] T046 [P] [US3] Implement `frontends/guardian-web/src/app/verify-email/page.tsx` (handles signed URL redirect, calls BFF, shows success or error with resend option)
- [X] T047 [P] [US3] Implement `frontends/guardian-mobile/src/screens/VerifyEmailScreen.tsx` (handles deep link, calls BFF, shows confirmation)
- [X] T048 [P] [US3] Implement `frontends/school-web/src/app/verify-email/page.tsx` (same pattern as guardian-web)

**Checkpoint**: EmailVerificationTest pass. Smoke test quickstart step 7: unverified user → Mailpit → click link → login succeeds.

---

## Phase 6: User Story 4 — Recuperação de Senha (P3)

**Goal**: Guardian and SchoolAdmin reset forgotten password via email link (60 min TTL, single-use). All Sanctum tokens revoked on reset. Generic response regardless of email existence (FR-007).

**Independent Test**: Request reset `guardian@test.com` → generic 200. Mailpit → link → new password → 200. Login new password → 200. Old token → 401. Reuse link → 422 `INVALID_RESET_TOKEN`. Unknown email → same generic 200.

### Tests for User Story 4 (TDD — write first, confirm failure before T050)

- [X] T049 [US4] Write `apps/api-laravel/tests/Feature/Modules/Auth/PasswordResetTest.php` covering: forgot-password generic 200 for valid email, generic 200 for unknown email (no information leak), valid link updates password (200), link invalidated after use (422 on reuse), expired link (422 `INVALID_RESET_TOKEN`), all Sanctum tokens revoked after reset, admin broker equivalents

### API Implementation — User Story 4

- [X] T050 [US4] Add `forgotPassword` + `resetPassword` to `apps/api-laravel/app/Modules/Auth/Controllers/GuardianAuthController.php` (broker: `Password::broker('guardians')`; `forgotPassword` always returns generic message; `resetPassword` calls `$user->tokens()->delete()` before 200)
- [X] T051 [P] [US4] Add `forgotPassword` + `resetPassword` to `apps/api-laravel/app/Modules/Auth/Controllers/AdminAuthController.php` (broker: `Password::broker('admins')`)
- [X] T052 [US4] Create `apps/api-laravel/app/Modules/Auth/Requests/GuardianForgotPasswordRequest.php` (email: required email format) and `GuardianResetPasswordRequest.php` (token: required, email: required, password: required min 8, password_confirmation: required matches)
- [X] T053 [P] [US4] Create `apps/api-laravel/app/Modules/Auth/Requests/AdminForgotPasswordRequest.php` and `AdminResetPasswordRequest.php` (same rules)
- [X] T054 [US4] Register password reset routes in `apps/api-laravel/app/Modules/Auth/routes.php` (`POST /api/v1/guardian/auth/forgot-password`, `POST /api/v1/guardian/auth/reset-password` unauthenticated; admin equivalents)

### Frontend — User Story 4

- [X] T055 [P] [US4] Implement `frontends/guardian-web/src/app/forgot-password/page.tsx` (email form, POST to BFF, generic confirmation) and `frontends/guardian-web/src/app/reset-password/page.tsx` (new password form, reads token+email from URL params, POST to BFF, redirect to login)
- [X] T056 [P] [US4] Implement `frontends/guardian-mobile/src/screens/ForgotPasswordScreen.tsx` and `frontends/guardian-mobile/src/screens/ResetPasswordScreen.tsx`
- [X] T057 [P] [US4] Implement `frontends/school-web/src/app/forgot-password/page.tsx` and `frontends/school-web/src/app/reset-password/page.tsx`

**Checkpoint**: PasswordResetTest pass. Smoke test quickstart step 8: full reset flow, old sessions rejected.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: HTML email templates, E2E tests, isolation check, final smoke

- [X] T058 [P] Create `apps/api-laravel/resources/views/emails/auth/verify-email.blade.php` (branded layout, verification button with signed URL, plain-text fallback)
- [X] T059 [P] Create `apps/api-laravel/resources/views/emails/auth/reset-password.blade.php` (branded layout, reset button, 60-min expiry notice)
- [X] T060 [P] Write Playwright E2E `e2e/guardian-web/auth.spec.ts` (happy-path login, wrong-password error, unverified-email warning with resend, logout clears session)
- [X] T061 [P] Write Playwright E2E `e2e/school-web/auth.spec.ts` (happy-path admin login, logout)
- [X] T062 Run full quickstart.md validation — execute all 9 scenarios (steps 5–9), confirm expected responses
- [X] T063 [P] Verify module isolation — grep Auth, Students, Administration modules for cross-module imports (`Modules\Financial`, `Modules\Billing`, `Modules\Contracts`, `Modules\Canteen`, `Modules\Commerce`); zero results required

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: No dependencies — start immediately
- **Phase 2 (Foundational)**: Depends on Phase 1 — BLOCKS all user story phases
- **Phase 3 (US1)**: Depends on Phase 2 — independent from Phases 4, 5, 6
- **Phase 4 (US2)**: Depends on Phase 2 — independent from Phases 3, 5, 6
- **Phase 5 (US3)**: Depends on Phase 2 — adds methods to controllers from Phase 3/4 (coordinate edits)
- **Phase 6 (US4)**: Depends on Phase 2 — same controllers (coordinate edits with US3)
- **Phase 7 (Polish)**: Depends on all story phases complete

### Within Each Phase

1. TDD: test tasks written and failing **before** implementation
2. Models before controllers
3. Controllers before routes
4. API routes before BFF
5. BFF before frontend

### User Story Dependencies

- **US1 (P1)**: Independent — start after Phase 2
- **US2 (P1)**: Independent from US1 — parallel after Phase 2
- **US3 (P2)**: Adds to US1/US2 controllers — coordinate edits
- **US4 (P3)**: Same controllers — coordinate edits with US3

---

## Parallel Execution Examples

### Phase 2 Parallel Window

```
Start simultaneously:
  T009 — SchoolAdmin migration
  T010 — guardian_password_resets migration
  T011 — admin_password_resets migration
  T013 — SchoolAdmin model
  T015 — SchoolAdminCreated event

After T004+T005 (ServiceProviders registered):
  T016 — Register migration paths
  T017 — AuthSeeder
```

### Phase 3 (US1) Parallel Window

```
T018 (tests, confirm failing) → T020 → T021 → T022

After T022:
  Branch A: T023 → T024 (BFF)
  Branch B: T025 → T026 (guardian-web)
  Branch C: T027 → T028 (guardian-mobile)
```

### Phase 5 (US3) Parallel Window

```
T037 (tests, confirm failing)
Parallel: T038 + T039 (notifications)
Parallel: T040 + T041 (listeners)
Then: T042 (bindings)
Parallel: T043 + T044 (controller additions)
Then: T045 (routes)
Parallel: T046 + T047 + T048 (frontends)
```

---

## Implementation Strategy

### MVP Scope (US1 + US2 — Phases 1–4)

1. Phase 1 → Phase 2 → Phase 3 → Phase 4
2. Demo: guardian logs in, admin logs in

> Seed users with `email_verified_at` pre-set for MVP. US3 not in MVP but login already enforces the field.

### Incremental Delivery

1. Phase 1+2 → foundation
2. +Phase 3 → guardian login MVP ✓
3. +Phase 4 → admin login MVP ✓
4. +Phase 5 → email verification ✓
5. +Phase 6 → password reset ✓
6. +Phase 7 → E2E + polish ✓

### Parallel Team Strategy

After Phase 2:
- Dev A: Phase 3 (US1 guardian full stack)
- Dev B: Phase 4 (US2 admin full stack)
- Dev C: Phase 5 (US3 email verification — coordinate controller edits with A+B)

---

## Notes

- `[P]` = different files, no incomplete deps — safe to parallelize
- Pest tests (T018, T019, T029, T037, T049) must fail before implementation starts
- `active` check in login (T020, T030) is part of US1/US2 implementation — not a separate task
- Laravel password broker handles reset token hashing — no custom logic needed
- `$user->tokens()->delete()` (T050, T051) satisfies SC-007 (all sessions revoked on reset)
- BFF apps are thin proxies — no token validation in bff-guardian or bff-school (research Decision 7)
- Auth controllers access models via `Auth::guard()` (framework) — not direct imports from Students/Administration (module isolation rule)
