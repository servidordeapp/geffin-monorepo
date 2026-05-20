import { loginAction } from '../../actions/loginAction'

jest.mock('../../lib/api', () => ({
  loginGuardian: jest.fn(),
}))

jest.mock('next/headers', () => ({
  cookies: () => ({
    set: jest.fn(),
  }),
}))

import { loginGuardian } from '../../lib/api'
const mockLogin = loginGuardian as jest.MockedFunction<typeof loginGuardian>

const mockSuccess = {
  data: {
    token: 'mock-token',
    guardianId: 'g_01',
    firstName: 'Maria',
    email: 'guardian@mock.local',
    requiresMfa: false,
  },
}

describe('loginAction', () => {
  beforeEach(() => jest.clearAllMocks())

  it('returns empty error on success', async () => {
    mockLogin.mockResolvedValueOnce(mockSuccess)
    const formData = new FormData()
    formData.set('email', 'guardian@mock.local')
    formData.set('password', 'password123')

    const result = await loginAction({}, formData)
    expect(result).toEqual({})
  })

  it('returns error on invalid credentials', async () => {
    mockLogin.mockRejectedValueOnce({ code: 'INVALID_CREDENTIALS', message: 'Inválido' })
    const formData = new FormData()
    formData.set('email', 'wrong@email.com')
    formData.set('password', 'badpass')

    const result = await loginAction({}, formData)
    expect(result.error).toBeTruthy()
  })

  it('validates email format', async () => {
    const formData = new FormData()
    formData.set('email', 'not-an-email')
    formData.set('password', 'pass123')

    const result = await loginAction({}, formData)
    expect(result.fieldErrors?.email).toBeTruthy()
  })

  it('validates password min length', async () => {
    const formData = new FormData()
    formData.set('email', 'guardian@mock.local')
    formData.set('password', '12')

    const result = await loginAction({}, formData)
    expect(result.fieldErrors?.password).toBeTruthy()
  })
})
