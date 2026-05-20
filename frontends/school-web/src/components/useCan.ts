'use client'

type Role = 'viewer' | 'staff' | 'admin'

const ROLE_LEVELS: Record<Role, number> = {
  viewer: 0,
  staff: 1,
  admin: 2,
}

export function useCan(requiredRole: 'staff' | 'admin'): boolean {
  if (typeof window === 'undefined') return false
  const role = (document.cookie.match(/session-role=([^;]+)/) ?? [])[1] as Role | undefined
  if (!role) return false
  return ROLE_LEVELS[role] >= ROLE_LEVELS[requiredRole]
}
