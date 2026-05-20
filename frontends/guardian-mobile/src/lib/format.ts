const brlFormatter = new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
  minimumFractionDigits: 2,
})

export function formatBRL(cents: number): string {
  return brlFormatter.format(cents / 100)
}

const relativeFormatter = new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' })

export function formatRelative(isoTimestamp: string): string {
  const diffMs = new Date(isoTimestamp).getTime() - Date.now()
  const diffSeconds = Math.round(diffMs / 1000)
  const diffMinutes = Math.round(diffSeconds / 60)
  const diffHours = Math.round(diffMinutes / 60)
  const diffDays = Math.round(diffHours / 24)

  if (Math.abs(diffDays) >= 1) return relativeFormatter.format(diffDays, 'day')
  if (Math.abs(diffHours) >= 1) return relativeFormatter.format(diffHours, 'hour')
  if (Math.abs(diffMinutes) >= 1) return relativeFormatter.format(diffMinutes, 'minute')
  return 'agora'
}

const dateFormatter = new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' })

export function formatDue(isoDate: string): string {
  const [year, month, day] = isoDate.split('-').map(Number)
  return dateFormatter.format(new Date(year, month - 1, day))
}
