'use client'

import { useState } from 'react'
import { ChevronUp, ChevronDown } from 'lucide-react'
import { Card } from '@gfn/design-system'
import { Badge } from '@gfn/design-system'
import { Button } from '@gfn/design-system'
import { cn } from '@gfn/design-system'
import type { SchoolCharge, SchoolChargesPage } from '../lib/types'
import { formatBRL, formatDate } from '../lib/format'

type SortKey = 'studentName' | 'dueDate' | 'amountCents' | 'status'

const statusBadge: Record<SchoolCharge['status'], { variant: 'success' | 'warning' | 'danger'; label: string }> = {
  paid:    { variant: 'success', label: 'Pago' },
  open:    { variant: 'warning', label: 'Em aberto' },
  overdue: { variant: 'danger',  label: 'Vencido' },
}

interface Props {
  page: SchoolChargesPage
}

export function ChargesTable({ page }: Props) {
  const [sortKey, setSortKey] = useState<SortKey>('dueDate')
  const [sortDir, setSortDir] = useState<'asc' | 'desc'>('asc')
  const [currentPage, setCurrentPage] = useState(1)

  function toggleSort(key: SortKey) {
    if (sortKey === key) {
      setSortDir((d) => (d === 'asc' ? 'desc' : 'asc'))
    } else {
      setSortKey(key)
      setSortDir('asc')
    }
  }

  const sorted = [...page.items].sort((a, b) => {
    let cmp = 0
    if (sortKey === 'studentName') cmp = a.studentName.localeCompare(b.studentName)
    else if (sortKey === 'dueDate') cmp = a.dueDate.localeCompare(b.dueDate)
    else if (sortKey === 'amountCents') cmp = a.amountCents - b.amountCents
    else if (sortKey === 'status') cmp = a.status.localeCompare(b.status)
    return sortDir === 'asc' ? cmp : -cmp
  })

  const totalPages = Math.ceil(page.total / page.pageSize)

  function SortIcon({ col }: { col: SortKey }) {
    if (sortKey !== col) return <ChevronDown size={12} className="text-neutral-300" />
    return sortDir === 'asc'
      ? <ChevronUp size={12} className="text-brand-primary-700" />
      : <ChevronDown size={12} className="text-brand-primary-700" />
  }

  return (
    <Card padding="none" context="admin">
      <div className="px-6 py-4 border-b border-neutral-200">
        <h3 className="text-base font-semibold text-neutral-900">Cobranças</h3>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="bg-neutral-50 border-b border-neutral-200 h-11">
              {[
                { key: 'studentName' as SortKey, label: 'Aluno' },
                { key: 'dueDate' as SortKey, label: 'Vencimento' },
                { key: 'amountCents' as SortKey, label: 'Valor', align: 'right' },
                { key: 'status' as SortKey, label: 'Status', align: 'right' },
              ].map(({ key, label, align }) => (
                <th
                  key={key}
                  scope="col"
                  className={cn(
                    'px-6 text-xs font-medium text-neutral-400 uppercase tracking-wide cursor-pointer select-none',
                    align === 'right' ? 'text-right' : 'text-left'
                  )}
                  onClick={() => toggleSort(key)}
                >
                  <span className="inline-flex items-center gap-1">
                    {label} <SortIcon col={key} />
                  </span>
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-neutral-100">
            {sorted.map((charge) => {
              const badge = statusBadge[charge.status]
              return (
                <tr key={charge.id} className="h-14 hover:bg-neutral-50 cursor-pointer transition-colors">
                  <td className="px-6 text-sm font-medium text-neutral-900">{charge.studentName}</td>
                  <td className="px-6 text-sm text-neutral-600">{formatDate(charge.dueDate)}</td>
                  <td className="px-6 text-sm font-semibold tabular-nums text-neutral-900 text-right">
                    {formatBRL(charge.amountCents)}
                  </td>
                  <td className="px-6 text-right">
                    <Badge variant={badge.variant} label={badge.label} />
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>

      {totalPages > 1 && (
        <div className="flex items-center justify-between px-6 py-4 border-t border-neutral-200">
          <p className="text-sm text-neutral-400">
            Mostrando {page.items.length} de {page.total} cobranças
          </p>
          <div className="flex items-center gap-2">
            <Button
              variant="secondary"
              size="sm"
              context="admin"
              disabled={currentPage === 1}
              onClick={() => setCurrentPage((p) => p - 1)}
            >
              Anterior
            </Button>
            <span className="text-sm text-neutral-600">
              {currentPage} / {totalPages}
            </span>
            <Button
              variant="secondary"
              size="sm"
              context="admin"
              disabled={currentPage === totalPages}
              onClick={() => setCurrentPage((p) => p + 1)}
            >
              Próximo
            </Button>
          </div>
        </div>
      )}
    </Card>
  )
}
