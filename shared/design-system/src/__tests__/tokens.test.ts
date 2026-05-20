import { colors } from '../tokens/colors'
import { typography } from '../tokens/typography'
import { spacing } from '../tokens/spacing'
import { radii } from '../tokens/radii'
import { shadowsRN } from '../tokens/shadows'

describe('colors', () => {
  it('brand primary matches CSS variables', () => {
    expect(colors.brand.primary50).toBe('#EFF6FF')
    expect(colors.brand.primary100).toBe('#DBEAFE')
    expect(colors.brand.primary500).toBe('#3B82F6')
    expect(colors.brand.primary700).toBe('#1E40AF')
    expect(colors.brand.primary900).toBe('#0B2E5C')
  })

  it('accent green matches CSS variables', () => {
    expect(colors.accent.green100).toBe('#D1FAE5')
    expect(colors.accent.green500).toBe('#10B981')
    expect(colors.accent.green700).toBe('#047857')
  })

  it('neutrals match CSS variables', () => {
    expect(colors.neutral[0]).toBe('#FFFFFF')
    expect(colors.neutral[50]).toBe('#F8FAFC')
    expect(colors.neutral[100]).toBe('#F1F5F9')
    expect(colors.neutral[200]).toBe('#E2E8F0')
    expect(colors.neutral[400]).toBe('#94A3B8')
    expect(colors.neutral[600]).toBe('#475569')
    expect(colors.neutral[900]).toBe('#0F172A')
  })

  it('semantic colors match CSS variables', () => {
    expect(colors.semantic.success).toBe('#10B981')
    expect(colors.semantic.warning).toBe('#F59E0B')
    expect(colors.semantic.danger).toBe('#EF4444')
    expect(colors.semantic.info).toBe('#3B82F6')
  })
})

describe('typography', () => {
  it('display-lg has correct values', () => {
    expect(typography['display-lg']).toEqual({ size: 32, weight: '700', lineHeight: 40 })
  })

  it('numeric-hero has correct values', () => {
    expect(typography['numeric-hero']).toEqual({ size: 36, weight: '700', lineHeight: 44 })
  })

  it('body-md has correct values', () => {
    expect(typography['body-md']).toEqual({ size: 14, weight: '400', lineHeight: 20 })
  })

  it('label-md has correct values', () => {
    expect(typography['label-md']).toEqual({ size: 14, weight: '500', lineHeight: 20 })
  })
})

describe('spacing', () => {
  it('maps scale keys to pixel values', () => {
    expect(spacing[0]).toBe(0)
    expect(spacing[1]).toBe(4)
    expect(spacing[4]).toBe(16)
    expect(spacing[6]).toBe(24)
    expect(spacing[16]).toBe(64)
  })
})

describe('radii', () => {
  it('sm is 6 (admin inputs)', () => expect(radii.sm).toBe(6))
  it('md is 8 (admin cards)', () => expect(radii.md).toBe(8))
  it('lg is 12 (guardian inputs)', () => expect(radii.lg).toBe(12))
  it('xl is 16 (guardian cards)', () => expect(radii.xl).toBe(16))
  it('full is 9999', () => expect(radii.full).toBe(9999))
})

describe('shadowsRN', () => {
  it('sm shadow has correct iOS/Android values', () => {
    expect(shadowsRN.sm.shadowColor).toBe('#0F172A')
    expect(shadowsRN.sm.shadowOpacity).toBe(0.06)
    expect(shadowsRN.sm.elevation).toBe(1)
  })

  it('md shadow has correct values', () => {
    expect(shadowsRN.md.shadowOpacity).toBe(0.08)
    expect(shadowsRN.md.elevation).toBe(4)
  })

  it('lg shadow has correct values', () => {
    expect(shadowsRN.lg.shadowOpacity).toBe(0.12)
    expect(shadowsRN.lg.elevation).toBe(12)
  })
})
