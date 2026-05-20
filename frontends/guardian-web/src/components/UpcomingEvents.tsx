import Link from 'next/link'
import { Calendar } from 'lucide-react'
import type { UpcomingEvent } from '../lib/types'
import { formatDate } from '../lib/format'

interface Props {
  events: UpcomingEvent[]
}

export function UpcomingEvents({ events }: Props) {
  if (!events.length) return null

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-base font-semibold text-neutral-900">Próximos Eventos</h3>
        <Link href="/calendar" className="text-sm text-brand-primary-700 hover:underline">
          Ver agenda
        </Link>
      </div>

      <ul className="flex flex-col divide-y divide-neutral-100">
        {events.map((event) => (
          <li key={event.id} className="flex items-center gap-3 py-3">
            <div className="size-8 rounded-lg bg-brand-primary-50 flex items-center justify-center shrink-0">
              <Calendar size={14} className="text-brand-primary-700" aria-hidden="true" />
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium text-neutral-900 truncate">{event.title}</p>
              <p className="text-xs text-neutral-400">{formatDate(event.datetime)}</p>
            </div>
          </li>
        ))}
      </ul>
    </div>
  )
}
