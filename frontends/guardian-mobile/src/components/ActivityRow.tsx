import { View, Text, StyleSheet } from 'react-native'
import { colors } from '@gfn/design-system/tokens'
import { formatBRL, formatRelative } from '../lib/format'
import type { Activity } from '../lib/types'

interface Props {
  activity: Activity
}

export function ActivityRow({ activity }: Props) {
  const isCredit = activity.type === 'credit'
  const amountColor = isCredit ? colors.accent.green700 : colors.semantic.danger
  const dotColor = isCredit ? colors.accent.green500 : colors.semantic.danger
  const amountPrefix = isCredit ? '+' : '-'

  return (
    <View style={styles.row}>
      <View style={[styles.dot, { backgroundColor: dotColor }]} />
      <View style={styles.content}>
        <Text style={styles.description} numberOfLines={1}>
          {activity.description}
        </Text>
        <Text style={styles.timestamp}>{formatRelative(activity.timestamp)}</Text>
      </View>
      <Text style={[styles.amount, { color: amountColor }]}>
        {amountPrefix}{formatBRL(activity.amountCents)}
      </Text>
    </View>
  )
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    height: 56,
    gap: 12,
    paddingHorizontal: 4,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    flexShrink: 0,
  },
  content: { flex: 1 },
  description: { fontSize: 14, fontWeight: '500', color: colors.neutral[900] },
  timestamp: { fontSize: 12, color: colors.neutral[400], marginTop: 2 },
  amount: { fontSize: 14, fontWeight: '600', fontVariant: ['tabular-nums'] as any },
})
