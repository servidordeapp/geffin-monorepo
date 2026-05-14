# Research: Epic Auth — Authentication

**Branch**: `001-epic-auth` | **Date**: 2026-05-10 | **Phase**: 0

---

## Decision 1: Auth Token Mechanism

**Decision**: Laravel Sanctum with Bearer tokens (personal access tokens)

**Rationale**: Sanctum is Laravel's official SPA/mobile token package. Supports Bearer tokens natively, no OAuth overhead. Laravel 13.7 needs `composer require laravel/sanctum`.

**Alternatives considered**:
- Laravel Passport: OAuth2 server — too heavy for internal apps with no third-party delegation. Rejected (YAGNI).
- Custom JWT (firebase/php-jwt): loses Laravel's auth integration, more surface area. Rejected.
- Session cookies: spec explicitly mandates Bearer tokens. Rejected.

---

## Decision 2: Multiple Authenticatable Models

**Decision**: Custom Sanctum guards per user type (`guardian`, `admin`)

**Rationale**: Two separate user entities (Guardian, SchoolAdmin) with independent tables and endpoints. Sanctum supports multiple models via `HasApiTokens` trait + guard configuration in `config/auth.php`. Framework-level guard config avoids direct cross-module imports — Auth module controllers call `Auth::guard('guardian')` / `Auth::guard('admin')` which is a framework access, not a module import.

**Config pattern**:
```php
// config/auth.php
'guards' => [
    'guardian' => ['driver' => 'sanctum', 'provider' => 'guardians'],
    'admin'    => ['driver' => 'sanctum', 'provider' => 'admins'],
],
'providers' => [
    'guardians' => ['driver' => 'eloquent', 'model' => App\Modules\Students\Models\Guardian::class],
    'admins'    => ['driver' => 'eloquent', 'model' => App\Modules\Administration\Models\SchoolAdmin::class],
],
```

**Alternatives considered**:
- Single User model with `type` discriminator: conflates two bounded contexts, complicates module isolation. Rejected.

---

## Decision 3: Rate Limiting / Account Lockout

**Decision**: Laravel `RateLimiter` (Redis-backed) — per email+IP; 5 attempts, 15-minute decay

**Rationale**: Redis-backed rate limiting is stateless (no DB column needed), survives horizontal scaling, and is idempotent. Laravel 13 `RateLimiter::for` in `AppServiceProvider`. Decay 15 min satisfies "temporary block" without permanent lock.

**Implementation**:
```php
RateLimiter::for('login.guardian', function (Request $request) {
    return Limit::perMinutes(15, 5)->by($request->input('email').'|'.$request->ip());
});
```

**Alternatives considered**:
- DB columns (`login_attempts`, `locked_until`): requires schema changes, doesn't survive horizontal scale. Rejected.

---

## Decision 4: Email Verification

**Decision**: Laravel built-in `MustVerifyEmail` + `URL::temporarySignedRoute` with 144h TTL

**Rationale**: Stateless signed URLs — no extra table, no token management. `auth.verification.expire` = `8640` (minutes = 144h).

Resend protection: `throttle:6,1` on resend endpoint (FR-006).

**Alternatives considered**:
- Custom verification token table: reinvents signed URLs with extra DB complexity. Rejected.

---

## Decision 5: Password Reset

**Decision**: Laravel password broker, 60-min TTL, separate tables per user type

**Rationale**: `Password::broker('guardians')` / `Password::broker('admins')` with tables `guardian_password_resets` and `admin_password_resets`. Avoids email collision (same email in both tables — edge case in spec). Expire = 60 min.

Session invalidation on reset: `$user->tokens()->delete()` after password update (Sanctum token revocation).

Generic response: returns success message regardless of whether email exists (FR-007).

**Password broker config**:
```php
'passwords' => [
    'guardians' => ['provider' => 'guardians', 'table' => 'guardian_password_resets', 'expire' => 60, 'throttle' => 60],
    'admins'    => ['provider' => 'admins',    'table' => 'admin_password_resets',    'expire' => 60, 'throttle' => 60],
],
```

**Alternatives considered**:
- Single `password_reset_tokens` table with type column: email collision risk. Rejected.
- JWT reset tokens: stateless but non-revocable before expiry. Rejected.

---

## Decision 6: Module Ownership

**Decision**:
- New `Auth` module: auth controllers, email notification listeners, rate limit config
- New `Administration` module: SchoolAdmin model (8th module — justified)
- Existing `Students` module: will own Guardian model

**Justification for 8th module**: SchoolAdmin is a distinct bounded context (administers schools, manages users, configures system). None of the six existing modules is appropriate. Adding to Students conflates two distinct domain roles. Per constitution Principle V, this complexity is documented.

**Cross-module communication via events**:
- `StudentsModule` emits `GuardianCreated` → `Auth` listener sends email verification
- `AdministrationModule` emits `SchoolAdminCreated` → `Auth` listener sends email verification

---

## Decision 7: BFF Auth Proxy (NestJS)

**Decision**: NestJS `HttpModule` + `@nestjs/axios` pass-through proxy — no auth logic in BFF

**Rationale**: Auth logic lives entirely in API Core. BFFs handle CORS, request forwarding, response pass-through. No token validation in BFF (keeps BFF thin per BFF pattern).

**Alternatives considered**:
- BFF validates tokens independently: doubles validation surface area. Rejected.
- BFF issues its own tokens: creates two token systems. Rejected.

---

## Decision 8: Web Token Storage (Next.js)

**Decision**: `localStorage` with in-memory copy on app boot, cleared on logout

**Rationale**: Spec mandates Bearer tokens (not cookies). `localStorage` accessible to JS (needed for `Authorization` header). XSS risk mitigated by CSP headers.

**Alternatives considered**:
- httpOnly cookie: contradicts spec Bearer token assumption. Rejected.
- sessionStorage: lost on tab close — poor UX for persistent login. Rejected.

---

## Decision 9: Mobile Token Storage (React Native)

**Decision**: `expo-secure-store` (iOS Keychain / Android Keystore)

**Rationale**: OS-level encrypted storage. Industry standard for React Native secrets.

**Alternatives considered**:
- AsyncStorage: unencrypted. Rejected for auth tokens.

---

## Decision 10: Mail Backend

**Decision**: Mailpit (dev), SMTP via `MAIL_*` env vars (prod — out of scope per spec assumptions)

---

## Decision 11: Test Framework

**Decision**: Pest (Laravel), Vitest + Testing Library (NestJS BFFs), Playwright (frontend E2E), Detox (React Native E2E)

**Rationale**: Constitution mandates Pest for Laravel. Vitest faster than Jest for TS. Playwright for web critical paths. Detox for React Native.

---

## All NEEDS CLARIFICATION Resolved

- Token mechanism: Sanctum Bearer ✅
- Multiple user types: custom guards ✅
- Rate limiting backend: Redis ✅
- Email verification TTL: 144h signed URL ✅
- Password reset TTL: 60min password broker ✅
- Module for SchoolAdmin: new Administration module ✅
- BFF pattern: NestJS HttpModule proxy ✅
- Web storage: localStorage ✅
- Mobile storage: expo-secure-store ✅
