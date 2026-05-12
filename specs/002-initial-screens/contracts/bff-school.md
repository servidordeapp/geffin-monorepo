# BFF School — HTTP Contracts

Base URL: `http://localhost:3002` (dev) | `https://bff-school.gfn.internal` (prod)
All responses follow the envelope: `{ data?, meta?, errors? }`.

---

## POST /auth/school-login

### Request
```json
{
  "institutionCode": "ESCOLA01",
  "email": "admin@escola.com",
  "password": "senha123"
}
```
`institutionCode` must match `/^[A-Z0-9]{4,10}$/` (validated client-side + server-side).

### Response 200
```json
{
  "data": {
    "token": "<opaque-session-token>",
    "staffId": "s_01PQR",
    "firstName": "Ana",
    "email": "admin@escola.com",
    "role": "admin",
    "schools": [
      { "id": "sch_01STU", "name": "Escola Municipal João Pessoa", "code": "ESCOLA01" }
    ],
    "activeSchoolId": "sch_01STU"
  }
}
```

### Response 401
```json
{
  "errors": [{ "code": "INVALID_CREDENTIALS", "message": "Credenciais inválidas." }]
}
```

### Response 422 (invalid institution code format)
```json
{
  "errors": [{ "code": "INVALID_INSTITUTION_CODE", "field": "institutionCode", "message": "Código inválido. Use 4–10 letras maiúsculas ou números." }]
}
```

---

## GET /school/dashboard?period=YYYY-MM

**Auth**: `Authorization: Bearer <token>` (forwarded from HttpOnly cookie)

### Response 200
```json
{
  "data": {
    "kpis": [
      { "id": "revenue", "label": "Receita do mês", "value": 12500000, "unit": "brl", "changePct": 5.2, "changePositive": true },
      { "id": "delinquency", "label": "Inadimplência", "value": 320, "unit": "percent", "changePct": -1.5, "changePositive": true },
      { "id": "activeStudents", "label": "Alunos ativos", "value": 248, "unit": "count", "changePct": 2.1, "changePositive": true },
      { "id": "canteenTickets", "label": "Tickets cantina", "value": 1834, "unit": "count", "changePct": 8.3, "changePositive": true }
    ],
    "revenueChart": [
      { "monthLabel": "Dez", "revenueCents": 11800000 },
      { "monthLabel": "Jan", "revenueCents": 12000000 },
      { "monthLabel": "Fev", "revenueCents": 11500000 },
      { "monthLabel": "Mar", "revenueCents": 12200000 },
      { "monthLabel": "Abr", "revenueCents": 12100000 },
      { "monthLabel": "Mai", "revenueCents": 12500000 }
    ],
    "chargeStatus": {
      "paidCents": 9800000, "paidCount": 185,
      "openCents": 1900000, "openCount": 43,
      "overdueCents": 800000, "overdueCount": 20,
      "totalCents": 12500000
    },
    "charges": {
      "items": [
        {
          "id": "ch_01VWX",
          "studentName": "Pedro Silva",
          "planName": "Mensalidade",
          "dueDate": "2026-05-20",
          "amountCents": 85000,
          "status": "open"
        }
      ],
      "total": 63,
      "page": 1,
      "pageSize": 10
    },
    "activity": [
      {
        "id": "ev_01YZA",
        "type": "payment",
        "description": "Pagamento recebido — Pedro Silva (Mensalidade)",
        "timestamp": "2026-05-12T15:00:00-03:00"
      }
    ]
  },
  "meta": { "period": "2026-05", "schoolId": "sch_01STU" }
}
```

---

## GET /school/dashboard/charges?period=YYYY-MM&page=N&sort=field&order=asc|desc

Paginated + sorted charges for the table. Same shape as `charges` above.

---

## GET /school/dashboard/activity

Returns latest 8 system events. Used by the 30-second polling client.

```json
{
  "data": {
    "activity": [ /* SystemEvent[] */ ]
  }
}
```
