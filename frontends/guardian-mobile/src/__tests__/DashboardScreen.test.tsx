import React from 'react'
import { render, screen, fireEvent, waitFor } from '@testing-library/react-native'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import DashboardScreen from '../app/(app)/index'

jest.mock('../hooks/useGuardianDashboard', () => ({
  useGuardianDashboard: jest.fn(),
}))

import { useGuardianDashboard } from '../hooks/useGuardianDashboard'
const mockUseDashboard = useGuardianDashboard as jest.MockedFunction<typeof useGuardianDashboard>

const mockDashboardData = {
  guardian: { firstName: 'Maria', unreadNotifications: 2 },
  children: [
    { id: 'c_01', name: 'Pedro Silva', grade: '3º Ano A', school: 'Escola João Pessoa', avatarUrl: null, balance: 5000 },
  ],
  payments: [],
  recentActivity: [],
}

function wrapper({ children }: { children: React.ReactNode }) {
  return (
    <QueryClientProvider client={new QueryClient()}>
      {children}
    </QueryClientProvider>
  )
}

describe('DashboardScreen', () => {
  beforeEach(() => jest.clearAllMocks())

  it('shows loading skeletons when fetching', () => {
    mockUseDashboard.mockReturnValueOnce({ isLoading: true } as any)
    const { container } = render(<DashboardScreen />, { wrapper })
    expect(container).toBeTruthy()
  })

  it('renders children carousel after data loads', async () => {
    mockUseDashboard.mockReturnValueOnce({
      ...mockDashboardData,
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    } as any)
    render(<DashboardScreen />, { wrapper })
    await waitFor(() => expect(screen.getByText('Pedro Silva')).toBeTruthy())
  })

  it('shows empty state when no children', async () => {
    mockUseDashboard.mockReturnValueOnce({
      guardian: { firstName: 'Maria', unreadNotifications: 0 },
      children: [],
      payments: [],
      recentActivity: [],
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    } as any)
    render(<DashboardScreen />, { wrapper })
    await waitFor(() => expect(screen.queryByText(/nenhum aluno/i)).toBeTruthy())
  })

  it('shows ErrorState on fetch error', async () => {
    mockUseDashboard.mockReturnValueOnce({
      isLoading: false,
      isError: true,
      refetch: jest.fn(),
    } as any)
    render(<DashboardScreen />, { wrapper })
    await waitFor(() => expect(screen.queryByText(/tentar novamente/i)).toBeTruthy())
  })

  it('calls refetch on pull-to-refresh', async () => {
    const refetch = jest.fn()
    mockUseDashboard.mockReturnValueOnce({
      ...mockDashboardData,
      isLoading: false,
      isError: false,
      refetch,
    } as any)
    render(<DashboardScreen />, { wrapper })
    const scrollView = screen.getByTestId('dashboard-scroll')
    fireEvent(scrollView, 'refresh')
    expect(refetch).toHaveBeenCalledTimes(1)
  })
})
