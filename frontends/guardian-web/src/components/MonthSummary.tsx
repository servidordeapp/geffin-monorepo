'use client'

import { BarChart, Bar, Tooltip, ResponsiveContainer } from 'recharts'
import { Card } from '@gfn/design-system'
import { Skeleton } from '@gfn/design-system'
import type { MonthSummary as TMonthSummary } from '../lib/types'
import { formatBRL } from '../lib/format'

interface Props {
  summary: TMonthSummary | null
  isLoading: boolean
}

export function MonthSummary({ summary, isLoading }: Props) {
  if (isLoading) {
    return (
      <Card className="lg:col-span-2">
        <Skeleton className="w-32 h-5 mb-6" />
        <div className="flex flex-col gap-3 mb-4">
          {[0, 1, 2].map((i) => <Skeleton key={i} className="w-full h-8" />)}
        </div>
        <Skeleton className="w-full h-24" />
      </Card>
    )
  }

  if (!summary) return null

  const chartData = summary.weeklySpend.map((w) => ({
    name: w.weekLabel,
    value: w.amountCents / 100,
  }))

  return (
    <Card className="lg:col-span-2">
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-base font-semibold text-neutral-900">Resumo do mês</h3>
      </div>

      <div className="flex flex-col gap-3 mb-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="size-2 rounded-full bg-brand-primary-500" />
            <span className="text-sm text-neutral-600">Total a pagar</span>
          </div>
          <span className="text-sm font-semibold tabular-nums text-neutral-900">
            {formatBRL(summary.totalDueCents)}
          </span>
        </div>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="size-2 rounded-full bg-accent-green-500" />
            <span className="text-sm text-neutral-600">Pago</span>
          </div>
          <span className="text-sm font-semibold tabular-nums text-accent-green-700">
            {formatBRL(summary.totalPaidCents)}
          </span>
        </div>
        {summary.overdueCount > 0 && (
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <div className="size-2 rounded-full bg-semantic-danger" />
              <span className="text-sm text-neutral-600">Em atraso</span>
            </div>
            <span className="text-sm font-semibold tabular-nums text-semantic-danger">
              {summary.overdueCount} cobranças
            </span>
          </div>
        )}
      </div>

      <div className="h-24">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={chartData} barSize={8}>
            <Bar dataKey="value" fill="#1E40AF" radius={[2, 2, 0, 0]} />
            <Tooltip
              formatter={(value: number) => [formatBRL(value * 100), 'Gasto']}
              contentStyle={{ fontSize: 12, borderRadius: 8, border: '1px solid #E2E8F0' }}
            />
          </BarChart>
        </ResponsiveContainer>
      </div>
    </Card>
  )
}
