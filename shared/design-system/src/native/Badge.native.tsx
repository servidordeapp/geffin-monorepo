import { View, Text, StyleSheet } from 'react-native'
import { colors } from '../tokens/colors'
import { radii } from '../tokens/radii'

type BadgeVariant = 'success' | 'warning' | 'danger' | 'info' | 'neutral'

const variantColors: Record<BadgeVariant, { bg: string; text: string; dot: string }> = {
  success: { bg: colors.accent.green100,     text: colors.accent.green700,    dot: colors.accent.green500 },
  warning: { bg: '#FEF3C7',                  text: '#92400E',                 dot: colors.semantic.warning },
  danger:  { bg: '#FEE2E2',                  text: '#991B1B',                 dot: colors.semantic.danger },
  info:    { bg: colors.brand.primary100,    text: colors.brand.primary700,   dot: colors.brand.primary500 },
  neutral: { bg: colors.neutral[100],        text: colors.neutral[600],       dot: colors.neutral[400] },
}

interface NativeBadgeProps {
  variant?: BadgeVariant
  label: string
  dot?: boolean
}

export function Badge({ variant = 'neutral', label, dot = false }: NativeBadgeProps) {
  const c = variantColors[variant]

  return (
    <View style={[styles.container, { backgroundColor: c.bg }]}>
      {dot && <View style={[styles.dot, { backgroundColor: c.dot }]} />}
      <Text style={[styles.label, { color: c.text }]}>{label}</Text>
    </View>
  )
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: radii.full,
    alignSelf: 'flex-start',
  },
  dot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  label: {
    fontSize: 12,
    fontWeight: '500',
  },
})
