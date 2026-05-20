import { Badge } from '@gfn/design-system'
import { Card } from '@gfn/design-system'
import { EmptyState } from '@gfn/design-system'
import { Button } from '@gfn/design-system'
import type { Payment } from '../lib/types'
import { formatBRL, formatDate } from '../lib/format'
import { cn } from '@gfn/design-system'

const statusBadge: Record<Payment['status'], { variant: 'success' | 'warning' | 'danger' | 'neutral'; label: string }> = {
  paid:     { variant: 'success', label: 'Pago' },
  dueSoon:  { variant: 'warning', label: 'Próximo' },
  overdue:  { variant: 'danger',  label: 'Atrasado' },
  onTime:   { variant: 'success', label: 'Em dia' },
}

interface Props {
  payments: Payment[]
}

export function DueTable({ payments }: Props) {
  return (
    <Card padding="none">
      <div className="flex items-center justify-between px-6 py-4">
        <h3 className="text-base font-semibold text-neutral-900">A pagar este mês</h3>
        {payments.length > 0 && (
          <Button variant="ghost" size="sm" context="guardian">Ver tudo</Button>
        )}
      </div>

      {payments.length === 0 ? (
        <EmptyState
          title="Tudo em dia!"
          description="Nenhuma cobrança pendente este mês."
          className="py-8"
        />
      ) : (
        <table className="w-full">
          <thead>
            <tr className="border-y border-neutral-200 bg-neutral-50">
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wide">
                Descrição
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wide hidden sm:table-cell">
                Vencimento
              </th>
              <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-neutral-400 uppercase tracking-wide">
                Valor
              </th>
              <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-neutral-400 uppercase tracking-wide">
                Status
              </th>
            </tr>
          </thead>
          <tbody className="divide-y divide-neutral-100">
            {payments.map((p) => {
              const badge = statusBadge[p.status]
              return (
                <tr
                  key={p.id}
                  className="h-16 hover:bg-neutral-50 cursor-pointer transition-colors"
                >
                  <td className="px-6 text-sm font-medium text-neutral-900">{p.description}</td>
                  <td className="px-6 text-sm text-neutral-600 hidden sm:table-cell">
                    {formatDate(p.dueDate)}
                  </td>
                  <td className="px-6 text-sm font-semibold tabular-nums text-neutral-900 text-right">
                    {formatBRL(p.amountCents)}
                  </td>
                  <td className="px-6 text-right">
                    <Badge variant={badge.variant}>{badge.label}</Badge>
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
      )}
    </Card>
  )
}
