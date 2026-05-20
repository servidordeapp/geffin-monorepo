import { useQuery } from '@tanstack/react-query'
import { getGuardianDashboard } from '../lib/api'

export function useGuardianDashboard() {
  const { data, isLoading, isError, refetch } = useQuery({
    queryKey: ['guardian-dashboard'],
    queryFn: getGuardianDashboard,
    staleTime: 30_000,
  })

  return {
    guardian: data?.guardian,
    children: data?.children ?? [],
    payments: data?.payments ?? [],
    recentActivity: data?.recentActivity ?? [],
    isLoading,
    isError,
    refetch,
  }
}
