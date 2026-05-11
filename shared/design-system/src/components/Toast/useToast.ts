import { useState, useCallback, useId } from 'react'
import type { ToastProps } from './Toast'

export interface ToastItem extends Omit<ToastProps, 'onDismiss'> {
  id: string
}

export function useToast() {
  const [toasts, setToasts] = useState<ToastItem[]>([])

  const dismiss = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id))
  }, [])

  const toast = useCallback(
    (props: Omit<ToastItem, 'id'>) => {
      const id = crypto.randomUUID()
      setToasts((prev) => [...prev, { ...props, id }])
      return id
    },
    []
  )

  return { toasts, toast, dismiss }
}
