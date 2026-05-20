import { useRef, useState } from 'react'
import {
  View,
  TextInput,
  Text,
  Pressable,
  Animated,
  StyleSheet,
  type TextInputProps,
} from 'react-native'
import { colors } from '../tokens/colors'
import { radii } from '../tokens/radii'
import { spacing } from '../tokens/spacing'

export interface NativeInputProps extends Omit<TextInputProps, 'placeholder'> {
  label: string
  error?: string
  type?: 'text' | 'email' | 'password' | 'numeric'
}

export function Input({ label, error, type = 'text', value, onFocus, onBlur, onChangeText, ...props }: NativeInputProps) {
  const [isFocused, setIsFocused] = useState(false)
  const [localHasValue, setLocalHasValue] = useState(Boolean(value))
  const [showPassword, setShowPassword] = useState(false)
  const labelAnim = useRef(new Animated.Value(value ? 1 : 0)).current

  const isPasswordField = type === 'password'
  const hasValue = value !== undefined ? Boolean(value) : localHasValue
  const isFloated = isFocused || hasValue

  function handleFocus(e: Parameters<NonNullable<TextInputProps['onFocus']>>[0]) {
    setIsFocused(true)
    Animated.timing(labelAnim, {
      toValue: 1,
      duration: 150,
      useNativeDriver: false,
    }).start()
    onFocus?.(e)
  }

  function handleBlur(e: Parameters<NonNullable<TextInputProps['onBlur']>>[0]) {
    setIsFocused(false)
    if (!hasValue) {
      Animated.timing(labelAnim, {
        toValue: 0,
        duration: 150,
        useNativeDriver: false,
      }).start()
    }
    onBlur?.(e)
  }

  function handleChangeText(text: string) {
    setLocalHasValue(Boolean(text))
    if (text && !isFloated) {
      Animated.timing(labelAnim, { toValue: 1, duration: 150, useNativeDriver: false }).start()
    }
    onChangeText?.(text)
  }

  const labelTop = labelAnim.interpolate({ inputRange: [0, 1], outputRange: [16, 6] })
  const labelFontSize = labelAnim.interpolate({ inputRange: [0, 1], outputRange: [14, 11] })
  const labelColor = error ? colors.semantic.danger : isFloated ? colors.brand.primary700 : colors.neutral[400]

  const borderColor = error
    ? colors.semantic.danger
    : isFocused
    ? colors.brand.primary500
    : colors.neutral[200]

  return (
    <View style={styles.wrapper}>
      <View
        style={[
          styles.inputContainer,
          { borderColor, borderWidth: isFocused ? 2 : 1 },
        ]}
        accessible
        accessibilityLabel={label}
      >
        <Animated.Text
          style={[
            styles.label,
            { top: labelTop, fontSize: labelFontSize, color: labelColor },
          ]}
          pointerEvents="none"
        >
          {label}
        </Animated.Text>

        <TextInput
          value={value}
          onFocus={handleFocus}
          onBlur={handleBlur}
          onChangeText={handleChangeText}
          secureTextEntry={isPasswordField && !showPassword}
          keyboardType={
            type === 'email' ? 'email-address' : type === 'numeric' ? 'numeric' : 'default'
          }
          autoCapitalize={type === 'email' ? 'none' : 'sentences'}
          style={[styles.input, isFloated && styles.inputFloated]}
          accessibilityLabel={label}
          {...props}
        />

        {isPasswordField && (
          <Pressable
            onPress={() => setShowPassword((v) => !v)}
            style={styles.passwordToggle}
            accessibilityLabel={showPassword ? 'Ocultar senha' : 'Mostrar senha'}
            hitSlop={8}
          >
            <Text style={styles.toggleText}>{showPassword ? '🙈' : '👁'}</Text>
          </Pressable>
        )}
      </View>

      {error && (
        <Text style={styles.errorText} accessibilityRole="alert">
          {error}
        </Text>
      )}
    </View>
  )
}

const styles = StyleSheet.create({
  wrapper: {
    gap: 4,
  },
  inputContainer: {
    height: 56,
    borderRadius: radii.lg,
    backgroundColor: colors.neutral[0],
    justifyContent: 'flex-end',
    paddingHorizontal: spacing[4],
    paddingBottom: spacing[1],
    minHeight: 44,
    position: 'relative',
  },
  label: {
    position: 'absolute',
    left: spacing[4],
  },
  input: {
    height: 24,
    fontSize: 14,
    color: colors.neutral[900],
    padding: 0,
  },
  inputFloated: {
    marginTop: 4,
  },
  passwordToggle: {
    position: 'absolute',
    right: spacing[4],
    top: '50%',
    transform: [{ translateY: -12 }],
    minHeight: 44,
    minWidth: 44,
    alignItems: 'center',
    justifyContent: 'center',
  },
  toggleText: {
    fontSize: 18,
  },
  errorText: {
    fontSize: 12,
    color: colors.semantic.danger,
    paddingHorizontal: spacing[1],
  },
})
