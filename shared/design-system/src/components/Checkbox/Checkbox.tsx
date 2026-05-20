import { useId } from 'react'
import { cn } from '../../lib/utils'

export interface CheckboxProps {
  label: string
  checked: boolean
  onChange: (checked: boolean) => void
  disabled?: boolean
  className?: string
}

export function Checkbox({ label, checked, onChange, disabled, className }: CheckboxProps) {
  const id = useId()

  return (
    <label
      htmlFor={id}
      className={cn(
        'inline-flex items-center gap-2 cursor-pointer select-none',
        disabled && 'opacity-50 cursor-not-allowed',
        className
      )}
    >
      <input
        id={id}
        type="checkbox"
        role="checkbox"
        checked={checked}
        disabled={disabled}
        aria-checked={checked}
        onChange={(e) => onChange(e.target.checked)}
        className={cn(
          'size-4 rounded border border-neutral-200 bg-neutral-0',
          'text-brand-primary-700 accent-brand-primary-700',
          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary-500 focus-visible:ring-offset-2',
          'cursor-pointer disabled:cursor-not-allowed'
        )}
      />
      <span className="text-sm text-neutral-900">{label}</span>
    </label>
  )
}
