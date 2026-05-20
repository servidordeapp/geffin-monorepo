'use client'

import { Toaster as SonnerToaster } from 'sonner'

export function Toaster() {
  return (
    <SonnerToaster
      position="top-right"
      toastOptions={{
        classNames: {
          toast: 'bg-neutral-0 border border-neutral-200 shadow-md rounded-xl text-neutral-900',
          title: 'text-sm font-medium text-neutral-900',
          description: 'text-sm text-neutral-600',
          success: 'border-l-4 border-l-accent-green-500',
          error: 'border-l-4 border-l-semantic-danger',
          warning: 'border-l-4 border-l-semantic-warning',
          info: 'border-l-4 border-l-brand-primary-500',
        },
      }}
    />
  )
}
