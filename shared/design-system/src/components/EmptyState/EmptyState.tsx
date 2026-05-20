import { Button } from '../Button'
import { cn } from '../../lib/utils'

export interface EmptyStateProps {
  icon?: React.ReactNode
  title: string
  description: string
  actionLabel?: string
  onAction?: () => void
  className?: string
}

export function EmptyState({
  icon,
  title,
  description,
  actionLabel,
  onAction,
  className,
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        'flex flex-col items-center justify-center gap-3 py-12 text-center',
        className
      )}
    >
      {icon && (
        <span className="text-neutral-400 [&>svg]:size-12" aria-hidden="true">
          {icon}
        </span>
      )}
      <div className="flex flex-col gap-1">
        <p className="text-base font-semibold text-neutral-900">{title}</p>
        <p className="text-sm text-neutral-400">{description}</p>
      </div>
      {actionLabel && onAction && (
        <Button variant="secondary" size="sm" onClick={onAction}>
          {actionLabel}
        </Button>
      )}
    </div>
  )
}
