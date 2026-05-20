'use client'

import { toast as sonnerToast } from 'sonner'

export function useToast() {
  return {
    toast: {
      success: (message: string, options?: { description?: string }) =>
        sonnerToast.success(message, options),
      error: (message: string, options?: { description?: string }) =>
        sonnerToast.error(message, options),
      warning: (message: string, options?: { description?: string }) =>
        sonnerToast.warning(message, options),
      info: (message: string, options?: { description?: string }) =>
        sonnerToast.info(message, options),
      dismiss: (id?: string | number) => sonnerToast.dismiss(id),
    },
  }
}
