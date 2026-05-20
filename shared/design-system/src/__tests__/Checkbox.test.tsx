import React from 'react'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Checkbox } from '../components/Checkbox'

describe('Checkbox', () => {
  it('renders label', () => {
    render(<Checkbox label="Lembrar de mim" checked={false} onChange={() => {}} />)
    expect(screen.getByLabelText('Lembrar de mim')).toBeInTheDocument()
  })

  it('reflects checked state', () => {
    render(<Checkbox label="Test" checked={true} onChange={() => {}} />)
    expect(screen.getByRole('checkbox')).toBeChecked()
  })

  it('reflects unchecked state', () => {
    render(<Checkbox label="Test" checked={false} onChange={() => {}} />)
    expect(screen.getByRole('checkbox')).not.toBeChecked()
  })

  it('calls onChange when clicked', async () => {
    const user = userEvent.setup()
    const onChange = jest.fn()
    render(<Checkbox label="Test" checked={false} onChange={onChange} />)
    await user.click(screen.getByRole('checkbox'))
    expect(onChange).toHaveBeenCalledTimes(1)
  })

  it('has aria-checked attribute', () => {
    render(<Checkbox label="Test" checked={true} onChange={() => {}} />)
    expect(screen.getByRole('checkbox')).toHaveAttribute('aria-checked', 'true')
  })

  it('shows focus-visible outline when focused', () => {
    render(<Checkbox label="Test" checked={false} onChange={() => {}} />)
    const checkbox = screen.getByRole('checkbox')
    expect(checkbox.className).toMatch(/focus-visible/)
  })
})
