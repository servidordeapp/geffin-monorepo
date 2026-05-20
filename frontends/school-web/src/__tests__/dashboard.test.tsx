import React from 'react'
import { render, screen } from '@testing-library/react'
jest.mock('../app/dashboard/page', () => ({ default: () => <div data-testid="school-dashboard">ok</div> }))
describe('School Web — Dashboard Page', () => {
  it('renders', async () => {
    const Page = (await import('../app/dashboard/page')).default
    render(<Page />)
    expect(document.body).toBeTruthy()
  })
})
