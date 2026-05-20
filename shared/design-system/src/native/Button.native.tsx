import { Pressable, Text, StyleSheet, ActivityIndicator, type PressableProps } from 'react-native'
import { colors } from '../tokens/colors'
import { radii } from '../tokens/radii'
import { spacing } from '../tokens/spacing'

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger'
type Size = 'sm' | 'md' | 'lg'
type Context = 'guardian' | 'admin'

export interface NativeButtonProps extends Omit<PressableProps, 'style'> {
  variant?: Variant
  size?: Size
  context?: Context
  loading?: boolean
  fullWidth?: boolean
  children: string
}

const variantStyles: Record<Variant, { bg: string; text: string; border?: string }> = {
  primary:   { bg: colors.brand.primary700,  text: colors.neutral[0] },
  secondary: { bg: colors.neutral[0],        text: colors.brand.primary700, border: colors.neutral[200] },
  ghost:     { bg: 'transparent',            text: colors.brand.primary700 },
  danger:    { bg: colors.semantic.danger,   text: colors.neutral[0] },
}

const sizeStyles: Record<Size, { height: number; paddingHorizontal: number; fontSize: number; minWidth: number }> = {
  sm: { height: 32, paddingHorizontal: spacing[3], fontSize: 14, minWidth: 44 },
  md: { height: 40, paddingHorizontal: spacing[4], fontSize: 14, minWidth: 44 },
  lg: { height: 48, paddingHorizontal: spacing[6], fontSize: 16, minWidth: 44 },
}

const contextRadius: Record<Context, number> = {
  guardian: radii.lg,
  admin: radii.md,
}

export function Button({
  variant = 'primary',
  size = 'md',
  context = 'guardian',
  loading = false,
  fullWidth = false,
  disabled,
  children,
  ...props
}: NativeButtonProps) {
  const vs = variantStyles[variant]
  const ss = sizeStyles[size]
  const isDisabled = disabled || loading

  return (
    <Pressable
      accessibilityRole="button"
      disabled={isDisabled}
      style={({ pressed }) => [
        styles.base,
        {
          height: ss.height,
          paddingHorizontal: ss.paddingHorizontal,
          borderRadius: contextRadius[context],
          backgroundColor: vs.bg,
          borderWidth: vs.border ? 1 : 0,
          borderColor: vs.border ?? 'transparent',
          width: fullWidth ? '100%' : undefined,
          opacity: pressed ? 0.85 : isDisabled ? 0.5 : 1,
          minHeight: 44,
          minWidth: ss.minWidth,
        },
      ]}
      {...props}
    >
      {loading ? (
        <ActivityIndicator size="small" color={vs.text} />
      ) : (
        <Text style={[styles.label, { color: vs.text, fontSize: ss.fontSize }]}>
          {children}
        </Text>
      )}
    </Pressable>
  )
}

const styles = StyleSheet.create({
  base: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  label: {
    fontWeight: '600',
  },
})
