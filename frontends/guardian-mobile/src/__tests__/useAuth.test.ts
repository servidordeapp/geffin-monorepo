import { renderHook, act } from '@testing-library/react'
import { useAuth } from '../hooks/useAuth'

jest.mock('../lib/api', () => ({
  postLogin: jest.fn(),
}))

jest.mock('expo-secure-store', () => ({
  setItemAsync: jest.fn(),
  deleteItemAsync: jest.fn(),
}))

import { postLogin } from '../lib/api'
import * as SecureStore from 'expo-secure-store'

const mockPostLogin = postLogin as jest.MockedFunction<typeof postLogin>

const mockSuccessResponse = {
  data: {
    token: 'mock-token-abc',
    guardianId: 'g_01',
    firstName: 'Maria',
    email: 'guardian@mock.local',
    requiresMfa: false,
  },
}

describe('useAuth', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  it('login success stores token and sets user', async () => {
    mockPostLogin.mockResolvedValueOnce(mockSuccessResponse)
    const { result } = renderHook(() => useAuth())

    await act(async () => {
      await result.current.login('guardian@mock.local', 'password123')
    })

    expect(SecureStore.setItemAsync).toHaveBeenCalledWith('auth_token', 'mock-token-abc')
    expect(result.current.isLoading).toBe(false)
  })

  it('login returns error on invalid credentials', async () => {
    mockPostLogin.mockRejectedValueOnce({ code: 'INVALID_CREDENTIALS', message: 'Inválido' })
    const { result } = renderHook(() => useAuth())

    let error: unknown
    await act(async () => {
      try {
        await result.current.login('wrong@email.com', 'badpass')
      } catch (e) {
        error = e
      }
    })

    expect(error).toBeTruthy()
  })

  it('login sets isLoading during request', async () => {
    let resolveLogin!: (v: unknown) => void
    mockPostLogin.mockReturnValueOnce(new Promise((res) => (resolveLogin = res)) as any)
    const { result } = renderHook(() => useAuth())

    act(() => {
      result.current.login('guardian@mock.local', 'pass')
    })

    expect(result.current.isLoading).toBe(true)
    resolveLogin(mockSuccessResponse)
  })

  it('requiresMfa: login does not store token when MFA required', async () => {
    mockPostLogin.mockResolvedValueOnce({
      data: { requiresMfa: true, sessionToken: 'mfa-session' },
    } as any)
    const { result } = renderHook(() => useAuth())

    await act(async () => {
      await result.current.login('guardian@mock.local', 'pass')
    })

    expect(SecureStore.setItemAsync).not.toHaveBeenCalledWith('auth_token', expect.any(String))
  })
})
