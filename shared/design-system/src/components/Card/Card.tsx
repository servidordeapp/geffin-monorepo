import { forwardRef } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../../lib/utils'
import { useAppearance } from '../../context/AppearanceContext'

const cardVariants = cva('bg-neutral-0 border border-neutral-200', {
  variants: {
    variant: {
      default:  'shadow-sm',
      hero:     'shadow-md bg-gradient-to-br from-brand-primary-50 to-brand-primary-100 border-transparent',
      elevated: 'shadow-md border-transparent',
      kpi:      'bg-neutral-0 shadow-sm',
    },
    padding: {
      none: '',
      sm:   'p-4',
      md:   'p-5',
      lg:   'p-6',
    },
    context: {
      guardian: 'rounded-xl',
      admin:    'rounded-md',
    },
  },
  defaultVariants: {
    variant: 'default',
    padding: 'lg',
    context: 'guardian',
  },
})

export interface CardProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof cardVariants> {}

export const Card = forwardRef<HTMLDivElement, CardProps>(
  ({ variant, padding, context: contextProp, className, children, ...props }, ref) => {
    let resolvedContext = contextProp
    if (resolvedContext === undefined || resolvedContext === null) {
      try {
        // eslint-disable-next-line react-hooks/rules-of-hooks
        resolvedContext = useAppearance().context
      } catch {
        resolvedContext = 'guardian'
      }
    }

    return (
      <div
        ref={ref}
        className={cn(cardVariants({ variant, padding, context: resolvedContext }), className)}
        {...props}
      >
        {children}
      </div>
    )
  }
)

Card.displayName = 'Card'

export { cardVariants }
