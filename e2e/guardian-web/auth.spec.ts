import { expect, test } from '@playwright/test';

const BASE = process.env.GUARDIAN_WEB_URL ?? 'http://localhost:3000';
const BFF = process.env.GUARDIAN_BFF_URL ?? 'http://localhost:3001';

test('happy-path guardian login', async ({ page }) => {
  await page.goto(`${BASE}/login`);
  await page.fill('input[type="email"]', 'guardian@test.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(`${BASE}/dashboard`);
});

test('wrong password shows error message', async ({ page }) => {
  await page.goto(`${BASE}/login`);
  await page.fill('input[type="email"]', 'guardian@test.com');
  await page.fill('input[type="password"]', 'wrongpassword');
  await page.click('button[type="submit"]');
  await expect(page.getByRole('alert')).toBeVisible();
});

test('unverified email shows resend link', async ({ page }) => {
  await page.goto(`${BASE}/login`);
  await page.fill('input[type="email"]', 'unverified@test.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await expect(page.getByText('verify your email')).toBeVisible();
  await expect(page.getByText('Resend verification email')).toBeVisible();
});

test('logout clears session', async ({ page }) => {
  await page.goto(`${BASE}/login`);
  await page.fill('input[type="email"]', 'guardian@test.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL(`${BASE}/dashboard`);

  await page.evaluate(() => localStorage.removeItem('auth_token'));
  await page.goto(`${BASE}/dashboard`);
  await expect(page).toHaveURL(`${BASE}/login`);
});
