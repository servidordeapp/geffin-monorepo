import { AlertTriangle } from 'lucide-react'
import { Button } from '../Button'
import { cn } from '../../lib/utils'

export interface ErrorStateProps {
  title: string
  onRetry: () => void
  className?: string
}

export function ErrorState({ title, onRetry, className }: ErrorStateProps) {
  return (
    <div
      className={cn(
        'flex flex-col items-center justify-center gap-3 py-12 text-center',
        className
      )}
    >
      <AlertTriangle
        className="size-12 text-semantic-warning"
        aria-hidden="true"
      />
      <p className="text-base font-semibold text-neutral-900">{title}</p>
      <Button variant="secondary" size="sm" onClick={onRetry}>
        Tentar novamente
      </Button>
    </div>
  )
}
