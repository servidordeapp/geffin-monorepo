import { forwardRef } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../../lib/utils'

const avatarVariants = cva(
  'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-primary-100 text-brand-primary-700 font-medium select-none',
  {
    variants: {
      size: {
        24: 'size-6 text-[0.5rem]',
        32: 'size-8 text-xs',
        40: 'size-10 text-sm',
        56: 'size-14 text-base',
      },
    },
    defaultVariants: {
      size: 40,
    },
  }
)

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

export interface AvatarProps
  extends Omit<React.HTMLAttributes<HTMLDivElement>, 'children'>,
    VariantProps<typeof avatarVariants> {
  src?: string
  alt?: string
  name?: string
}

export const Avatar = forwardRef<HTMLDivElement, AvatarProps>(
  ({ src, alt, name, size, className, ...props }, ref) => {
    const initials = name ? getInitials(name) : null

    return (
      <div
        ref={ref}
        className={cn(avatarVariants({ size }), className)}
        {...props}
      >
        {src ? (
          <img
            src={src}
            alt={alt ?? name ?? 'Avatar'}
            className="size-full object-cover"
          />
        ) : initials ? (
          <span aria-label={name}>{initials}</span>
        ) : (
          <span className="size-full bg-neutral-200" aria-hidden="true" />
        )}
      </div>
    )
  }
)

Avatar.displayName = 'Avatar'
