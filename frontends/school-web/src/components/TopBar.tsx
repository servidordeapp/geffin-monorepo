import { Bell, Settings } from 'lucide-react'
import { Avatar } from '@gfn/design-system'

interface TopBarProps {
  title: string
  staffName: string
}

export function TopBar({ title, staffName }: TopBarProps) {
  return (
    <header className="sticky top-0 z-30 h-16 bg-neutral-0 border-b border-neutral-200 flex items-center justify-between px-6">
      <h1 className="text-xl font-semibold text-neutral-900">{title}</h1>
      <div className="flex items-center gap-2">
        <button
          type="button"
          className="size-9 flex items-center justify-center rounded-lg text-neutral-400 hover:bg-neutral-50 transition-colors"
          aria-label="Notificações"
        >
          <Bell size={18} />
        </button>
        <button
          type="button"
          className="size-9 flex items-center justify-center rounded-lg text-neutral-400 hover:bg-neutral-50 transition-colors"
          aria-label="Configurações"
        >
          <Settings size={18} />
        </button>
        <Avatar name={staffName} size={32} />
      </div>
    </header>
  )
}
