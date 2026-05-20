import { useState, useCallback } from 'react'
import * as SecureStore from 'expo-secure-store'
import { postLogin } from '../lib/api'
import { useRouter } from 'expo-router'

export function useAuth() {
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()

  const login = useCallback(async (email: string, password: string) => {
    setIsLoading(true)
    try {
      const result = await postLogin(email, password)
      if (result.data?.requiresMfa) {
        router.push('/(auth)/mfa')
        return result
      }
      if (result.data?.token) {
        await SecureStore.setItemAsync('auth_token', result.data.token)
        router.replace('/(app)/')
      }
      return result
    } finally {
      setIsLoading(false)
    }
  }, [router])

  const logout = useCallback(async () => {
    await SecureStore.deleteItemAsync('auth_token')
    router.replace('/(auth)/login')
  }, [router])

  return { login, logout, isLoading }
}
