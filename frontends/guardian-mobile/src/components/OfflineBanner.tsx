import { useEffect, useState } from 'react'
import { View, Text, StyleSheet, Animated } from 'react-native'
import NetInfo from '@react-native-community/netinfo'
import { colors } from '@gfn/design-system/tokens'

export function OfflineBanner() {
  const [isOffline, setIsOffline] = useState(false)
  const opacity = new Animated.Value(0)

  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener((state) => {
      const offline = state.isConnected === false
      setIsOffline(offline)
      Animated.timing(opacity, {
        toValue: offline ? 1 : 0,
        duration: 300,
        useNativeDriver: true,
      }).start()
    })
    return unsubscribe
  }, [])

  if (!isOffline) return null

  return (
    <Animated.View style={[styles.banner, { opacity }]}>
      <Text style={styles.text}>Você está offline. Tentando reconectar...</Text>
    </Animated.View>
  )
}

const styles = StyleSheet.create({
  banner: {
    backgroundColor: colors.neutral[600],
    paddingVertical: 8,
    paddingHorizontal: 16,
    alignItems: 'center',
  },
  text: {
    color: colors.neutral[0],
    fontSize: 13,
    fontWeight: '500',
  },
})
