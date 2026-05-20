import React from 'react'
import { render, screen, fireEvent, waitFor } from '@testing-library/react-native'
import LoginScreen from '../screens/LoginScreen'

jest.mock('../lib/api', () => ({
  postLogin: jest.fn(),
}))

jest.mock('expo-secure-store', () => ({
  setItemAsync: jest.fn(),
}))

jest.mock('expo-router', () => ({
  useRouter: () => ({ replace: jest.fn(), push: jest.fn() }),
  Link: ({ children }: any) => children,
}))

import { postLogin } from '../lib/api'
const mockPostLogin = postLogin as jest.MockedFunction<typeof postLogin>

describe('LoginScreen', () => {
  beforeEach(() => jest.clearAllMocks())

  it('renders email and password fields', () => {
    render(<LoginScreen />)
    expect(screen.getByLabelText(/e-mail/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/senha/i)).toBeInTheDocument()
  })

  it('renders sign-in button', () => {
    render(<LoginScreen />)
    expect(screen.getByRole('button', { name: /entrar/i })).toBeInTheDocument()
  })

  it('valid submit calls postLogin', async () => {
    mockPostLogin.mockResolvedValueOnce({
      data: {
        token: 'mock-token',
        guardianId: 'g_01',
        firstName: 'Maria',
        email: 'guardian@mock.local',
        requiresMfa: false,
      },
    } as any)
    render(<LoginScreen />)

    fireEvent.changeText(screen.getByLabelText(/e-mail/i), 'guardian@mock.local')
    fireEvent.changeText(screen.getByLabelText(/senha/i), 'password123')
    fireEvent.press(screen.getByRole('button', { name: /entrar/i }))

    await waitFor(() => expect(mockPostLogin).toHaveBeenCalledTimes(1))
  })

  it('shows error toast on network error', async () => {
    mockPostLogin.mockRejectedValueOnce(new Error('Network error'))
    render(<LoginScreen />)

    fireEvent.changeText(screen.getByLabelText(/e-mail/i), 'guardian@mock.local')
    fireEvent.changeText(screen.getByLabelText(/senha/i), 'password123')
    fireEvent.press(screen.getByRole('button', { name: /entrar/i }))

    await waitFor(() =>
      expect(screen.queryByText(/erro de rede/i)).toBeTruthy()
    )
  })
})
