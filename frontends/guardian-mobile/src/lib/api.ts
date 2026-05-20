import * as SecureStore from 'expo-secure-store'

const BFF_URL = process.env.EXPO_PUBLIC_BFF_GUARDIAN_URL ?? ''

async function getAuthHeaders(): Promise<Record<string, string>> {
  const token = await SecureStore.getItemAsync('auth_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export async function postLogin(email: string, password: string) {
  if (!BFF_URL) {
    const fixtures = await import('../../../specs/002-initial-screens/fixtures/auth-responses.json')
    return fixtures.guardian_login_success
  }

  const res = await fetch(`${BFF_URL}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  })
  const json = await res.json()
  if (!res.ok) throw json.errors?.[0] ?? { message: 'Login failed' }
  return json
}

export async function getGuardianDashboard() {
  if (!BFF_URL) {
    const fixtures = await import('../../../specs/002-initial-screens/fixtures/guardian-dashboard.json')
    return fixtures.data
  }

  const headers = await getAuthHeaders()
  const res = await fetch(`${BFF_URL}/guardian/dashboard`, { headers })
  const json = await res.json()
  if (!res.ok) throw json.errors?.[0] ?? { message: 'Failed to load dashboard' }
  return json.data
}
