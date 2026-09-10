import { test, expect } from '@playwright/test';

test.describe('Phase 3 Maintenance Kanban & Analytics Tests', () => {
  test('Admin can access Maintenance Kanban and Analytics Dashboard', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    await page.fill('#email', 'admin@summitgear.com');
    await page.fill('#password', 'password123');
    await page.locator('#password').press('Enter');

    // 2. Wait for dashboard
    await expect(page).toHaveURL(/.*dashboard/, { timeout: 15000 });

    // 3. Test Maintenance Kanban
    await page.goto('/admin/maintenance/kanban');
    await expect(page.getByRole('heading', { name: 'Papan Kerja (Kanban) Maintenance & Gudang' }).first()).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Pembersihan (Cleaning)' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Servis / Rusak (Repair)' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Siap Sewa (Available)' })).toBeVisible();

    // 4. Test Analytics Dashboard
    await page.goto('/admin/analytics/dashboard');
    await expect(page.getByRole('heading', { name: 'Dashboard Analitik & Laporan Bisnis' }).first()).toBeVisible();
    await expect(page.getByText('Total Transaksi', { exact: true })).toBeVisible();
    await expect(page.locator('text=Total Pendapatan')).toBeVisible();
    await expect(page.locator('text=Rasio Sengketa (KPI)')).toBeVisible();
    await expect(page.locator('text=Export CSV')).toBeVisible();
  });
});
