'use server'

import { z } from 'zod'
import { cookies } from 'next/headers'
import { redirect } from 'next/navigation'
import { loginSchool } from '../lib/api'

const schema = z.object({
  // institutionCode: z.string().regex(/^[A-Z0-9]{4,10}$/, 'Código inválido. Use 4–10 letras maiúsculas ou números.'),
  email: z.string().email('E-mail inválido'),
  password: z.string().min(6, 'Senha deve ter pelo menos 6 caracteres'),
})

type ActionState = {
  error?: string
  // fieldErrors?: { institutionCode?: string; email?: string; password?: string }
}

export async function schoolLoginAction(
  _prevState: ActionState,
  formData: FormData
): Promise<ActionState> {
  const raw = {
    // // institutionCode: (formData.get('institutionCode') as string)?.toUpperCase(),
    email: formData.get('email') as string,
    password: formData.get('password') as string,
  }

  const result = schema.safeParse(raw)
  if (!result.success) {
    const errors = result.error.flatten().fieldErrors
    return {
      fieldErrors: {
        // // institutionCode: errors.institutionCode?.[0],
        email: errors.email?.[0],
        password: errors.password?.[0],
      },
    }
  }

  try {
    const json = await loginSchool(
      // result.data.institutionCode,
      result.data.email,
      result.data.password
    )
    if (!json.data?.token) return { error: 'Login falhou. Tente novamente.' }

    const cookieStore = await cookies()
    cookieStore.set('session', json.data.token, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 60 * 60 * 24 * 7,
      path: '/',
    })
    cookieStore.set('activeSchoolId', json.data.activeSchoolId, {
      httpOnly: true,
      sameSite: 'lax',
      path: '/',
    })
  } catch (err: any) {
    return { error: err?.message ?? 'Credenciais inválidas.' }
  }

  redirect('/dashboard')
}
