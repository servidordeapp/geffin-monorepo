import React from 'react'
import { render, screen } from '@testing-library/react'
import { AppearanceProvider, useAppearance } from '../context/AppearanceContext'

function TestConsumer() {
  const { appearance, context } = useAppearance()
  return (
    <div data-testid="output">
      {appearance}:{context}
    </div>
  )
}

describe('AppearanceContext', () => {
  it('provides warm appearance and guardian context by default', () => {
    render(
      <AppearanceProvider>
        <TestConsumer />
      </AppearanceProvider>
    )
    expect(screen.getByTestId('output').textContent).toBe('warm:guardian')
  })

  it('maps warm appearance to guardian context', () => {
    render(
      <AppearanceProvider appearance="warm">
        <TestConsumer />
      </AppearanceProvider>
    )
    expect(screen.getByTestId('output').textContent).toBe('warm:guardian')
  })

  it('maps pro appearance to admin context', () => {
    render(
      <AppearanceProvider appearance="pro">
        <TestConsumer />
      </AppearanceProvider>
    )
    expect(screen.getByTestId('output').textContent).toBe('pro:admin')
  })

  it('useAppearance throws outside provider', () => {
    const spy = jest.spyOn(console, 'error').mockImplementation(() => {})
    expect(() => render(<TestConsumer />)).toThrow()
    spy.mockRestore()
  })
})
