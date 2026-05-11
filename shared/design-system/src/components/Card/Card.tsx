import { forwardRef } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../../lib/utils'

const cardVariants = cva('bg-neutral-0 border border-neutral-200', {
  variants: {
    variant: {
      default: 'shadow-sm',
      hero: 'shadow-md bg-gradient-to-br from-brand-primary-50 to-brand-primary-100 border-transparent',
      elevated: 'shadow-md border-transparent',
    },
    padding: {
      none: '',
      sm: 'p-4',
      md: 'p-5',
      lg: 'p-6',
    },
    context: {
      guardian: 'rounded-xl',
      admin: 'rounded-md',
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
  ({ variant, padding, context, className, children, ...props }, ref) => (
    <div
      ref={ref}
      className={cn(cardVariants({ variant, padding, context }), className)}
      {...props}
    >
      {children}
    </div>
  )
)

Card.displayName = 'Card'

export { cardVariants }
