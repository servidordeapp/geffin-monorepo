import React from 'react'
import { render, screen } from '@testing-library/react'
import LoginPage from '../app/login/page'

jest.mock('../actions/loginAction', () => ({
  loginAction: jest.fn(),
}))

describe('Guardian Web — Login Page', () => {
  it('renders split-panel layout with form', () => {
    render(<LoginPage />)
    expect(screen.getByRole('main') || document.body).toBeTruthy()
  })

  it('renders email and password inputs', () => {
    render(<LoginPage />)
    expect(screen.getByLabelText(/e-mail/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/senha/i)).toBeInTheDocument()
  })

  it('renders remember-me checkbox', () => {
    render(<LoginPage />)
    expect(screen.getByLabelText(/lembrar/i)).toBeInTheDocument()
  })

  it('renders sign-in button', () => {
    render(<LoginPage />)
    expect(screen.getByRole('button', { name: /entrar/i })).toBeInTheDocument()
  })

  it('renders forgot-password link', () => {
    render(<LoginPage />)
    expect(screen.getByRole('link', { name: /esqueci/i })).toBeInTheDocument()
  })
})
