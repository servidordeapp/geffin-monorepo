import { FlatList, Pressable, StyleSheet, Text, View } from 'react-native'
import { colors } from '@gfn/design-system/tokens'
import { radii } from '@gfn/design-system/tokens'
import { t } from '../lib/i18n'

const ITEMS = [
  { key: 'mensalidades', icon: '📋', label: 'Mensalidades' },
  { key: 'cantina',      icon: '🍽️', label: 'Cantina' },
  { key: 'loja',         icon: '🛍️', label: 'Loja' },
  { key: 'boletos',      icon: '📄', label: 'Boletos' },
] as const

export function QuickAccessGrid() {
  return (
    <FlatList
      data={ITEMS}
      numColumns={4}
      scrollEnabled={false}
      keyExtractor={(item) => item.key}
      columnWrapperStyle={styles.row}
      renderItem={({ item }) => (
        <Pressable style={styles.item} android_ripple={{ color: colors.brand.primary100 }}>
          <Text style={styles.icon}>{item.icon}</Text>
          <Text style={styles.label} numberOfLines={1}>{item.label}</Text>
        </Pressable>
      )}
    />
  )
}

const styles = StyleSheet.create({
  row: { justifyContent: 'space-between', gap: 8 },
  item: {
    flex: 1,
    aspectRatio: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.neutral[200],
    backgroundColor: colors.neutral[0],
    padding: 8,
    minHeight: 80,
    minWidth: 44,
  },
  icon: { fontSize: 22 },
  label: { fontSize: 11, fontWeight: '500', color: colors.neutral[600], textAlign: 'center' },
})
