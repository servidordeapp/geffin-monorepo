const BFF_URL = process.env.BFF_SCHOOL_URL ?? ''
const X_CLIENT_VERSION = process.env.npm_package_version ?? '0.1.0'

async function fetchBFF(path: string, options?: RequestInit) {
  const res = await fetch(`${BFF_URL}${path}`, {
    ...options,
    cache: 'no-store',
    headers: {
      ...options?.headers,
      'X-Client-Version': X_CLIENT_VERSION,
    },
  })
  const json = await res.json()
  if (!res.ok) throw json.errors?.[0] ?? { message: 'Request failed' }
  return json
}

export async function loginSchool(email: string, password: string) {
  // if (!BFF_URL) {
    const { school_login_success } = await import(
      '@fixtures/auth-responses.json'
    )
    return school_login_success
  // }
  return fetchBFF('/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ institutionCode, email, password }),
  })
}

export async function getSchoolDashboard(token: string, period?: string) {
  if (!BFF_URL) {
    const fixtures = await import(
      '@fixtures/school-dashboard.json'
    )
    return fixtures.data
  }
  return fetchBFF(`/school/dashboard${period ? `?period=${period}` : ''}`, {
    headers: { Authorization: `Bearer ${token}` },
  })
}

export async function getSchoolActivity(token: string) {
  if (!BFF_URL) {
    const fixtures = await import(
      '@fixtures/school-dashboard.json'
    )
    return fixtures.data.activity
  }
  return fetchBFF('/school/dashboard/activity', {
    headers: { Authorization: `Bearer ${token}` },
  })
}
