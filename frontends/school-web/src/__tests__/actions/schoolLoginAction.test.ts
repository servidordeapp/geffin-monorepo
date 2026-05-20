import { schoolLoginAction } from '../../actions/schoolLoginAction'

jest.mock('../../lib/api', () => ({ loginSchool: jest.fn() }))
jest.mock('next/headers', () => ({ cookies: () => ({ set: jest.fn() }) }))

import { loginSchool } from '../../lib/api'
const mockLogin = loginSchool as jest.MockedFunction<typeof loginSchool>

describe('schoolLoginAction', () => {
  beforeEach(() => jest.clearAllMocks())

  it('validates institution code regex', async () => {
    const fd = new FormData()
    fd.set('institutionCode', 'invalid!'); fd.set('email', 'a@a.com'); fd.set('password', 'pass123')
    const result = await schoolLoginAction({}, fd)
    expect(result.fieldErrors?.institutionCode).toBeTruthy()
  })

  it('returns empty on success', async () => {
    mockLogin.mockResolvedValueOnce({ data: { token: 'tok', staffId: 's_01', firstName: 'Ana', email: 'a@a.com', role: 'admin', schools: [], activeSchoolId: 'sch_01' } } as any)
    const fd = new FormData()
    fd.set('institutionCode', 'ESCOLA01'); fd.set('email', 'admin@mock.local'); fd.set('password', 'pass123')
    const result = await schoolLoginAction({}, fd)
    expect(result).toEqual({})
  })

  it('returns error on invalid credentials', async () => {
    mockLogin.mockRejectedValueOnce({ code: 'INVALID_CREDENTIALS', message: 'Inválido' })
    const fd = new FormData()
    fd.set('institutionCode', 'ESCOLA01'); fd.set('email', 'wrong@mock.local'); fd.set('password', 'pass123')
    const result = await schoolLoginAction({}, fd)
    expect(result.error).toBeTruthy()
  })
})
