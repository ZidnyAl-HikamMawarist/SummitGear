import { test, expect } from '@playwright/test';

test.describe('Kasir Role RBAC Access Test', () => {
  test('Kasir can access Cashier, Handover, Calendar, Customers, and Inventory, but blocked from Admin Settings and Users', async ({ page }) => {
    // 1. Login sebagai Kasir
    await page.goto('/login');
    await page.fill('#email', 'kasir@summitgear.com');
    await page.fill('#password', 'password123');
    await page.locator('#password').press('Enter');
    // 2. Akses Kasir & Sewa (Create Transaction) - Otomatis mendarat di halaman Kasir
    await expect(page).toHaveURL(/.*\/admin\/transactions\/create/, { timeout: 15000 });
    await expect(page.getByRole('heading', { name: 'Kasir & Buat Transaksi Baru' })).toBeVisible();

    // 3. Akses Serah Terima Operasional (Handover) - Harus BERHASIL
    await page.goto('/admin/operations/handover');
    await expect(page.getByRole('heading', { name: 'Serah Terima & Checklist QC' }).first()).toBeVisible();

    // 4. Akses Kalender Booking - Harus BERHASIL
    await page.goto('/admin/operations/calendar');
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat (Grid)' })).toBeVisible();

    // 5. Akses Data Pelanggan - Harus BERHASIL
    await page.goto('/admin/customers');
    await expect(page.getByRole('heading', { name: 'Manajemen Data Pelanggan' })).toBeVisible();

    // 6. Akses Inventaris Barang - Harus BERHASIL
    await page.goto('/admin/inventory/items');
    await expect(page.getByRole('heading', { name: 'Master Katalog Barang' })).toBeVisible();

    // 7. Akses Kelola Akun (Admin Only) - Harus DIBLOKIR (403 Forbidden)
    const responseUsers = await page.goto('/admin/users');
    expect(responseUsers.status()).toBe(403);

    // 8. Akses Pengaturan Sistem (Admin Only) - Harus DIBLOKIR (403 Forbidden)
    const responseSettings = await page.goto('/admin/settings');
    expect(responseSettings.status()).toBe(403);
  });
});
