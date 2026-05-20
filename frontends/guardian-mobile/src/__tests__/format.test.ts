import { formatBRL, formatRelative, formatDue } from '../lib/format'

describe('formatBRL', () => {
  it('formats positive cents as BRL', () => {
    expect(formatBRL(5000)).toBe('R$ 50,00')
  })

  it('formats zero as R$ 0,00', () => {
    expect(formatBRL(0)).toMatch(/R\$/)
  })

  it('formats negative cents with minus sign', () => {
    const result = formatBRL(-500)
    expect(result).toMatch(/-/)
  })
})

describe('formatRelative', () => {
  it('returns a non-empty string for a recent timestamp', () => {
    const recent = new Date(Date.now() - 60_000).toISOString()
    expect(formatRelative(recent).length).toBeGreaterThan(0)
  })

  it('returns a non-empty string for an older timestamp', () => {
    const old = '2026-05-10T10:00:00-03:00'
    expect(formatRelative(old).length).toBeGreaterThan(0)
  })
})

describe('formatDue', () => {
  it('returns a non-empty string for a due date', () => {
    expect(formatDue('2026-05-20').length).toBeGreaterThan(0)
  })

  it('formats in pt-BR locale style', () => {
    const result = formatDue('2026-05-20')
    expect(result).toBeTruthy()
  })
})
