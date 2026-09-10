import { test, expect } from '@playwright/test';

test('login page loads and responds', async ({ page }) => {
  await page.goto('/login');
  await expect(page).toHaveTitle(/SummitGear/i);
});
