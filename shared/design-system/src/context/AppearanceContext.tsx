'use client'

import { createContext, useContext, ReactNode } from 'react'

type Appearance = 'warm' | 'pro'
type ComponentContext = 'guardian' | 'admin'

interface AppearanceContextValue {
  appearance: Appearance
  context: ComponentContext
}

const AppearanceContext = createContext<AppearanceContextValue | null>(null)

export function AppearanceProvider({
  appearance = 'warm',
  children,
}: {
  appearance?: Appearance
  children: ReactNode
}) {
  const context: ComponentContext = appearance === 'pro' ? 'admin' : 'guardian'
  return (
    <AppearanceContext.Provider value={{ appearance, context }}>
      {children}
    </AppearanceContext.Provider>
  )
}

export function useAppearance(): AppearanceContextValue {
  const value = useContext(AppearanceContext)
  if (value === null) {
    throw new Error('useAppearance must be used inside <AppearanceProvider>')
  }
  return value
}
