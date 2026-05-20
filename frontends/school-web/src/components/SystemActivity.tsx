'use client'

import useSWR from 'swr'
import { Button } from '@gfn/design-system'
import { cn } from '@gfn/design-system'
import type { SystemEvent } from '../lib/types'

const eventColors: Record<SystemEvent['type'], string> = {
  payment:    'bg-accent-green-500',
  enrollment: 'bg-brand-primary-500',
  contract:   'bg-neutral-400',
  error:      'bg-semantic-danger',
}

const SESSION_TOKEN_KEY = 'session'

function formatTimestamp(iso: string): string {
  return new Intl.DateTimeFormat('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(iso))
}

interface Props {
  initialData: SystemEvent[]
}

export function SystemActivity({ initialData }: Props) {
  const { data: events = initialData } = useSWR<SystemEvent[]>(
    '/school/dashboard/activity',
    async () => {
      const res = await fetch('/api/school/activity', { cache: 'no-store' })
      if (!res.ok) return initialData
      return res.json()
    },
    { refreshInterval: 30_000, fallbackData: initialData }
  )

  return (
    <div role="status" aria-live="polite" aria-label="Atividade do sistema">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-base font-semibold text-neutral-900">Atividade do Sistema</h3>
      </div>

      <ul className="flex flex-col gap-1">
        {events.slice(0, 8).map((event) => (
          <li key={event.id} className="flex items-start gap-3 py-2">
            <div
              className={cn('size-2 rounded-full shrink-0 mt-1.5', eventColors[event.type])}
              aria-hidden="true"
            />
            <span className="flex-1 text-sm text-neutral-900 leading-relaxed">
              {event.description}
            </span>
            <span className="text-xs text-neutral-400 shrink-0">{formatTimestamp(event.timestamp)}</span>
          </li>
        ))}
      </ul>

      <div className="mt-4 pt-4 border-t border-neutral-100">
        <Button variant="ghost" size="sm" context="admin">
          Ver log completo
        </Button>
      </div>
    </div>
  )
}
