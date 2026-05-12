# Data Model — Initial Screens (002)

All types are TypeScript. These are the shapes consumed by the frontend components.
Backend API contracts live in `contracts/`. Mock fixtures must conform to these types.

---

## Shared types

```ts
/** Monetary amount in BRL cents to avoid float precision issues */
type BRLCents = number

/** ISO 8601 date-time string, always in America/Sao_Paulo timezone */
type ISOTimestamp = string

/** ISO 8601 date string (YYYY-MM-DD) */
type ISODate = string
```

---

## Auth

### GuardianSession
```ts
interface GuardianSession {
  token: string         // opaque session token (stored in HttpOnly cookie, never exposed to client)
  guardianId: string
  firstName: string
  email: string
  requiresMfa: boolean  // if true, client redirects to OTP screen
}
```

### SchoolSession
```ts
interface SchoolSession {
  token: string
  staffId: string
  firstName: string
  email: string
  role: 'viewer' | 'staff' | 'admin'
  schools: SchoolSummary[]  // all schools this staff member can access
  activeSchoolId: string
}

interface SchoolSummary {
  id: string
  name: string
  code: string  // institution code (4-10 uppercase alphanumeric)
}
```

---

## Guardian Dashboard

### Child (Aluno)
```ts
interface Child {
  id: string
  name: string          // full name; truncate at 30 chars in UI
  grade: string         // e.g. "3º Ano A"
  school: string        // school name
  avatarUrl?: string    // nullable; fallback to initials
  balance: BRLCents     // wallet balance; positive/zero/negative drives color
}
```

### Payment (Cobrança — guardian view)
```ts
type PaymentStatus = 'paid' | 'dueSoon' | 'overdue' | 'onTime'

interface Payment {
  id: string
  childId: string
  description: string
  amountCents: BRLCents
  dueDate: ISODate
  status: PaymentStatus
}
```

Status display mapping:
| `status` | Label | Badge variant |
|---|---|---|
| `paid` | "Pago" | `success` |
| `dueSoon` | "Próximo" | `warning` |
| `overdue` | "Atrasado" | `danger` |
| `onTime` | "Em dia" | `success` |

`dueSoon` = due date is within 7 days from today.

### Activity (Atividade)
```ts
type ActivityType = 'credit' | 'debit'

interface Activity {
  id: string
  childId: string
  description: string
  amountCents: BRLCents   // always positive; sign derived from type
  type: ActivityType
  timestamp: ISOTimestamp
}
```

### GuardianDashboard (BFF response shape)
```ts
interface GuardianDashboard {
  guardian: {
    firstName: string
    unreadNotifications: number
  }
  children: Child[]
  payments: Payment[]          // all unpaid charges for current month, all children
  recentActivity: Activity[]   // last 20 events across all children (UI caps at 5)
}
```

### UpcomingEvent (guardian-web only)
```ts
interface UpcomingEvent {
  id: string
  title: string
  datetime: ISOTimestamp
  schoolId: string
}
```

### MonthSummary (guardian-web only)
```ts
interface MonthSummary {
  totalDueCents: BRLCents
  totalPaidCents: BRLCents
  overdueCount: number
  weeklySpend: Array<{       // last 7 weeks, oldest first
    weekLabel: string        // e.g. "Sem 1"
    amountCents: BRLCents
  }>
}
```

---

## School Dashboard

### KPI
```ts
type KpiId = 'revenue' | 'delinquency' | 'activeStudents' | 'canteenTickets'

interface Kpi {
  id: KpiId
  label: string
  value: number              // raw: cents for revenue, percentage × 100 for rate, count otherwise
  unit: 'brl' | 'percent' | 'count'
  changePct: number          // e.g. 5.2 = +5.2%, -3.1 = -3.1% vs previous period
  changePositive: boolean    // true = green, false = red (delinquency: lower is better)
}
```

### RevenuePoint
```ts
interface RevenuePoint {
  monthLabel: string    // e.g. "Dez", "Jan"
  revenueCents: BRLCents
}
// Array of 6, oldest first
type RevenueChart = RevenuePoint[]
```

### ChargeStatus (donut chart)
```ts
interface ChargeStatusBreakdown {
  paidCents: BRLCents
  paidCount: number
  openCents: BRLCents
  openCount: number
  overdueCents: BRLCents
  overdueCount: number
  totalCents: BRLCents
}
```

### SchoolCharge (charges table)
```ts
type SchoolChargeStatus = 'paid' | 'open' | 'overdue'

interface SchoolCharge {
  id: string
  studentName: string
  planName: string
  dueDate: ISODate
  amountCents: BRLCents
  status: SchoolChargeStatus
}

interface SchoolChargesPage {
  items: SchoolCharge[]
  total: number
  page: number
  pageSize: number   // always 10
}
```

### SystemEvent
```ts
type SystemEventType = 'enrollment' | 'contract' | 'payment' | 'error'

interface SystemEvent {
  id: string
  type: SystemEventType
  description: string
  timestamp: ISOTimestamp
}
```

### SchoolDashboard (BFF response shape)
```ts
interface SchoolDashboard {
  kpis: Kpi[]
  revenueChart: RevenueChart
  chargeStatus: ChargeStatusBreakdown
  charges: SchoolChargesPage
  activity: SystemEvent[]      // last 8 events; client polls every 30s
}
```

---

## Mock data fixtures location

```
specs/002-initial-screens/fixtures/
├── guardian-dashboard.json
├── school-dashboard.json
└── auth-responses.json
```

Fixtures must be used in component tests (Testing Library) and Storybook stories.
