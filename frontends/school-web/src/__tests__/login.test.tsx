import React from 'react'
import { render, screen } from '@testing-library/react'
import LoginPage from '../app/login/page'

jest.mock('../actions/schoolLoginAction', () => ({ schoolLoginAction: jest.fn() }))

describe('School Web — Login Page', () => {
  it('renders institution code field', () => {
    render(<LoginPage />)
    expect(screen.getByLabelText(/código/i)).toBeInTheDocument()
  })

  it('renders email and password fields', () => {
    render(<LoginPage />)
    expect(screen.getByLabelText(/e-mail/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/senha/i)).toBeInTheDocument()
  })

  it('renders security message', () => {
    render(<LoginPage />)
    expect(screen.getByText(/protegida/i)).toBeInTheDocument()
  })

  it('renders sign-in button', () => {
    render(<LoginPage />)
    expect(screen.getByRole('button', { name: /acessar/i })).toBeInTheDocument()
  })
})
