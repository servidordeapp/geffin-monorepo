export type PaymentStatus = 'paid' | 'dueSoon' | 'overdue' | 'onTime'
export type ActivityType = 'credit' | 'debit'

export interface Child {
  id: string
  name: string
  grade: string
  school: string
  avatarUrl?: string | null
  balance: number
}

export interface Payment {
  id: string
  childId: string
  description: string
  amountCents: number
  dueDate: string
  status: PaymentStatus
}

export interface Activity {
  id: string
  childId: string
  description: string
  amountCents: number
  type: ActivityType
  timestamp: string
}

export interface UpcomingEvent {
  id: string
  title: string
  datetime: string
  schoolId: string
}

export interface MonthSummary {
  totalDueCents: number
  totalPaidCents: number
  overdueCount: number
  weeklySpend: Array<{ weekLabel: string; amountCents: number }>
}

export interface GuardianDashboard {
  guardian: { firstName: string; unreadNotifications: number }
  children: Child[]
  payments: Payment[]
  recentActivity: Activity[]
  upcomingEvents?: UpcomingEvent[]
  summary?: MonthSummary
}
