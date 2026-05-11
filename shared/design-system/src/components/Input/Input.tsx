import { forwardRef, useId, useRef, useState } from 'react'
import { Eye, EyeOff } from 'lucide-react'
import { cn } from '../../lib/utils'

export interface InputProps
  extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'id' | 'placeholder'> {
  label: string
  error?: string
  leftIcon?: React.ReactNode
  /**
   * guardian: rounded-lg (12px), h-14 (56px)
   * admin:    rounded-sm (6px),  h-11 (44px)
   */
  context?: 'guardian' | 'admin'
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      label,
      error,
      leftIcon,
      context = 'guardian',
      className,
      type = 'text',
      value,
      defaultValue,
      disabled,
      onChange,
      onFocus,
      onBlur,
      ...props
    },
    forwardedRef
  ) => {
    const generatedId = useId()
    const id = props.id ?? generatedId
    const internalRef = useRef<HTMLInputElement>(null)
    const ref = (forwardedRef ?? internalRef) as React.RefObject<HTMLInputElement>

    const [isFocused, setIsFocused] = useState(false)
    const [localHasValue, setLocalHasValue] = useState(() => Boolean(defaultValue))

    const isControlled = value !== undefined
    const hasValue = isControlled ? Boolean(value) : localHasValue
    const isFloated = isFocused || hasValue

    const [showPassword, setShowPassword] = useState(false)
    const isPassword = type === 'password'
    const resolvedType = isPassword && showPassword ? 'text' : type

    const isGuardian = context === 'guardian'

    const wrapperHeight = isGuardian ? 'h-14' : 'h-11'
    const wrapperRadius = isGuardian ? 'rounded-lg' : 'rounded-sm'
    const borderBase = error
      ? 'border-semantic-danger'
      : 'border-neutral-200'
    const borderFocus = error
      ? 'focus-within:border-semantic-danger focus-within:ring-0'
      : 'focus-within:border-brand-primary-500 focus-within:ring-2 focus-within:ring-brand-primary-500/20'

    return (
      <div className={cn('flex flex-col gap-1.5', className)}>
        <div
          className={cn(
            'relative flex items-center border bg-neutral-0 transition-all',
            wrapperHeight,
            wrapperRadius,
            borderBase,
            borderFocus,
            disabled && 'bg-neutral-50 opacity-60 cursor-not-allowed'
          )}
        >
          {leftIcon && (
            <span
              className="absolute left-4 text-neutral-400 pointer-events-none"
              aria-hidden="true"
            >
              {leftIcon}
            </span>
          )}

          <input
            ref={ref}
            id={id}
            type={resolvedType}
            value={value}
            defaultValue={defaultValue}
            disabled={disabled}
            aria-invalid={Boolean(error)}
            aria-describedby={error ? `${id}-error` : undefined}
            onChange={(e) => {
              if (!isControlled) setLocalHasValue(Boolean(e.target.value))
              onChange?.(e)
            }}
            onFocus={(e) => {
              setIsFocused(true)
              onFocus?.(e)
            }}
            onBlur={(e) => {
              setIsFocused(false)
              onBlur?.(e)
            }}
            className={cn(
              'peer w-full bg-transparent text-sm text-neutral-900 outline-none transition-all',
              'placeholder-transparent',
              leftIcon ? 'pl-11' : 'pl-4',
              isPassword ? 'pr-11' : 'pr-4',
              isFloated ? 'pt-4 pb-1' : 'py-3'
            )}
            placeholder=" "
            {...props}
          />

          <label
            htmlFor={id}
            className={cn(
              'absolute pointer-events-none select-none transition-all',
              leftIcon ? 'left-11' : 'left-4',
              isFloated
                ? 'top-2 text-[0.6875rem] font-medium leading-none text-brand-primary-700'
                : 'top-1/2 -translate-y-1/2 text-sm text-neutral-400',
              error && isFloated && 'text-semantic-danger'
            )}
          >
            {label}
          </label>

          {isPassword && (
            <button
              type="button"
              tabIndex={-1}
              onClick={() => setShowPassword((v) => !v)}
              className="absolute right-4 text-neutral-400 hover:text-neutral-600 transition-colors"
              aria-label={showPassword ? 'Ocultar senha' : 'Mostrar senha'}
            >
              {showPassword ? (
                <EyeOff size={18} aria-hidden="true" />
              ) : (
                <Eye size={18} aria-hidden="true" />
              )}
            </button>
          )}
        </div>

        {error && (
          <p id={`${id}-error`} role="alert" className="text-xs text-semantic-danger px-1">
            {error}
          </p>
        )}
      </div>
    )
  }
)

Input.displayName = 'Input'
