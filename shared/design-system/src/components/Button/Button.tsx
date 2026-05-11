import { forwardRef } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { Loader2 } from 'lucide-react'
import { cn } from '../../lib/utils'

const buttonVariants = cva(
  [
    'inline-flex items-center justify-center font-medium transition-colors',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary-500 focus-visible:ring-offset-2',
    'disabled:pointer-events-none disabled:opacity-50',
    'cursor-pointer',
  ],
  {
    variants: {
      variant: {
        primary: [
          'bg-brand-primary-700 text-white',
          'hover:bg-brand-primary-900 active:bg-brand-primary-900',
        ],
        secondary: [
          'bg-neutral-0 text-brand-primary-700 border border-neutral-200',
          'hover:bg-neutral-50 active:bg-neutral-100',
        ],
        ghost: [
          'text-brand-primary-700 border border-transparent',
          'hover:bg-brand-primary-50 active:bg-brand-primary-100',
        ],
        danger: [
          'bg-semantic-danger text-white',
          'hover:brightness-90 active:brightness-80',
        ],
      },
      size: {
        sm: 'h-8 px-3 text-sm gap-1.5',
        md: 'h-10 px-4 text-sm gap-2',
        lg: 'h-12 px-6 text-base gap-2',
      },
      /**
       * guardian: rounded-lg (12px) — warmer, for responsável product
       * admin:    rounded-md  (8px)  — sober, for escola admin product
       */
      context: {
        guardian: 'rounded-lg',
        admin: 'rounded-md',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
      context: 'guardian',
    },
  }
)

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  loading?: boolean
  leftIcon?: React.ReactNode
  rightIcon?: React.ReactNode
  fullWidth?: boolean
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      variant,
      size,
      context,
      loading = false,
      leftIcon,
      rightIcon,
      fullWidth = false,
      className,
      children,
      disabled,
      ...props
    },
    ref
  ) => (
    <button
      ref={ref}
      className={cn(
        buttonVariants({ variant, size, context }),
        fullWidth && 'w-full',
        className
      )}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? (
        <Loader2 className="size-4 animate-spin" aria-hidden="true" />
      ) : (
        leftIcon && <span aria-hidden="true">{leftIcon}</span>
      )}
      {children}
      {!loading && rightIcon && <span aria-hidden="true">{rightIcon}</span>}
    </button>
  )
)

Button.displayName = 'Button'

export { buttonVariants }
