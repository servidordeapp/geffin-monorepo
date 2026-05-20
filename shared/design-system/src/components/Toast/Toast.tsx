'use client'

import { useEffect, forwardRef } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { CheckCircle2, AlertTriangle, XCircle, Info, X } from 'lucide-react'
import { cn } from '../../lib/utils'

const toastVariants = cva(
  [
    'flex items-start gap-3 w-full max-w-sm rounded-lg border px-4 py-3 shadow-lg',
    'pointer-events-auto',
  ],
  {
    variants: {
      variant: {
        success: 'bg-neutral-0 border-accent-green-500 text-neutral-900',
        warning: 'bg-neutral-0 border-semantic-warning text-neutral-900',
        danger:  'bg-neutral-0 border-semantic-danger text-neutral-900',
        info:    'bg-neutral-0 border-brand-primary-500 text-neutral-900',
      },
    },
    defaultVariants: {
      variant: 'info',
    },
  }
)

const icons = {
  success: <CheckCircle2 className="size-5 text-accent-green-500 shrink-0" aria-hidden="true" />,
  warning: <AlertTriangle className="size-5 text-semantic-warning shrink-0" aria-hidden="true" />,
  danger:  <XCircle      className="size-5 text-semantic-danger  shrink-0" aria-hidden="true" />,
  info:    <Info         className="size-5 text-brand-primary-500 shrink-0" aria-hidden="true" />,
}

export interface ToastProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof toastVariants> {
  title: string
  description?: string
  onDismiss?: () => void
  /** Auto-dismiss after ms. Set to 0 to disable. Default: 4000 */
  duration?: number
}

export const Toast = forwardRef<HTMLDivElement, ToastProps>(
  (
    {
      variant = 'info',
      title,
      description,
      onDismiss,
      duration = 4000,
      className,
      ...props
    },
    ref
  ) => {
    useEffect(() => {
      if (!duration || !onDismiss) return
      const timer = setTimeout(onDismiss, duration)
      return () => clearTimeout(timer)
    }, [duration, onDismiss])

    return (
      <div
        ref={ref}
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        className={cn(toastVariants({ variant }), className)}
        {...props}
      >
        {icons[variant!]}

        <div className="flex-1 min-w-0">
          <p className="text-sm font-medium text-neutral-900">{title}</p>
          {description && (
            <p className="mt-0.5 text-sm text-neutral-600">{description}</p>
          )}
        </div>

        {onDismiss && (
          <button
            type="button"
            onClick={onDismiss}
            className="shrink-0 text-neutral-400 hover:text-neutral-600 transition-colors"
            aria-label="Fechar notificação"
          >
            <X size={16} aria-hidden="true" />
          </button>
        )}
      </div>
    )
  }
)

Toast.displayName = 'Toast'
