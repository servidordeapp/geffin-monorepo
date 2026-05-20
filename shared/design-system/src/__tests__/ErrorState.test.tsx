import React from 'react'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { ErrorState } from '../components/ErrorState'

describe('ErrorState', () => {
  it('renders title', () => {
    render(<ErrorState title="Erro ao carregar" onRetry={() => {}} />)
    expect(screen.getByText('Erro ao carregar')).toBeInTheDocument()
  })

  it('renders retry button', () => {
    render(<ErrorState title="Erro" onRetry={() => {}} />)
    expect(screen.getByRole('button', { name: /tentar novamente/i })).toBeInTheDocument()
  })

  it('calls onRetry when retry button clicked', async () => {
    const user = userEvent.setup()
    const onRetry = jest.fn()
    render(<ErrorState title="Erro" onRetry={onRetry} />)
    await user.click(screen.getByRole('button', { name: /tentar novamente/i }))
    expect(onRetry).toHaveBeenCalledTimes(1)
  })

  it('renders AlertTriangle icon', () => {
    const { container } = render(<ErrorState title="Erro" onRetry={() => {}} />)
    expect(container.querySelector('svg')).toBeInTheDocument()
  })
})
