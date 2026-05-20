import React from 'react'
import { render, screen, fireEvent } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Input } from '../components/Input'

describe('Input — floating label', () => {
  it('label starts at center when empty and unfocused', () => {
    render(<Input label="Email" />)
    const label = screen.getByText('Email')
    expect(label.className).toMatch(/top-1\/2/)
  })

  it('label floats to top on focus', async () => {
    render(<Input label="Email" />)
    const input = screen.getByRole('textbox', { hidden: true }) as HTMLInputElement
    fireEvent.focus(input)
    const label = screen.getByText('Email')
    expect(label.className).toMatch(/top-2/)
  })

  it('label stays floated when input has value', () => {
    render(<Input label="Email" value="test@email.com" onChange={() => {}} />)
    const label = screen.getByText('Email')
    expect(label.className).toMatch(/top-2/)
  })
})

describe('Input — error state', () => {
  it('renders error message', () => {
    render(<Input label="Email" error="Campo obrigatório" />)
    expect(screen.getByRole('alert')).toHaveTextContent('Campo obrigatório')
  })

  it('sets aria-invalid on input', () => {
    render(<Input label="Email" error="Invalid" />)
    const input = screen.getByRole('textbox', { hidden: true })
    expect(input).toHaveAttribute('aria-invalid', 'true')
  })

  it('sets aria-describedby linking to error element', () => {
    render(<Input label="Email" error="Invalid" />)
    const input = screen.getByRole('textbox', { hidden: true })
    const errorId = input.getAttribute('aria-describedby')
    expect(errorId).toBeTruthy()
    expect(document.getElementById(errorId!)).toHaveTextContent('Invalid')
  })

  it('no aria-invalid when no error', () => {
    render(<Input label="Email" />)
    const input = screen.getByRole('textbox', { hidden: true })
    expect(input).toHaveAttribute('aria-invalid', 'false')
  })
})

describe('Input — password toggle', () => {
  it('renders password input by default', () => {
    const { container } = render(<Input label="Senha" type="password" />)
    const input = container.querySelector('input')!
    expect(input.type).toBe('password')
  })

  it('toggles to text on show-password click', async () => {
    const user = userEvent.setup()
    const { container } = render(<Input label="Senha" type="password" />)
    const input = container.querySelector('input')!
    const toggleBtn = screen.getByRole('button', { name: /mostrar senha/i })
    await user.click(toggleBtn)
    expect(input.type).toBe('text')
  })

  it('toggles aria-label on show/hide', async () => {
    const user = userEvent.setup()
    render(<Input label="Senha" type="password" />)
    const btn = screen.getByRole('button', { name: /mostrar senha/i })
    await user.click(btn)
    expect(screen.getByRole('button', { name: /ocultar senha/i })).toBeInTheDocument()
  })
})

describe('Input — context variants', () => {
  it('guardian context uses h-14', () => {
    const { container } = render(<Input label="Email" context="guardian" />)
    const wrapper = container.firstChild?.firstChild as HTMLElement
    expect(wrapper.className).toMatch(/h-14/)
  })

  it('admin context uses h-11', () => {
    const { container } = render(<Input label="Email" context="admin" />)
    const wrapper = container.firstChild?.firstChild as HTMLElement
    expect(wrapper.className).toMatch(/h-11/)
  })
})
