'use client'

import { useActionState } from 'react'
import Link from 'next/link'
import { Lock } from 'lucide-react'
import { Input } from '@gfn/design-system'
import { Button } from '@gfn/design-system'
import { Checkbox } from '@gfn/design-system'
import { Card } from '@gfn/design-system'
import { schoolLoginAction } from '../../actions/schoolLoginAction'

export default function SchoolLoginPage() {
  const [state, action, isPending] = useActionState(schoolLoginAction, {})

  return (
    <div className="min-h-screen bg-neutral-50 flex flex-col items-center justify-center px-4 py-12">
      {/* Brand header */}
      <div className="flex items-center gap-3 mb-8">
        <div className="size-9 rounded-md bg-brand-primary-700 flex items-center justify-center">
          <span className="text-white font-black text-xs tracking-tight">GFN</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="text-base font-bold text-brand-primary-700">GFN</span>
          <span className="text-neutral-200">|</span>
          <span className="text-base font-medium text-neutral-600">Administrativo</span>
        </div>
      </div>

      {/* Login card */}
      <Card variant="default" context="admin" padding="none" className="w-full max-w-[440px] shadow-md">
        <div className="p-10">
          <div className="mb-8">
            <h1 className="text-2xl font-bold text-neutral-900">Acesso ao Sistema</h1>
            <p className="mt-1 text-sm text-neutral-400">
              Insira as credenciais da sua instituição
            </p>
          </div>

          <form action={action} className="flex flex-col gap-4">
            <Input
              label="Código da Instituição"
              name="institutionCode"
              autoComplete="organization"
              context="admin"
              error={state.fieldErrors?.institutionCode}
            />
            <Input
              label="E-mail"
              name="email"
              type="email"
              autoComplete="email"
              context="admin"
              error={state.fieldErrors?.email}
            />
            <Input
              label="Senha"
              name="password"
              type="password"
              autoComplete="current-password"
              context="admin"
              error={state.fieldErrors?.password}
            />

            <div className="flex items-center justify-between mt-1">
              <Checkbox label="Manter conectado" checked={false} onChange={() => {}} />
              <Link
                href="/forgot-password"
                className="text-sm text-brand-primary-700 hover:text-brand-primary-900 transition-colors"
              >
                Esqueci minha senha
              </Link>
            </div>

            {state.error && (
              <p role="alert" className="text-sm text-semantic-danger bg-red-50 rounded-md px-3 py-2">
                {state.error}
              </p>
            )}

            <Button
              type="submit"
              variant="primary"
              size="lg"
              fullWidth
              loading={isPending}
              context="admin"
              className="mt-2"
            >
              {isPending ? 'Acessando...' : 'Acessar Sistema'}
            </Button>
          </form>
        </div>

        {/* Security footer */}
        <div className="border-t border-neutral-200 px-10 py-4 flex items-center gap-2">
          <Lock size={13} className="text-neutral-400 shrink-0" aria-hidden="true" />
          <p className="text-xs text-neutral-400">
            Sua sessão é protegida e auditada
          </p>
        </div>
      </Card>

      {/* Footer links */}
      <div className="flex items-center gap-4 mt-6">
        <Link href="/forgot-password" className="text-sm text-neutral-400 hover:text-neutral-600 transition-colors">
          Esqueci minha senha
        </Link>
        <span className="text-neutral-200">·</span>
        <Link href="/support" className="text-sm text-neutral-400 hover:text-neutral-600 transition-colors">
          Suporte
        </Link>
      </div>

      {/* Version tag */}
      <p className="text-xs text-neutral-300 mt-4">v{process.env.npm_package_version ?? '0.1.0'}</p>
    </div>
  )
}
