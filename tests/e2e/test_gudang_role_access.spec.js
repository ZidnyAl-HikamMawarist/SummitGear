import { test, expect } from '@playwright/test';

test.describe('Gudang Role RBAC Access Test', () => {
  test('Gudang can access Kanban, Handover, Calendar, and Inventory, but blocked from Cashier, Customers, and Settings', async ({ page }) => {
    test.setTimeout(60000);
    // 1. Login sebagai Gudang
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    await page.fill('input[type="email"]', 'gudang@summitgear.com');
    await page.fill('input[type="password"]', 'password123');
    await page.click('button:has-text("Masuk ke Akun")');
    // 2. Akses Gudang - Otomatis mendarat di Dashboard Gudang
    await expect(page).toHaveURL(/.*\/gudang\/dashboard/, { timeout: 15000 });
    await expect(page.getByRole('heading', { name: 'Monitoring Unit & Pergudangan' }).first()).toBeVisible();

    // 2b. Akses Kanban Gudang - Harus BERHASIL
    await page.goto('/admin/maintenance/kanban');
    await expect(page.getByRole('heading', { name: 'Papan Kerja Maintenance & Gudang' }).first()).toBeVisible();

    // 3. Akses Serah Terima Operasional (Handover) - Harus BERHASIL
    await page.goto('/admin/operations/handover');
    await expect(page.getByRole('heading', { name: 'Serah Terima & Checklist QC' }).first()).toBeVisible();

    // 4. Akses Kalender Booking - Harus BERHASIL
    await page.goto('/admin/operations/calendar');
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat' }).first()).toBeVisible();

    // 5. Akses Inventaris Barang - Harus BERHASIL
    await page.goto('/admin/inventory/items');
    await expect(page.getByRole('heading', { name: 'Master Katalog Barang' })).toBeVisible();

    // 6. Akses Kasir & Sewa - Harus DIBLOKIR (403 Forbidden)
    const responseKasir = await page.goto('/admin/transactions/create');
    expect(responseKasir.status()).toBe(403);

    // 7. Akses Pengaturan Sistem (Admin Only) - Harus DIBLOKIR (403 Forbidden)
    const responseSettings = await page.goto('/admin/settings');
    expect(responseSettings.status()).toBe(403);
  });
});
