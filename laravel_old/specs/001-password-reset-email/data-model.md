# Phase 1 Data Model — Password Reset by Email

Three tables participate in this feature. Two are pre-existing and unchanged. One is new.

## 1. `users` (existing, unchanged)

Owner: future `Modules/Shared/Auth/` (currently flat). Existing migration: `database/migrations/0001_01_01_000000_create_users_table.php`.

| Column | Type | Note |
|---|---|---|
| `id` | bigint PK | — |
| `name` | string | — |
| `email` | string UNIQUE | the natural identifier for password reset; case-insensitive comparison applied at query time |
| `email_verified_at` | timestamp nullable | not required for reset to succeed |
| `password` | string | bcrypt; `User::casts()` declares `'password' => 'hashed'` so reassigning rehashes automatically |
| `remember_token` | string nullable | **rotated on successful reset** to invalidate "remember me" cookies platform-wide |
| `created_at`, `updated_at` | timestamps | — |

**Account-status note**: the spec describes "active / suspended / deactivated" status. The current `users` migration has no `status` column. **Scope decision**: this feature does not add the column. The Phase-2 service treats every existing user row as active (`is_active = true`). When the platform later adds the status column the `PasswordResetService::resolveAccount()` method is the single place that needs to check it (a `// TODO(status)` marker is left there).

## 2. `password_reset_tokens` (existing, unchanged)

Owned by the Laravel `Password` broker. Existing migration: same file as `users`.

| Column | Type | Note |
|---|---|---|
| `email` | string PRIMARY KEY | one row per outstanding token per email — the schema itself enforces FR-007 ("only the most recent link is valid") |
| `token` | string | bcrypt of the plaintext token sent in the URL |
| `created_at` | timestamp nullable | issued-at; expiry is computed as `created_at + 60 min` via `auth.passwords.users.expire` |

**No migration change.** Phase 2 may add an explicit `DB::delete` before re-issuance as belt-and-braces (Decision 2 in `research.md`).

## 3. `password_reset_audit_events` (new)

New migration: `database/migrations/2026_05_23_000000_create_password_reset_audit_events_table.php`.

| Column | Type | Nullable | Index | Note |
|---|---|---|---|---|
| `id` | bigint PK auto-increment | no | PK | — |
| `event_type` | string(40) | no | yes | enum: `requested`, `email_sent`, `link_opened`, `password_changed`, `token_rejected`, `request_throttled` |
| `user_id` | bigint | yes | FK→users(id) ON DELETE SET NULL | nullable: requests for unknown email leave it null |
| `email_hash` | char(64) | no | yes | `hash('sha256', strtolower(trim($email)))`; PII-safe correlation key |
| `ip_address` | string(45) | yes | no | IPv6-wide |
| `user_agent` | string(255) | yes | no | truncated to 255 |
| `outcome` | string(20) | no | no | enum: `accepted`, `rejected`, `delivered`, `failed`, `throttled` |
| `reason` | string(80) | yes | no | machine-readable code when `outcome=rejected`: `expired`, `consumed`, `invalid`, `tampered`, `account_inactive` |
| `created_at` | timestamp | no | yes | retention-prune column |

Indexes:

- `(event_type, created_at)` — support / investigation queries.
- `(email_hash, created_at)` — per-account history.
- `(ip_address, created_at)` — abuse investigation.

No `updated_at` column. No model-level `update()` is ever called.

### Eloquent Model

```php
// app/Models/PasswordResetAuditEvent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_type', 'user_id', 'email_hash', 'ip_address', 'user_agent', 'outcome', 'reason'])]
class PasswordResetAuditEvent extends Model
{
    public const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function emailHash(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }
}
```

### Lifecycle / state transitions

A single password-reset flow produces an ordered series of rows. None of them transition between states; each is a discrete fact. The expected sequences are:

| Sequence | Audit rows in order |
|---|---|
| Happy path | `requested:accepted` → `email_sent:delivered` → `link_opened:accepted` → `password_changed:accepted` |
| Unknown email | `requested:accepted` (with `user_id=null`) — no further rows |
| Inactive account (future) | `requested:rejected:account_inactive` (with `user_id` resolved, no email sent) |
| Expired link clicked | `link_opened:rejected:expired` |
| Reused link | `link_opened:rejected:consumed` |
| Tampered URL signature | `token_rejected:rejected:tampered` |
| Per-email throttle hit | `request_throttled:throttled` |
| Mail bounce (worker callback, future) | `email_sent:failed` |

The "Unknown email" branch deliberately writes `outcome=accepted` rather than `rejected` so that an attacker with read access to the audit table cannot distinguish enumeration probes from real not-found by outcome alone — the `user_id IS NULL` distinguisher is intentional and only visible to operators.

## Entity Relationship

```text
users (1) ──< password_reset_tokens (0..1 per email)
users (1) ──< password_reset_audit_events (0..N)
users (1) ──< sessions (0..N)   ← already exists; we DELETE WHERE user_id=? on successful reset
```

No new foreign-key edges beyond `password_reset_audit_events.user_id → users.id`.
