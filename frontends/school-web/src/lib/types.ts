export type KpiId = 'revenue' | 'delinquency' | 'activeStudents' | 'canteenTickets'

export interface Kpi {
  id: KpiId
  label: string
  value: number
  unit: 'brl' | 'percent' | 'count'
  changePct: number
  changePositive: boolean
}

export interface RevenuePoint {
  monthLabel: string
  revenueCents: number
}

export interface ChargeStatusBreakdown {
  paidCents: number; paidCount: number
  openCents: number; openCount: number
  overdueCents: number; overdueCount: number
  totalCents: number
}

export type SchoolChargeStatus = 'paid' | 'open' | 'overdue'

export interface SchoolCharge {
  id: string
  studentName: string
  planName: string
  dueDate: string
  amountCents: number
  status: SchoolChargeStatus
}

export interface SchoolChargesPage {
  items: SchoolCharge[]
  total: number
  page: number
  pageSize: number
}

export type SystemEventType = 'enrollment' | 'contract' | 'payment' | 'error'

export interface SystemEvent {
  id: string
  type: SystemEventType
  description: string
  timestamp: string
}

export interface SchoolDashboard {
  kpis: Kpi[]
  revenueChart: RevenuePoint[]
  chargeStatus: ChargeStatusBreakdown
  charges: SchoolChargesPage
  activity: SystemEvent[]
}
