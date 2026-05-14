import { expect, test } from '@playwright/test';

const BASE = process.env.SCHOOL_WEB_URL ?? 'http://localhost:3003';

test('happy-path admin login', async ({ page }) => {
  await page.goto(`${BASE}/login`);
  await page.fill('input[type="email"]', 'admin@test.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(`${BASE}/dashboard`);
});

test('admin logout clears session', async ({ page }) => {
  await page.goto(`${BASE}/login`);
  await page.fill('input[type="email"]', 'admin@test.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(`${BASE}/dashboard`);

  await page.evaluate(() => localStorage.removeItem('auth_token'));
  await page.goto(`${BASE}/dashboard`);
  await expect(page).toHaveURL(`${BASE}/login`);
});
