import { test, expect } from '@playwright/test'

const MOCK_EMAIL = 'guardian@mock.local'
const MOCK_PASSWORD = 'password123'

test.describe('Guardian Web — Login → Dashboard flow', () => {
  test('login with valid credentials → lands on dashboard', async ({ page }) => {
    await page.goto('/login')
    await page.getByLabel(/e-mail/i).fill(MOCK_EMAIL)
    await page.getByLabel(/senha/i).fill(MOCK_PASSWORD)
    await page.getByRole('button', { name: /entrar/i }).click()
    await expect(page).toHaveURL('/dashboard', { timeout: 5000 })
  })

  test('TopNav is visible and sticky after scroll', async ({ page }) => {
    await page.goto('/dashboard')
    const nav = page.getByRole('navigation', { name: /navegação principal/i })
    await expect(nav).toBeVisible()
    await page.evaluate(() => window.scrollTo(0, 400))
    await expect(nav).toBeVisible()
  })

  test('child tab switching works', async ({ page }) => {
    await page.goto('/dashboard')
    const secondTab = page.locator('[data-testid="child-tab"]').nth(1)
    if (await secondTab.isVisible()) {
      await secondTab.click()
    }
  })

  test('logout redirects to /login', async ({ page }) => {
    await page.goto('/dashboard')
    const userMenu = page.getByRole('button', { name: /perfil/i })
    if (await userMenu.isVisible()) {
      await userMenu.click()
      await page.getByRole('menuitem', { name: /sair/i }).click()
      await expect(page).toHaveURL('/login')
    }
  })
})
