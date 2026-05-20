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
