import { View, Text, StyleSheet } from 'react-native'
import { Image } from 'expo-image'
import { colors } from '../tokens/colors'

type AvatarSize = 24 | 32 | 40 | 56

interface NativeAvatarProps {
  src?: string | null
  name: string
  size?: AvatarSize
}

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

const fontSizeMap: Record<AvatarSize, number> = {
  24: 9,
  32: 12,
  40: 15,
  56: 20,
}

export function Avatar({ src, name, size = 40 }: NativeAvatarProps) {
  const initials = getInitials(name)
  const fontSize = fontSizeMap[size]

  if (src) {
    return (
      <Image
        source={src}
        style={{ width: size, height: size, borderRadius: size / 2 }}
        contentFit="cover"
        accessibilityLabel={name}
      />
    )
  }

  return (
    <View
      style={[
        styles.fallback,
        {
          width: size,
          height: size,
          borderRadius: size / 2,
        },
      ]}
      accessibilityLabel={name}
    >
      <Text style={[styles.initials, { fontSize }]}>{initials}</Text>
    </View>
  )
}

const styles = StyleSheet.create({
  fallback: {
    backgroundColor: colors.brand.primary100,
    alignItems: 'center',
    justifyContent: 'center',
  },
  initials: {
    color: colors.brand.primary700,
    fontWeight: '600',
  },
})
