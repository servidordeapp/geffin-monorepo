# Quickstart: Epic Auth — Local Development

**Branch**: `001-epic-auth` | **Date**: 2026-05-10

---

## Prerequisites

- Docker + Docker Compose running
- `make up` executed at repo root (starts API Core, Redis, Mailpit, PostgreSQL)

---

## 1. Install Laravel Sanctum

```bash
make shell
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
exit
```

---

## 2. Run Migrations

```bash
make migrate
# or for a clean slate:
make fresh
```

---

## 3. Seed Test Users

After implementing seeders (created in tasks phase):

```bash
make artisan "db:seed --class=AuthSeeder"
```

This creates:
- Guardian: `guardian@test.com` / `password` (email pre-verified)
- Guardian unverified: `unverified@test.com` / `password`
- SchoolAdmin: `admin@test.com` / `password` (email pre-verified)

---

## 4. Check Mailpit (Email Dev UI)

```bash
make minio-ui  # wrong — for mail:
```

Access Mailpit at: `http://localhost:8025`

All emails sent by the API Core (verification, password reset) appear here.

---

## 5. Test Guardian Login

```bash
curl -X POST http://localhost:8000/api/v1/guardian/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"guardian@test.com","password":"password"}'
```

Expected response:
```json
{
  "data": {
    "token": "1|<token>",
    "user": {
      "id": "<uuid>",
      "name": "Test Guardian",
      "email": "guardian@test.com",
      "email_verified": true
    }
  }
}
```

---

## 6. Test Admin Login

```bash
curl -X POST http://localhost:8000/api/v1/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'
```

---

## 7. Test Email Verification Flow

1. Create an unverified guardian (or use seeded `unverified@test.com`)
2. Try to login → expect `EMAIL_NOT_VERIFIED` error
3. Check Mailpit at `http://localhost:8025` for verification email
4. Click verification link in the email
5. Try login again → success

---

## 8. Test Password Reset Flow

```bash
# Request reset
curl -X POST http://localhost:8000/api/v1/guardian/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"guardian@test.com"}'

# Check Mailpit for reset link
# Click link → shows reset form
# Submit new password
```

---

## 9. Test Rate Limiting

Send 5 failed login attempts for the same email:

```bash
for i in {1..6}; do
  curl -s -X POST http://localhost:8000/api/v1/guardian/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"guardian@test.com","password":"wrong"}' | jq '.errors[0].code'
done
# 6th attempt returns TOO_MANY_ATTEMPTS
```

---

## BFF Development (Future)

BFFs (`apps/bff-guardian`, `apps/bff-school`) are scaffolded as empty NestJS apps.
Once bootstrapped, they proxy auth requests to API Core at `http://api:8000`.

---

## Frontend Development (Future)

Frontends (`frontends/guardian-web`, `frontends/school-web`, `frontends/guardian-mobile`)
will be Next.js and React Native apps. Auth pages hit BFF endpoints, not API Core directly.

---

## Environment Variables

Key env vars in `apps/api-laravel/.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=geffin
DB_USERNAME=geffin
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:3001

APP_URL=http://localhost:8000
FRONTEND_GUARDIAN_URL=http://localhost:3000
FRONTEND_SCHOOL_URL=http://localhost:3001

AUTH_VERIFICATION_EXPIRE=8640  # 144 hours in minutes
```
