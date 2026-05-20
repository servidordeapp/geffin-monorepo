import { forwardRef } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../../lib/utils'

const badgeVariants = cva(
  'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium',
  {
    variants: {
      variant: {
        neutral: 'bg-neutral-100 text-neutral-600',
        success: 'bg-accent-green-100 text-accent-green-700',
        warning: 'bg-amber-100 text-amber-700',
        danger:  'bg-red-100 text-semantic-danger',
        info:    'bg-brand-primary-100 text-brand-primary-700',
      },
    },
    defaultVariants: {
      variant: 'neutral',
    },
  }
)

const dotColors: Record<NonNullable<BadgeProps['variant']>, string> = {
  neutral: 'bg-neutral-400',
  success: 'bg-accent-green-500',
  warning: 'bg-amber-500',
  danger:  'bg-semantic-danger',
  info:    'bg-brand-primary-500',
}

export interface BadgeProps
  extends React.HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof badgeVariants> {
  dot?: boolean
}

export const Badge = forwardRef<HTMLSpanElement, BadgeProps>(
  ({ variant = 'neutral', dot = false, className, children, ...props }, ref) => (
    <span
      ref={ref}
      className={cn(badgeVariants({ variant }), className)}
      {...props}
    >
      {dot && (
        <span
          className={cn('size-2 rounded-full shrink-0', dotColors[variant || 'neutral'])}
          aria-hidden="true"
        />
      )}
      {children}
    </span>
  )
)

Badge.displayName = 'Badge'

export { badgeVariants }
