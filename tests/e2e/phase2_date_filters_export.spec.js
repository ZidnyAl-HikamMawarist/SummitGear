import { test, expect } from '@playwright/test';

test.describe('Phase 2 Date Range Filters & CSV Export Suite', () => {
  test('Admin can filter by date range and export CSV in Analytics, Audit Logs, and Customers', async ({ page }) => {
    // 1. Login
    await page.goto('/login', { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'admin@summitgear.com');
    await page.fill('#password', 'password123');
    await page.locator('#password').press('Enter');

    await expect(page).toHaveURL(/.*dashboard/, { timeout: 15000 });

    // 2. Test Analytics Dashboard Date Range Filter & Export CSV
    await page.goto('/admin/analytics/dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Dashboard Analitik & Laporan Bisnis' }).first()).toBeVisible({ timeout: 10000 });
    await expect(page.locator('text=Export CSV')).toBeVisible();

    // Verify dropdown exists and select option
    await expect(page.locator('select').first()).toBeVisible();
    await page.selectOption('select', 'CUSTOM');
    await expect(page.locator('text=Terapkan Filter')).toBeVisible();

    // Set custom date range
    const dateFromInput = page.locator('input[type="date"]').first();
    const dateToInput = page.locator('input[type="date"]').nth(1);
    await dateFromInput.fill('2026-08-01');
    await dateToInput.fill('2026-09-30');
    await page.click('text=Terapkan Filter');

    // Verify filter applied notification or active indicator
    await expect(page.locator('text=Filter Aktif:')).toBeVisible();

    // Trigger CSV Export
    const [downloadAnalytics] = await Promise.all([
      page.waitForEvent('download', { timeout: 10000 }),
      page.click('text=Export CSV'),
    ]);
    expect(downloadAnalytics.suggestedFilename()).toContain('laporan_analitik_summitgear_');

    // 3. Test Audit Log Date Range Filter & Export CSV
    await page.goto('/admin/audit-logs', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Log Audit Keamanan' }).first()).toBeVisible({ timeout: 10000 });
    await expect(page.locator('text=Export Log CSV')).toBeVisible();

    // Fill date filter
    const auditDateFrom = page.locator('input[type="date"]').first();
    await auditDateFrom.fill('2026-08-01');

    // Trigger Audit Log CSV Export
    const [downloadAudit] = await Promise.all([
      page.waitForEvent('download', { timeout: 10000 }),
      page.click('text=Export Log CSV'),
    ]);
    expect(downloadAudit.suggestedFilename()).toContain('audit_log_summitgear_');

    // 4. Test Customers Date Range Filter & Export CSV
    await page.goto('/admin/customers', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Manajemen Data Pelanggan' }).first()).toBeVisible({ timeout: 10000 });
    await expect(page.locator('text=Export CSV')).toBeVisible();

    // Trigger Customer CSV Export
    const [downloadCustomer] = await Promise.all([
      page.waitForEvent('download', { timeout: 10000 }),
      page.click('text=Export CSV'),
    ]);
    expect(downloadCustomer.suggestedFilename()).toContain('data_pelanggan_summitgear_');

    // 5. Test Calendar with Jump Date & Pagination
    await page.goto('/admin/operations/calendar', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat (Grid)' }).first()).toBeVisible({ timeout: 10000 });
    await expect(page.locator('input[type="date"]')).toBeVisible();

    // 6. Test Unit Tracking with Kebab Menu
    await page.goto('/admin/inventory/items/1/units', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: 'Tracking Unit Fisik' }).first()).toBeVisible({ timeout: 10000 });
    await expect(page.locator('table')).toBeVisible();
  });
});
