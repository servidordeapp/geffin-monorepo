import { Pressable, View, Text, StyleSheet } from 'react-native'
import { Badge } from '@gfn/design-system/native'
import { colors } from '@gfn/design-system/tokens'
import { radii } from '@gfn/design-system/tokens'
import { formatBRL, formatDue } from '../lib/format'
import type { Payment } from '../lib/types'

const statusBadgeMap: Record<Payment['status'], { variant: 'success' | 'warning' | 'danger' | 'info' | 'neutral'; label: string }> = {
  paid:     { variant: 'success', label: 'Pago' },
  dueSoon:  { variant: 'warning', label: 'Próximo' },
  overdue:  { variant: 'danger',  label: 'Atrasado' },
  onTime:   { variant: 'success', label: 'Em dia' },
}

interface Props {
  payment: Payment
}

export function DueItemCard({ payment }: Props) {
  const badge = statusBadgeMap[payment.status]

  return (
    <Pressable style={styles.row} android_ripple={{ color: colors.neutral[100] }}>
      <View style={styles.info}>
        <Text style={styles.description} numberOfLines={1}>{payment.description}</Text>
        <Text style={styles.dueDate}>{formatDue(payment.dueDate)}</Text>
      </View>
      <View style={styles.right}>
        <Text style={styles.amount}>{formatBRL(payment.amountCents)}</Text>
        <Badge variant={badge.variant} label={badge.label} dot />
      </View>
    </Pressable>
  )
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: colors.neutral[0],
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.neutral[200],
    gap: 12,
  },
  info: { flex: 1 },
  description: { fontSize: 14, fontWeight: '500', color: colors.neutral[900] },
  dueDate: { fontSize: 12, color: colors.neutral[400], marginTop: 2 },
  right: { alignItems: 'flex-end', gap: 4 },
  amount: { fontSize: 14, fontWeight: '600', color: colors.neutral[900], fontVariant: ['tabular-nums'] as any },
})
