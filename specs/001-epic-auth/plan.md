# Implementation Plan: Epic Auth — Autenticação

**Branch**: `001-epic-auth` | **Date**: 2026-05-10 | **Spec**: `specs/001-epic-auth/spec.md`
**Input**: Feature specification from `specs/001-epic-auth/spec.md`

---

## Summary

Auth epic delivers login, email verification, and password reset for two independent user types (Guardian and SchoolAdmin) across three client surfaces (guardian-web, guardian-mobile, school-web) via BFF proxies. API Core (Laravel 13.7 + Sanctum) handles all auth logic; BFFs (NestJS) are thin proxies; frontends (Next.js + React Native) store Bearer tokens.

---

## Technical Context

**Language/Version**: PHP 8.3 (API Core), Node.js/TypeScript (BFFs), TypeScript/React (frontends)
**Primary Dependencies**: Laravel 13.7, Laravel Sanctum, NestJS, Next.js, React Native, Pest
**Storage**: PostgreSQL (users, password resets), Redis (rate limiting), Mailpit (dev email)
**Testing**: Pest (Laravel), Vitest (BFFs), Playwright (web E2E), Detox (mobile E2E)
**Target Platform**: Linux server (API), Node.js server (BFFs), Browser/iOS/Android (frontends)
**Project Type**: Multi-service web + mobile application
**Performance Goals**: Auth endpoints < 200ms p95 (constitution Principle IV)
**Constraints**: Bearer tokens (no cookies), two independent auth guards, email verification mandatory before login
**Scale/Scope**: Two user types × three client surfaces; ~6 auth flows total

---

## Constitution Check

*GATE: Checked before Phase 0 research. Re-checked after Phase 1 design.*

| # | Principle | Pre-Design | Post-Design |
|---|-----------|------------|-------------|
| I | **Code Quality** — Single responsibility, explicit naming, no dead code, linting enforced, no opportunistic refactors, complexity justified. | ✅ | ✅ |
| II | **Testing Standards (TDD)** — Failing test written first; financial paths use real DB integration tests; contract tests for all APIs/events. | ✅ | ✅ |
| III | **UX Consistency** — Design-system components used; `{ data, meta, errors }` envelope; actionable errors; WCAG 2.1 AA; offline guardian flows. | ✅ | ✅ |
| IV | **Performance Requirements** — Sync endpoints < 200ms p95; cross-domain effects via RabbitMQ; Redis cache strategy documented; bulk ops via workers. | ✅ | ✅ |
| V | **Simplicity (YAGNI)** — No speculative abstractions; no feature flags for hypothetical callers; patterns justified over simpler alternatives. | ✅ | ✅ |
| VI | **Module Isolation** — Each module owns its controllers/models/services. No cross-module imports. Cross-module reads via Query Services. Cross-module writes via events. | ✅ | ✅ |

**Justified complexity** (Constitution Principle V documentation):
- New `Administration` module (8th): SchoolAdmin is a distinct bounded context not fitting in any existing module. Simpler alternative (putting SchoolAdmin in `Students` module) rejected because it conflates Guardian management with school administration.

---

## Project Structure

### Documentation (this feature)

```text
specs/001-epic-auth/
├── plan.md          ← This file
├── research.md      ← Phase 0 — tech decisions
├── data-model.md    ← Phase 1 — entities and schema
├── quickstart.md    ← Phase 1 — local dev guide
├── contracts/
│   └── auth-api.yml ← Phase 1 — OpenAPI spec
└── tasks.md         ← Phase 2 output (/speckit-tasks — NOT created here)
```

### Source Code (repository root)

