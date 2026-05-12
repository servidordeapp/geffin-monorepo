# BFF Guardian — HTTP Contracts

Base URL: `http://localhost:3001` (dev) | `https://bff-guardian.gfn.internal` (prod)
All responses follow the envelope: `{ data?, meta?, errors? }`.

---

## POST /auth/login

### Request
```json
{
  "email": "guardian@example.com",
  "password": "senha123"
}
```

### Response 200
```json
{
  "data": {
    "token": "<opaque-session-token>",
    "guardianId": "g_01HXYZ",
    "firstName": "Maria",
    "email": "guardian@example.com",
    "requiresMfa": false
  }
}
```

### Response 401 (invalid credentials)
```json
{
  "errors": [{ "code": "INVALID_CREDENTIALS", "message": "E-mail ou senha inválidos." }]
}
```

### Response 403 (MFA required)
```json
{
  "data": { "requiresMfa": true, "sessionToken": "<temp-mfa-session>" },
  "errors": []
}
```

### Response 429 (rate limited)
```json
{
  "errors": [{ "code": "RATE_LIMITED", "message": "Muitas tentativas. Tente novamente em 5 minutos." }]
}
```

---

## GET /guardian/dashboard

**Auth**: `Authorization: Bearer <token>` (server-side via HttpOnly cookie forwarding)

### Response 200
```json
{
  "data": {
    "guardian": { "firstName": "Maria", "unreadNotifications": 2 },
    "children": [
      {
        "id": "c_01ABC",
        "name": "Pedro Silva",
        "grade": "3º Ano A",
        "school": "Escola Municipal João Pessoa",
        "avatarUrl": null,
        "balance": 5000
      }
    ],
    "payments": [
      {
        "id": "p_01DEF",
        "childId": "c_01ABC",
        "description": "Mensalidade Março",
        "amountCents": 85000,
        "dueDate": "2026-05-20",
        "status": "dueSoon"
      }
    ],
    "recentActivity": [
      {
        "id": "a_01GHI",
        "childId": "c_01ABC",
        "description": "Cantina — Almoço",
        "amountCents": 1500,
        "type": "debit",
        "timestamp": "2026-05-12T12:30:00-03:00"
      }
    ]
  },
  "meta": { "generatedAt": "2026-05-12T15:00:00-03:00" }
}
```

---

## GET /guardian/dashboard/summary

Returns MonthSummary + UpcomingEvents for the guardian-web hero row.

### Response 200
```json
{
  "data": {
    "monthSummary": {
      "totalDueCents": 285000,
      "totalPaidCents": 200000,
      "overdueCount": 0,
      "weeklySpend": [
        { "weekLabel": "Sem 1", "amountCents": 4500 },
        { "weekLabel": "Sem 2", "amountCents": 6200 }
      ]
    },
    "upcomingEvents": [
      {
        "id": "e_01JKL",
        "title": "Reunião de Pais",
        "datetime": "2026-05-28T19:00:00-03:00",
        "schoolId": "s_01MNO"
      }
    ]
  }
}
```
