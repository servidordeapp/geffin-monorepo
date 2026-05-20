import { useCallback, useRef, useState } from 'react'
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native'
import Animated, {
  useSharedValue,
  useAnimatedStyle,
  withSequence,
  withTiming,
} from 'react-native-reanimated'
import Toast from 'react-native-toast-message'
import { Button } from '@gfn/design-system/native'
import { Input } from '@gfn/design-system/native'
import { colors } from '@gfn/design-system/tokens'
import { radii } from '@gfn/design-system/tokens'
import { useAuth } from '../hooks/useAuth'
import { t } from '../lib/i18n'

export default function LoginScreen() {
  const { login, isLoading } = useAuth()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')

  const shakeX = useSharedValue(0)
  const formStyle = useAnimatedStyle(() => ({ transform: [{ translateX: shakeX.value }] }))

  const shake = useCallback(() => {
    shakeX.value = withSequence(
      withTiming(-8, { duration: 60 }),
      withTiming(8, { duration: 60 }),
      withTiming(-6, { duration: 50 }),
      withTiming(6, { duration: 50 }),
      withTiming(0, { duration: 50 })
    )
  }, [shakeX])

  async function handleLogin() {
    if (!email || !password) {
      shake()
      return
    }

    try {
      await login(email, password)
    } catch (err: any) {
      const code = err?.code
      if (code === 'INVALID_CREDENTIALS') {
        shake()
        Toast.show({
          type: 'error',
          text1: t('login.errors.invalidCredentials'),
        })
      } else {
        Toast.show({
          type: 'error',
          text1: t('login.errors.networkError'),
        })
      }
    }
  }

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        {/* Logo + heading */}
        <View style={styles.header}>
          <View style={styles.logoMark}>
            <Text style={styles.logoText}>GFN</Text>
          </View>
          <Text style={styles.title}>{t('login.title')}</Text>
          <Text style={styles.subtitle}>{t('login.subtitle')}</Text>
        </View>

        {/* Form card */}
        <Animated.View style={[styles.card, formStyle]}>
          <View style={styles.fields}>
            <Input
              label={t('login.email')}
              type="email"
              value={email}
              onChangeText={setEmail}
              autoComplete="email"
              textContentType="emailAddress"
            />
            <Input
              label={t('login.password')}
              type="password"
              value={password}
              onChangeText={setPassword}
              autoComplete="current-password"
              textContentType="password"
            />
          </View>

          <Pressable style={styles.forgotLink}>
            <Text style={styles.forgotText}>{t('login.forgotPassword')}</Text>
          </Pressable>

          <Button
            variant="primary"
            size="lg"
            fullWidth
            loading={isLoading}
            onPress={handleLogin}
            disabled={isLoading}
          >
            {isLoading ? t('login.signingIn') : t('login.signIn')}
          </Button>

          <View style={styles.divider}>
            <View style={styles.dividerLine} />
            <Text style={styles.dividerText}>{t('login.orContinueWith')}</Text>
            <View style={styles.dividerLine} />
          </View>

          <View style={styles.socialRow}>
            <Pressable style={styles.socialButton} accessibilityLabel="Entrar com Google">
              <Text style={styles.socialButtonText}>Google</Text>
            </Pressable>
            <Pressable style={styles.socialButton} accessibilityLabel="Entrar com Apple">
              <Text style={styles.socialButtonText}>Apple</Text>
            </Pressable>
          </View>
        </Animated.View>
      </ScrollView>
    </KeyboardAvoidingView>
  )
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.brand.primary50 },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 24,
    paddingTop: 64,
    paddingBottom: 40,
    justifyContent: 'center',
    gap: 24,
  },
  header: { alignItems: 'center', gap: 8 },
  logoMark: {
    width: 56,
    height: 56,
    borderRadius: radii.lg,
    backgroundColor: colors.brand.primary700,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  logoText: { color: colors.neutral[0], fontSize: 18, fontWeight: '800', letterSpacing: 1 },
  title: { fontSize: 24, fontWeight: '700', color: colors.brand.primary900, textAlign: 'center' },
  subtitle: { fontSize: 15, color: colors.neutral[600], textAlign: 'center' },

  card: {
    backgroundColor: colors.neutral[0],
    borderRadius: radii.xl,
    padding: 24,
    gap: 16,
    shadowColor: colors.neutral[900],
    shadowOpacity: 0.06,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 4 },
    elevation: 4,
  },
  fields: { gap: 12 },

  forgotLink: { alignSelf: 'flex-end' },
  forgotText: { fontSize: 13, color: colors.brand.primary700, fontWeight: '500' },

  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginVertical: 4,
  },
  dividerLine: { flex: 1, height: 1, backgroundColor: colors.neutral[200] },
  dividerText: { fontSize: 13, color: colors.neutral[400] },

  socialRow: { flexDirection: 'row', gap: 12 },
  socialButton: {
    flex: 1,
    height: 44,
    borderRadius: radii.lg,
    borderWidth: 1,
    borderColor: colors.neutral[200],
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.neutral[0],
  },
  socialButtonText: { fontSize: 14, fontWeight: '500', color: colors.neutral[900] },
})
