import { ReactNode } from 'react'
import './globals.css'
import type { Metadata } from 'next'
import { AppearanceProvider } from '@gfn/design-system'
import { Toaster } from '@gfn/design-system'

export const metadata: Metadata = {
  title: 'GFN Guardian',
  description: 'Portal do responsável',
}

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="pt-BR">
      <body suppressHydrationWarning>
        <AppearanceProvider appearance="warm">
          {children}
          <Toaster />
        </AppearanceProvider>
      </body>
    </html>
  )
}
