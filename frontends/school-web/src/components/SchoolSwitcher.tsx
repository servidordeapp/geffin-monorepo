'use client'

import { useState } from 'react'
import { ChevronDown } from 'lucide-react'
import { cn } from '@gfn/design-system'

interface School {
  id: string
  name: string
  code: string
}

interface Props {
  schools: School[]
  activeSchoolId: string
}

export function SchoolSwitcher({ schools, activeSchoolId }: Props) {
  const [open, setOpen] = useState(false)
  const active = schools.find((s) => s.id === activeSchoolId) ?? schools[0]

  if (!schools || schools.length <= 1) return null

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-2 text-sm font-medium text-neutral-900 hover:text-brand-primary-700 transition-colors"
        aria-expanded={open}
        aria-haspopup="listbox"
      >
        <span className="truncate max-w-[160px]">{active?.name}</span>
        <ChevronDown size={14} className={cn('transition-transform', open && 'rotate-180')} />
      </button>

      {open && (
        <ul
          role="listbox"
          className="absolute top-full mt-1 right-0 z-50 bg-neutral-0 border border-neutral-200 rounded-lg shadow-md py-1 min-w-[200px]"
        >
          {schools.map((s) => (
            <li
              key={s.id}
              role="option"
              aria-selected={s.id === activeSchoolId}
              onClick={() => setOpen(false)}
              className={cn(
                'px-4 py-2 text-sm cursor-pointer hover:bg-neutral-50 transition-colors',
                s.id === activeSchoolId ? 'text-brand-primary-700 font-medium' : 'text-neutral-900'
              )}
            >
              {s.name}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
