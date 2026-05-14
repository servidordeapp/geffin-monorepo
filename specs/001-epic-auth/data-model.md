# Data Model: Epic Auth — Authentication

**Branch**: `001-epic-auth` | **Date**: 2026-05-10 | **Phase**: 1

---

## Entities

### Guardian

**Module**: `Students`
**Table**: `guardians`
**Implements**: `MustVerifyEmail`, `HasApiTokens` (Sanctum)

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | `uuid` | no | PK, gen_random_uuid() |
| `name` | `varchar(255)` | no | |
| `email` | `varchar(255)` | no | UNIQUE |
| `email_verified_at` | `timestamp` | yes | null = unverified |
| `password` | `varchar(255)` | no | bcrypt hash |
| `active` | `boolean` | no | default true; inactive = login denied |
| `created_at` | `timestamp` | no | |
| `updated_at` | `timestamp` | no | |

**Validation rules**:
- `email`: valid email format, max 255 chars
- `password`: min 8 chars on set
- `active`: controlled by admin, not user

**State transitions**:
```
[created] → email_verified_at=null → [pending verification]
[pending verification] + click link (within 144h) → email_verified_at=now → [active]
[active] + admin deactivates → active=false → [inactive]
[inactive] + admin reactivates → active=true → [active]
```

---

### SchoolAdmin

**Module**: `Administration`
**Table**: `school_admins`
**Implements**: `MustVerifyEmail`, `HasApiTokens` (Sanctum)

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | `uuid` | no | PK, gen_random_uuid() |
| `name` | `varchar(255)` | no | |
| `email` | `varchar(255)` | no | UNIQUE |
| `email_verified_at` | `timestamp` | yes | null = unverified |
| `password` | `varchar(255)` | no | bcrypt hash |
| `active` | `boolean` | no | default true |
| `created_at` | `timestamp` | no | |
| `updated_at` | `timestamp` | no | |

**State transitions**: identical to Guardian.

---

### PersonalAccessToken (Sanctum — framework level)

**Module**: framework (not owned by any domain module)
**Table**: `personal_access_tokens` (Sanctum standard)

| Column | Type | Notes |
|--------|------|-------|
| `id` | `bigint` | PK |
| `tokenable_type` | `varchar` | polymorphic — Guardian or SchoolAdmin |
| `tokenable_id` | `uuid` | user id |
| `name` | `varchar(255)` | token name (e.g., 'auth') |
| `token` | `varchar(64)` | hashed token |
| `abilities` | `json` | scopes — `['*']` for auth tokens |
| `last_used_at` | `timestamp` | nullable |
| `expires_at` | `timestamp` | nullable (tokens are non-expiring by default; session managed by revocation) |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**Token lifecycle**:
- Created on successful login
- Revoked (deleted) on logout
- All user tokens revoked on successful password reset

---

### GuardianPasswordReset

**Module**: `Auth` (migration in `database/migrations/Auth/`)
**Table**: `guardian_password_resets`

| Column | Type | Notes |
|--------|------|-------|
| `email` | `varchar(255)` | PK (Laravel broker standard) |
| `token` | `varchar(255)` | hashed reset token |
| `created_at` | `timestamp` | broker checks expiry (60 min) |

---

### AdminPasswordReset

**Module**: `Auth` (migration in `database/migrations/Auth/`)
**Table**: `admin_password_resets`

| Column | Type | Notes |
|--------|------|-------|
| `email` | `varchar(255)` | PK |
| `token` | `varchar(255)` | hashed reset token |
| `created_at` | `timestamp` | broker checks expiry (60 min) |

---

## Guard Configuration

```php
// config/auth.php — framework level, not a module

'guards' => [
    'guardian' => ['driver' => 'sanctum', 'provider' => 'guardians'],
    'admin'    => ['driver' => 'sanctum', 'provider' => 'admins'],
],
'providers' => [
    'guardians' => ['driver' => 'eloquent', 'model' => App\Modules\Students\Models\Guardian::class],
    'admins'    => ['driver' => 'eloquent', 'model' => App\Modules\Administration\Models\SchoolAdmin::class],
],
'passwords' => [
    'guardians' => ['provider' => 'guardians', 'table' => 'guardian_password_resets', 'expire' => 60, 'throttle' => 60],
    'admins'    => ['provider' => 'admins',    'table' => 'admin_password_resets',    'expire' => 60, 'throttle' => 60],
],
```

---

## Migration Ownership

```
database/migrations/
  Students/
    xxxx_create_guardians_table.php
  Administration/
    xxxx_create_school_admins_table.php
  Auth/
    xxxx_create_guardian_password_resets_table.php
    xxxx_create_admin_password_resets_table.php
```

Sanctum's `personal_access_tokens` migration is installed via `artisan vendor:publish`.

---

## Relationships

```
Guardian        1──* PersonalAccessToken (tokenable polymorphic)
SchoolAdmin     1──* PersonalAccessToken (tokenable polymorphic)
Guardian        1──0..1 GuardianPasswordReset (by email)
SchoolAdmin     1──0..1 AdminPasswordReset (by email)
```

---

## Domain Events

| Event | Emitted By | Consumed By | Purpose |
|-------|-----------|-------------|---------|
| `GuardianCreated` | Students module | Auth module listener | Trigger email verification send |
| `SchoolAdminCreated` | Administration module | Auth module listener | Trigger email verification send |

---

## Rate Limiting (Redis — no DB schema)

Two named limiters registered in `AppServiceProvider`:

```
login.guardian  →  5 attempts per 15 min, keyed by email|ip
login.admin     →  5 attempts per 15 min, keyed by email|ip
resend.guardian →  1 attempt per 1 min (per user), keyed by user id
resend.admin    →  1 attempt per 1 min (per user), keyed by user id
```
