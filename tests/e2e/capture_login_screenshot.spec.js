import { test, expect } from '@playwright/test';

test('Capture new login page screenshot and verify role redirections', async ({ page }) => {
  // 1. Visit Login Page & Take Screenshot
  await page.goto('/login');
  await expect(page.getByRole('heading', { name: 'SummitGear POS' })).toBeVisible();
  await page.screenshot({ 
    path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_login.png', 
    fullPage: true 
  });

  // Test Show/Hide password toggle driven by backend Livewire
  const passwordInput = page.locator('#password');
  await expect(passwordInput).toHaveAttribute('type', 'password');
  await page.fill('#password', 'rahasia123');

  // Click eye button to show password
  await page.locator('button[wire\\:click="toggleShowPassword"]').click();
  await expect(passwordInput).toHaveAttribute('type', 'text');
  await expect(passwordInput).toHaveValue('rahasia123');

  // Click eye button again to hide password
  await page.locator('button[wire\\:click="toggleShowPassword"]').click();
  await expect(passwordInput).toHaveAttribute('type', 'password');
  await expect(passwordInput).toHaveValue('rahasia123');

  // 2. Test Kasir Login Redirection -> Directly to /admin/transactions/create
  await page.fill('#email', 'kasir@summitgear.com');
  await page.fill('#password', 'password123');
  await page.locator('#password').press('Enter');
  await expect(page).toHaveURL(/.*\/admin\/transactions\/create/, { timeout: 15000 });

  // Logout
  await page.locator('form[action="/logout"] a').click();
  await expect(page).toHaveURL(/.*login/);

  // 3. Test Gudang Login Redirection -> Directly to /admin/maintenance/kanban
  await page.fill('#email', 'gudang@summitgear.com');
  await page.fill('#password', 'password123');
  await page.locator('#password').press('Enter');
  await expect(page).toHaveURL(/.*\/admin\/maintenance\/kanban/, { timeout: 15000 });

  // Logout
  await page.locator('form[action="/logout"] a').click();
  await expect(page).toHaveURL(/.*login/);

  // 4. Test Admin Login Redirection -> Directly to /dashboard
  await page.fill('#email', 'admin@summitgear.com');
  await page.fill('#password', 'password123');
  await page.locator('#password').press('Enter');
  await expect(page).toHaveURL(/.*dashboard/, { timeout: 15000 });
});
