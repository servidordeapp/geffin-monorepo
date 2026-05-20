import './globals.css'
import type { Metadata } from 'next'
import { AppearanceProvider } from '@gfn/design-system'
import { Toaster } from '@gfn/design-system'

export const metadata: Metadata = {
  title: 'Geffin | Administrativo',
  description: 'Dashboard escolar',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="pt-BR">
      <body suppressHydrationWarning>
        <AppearanceProvider appearance="pro">
          {children}
          <Toaster />
        </AppearanceProvider>
      </body>
    </html>
  )
}
