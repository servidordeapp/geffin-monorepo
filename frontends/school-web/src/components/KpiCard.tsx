import { TrendingUp, TrendingDown } from 'lucide-react'
import { Card } from '@gfn/design-system'
import { Skeleton } from '@gfn/design-system'
import { cn } from '@gfn/design-system'
import type { Kpi } from '../lib/types'
import { formatBRL, formatPct } from '../lib/format'

interface Props {
  kpi: Kpi
  isLoading?: boolean
}

function formatValue(kpi: Kpi): string {
  if (kpi.unit === 'brl') return formatBRL(kpi.value)
  if (kpi.unit === 'percent') return formatPct(kpi.value)
  return kpi.value.toLocaleString('pt-BR')
}

export function KpiCard({ kpi, isLoading }: Props) {
  if (isLoading) {
    return (
      <Card variant="kpi" padding="md" context="admin">
        <Skeleton className="w-24 h-4 mb-3" />
        <Skeleton className="w-32 h-8 mb-3" />
        <Skeleton className="w-20 h-4" />
      </Card>
    )
  }

  const TrendIcon = kpi.changePositive ? TrendingUp : TrendingDown
  const trendColor = kpi.changePositive ? 'text-accent-green-700' : 'text-semantic-danger'
  const changeSuffix = kpi.changePct >= 0 ? `+${kpi.changePct.toFixed(1)}%` : `${kpi.changePct.toFixed(1)}%`

  return (
    <Card variant="kpi" padding="md" context="admin">
      <p className="text-sm font-medium text-neutral-600 mb-1">{kpi.label}</p>
      <p className="text-3xl font-bold text-neutral-900 tabular-nums mb-2">
        {formatValue(kpi)}
      </p>
      <div className={cn('flex items-center gap-1 text-sm font-medium', trendColor)}>
        <TrendIcon size={14} aria-hidden="true" />
        <span>{changeSuffix}</span>
        <span className="text-neutral-400 font-normal">vs mês anterior</span>
      </div>
    </Card>
  )
}
