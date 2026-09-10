import { test, expect } from '@playwright/test';

test.describe('Phase 1.7 & 1.8 Operations Tests', () => {
  test('Admin can access Handover Operations page', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@summitgear.com');
    await page.fill('input[type="password"]', 'password123');
    await page.click('button[type="submit"]');

    // 2. Wait for dashboard
    await expect(page).toHaveURL(/.*dashboard/);

    // 3. Navigate to Handover Operations
    await page.goto('/admin/operations/handover');
    await expect(page.getByRole('heading', { name: 'Serah Terima & Checklist QC' }).first()).toBeVisible();

    // Check summary cards exist
    await expect(page.locator('text=Check-Out Hari Ini')).toBeVisible();
    await expect(page.locator('text=Pengembalian Hari Ini')).toBeVisible();
  });
});
