'use client'

import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer } from 'recharts'
import { Card } from '@gfn/design-system'
import type { ChargeStatusBreakdown } from '../lib/types'
import { formatBRL } from '../lib/format'

const COLORS = {
  paid: '#10B981',
  open: '#F59E0B',
  overdue: '#EF4444',
}

interface Props {
  data: ChargeStatusBreakdown
}

export function ChargesDonut({ data }: Props) {
  const total = data.totalCents
  const pieData = [
    { name: 'Pago', value: data.paidCents, color: COLORS.paid, count: data.paidCount },
    { name: 'Em aberto', value: data.openCents, color: COLORS.open, count: data.openCount },
    { name: 'Vencido', value: data.overdueCents, color: COLORS.overdue, count: data.overdueCount },
  ]

  return (
    <Card context="admin" className="col-span-2">
      <h3 className="text-base font-semibold text-neutral-900 mb-4">Status de Cobranças</h3>
      <div className="flex items-center gap-6">
        <div className="h-40 w-40 shrink-0 relative">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={pieData}
                cx="50%"
                cy="50%"
                innerRadius={42}
                outerRadius={64}
                paddingAngle={2}
                dataKey="value"
                startAngle={90}
                endAngle={-270}
              >
                {pieData.map((entry) => (
                  <Cell key={entry.name} fill={entry.color} stroke="none" />
                ))}
              </Pie>
              <Tooltip
                formatter={(v: number) => [formatBRL(v), '']}
                contentStyle={{ fontSize: 12, borderRadius: 8 }}
              />
            </PieChart>
          </ResponsiveContainer>
          {/* Center label */}
          <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span className="text-xs text-neutral-400">Total</span>
            <span className="text-sm font-bold text-neutral-900 tabular-nums">
              {formatBRL(total)}
            </span>
          </div>
        </div>

        <ul className="flex flex-col gap-3 flex-1">
          {pieData.map((item) => {
            const pct = total > 0 ? ((item.value / total) * 100).toFixed(1) : '0.0'
            return (
              <li key={item.name} className="flex items-center gap-2">
                <div className="size-2.5 rounded-full shrink-0" style={{ backgroundColor: item.color }} />
                <span className="text-sm text-neutral-600 flex-1">{item.name}</span>
                <span className="text-sm font-semibold text-neutral-900 tabular-nums">{pct}%</span>
              </li>
            )
          })}
        </ul>
      </div>
    </Card>
  )
}