```text
apps/api-laravel/
├── app/
│   └── Modules/
│       ├── Auth/
│       │   ├── Controllers/
│       │   │   ├── GuardianAuthController.php
│       │   │   └── AdminAuthController.php
│       │   ├── Notifications/
│       │   │   ├── GuardianEmailVerificationNotification.php
│       │   │   └── AdminEmailVerificationNotification.php
│       │   ├── Listeners/
│       │   │   ├── SendGuardianEmailVerification.php
│       │   │   └── SendAdminEmailVerification.php
│       │   ├── routes.php
│       │   └── Providers/
│       │       └── AuthServiceProvider.php
│       ├── Students/
│       │   ├── Models/
│       │   │   └── Guardian.php           ← implements MustVerifyEmail, HasApiTokens
│       │   ├── Events/
│       │   │   └── GuardianCreated.php
│       │   └── Providers/
│       │       └── StudentsServiceProvider.php
│       └── Administration/                ← NEW module
│           ├── Models/
│           │   └── SchoolAdmin.php        ← implements MustVerifyEmail, HasApiTokens
│           ├── Events/
│           │   └── SchoolAdminCreated.php
│           └── Providers/
│               └── AdministrationServiceProvider.php
├── config/
│   └── auth.php                          ← updated: guardian + admin guards
└── database/
    └── migrations/
        ├── Students/
        │   └── xxxx_create_guardians_table.php
        ├── Administration/
        │   └── xxxx_create_school_admins_table.php
        └── Auth/
            ├── xxxx_create_guardian_password_resets_table.php
            └── xxxx_create_admin_password_resets_table.php

apps/bff-guardian/                        ← NestJS bootstrap (new)
├── src/
│   ├── auth/
│   │   ├── auth.controller.ts
│   │   ├── auth.service.ts
│   │   └── auth.module.ts
│   └── app.module.ts
└── package.json

apps/bff-school/                          ← NestJS bootstrap (new)
├── src/
│   ├── auth/
│   │   ├── auth.controller.ts
│   │   ├── auth.service.ts
│   │   └── auth.module.ts
│   └── app.module.ts
└── package.json

frontends/guardian-web/                   ← Next.js bootstrap (new)
├── src/
│   └── app/
│       ├── login/page.tsx
│       ├── forgot-password/page.tsx
│       ├── reset-password/page.tsx
│       └── verify-email/page.tsx
└── package.json

frontends/school-web/                     ← Next.js bootstrap (new)
├── src/
│   └── app/
│       ├── login/page.tsx
│       ├── forgot-password/page.tsx
│       ├── reset-password/page.tsx
│       └── verify-email/page.tsx
└── package.json

frontends/guardian-mobile/                ← React Native/Expo bootstrap (new)
├── src/
│   └── screens/
│       ├── LoginScreen.tsx
│       ├── ForgotPasswordScreen.tsx
│       ├── ResetPasswordScreen.tsx
│       └── VerifyEmailScreen.tsx
└── package.json

apps/api-laravel/tests/
├── Feature/
│   └── Modules/
│       ├── Auth/
│       │   ├── GuardianLoginTest.php
│       │   ├── AdminLoginTest.php
│       │   ├── EmailVerificationTest.php
│       │   └── PasswordResetTest.php
│       ├── Students/
│       ├── Administration/
│       ├── Financial/
│       ├── Billing/
│       ├── Contracts/
│       ├── Canteen/
│       └── Commerce/
├── Unit/
│   └── Modules/
│       ├── Auth/
│       │   └── RateLimiterTest.php
│       ├── Students/
│       ├── Administration/
│       ├── Financial/
│       ├── Billing/
│       ├── Contracts/
│       ├── Canteen/
│       └── Commerce/
└── TestCase.php

apps/bff-guardian/tests/
apps/bff-school/tests/

e2e/
├── guardian-web/auth.spec.ts             ← Playwright
└── school-web/auth.spec.ts              ← Playwright
```

**Structure Decision**: Multi-service feature spanning API Core, 2 BFFs, 2 web frontends, 1 mobile frontend. API Core uses modular monolith pattern. BFFs are NestJS monorepo apps. Frontends are Next.js (web) and Expo/React Native (mobile). All services communicate via HTTP; auth tokens are Bearer-only.

---

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| 8th module (Administration) | SchoolAdmin is a distinct bounded context requiring its own module | Placing SchoolAdmin in Students module conflates two separate domain roles (guardian management vs. school administration), violating single responsibility |
