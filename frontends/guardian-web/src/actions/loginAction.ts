'use server'

import { z } from 'zod'
import { cookies } from 'next/headers'
import { redirect } from 'next/navigation'
import { loginGuardian } from '../lib/api'

const schema = z.object({
  email: z.string().email('E-mail inválido'),
  password: z.string().min(6, 'Senha deve ter pelo menos 6 caracteres'),
})

type ActionState = {
  error?: string
  fieldErrors?: { email?: string; password?: string }
}

export async function loginAction(
  _prevState: ActionState,
  formData: FormData
): Promise<ActionState> {
  const raw = {
    email: formData.get('email') as string,
    password: formData.get('password') as string,
  }

  const result = schema.safeParse(raw)
  if (!result.success) {
    const errors = result.error.flatten().fieldErrors
    return {
      fieldErrors: {
        email: errors.email?.[0],
        password: errors.password?.[0],
      },
    }
  }

  try {
    const json = await loginGuardian(result.data.email, result.data.password)
    if (!json.data?.token) return { error: 'Login falhou. Tente novamente.' }

    const cookieStore = await cookies()
    cookieStore.set('session', json.data.token, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 60 * 60 * 24 * 7,
      path: '/',
    })
  } catch (err: any) {
    return { error: err?.message ?? 'Credenciais inválidas.' }
  }

  redirect('/dashboard')
}
