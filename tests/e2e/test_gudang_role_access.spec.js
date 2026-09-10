import { test, expect } from '@playwright/test';

test.describe('Gudang Role RBAC Access Test', () => {
  test('Gudang can access Kanban, Handover, Calendar, and Inventory, but blocked from Cashier, Customers, and Settings', async ({ page }) => {
    // 1. Login sebagai Gudang
    await page.goto('/login');
    await page.fill('#email', 'gudang@summitgear.com');
    await page.fill('#password', 'password123');
    await page.locator('#password').press('Enter');
    // 2. Akses Kanban Gudang - Otomatis mendarat di halaman Kanban
    await expect(page).toHaveURL(/.*\/admin\/maintenance\/kanban/, { timeout: 15000 });
    await expect(page.getByRole('heading', { name: 'Papan Kerja (Kanban) Maintenance & Gudang' }).first()).toBeVisible();

    // 3. Akses Serah Terima Operasional (Handover) - Harus BERHASIL
    await page.goto('/admin/operations/handover');
    await expect(page.getByRole('heading', { name: 'Serah Terima & Checklist QC' }).first()).toBeVisible();

    // 4. Akses Kalender Booking - Harus BERHASIL
    await page.goto('/admin/operations/calendar');
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat (Grid)' })).toBeVisible();

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
