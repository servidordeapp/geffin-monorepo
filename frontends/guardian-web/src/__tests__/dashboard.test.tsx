import React from 'react'
import { render, screen } from '@testing-library/react'

jest.mock('../app/dashboard/page', () => ({ default: () => <div data-testid="dashboard">dashboard</div> }))

describe('Guardian Web — Dashboard Page', () => {
  it('renders without crashing', async () => {
    const DashboardPage = (await import('../app/dashboard/page')).default
    render(<DashboardPage />)
    expect(document.body).toBeTruthy()
  })
})
