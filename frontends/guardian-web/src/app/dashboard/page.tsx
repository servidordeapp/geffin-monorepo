import { cookies } from 'next/headers'
import { redirect } from 'next/navigation'
import { Suspense } from 'react'
import { Skeleton } from '@gfn/design-system'
import { getGuardianDashboard, getGuardianSummary } from '../../lib/api'
import { TopNav } from '../../components/TopNav'
import { ChildCard } from '../../components/ChildCard'
import { MonthSummary } from '../../components/MonthSummary'
import { DueTable } from '../../components/DueTable'
import { RecentActivity } from '../../components/RecentActivity'
import { UpcomingEvents } from '../../components/UpcomingEvents'

export default async function DashboardPage() {
  const cookieStore = await cookies()
  const token = cookieStore.get('session')?.value
  if (!token) redirect('/login')

  const [dashboard, summary] = await Promise.all([
    getGuardianDashboard(token),
    getGuardianSummary(token),
  ]).catch(() => [null, null])

  if (!dashboard) redirect('/login')

  return (
    <>
      <TopNav
        activePath="/dashboard"
        guardianName={dashboard.guardian.firstName}
        unreadCount={dashboard.guardian.unreadNotifications}
      />

      <main className="mx-auto max-w-[1200px] px-6 py-8">
        {/* Page header */}
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-neutral-900">
            Olá, {dashboard.guardian.firstName}!
          </h1>
          <p className="text-sm text-neutral-400 mt-1">
            Veja o resumo de hoje
          </p>
        </div>

        {/* Hero row: ChildCard + MonthSummary */}
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
          <Suspense fallback={<Skeleton className="lg:col-span-3 h-64" />}>
            <ChildCard children={dashboard.children} />
          </Suspense>
          <Suspense fallback={<Skeleton className="lg:col-span-2 h-64" />}>
            <MonthSummary summary={summary} isLoading={false} />
          </Suspense>
        </div>

        {/* Charges table */}
        <div className="mb-6">
          <Suspense fallback={<Skeleton className="w-full h-48" />}>
            <DueTable payments={dashboard.payments} />
          </Suspense>
        </div>

        {/* Bottom row: activity + events */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Suspense fallback={<Skeleton className="h-48" />}>
            <RecentActivity activities={dashboard.recentActivity} />
          </Suspense>
          <Suspense fallback={<Skeleton className="h-48" />}>
            <UpcomingEvents events={dashboard.upcomingEvents ?? []} />
          </Suspense>
        </div>
      </main>
    </>
  )
}
