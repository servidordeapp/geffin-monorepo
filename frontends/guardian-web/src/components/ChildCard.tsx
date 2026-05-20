'use client'

import { useState } from 'react'
import { Card } from '@gfn/design-system'
import { Avatar } from '@gfn/design-system'
import { Button } from '@gfn/design-system'
import { cn } from '@gfn/design-system'
import type { Child } from '../lib/types'
import { formatBRL } from '../lib/format'

interface ChildCardProps {
  children: Child[]
}

export function ChildCard({ children }: ChildCardProps) {
  const [activeIdx, setActiveIdx] = useState(0)
  const child = children[activeIdx]

  if (!child) return null

  const isNegative = child.balance < 0
  const balanceColor = isNegative ? 'text-semantic-danger' : 'text-accent-green-700'

  return (
    <Card variant="hero" padding="lg" className="lg:col-span-3">
      {children.length > 1 && (
        <div className="flex gap-2 mb-6 overflow-x-auto">
          {children.map((c, i) => (
            <button
              key={c.id}
              type="button"
              onClick={() => setActiveIdx(i)}
              className={cn(
                'shrink-0 rounded-full px-3 py-1 text-sm font-medium transition-colors',
                i === activeIdx
                  ? 'bg-brand-primary-700 text-white'
                  : 'bg-white/60 text-brand-primary-700 hover:bg-white/80'
              )}
            >
              {c.name.split(' ')[0]}
            </button>
          ))}
        </div>
      )}

      <div className="flex items-start gap-4">
        <Avatar name={child.name} size={56} src={child.avatarUrl ?? undefined} />
        <div className="flex-1 min-w-0">
          <h2 className="text-lg font-bold text-brand-primary-900 truncate">{child.name}</h2>
          <p className="text-sm text-brand-primary-700">{child.grade} · {child.school}</p>
        </div>
      </div>

      <div className="mt-6">
        <p className="text-xs font-medium text-neutral-600 uppercase tracking-wide mb-1">
          Saldo disponível
        </p>
        <p className={cn('text-4xl font-bold tabular-nums', balanceColor)}>
          {formatBRL(child.balance)}
        </p>
      </div>

      <div className="flex gap-3 mt-6">
        <Button variant="primary" size="md" context="guardian">
          Recarregar
        </Button>
        <Button variant="ghost" size="md" context="guardian">
          Ver extrato
        </Button>
      </div>
    </Card>
  )
}
