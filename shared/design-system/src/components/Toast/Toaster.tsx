import { cn } from '../../lib/utils'
import { Toast } from './Toast'
import type { ToastItem } from './useToast'

interface ToasterProps {
  toasts: ToastItem[]
  onDismiss: (id: string) => void
  /**
   * web:    top-right corner
   * mobile: top-center (full width)
   */
  position?: 'top-right' | 'top-center'
}

export function Toaster({ toasts, onDismiss, position = 'top-right' }: ToasterProps) {
  if (!toasts.length) return null

  return (
    <div
      aria-label="Notificações"
      className={cn(
        'fixed z-50 flex flex-col gap-2 p-4 pointer-events-none',
        position === 'top-right' && 'top-0 right-0 items-end',
        position === 'top-center' && 'top-0 left-0 right-0 items-center'
      )}
    >
      {toasts.map((t) => (
        <Toast
          key={t.id}
          {...t}
          onDismiss={() => onDismiss(t.id)}
        />
      ))}
    </div>
  )
}
