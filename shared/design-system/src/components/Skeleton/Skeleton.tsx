import { cn } from '../../lib/utils'

export interface SkeletonProps {
  className?: string
}

export function Skeleton({ className }: SkeletonProps) {
  return (
    <div
      className={cn(
        'rounded bg-neutral-100',
        'animate-pulse',
        '@media(prefers-reduced-motion:reduce):animate-none',
        className
      )}
      aria-hidden="true"
    />
  )
}
