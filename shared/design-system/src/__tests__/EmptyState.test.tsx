import React from 'react'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { EmptyState } from '../components/EmptyState'

describe('EmptyState', () => {
  it('renders title and description', () => {
    render(<EmptyState title="Nenhum resultado" description="Sem dados disponíveis." />)
    expect(screen.getByText('Nenhum resultado')).toBeInTheDocument()
    expect(screen.getByText('Sem dados disponíveis.')).toBeInTheDocument()
  })

  it('renders icon when provided', () => {
    render(
      <EmptyState
        title="Vazio"
        description="Nada aqui"
        icon={<svg data-testid="icon" />}
      />
    )
    expect(screen.getByTestId('icon')).toBeInTheDocument()
  })

  it('renders action button when provided', async () => {
    const user = userEvent.setup()
    const onAction = jest.fn()
    render(
      <EmptyState
        title="Vazio"
        description="Nada aqui"
        actionLabel="Adicionar"
        onAction={onAction}
      />
    )
    await user.click(screen.getByRole('button', { name: 'Adicionar' }))
    expect(onAction).toHaveBeenCalledTimes(1)
  })

  it('does not render action button when no action provided', () => {
    render(<EmptyState title="Vazio" description="Nada" />)
    expect(screen.queryByRole('button')).not.toBeInTheDocument()
  })
})
