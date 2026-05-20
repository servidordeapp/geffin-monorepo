import Link from 'next/link'
import { cn } from '@gfn/design-system'
import type { Activity } from '../lib/types'
import { formatBRL, formatRelative } from '../lib/format'

const dotColor: Record<Activity['type'], string> = {
  credit: 'bg-accent-green-500',
  debit: 'bg-semantic-danger',
}
const amountColor: Record<Activity['type'], string> = {
  credit: 'text-accent-green-700',
  debit: 'text-semantic-danger',
}

interface Props {
  activities: Activity[]
}

export function RecentActivity({ activities }: Props) {
  if (!activities.length) return null

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-base font-semibold text-neutral-900">Atividade Recente</h3>
        <Link href="/history" className="text-sm text-brand-primary-700 hover:underline">
          Ver tudo
        </Link>
      </div>

      <ul className="flex flex-col gap-1">
        {activities.slice(0, 5).map((a) => (
          <li key={a.id} className="flex items-center gap-3 py-2">
            <div className={cn('size-2 rounded-full shrink-0', dotColor[a.type])} aria-hidden="true" />
            <span className="flex-1 text-sm text-neutral-900 truncate">{a.description}</span>
            <span className="text-xs text-neutral-400 shrink-0">{formatRelative(a.timestamp)}</span>
            <span className={cn('text-sm font-semibold tabular-nums shrink-0', amountColor[a.type])}>
              {a.type === 'credit' ? '+' : '-'}{formatBRL(a.amountCents)}
            </span>
          </li>
        ))}
      </ul>
    </div>
  )
}
