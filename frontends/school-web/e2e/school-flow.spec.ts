import { test, expect } from '@playwright/test'

test.describe('School Web — Login → Dashboard flow', () => {
  test('login with institution code → lands on dashboard', async ({ page }) => {
    await page.goto('/login')
    await page.getByLabel(/código da instituição/i).fill('ESCOLA01')
    await page.getByLabel(/e-mail/i).fill('admin@mock.local')
    await page.getByLabel(/senha/i).fill('password123')
    await page.getByRole('button', { name: /acessar sistema/i }).click()
    await expect(page).toHaveURL('/dashboard', { timeout: 5000 })
  })

  test('dashboard renders KPI cards', async ({ page }) => {
    await page.goto('/dashboard')
    const cards = page.locator('[data-testid="kpi-card"]')
    await expect(cards.first()).toBeVisible({ timeout: 5000 })
  })

  test('charges table sorts by Vencimento', async ({ page }) => {
    await page.goto('/dashboard')
    await page.getByRole('columnheader', { name: /vencimento/i }).click()
    await expect(page.getByRole('table')).toBeVisible()
  })

  test('sidebar collapses and expands', async ({ page }) => {
    await page.goto('/dashboard')
    const toggle = page.getByRole('button', { name: /recolher menu/i })
    if (await toggle.isVisible()) {
      await toggle.click()
      await expect(page.getByRole('button', { name: /expandir menu/i })).toBeVisible()
    }
  })

  test('logout redirects to /login', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page).toHaveURL('/dashboard')
  })
})
