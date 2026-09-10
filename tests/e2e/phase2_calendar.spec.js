import { test, expect } from '@playwright/test';

test.describe('Phase 2 Operations & Calendar Tests', () => {
  test('Admin can access Visual Availability Calendar Grid', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    await page.fill('input[type="email"]', 'admin@summitgear.com');
    await page.fill('input[type="password"]', 'password123');
    await page.click('button[type="submit"]');

    // 2. Wait for dashboard
    await expect(page).toHaveURL(/.*dashboard/);

    // 3. Navigate to Calendar Grid
    await page.goto('/admin/operations/calendar');
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat' }).first()).toBeVisible();

    // Check legend items exist
    await expect(page.locator('text=Tersedia (Ready)')).toBeVisible();
    await expect(page.locator('text=Booked (Dipesan)')).toBeVisible();

    // Check table headers have days and dates
    const thCount = await page.locator('table th').count();
    expect(thCount).toBeGreaterThan(5);
  });
});
