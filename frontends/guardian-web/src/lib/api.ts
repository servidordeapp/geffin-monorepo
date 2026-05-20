const FIXTURES_PATH = '/home/bdsoliveira/coding/geffin-monorepo/specs/002-initial-screens/fixtures'
const BFF_URL = process.env.BFF_GUARDIAN_URL ?? ''

async function fetchBFF(path: string, options?: RequestInit) {
  const res = await fetch(`${BFF_URL}${path}`, {
    ...options,
    cache: 'no-store',
  })
  const json = await res.json()
  if (!res.ok) throw json.errors?.[0] ?? { message: 'BFF request failed' }
  return json
}

export async function loginGuardian(email: string, password: string) {
  if (!BFF_URL) {
    const { guardian_login_success } = await import(
      '@fixtures/auth-responses.json'
    )
    return guardian_login_success
  }
  return fetchBFF('/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  })
}

export async function getGuardianDashboard(token: string) {
  if (!BFF_URL) {
    const fixtures = await import(
      '@fixtures/guardian-dashboard.json'
    )
    return fixtures.data
  }
  return fetchBFF('/guardian/dashboard', {
    headers: { Authorization: `Bearer ${token}` },
  })
}

export async function getGuardianSummary(token: string) {
  if (!BFF_URL) {
    const fixtures = await import(
      '@fixtures/guardian-dashboard.json'
    )
    return fixtures.data
  }
  return fetchBFF('/guardian/summary', {
    headers: { Authorization: `Bearer ${token}` },
  })
}
