'use client'

import { useActionState } from 'react'
import Link from 'next/link'
import { Input } from '@gfn/design-system'
import { Button } from '@gfn/design-system'
import { Checkbox } from '@gfn/design-system'
import { loginAction } from '../../actions/loginAction'

export default function LoginPage() {
  const [state, action, isPending] = useActionState(loginAction, {})

  return (
    <main className="min-h-screen grid lg:grid-cols-2">
      {/* Left panel — decorative, hidden on small screens */}
      <div className="hidden lg:flex flex-col items-center justify-center bg-gradient-to-br from-brand-primary-700 to-brand-primary-900 relative overflow-hidden p-12">
        {/* Decorative circles */}
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
          <div className="absolute -top-32 -left-32 size-96 rounded-full bg-white/5" />
          <div className="absolute -bottom-40 -right-20 size-[28rem] rounded-full bg-white/5" />
          <div className="absolute top-1/3 left-1/4 size-64 rounded-full bg-brand-primary-500/20" />
        </div>

        <div className="relative z-10 flex flex-col items-center gap-8 text-center max-w-sm">
          <div className="size-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
            <span className="text-white font-black text-2xl tracking-tight">GFN</span>
          </div>
          <div>
            <h1 className="text-3xl font-bold text-white leading-tight">
              Gestão financeira<br />escolar simplificada.
            </h1>
            <p className="mt-3 text-brand-primary-100 text-base leading-relaxed">
              Acompanhe pagamentos, cantina e atividades do seu filho em um só lugar.
            </p>
          </div>

          {/* Trust badges */}
          <div className="flex flex-col gap-3 w-full">
            {['Pagamentos em dia', 'Extrato em tempo real', 'Notificações automáticas'].map((item) => (
              <div key={item} className="flex items-center gap-3 bg-white/10 rounded-xl px-4 py-3">
                <div className="size-2 rounded-full bg-accent-green-400 shrink-0" />
                <span className="text-sm text-white/90">{item}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Right panel — login form */}
      <div className="flex items-center justify-center p-6 bg-neutral-50">
        <div className="w-full max-w-md">
          {/* Mobile logo */}
          <div className="lg:hidden flex items-center gap-3 mb-8">
            <div className="size-10 rounded-xl bg-brand-primary-700 flex items-center justify-center">
              <span className="text-white font-black text-sm">GFN</span>
            </div>
            <span className="text-lg font-bold text-brand-primary-900">Guardian</span>
          </div>

          <div className="bg-neutral-0 rounded-2xl shadow-md border border-neutral-200 p-8">
            <div className="mb-8">
              <h2 className="text-2xl font-bold text-neutral-900">Bem-vindo de volta</h2>
              <p className="mt-1 text-sm text-neutral-400">
                Acesse o portal do responsável
              </p>
            </div>

            <form action={action} className="flex flex-col gap-4">
              <Input
                label="E-mail"
                name="email"
                type="email"
                autoComplete="email"
                error={state.fieldErrors?.email}
                context="guardian"
              />
              <Input
                label="Senha"
                name="password"
                type="password"
                autoComplete="current-password"
                error={state.fieldErrors?.password}
                context="guardian"
              />

              <div className="flex items-center justify-between mt-1">
                <Checkbox
                  label="Lembrar de mim"
                  checked={false}
                  onChange={() => {}}
                />
                <Link
                  href="/forgot-password"
                  className="text-sm text-brand-primary-700 hover:text-brand-primary-900 transition-colors"
                >
                  Esqueci minha senha
                </Link>
              </div>

              {state.error && (
                <p role="alert" className="text-sm text-semantic-danger bg-red-50 rounded-lg px-3 py-2">
                  {state.error}
                </p>
              )}

              <Button
                type="submit"
                variant="primary"
                size="lg"
                fullWidth
                loading={isPending}
                context="guardian"
                className="mt-2"
              >
                {isPending ? 'Entrando...' : 'Entrar'}
              </Button>

              <div className="relative flex items-center gap-3 my-2">
                <div className="flex-1 h-px bg-neutral-200" />
                <span className="text-xs text-neutral-400">ou continue com</span>
                <div className="flex-1 h-px bg-neutral-200" />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <button
                  type="button"
                  className="flex h-11 items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-neutral-0 text-sm font-medium text-neutral-900 hover:bg-neutral-50 transition-colors"
                  aria-label="Entrar com Google"
                >
                  Google
                </button>
                <button
                  type="button"
                  className="flex h-11 items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-neutral-0 text-sm font-medium text-neutral-900 hover:bg-neutral-50 transition-colors"
                  aria-label="Entrar com Apple"
                >
                  Apple
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  )
}
