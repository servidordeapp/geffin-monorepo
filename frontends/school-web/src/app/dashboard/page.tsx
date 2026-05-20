import { cookies } from 'next/headers'
import { redirect } from 'next/navigation'
import { Suspense } from 'react'
import { Skeleton } from '@gfn/design-system'
import { getSchoolDashboard } from '../../lib/api'
import { Sidebar } from '../../components/Sidebar'
import { TopBar } from '../../components/TopBar'
import { KpiCard } from '../../components/KpiCard'
import { RevenueLineChart } from '../../components/RevenueLineChart'
import { ChargesDonut } from '../../components/ChargesDonut'
import { ChargesTable } from '../../components/ChargesTable'
import { SystemActivity } from '../../components/SystemActivity'

export default async function SchoolDashboardPage() {
  const cookieStore = await cookies()
  const token = cookieStore.get('session')?.value
  if (!token) redirect('/login')

  const dashboard = await getSchoolDashboard(token).catch(() => null)
  if (!dashboard) redirect('/login')

  return (
    <div className="flex min-h-screen bg-neutral-50">
      <Sidebar activePath="/dashboard" />

      <div className="flex-1 flex flex-col min-w-0">
        <TopBar title="Dashboard" staffName="Admin" />

        <main className="flex-1 p-6 flex flex-col gap-6">
          {/* Page header */}
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-bold text-neutral-900">
              {new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' }).format(new Date())}
            </h2>
            <button
              type="button"
              className="flex h-9 items-center gap-2 px-4 rounded-md border border-neutral-200 bg-neutral-0 text-sm font-medium text-neutral-600 hover:bg-neutral-50 transition-colors"
            >
              Exportar
            </button>
          </div>

          {/* KPI grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {dashboard.kpis.map((kpi) => (
              <KpiCard key={kpi.id} kpi={kpi} />
            ))}
          </div>

          {/* Charts row */}
          <div className="grid grid-cols-5 gap-4">
            <Suspense fallback={<Skeleton className="col-span-3 h-64" />}>
              <RevenueLineChart data={dashboard.revenueChart} />
            </Suspense>
            <Suspense fallback={<Skeleton className="col-span-2 h-64" />}>
              <ChargesDonut data={dashboard.chargeStatus} />
            </Suspense>
          </div>

          {/* Charges table */}
          <Suspense fallback={<Skeleton className="w-full h-64" />}>
            <ChargesTable page={dashboard.charges} />
          </Suspense>

          {/* System activity */}
          <div className="bg-neutral-0 border border-neutral-200 rounded-md p-6">
            <Suspense fallback={<Skeleton className="w-full h-48" />}>
              <SystemActivity initialData={dashboard.activity} />
            </Suspense>
          </div>
        </main>
      </div>
    </div>
  )
}
