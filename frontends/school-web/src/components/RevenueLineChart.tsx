'use client'

import { LineChart, Line, XAxis, YAxis, Tooltip, Area, AreaChart, ResponsiveContainer } from 'recharts'
import { Card } from '@gfn/design-system'
import type { RevenuePoint } from '../lib/types'
import { formatBRL } from '../lib/format'

interface Props {
  data: RevenuePoint[]
}

export function RevenueLineChart({ data }: Props) {
  const chartData = data.map((p) => ({
    name: p.monthLabel,
    value: p.revenueCents / 100,
  }))

  return (
    <Card context="admin" className="col-span-3">
      <h3 className="text-base font-semibold text-neutral-900 mb-4">Receita Mensal</h3>
      <div className="h-48">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={chartData}>
            <defs>
              <linearGradient id="revenueGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="#1E40AF" stopOpacity={0.15} />
                <stop offset="95%" stopColor="#1E40AF" stopOpacity={0} />
              </linearGradient>
            </defs>
            <XAxis
              dataKey="name"
              tick={{ fontSize: 12, fill: '#94A3B8' }}
              axisLine={false}
              tickLine={false}
            />
            <YAxis
              tickFormatter={(v) => `R$ ${(v / 1000).toFixed(0)}k`}
              tick={{ fontSize: 11, fill: '#94A3B8' }}
              axisLine={false}
              tickLine={false}
              width={60}
            />
            <Tooltip
              formatter={(v: number) => [formatBRL(v * 100), 'Receita']}
              contentStyle={{ fontSize: 12, borderRadius: 8, border: '1px solid #E2E8F0' }}
            />
            <Area
              type="monotone"
              dataKey="value"
              stroke="#1E40AF"
              strokeWidth={2}
              fill="url(#revenueGrad)"
              dot={{ r: 3, fill: '#1E40AF', strokeWidth: 0 }}
              activeDot={{ r: 5, fill: '#1E40AF' }}
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </Card>
  )
}
