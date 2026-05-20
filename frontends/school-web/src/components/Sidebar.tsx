'use client'

import { useEffect, useState } from 'react'
import Link from 'next/link'
import { cn } from '@gfn/design-system'
import {
  LayoutDashboard,
  CreditCard,
  FileText,
  Users,
  ShoppingBag,
  UtensilsCrossed,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react'

const PRINCIPAL_NAV = [
  { href: '/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
  { href: '/payments', icon: CreditCard, label: 'Cobranças' },
  { href: '/students', icon: Users, label: 'Alunos' },
  { href: '/contracts', icon: FileText, label: 'Contratos' },
]

const OPERATIONAL_NAV = [
  { href: '/canteen', icon: UtensilsCrossed, label: 'Cantina' },
  { href: '/shop', icon: ShoppingBag, label: 'Loja' },
]

interface SidebarProps {
  activePath?: string
}

export function Sidebar({ activePath = '/dashboard' }: SidebarProps) {
  const [collapsed, setCollapsed] = useState(() => {
    if (typeof window !== 'undefined') {
      return localStorage.getItem('sidebar-collapsed') === 'true'
    }
    return false
  })

  useEffect(() => {
    localStorage.setItem('sidebar-collapsed', String(collapsed))
  }, [collapsed])

  return (
    <aside
      className={cn(
        'flex flex-col h-full bg-neutral-0 border-r border-neutral-200 transition-[width] duration-200 ease-in-out shrink-0',
        collapsed ? 'w-16' : 'w-60'
      )}
    >
      {/* Logo */}
      <div className="h-16 flex items-center px-4 border-b border-neutral-200 shrink-0">
        <div className="size-8 rounded-md bg-brand-primary-700 flex items-center justify-center shrink-0">
          <span className="text-white font-black text-xs">GFN</span>
        </div>
        {!collapsed && (
          <span className="ml-3 text-sm font-bold text-brand-primary-900 truncate">
            Administrativo
          </span>
        )}
      </div>

      {/* Nav */}
      <nav aria-label="Navegação principal" className="flex-1 overflow-y-auto px-2 py-4 flex flex-col gap-1">
        {PRINCIPAL_NAV.map(({ href, icon: Icon, label }) => {
          const isActive = activePath === href
          return (
            <Link
              key={href}
              href={href}
              title={collapsed ? label : undefined}
              className={cn(
                'flex items-center gap-3 h-11 px-3 rounded-lg text-sm font-medium transition-colors relative',
                isActive
                  ? 'bg-brand-primary-50 text-brand-primary-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-[3px] before:rounded-r before:bg-brand-primary-700'
                  : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900'
              )}
            >
              <Icon size={18} className="shrink-0" aria-hidden="true" />
              {!collapsed && <span className="truncate">{label}</span>}
            </Link>
          )
        })}

        <hr className="my-2 border-neutral-100" />

        {OPERATIONAL_NAV.map(({ href, icon: Icon, label }) => {
          const isActive = activePath === href
          return (
            <Link
              key={href}
              href={href}
              title={collapsed ? label : undefined}
              className={cn(
                'flex items-center gap-3 h-11 px-3 rounded-lg text-sm font-medium transition-colors relative',
                isActive
                  ? 'bg-brand-primary-50 text-brand-primary-700 before:absolute before:left-0 before:top-2 before:bottom-2 before:w-[3px] before:rounded-r before:bg-brand-primary-700'
                  : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900'
              )}
            >
              <Icon size={18} className="shrink-0" aria-hidden="true" />
              {!collapsed && <span className="truncate">{label}</span>}
            </Link>
          )
        })}
      </nav>

      {/* Collapse toggle */}
      <div className="p-2 border-t border-neutral-200">
        <button
          type="button"
          onClick={() => setCollapsed((v) => !v)}
          className="w-full h-10 flex items-center justify-center rounded-lg text-neutral-400 hover:bg-neutral-50 hover:text-neutral-600 transition-colors"
          aria-label={collapsed ? 'Expandir menu' : 'Recolher menu'}
        >
          {collapsed ? <ChevronRight size={16} /> : <ChevronLeft size={16} />}
        </button>
      </div>
    </aside>
  )
}
