import React from 'react'
import { render } from '@testing-library/react'
import { Skeleton } from '../components/Skeleton'

describe('Skeleton', () => {
  it('renders with default dimensions', () => {
    const { container } = render(<Skeleton />)
    expect(container.firstChild).toBeInTheDocument()
  })

  it('accepts className for width/height overrides', () => {
    const { container } = render(<Skeleton className="w-32 h-4" />)
    expect((container.firstChild as HTMLElement).className).toMatch(/w-32/)
    expect((container.firstChild as HTMLElement).className).toMatch(/h-4/)
  })

  it('applies shimmer animation class', () => {
    const { container } = render(<Skeleton />)
    const el = container.firstChild as HTMLElement
    expect(el.className).toMatch(/animate-/)
  })

  it('has rounded corners by default', () => {
    const { container } = render(<Skeleton />)
    const el = container.firstChild as HTMLElement
    expect(el.className).toMatch(/rounded/)
  })
})
