import { useRef, useState } from 'react'
import { Dimensions, FlatList, Pressable, StyleSheet, Text, View } from 'react-native'
import { Card } from '@gfn/design-system/native'
import { Avatar } from '@gfn/design-system/native'
import { Button } from '@gfn/design-system/native'
import { EmptyState } from '@gfn/design-system'
import { colors } from '@gfn/design-system/tokens'
import { radii } from '@gfn/design-system/tokens'
import { formatBRL } from '../lib/format'
import type { Child } from '../lib/types'

const { width: SCREEN_WIDTH } = Dimensions.get('window')
const CARD_WIDTH = SCREEN_WIDTH - 48

interface Props {
  children: Child[]
}

export function ChildCarousel({ children }: Props) {
  const [activeIndex, setActiveIndex] = useState(0)

  if (!children.length) {
    return (
      <EmptyState
        title="Nenhum aluno encontrado"
        description="Verifique seu cadastro."
      />
    )
  }

  return (
    <View style={styles.container}>
      <FlatList
        data={children}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        snapToInterval={CARD_WIDTH + 16}
        decelerationRate="fast"
        contentContainerStyle={styles.list}
        keyExtractor={(child) => child.id}
        onMomentumScrollEnd={(e) => {
          const idx = Math.round(e.nativeEvent.contentOffset.x / (CARD_WIDTH + 16))
          setActiveIndex(idx)
        }}
        renderItem={({ item: child }) => {
          const isNegative = child.balance < 0
          const balanceColor = isNegative ? colors.semantic.danger : colors.accent.green700

          return (
            <Card variant="hero" padding="md" context="guardian" style={{ width: CARD_WIDTH }}>
              <View style={styles.cardHeader}>
                <Avatar src={child.avatarUrl} name={child.name} size={56} />
                <View style={styles.childInfo}>
                  <Text style={styles.childName} numberOfLines={1}>{child.name}</Text>
                  <Text style={styles.childMeta}>{child.grade} • {child.school}</Text>
                </View>
              </View>

              <View style={styles.balanceSection}>
                <Text style={styles.balanceLabel}>Saldo disponível</Text>
                <Text style={[styles.balanceValue, { color: balanceColor }]}>
                  {formatBRL(child.balance)}
                </Text>
              </View>

              <View style={styles.cardActions}>
                <Button variant="primary" size="sm" context="guardian">
                  Recarregar
                </Button>
                <Button variant="ghost" size="sm" context="guardian">
                  Ver extrato
                </Button>
              </View>
            </Card>
          )
        }}
      />

      {children.length > 1 && (
        <View style={styles.dots}>
          {children.map((_, i) => (
            <View
              key={i}
              style={[
                styles.dot,
                { backgroundColor: i === activeIndex ? colors.brand.primary700 : colors.neutral[200] },
              ]}
            />
          ))}
        </View>
      )}
    </View>
  )
}

const styles = StyleSheet.create({
  container: { gap: 12 },
  list: { gap: 16, paddingHorizontal: 0 },
  cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 16 },
  childInfo: { flex: 1 },
  childName: { fontSize: 18, fontWeight: '700', color: colors.brand.primary900 },
  childMeta: { fontSize: 13, color: colors.brand.primary700, marginTop: 2 },
  balanceSection: { marginBottom: 16 },
  balanceLabel: { fontSize: 12, color: colors.neutral[600], marginBottom: 4 },
  balanceValue: { fontSize: 36, fontWeight: '700', fontVariant: ['tabular-nums'] as any },
  cardActions: { flexDirection: 'row', gap: 8 },
  dots: { flexDirection: 'row', justifyContent: 'center', gap: 6 },
  dot: { width: 6, height: 6, borderRadius: 3 },
})
