'use client'

import { useEffect, useRef, useState } from 'react'
import Link from 'next/link'
import { Bell, Menu } from 'lucide-react'
import { Avatar } from '@gfn/design-system'
import { cn } from '@gfn/design-system'

const NAV_LINKS = [
  { href: '/dashboard', label: 'Início' },
  { href: '/payments', label: 'Pagamentos' },
  { href: '/canteen', label: 'Cantina' },
  { href: '/shop', label: 'Loja' },
  { href: '/history', label: 'Histórico' },
]

interface TopNavProps {
  activePath?: string
  guardianName?: string
  unreadCount?: number
}

export function TopNav({ activePath = '/dashboard', guardianName = '', unreadCount = 0 }: TopNavProps) {
  const [isScrolled, setIsScrolled] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)
  const sentinel = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => setIsScrolled(!entry.isIntersecting),
      { threshold: 0 }
    )
    if (sentinel.current) observer.observe(sentinel.current)
    return () => observer.disconnect()
  }, [])

  return (
    <>
      <div ref={sentinel} className="h-px" aria-hidden="true" />
      <header
        className={cn(
          'sticky top-0 z-40 w-full bg-neutral-0 transition-shadow duration-200',
          'border-b border-neutral-200',
          isScrolled && 'shadow-sm'
        )}
      >
        <nav
          className="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-6"
          aria-label="Navegação principal"
        >
          {/* Logo */}
          <Link href="/dashboard" className="flex items-center gap-2 shrink-0">
            <div className="size-8 rounded-lg bg-brand-primary-700 flex items-center justify-center">
              <span className="text-white font-black text-xs tracking-tight">GFN</span>
            </div>
            <span className="text-base font-bold text-brand-primary-900">Guardian</span>
          </Link>

          {/* Desktop nav links */}
          <ul className="hidden lg:flex items-center gap-1" role="list">
            {NAV_LINKS.map(({ href, label }) => {
              const isActive = activePath === href
              return (
                <li key={href}>
                  <Link
                    href={href}
                    className={cn(
                      'relative px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                      isActive
                        ? 'text-brand-primary-700 after:absolute after:bottom-0 after:left-3 after:right-3 after:h-0.5 after:bg-brand-primary-700 after:rounded-full'
                        : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-50'
                    )}
                  >
                    {label}
                  </Link>
                </li>
              )
            })}
          </ul>

          {/* Right actions */}
          <div className="flex items-center gap-2">
            <button
              type="button"
              className="relative size-9 flex items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-50 transition-colors"
              aria-label={`Notificações${unreadCount > 0 ? `, ${unreadCount} não lidas` : ''}`}
            >
              <Bell size={18} />
              {unreadCount > 0 && (
                <span className="absolute top-1.5 right-1.5 size-2 rounded-full bg-semantic-danger" aria-hidden="true" />
              )}
            </button>

            <Avatar name={guardianName} size={32} />

            {/* Mobile hamburger */}
            <button
              type="button"
              className="lg:hidden size-9 flex items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-50 transition-colors"
              aria-label="Abrir menu"
              aria-expanded={mobileOpen}
              onClick={() => setMobileOpen((v) => !v)}
            >
              <Menu size={18} />
            </button>
          </div>
        </nav>

        {/* Mobile nav menu */}
        {mobileOpen && (
          <div className="lg:hidden border-t border-neutral-200 bg-neutral-0 px-6 py-4">
            <ul className="flex flex-col gap-1" role="list">
              {NAV_LINKS.map(({ href, label }) => (
                <li key={href}>
                  <Link
                    href={href}
                    onClick={() => setMobileOpen(false)}
                    className={cn(
                      'block px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                      activePath === href
                        ? 'text-brand-primary-700 bg-brand-primary-50'
                        : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-50'
                    )}
                  >
                    {label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        )}
      </header>
    </>
  )
}
