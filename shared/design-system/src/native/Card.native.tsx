import { View, StyleSheet, type ViewProps } from 'react-native'
import { LinearGradient } from 'expo-linear-gradient'
import { colors } from '../tokens/colors'
import { radii } from '../tokens/radii'
import { shadowsRN } from '../tokens/shadows'

type Variant = 'default' | 'hero' | 'elevated' | 'kpi'
type Context = 'guardian' | 'admin'
type Padding = 'none' | 'sm' | 'md' | 'lg'

const paddingMap: Record<Padding, number> = {
  none: 0,
  sm: 16,
  md: 20,
  lg: 24,
}

const contextRadius: Record<Context, number> = {
  guardian: radii.xl,
  admin: radii.md,
}

export interface NativeCardProps extends ViewProps {
  variant?: Variant
  padding?: Padding
  context?: Context
  children: React.ReactNode
}

export function Card({
  variant = 'default',
  padding = 'lg',
  context = 'guardian',
  style,
  children,
  ...props
}: NativeCardProps) {
  const p = paddingMap[padding]
  const r = contextRadius[context]

  if (variant === 'hero') {
    return (
      <LinearGradient
        colors={[colors.brand.primary50, colors.brand.primary100]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={[styles.base, { borderRadius: r, padding: p, ...shadowsRN.md }, style]}
        {...props}
      >
        {children}
      </LinearGradient>
    )
  }

  return (
    <View
      style={[
        styles.base,
        {
          borderRadius: r,
          padding: p,
          backgroundColor: colors.neutral[0],
          borderColor: colors.neutral[200],
          borderWidth: 1,
          ...(variant === 'elevated' ? shadowsRN.md : shadowsRN.sm),
        },
        style,
      ]}
      {...props}
    >
      {children}
    </View>
  )
}

const styles = StyleSheet.create({
  base: {
    overflow: 'hidden',
  },
})
