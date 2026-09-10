import { test, expect } from '@playwright/test';

test.describe('Dashboard & Complete Navigation Suite', () => {
  test('Admin Dashboard is fully functional with live metrics and working sidebar navigation', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    await page.fill('#email', 'admin@summitgear.com');
    await page.fill('#password', 'password123');
    await page.locator('#password').press('Enter');

    // 2. Wait for dashboard
    await expect(page).toHaveURL(/.*dashboard/, { timeout: 15000 });
    await expect(page.getByRole('heading', { name: 'Dashboard Utama' })).toBeVisible();

    // 3. Verify Metric KPI Cards
    await expect(page.getByText('Check-Out Hari Ini')).toBeVisible();
    await expect(page.getByText('Check-In Terlambat')).toBeVisible();
    await expect(page.getByText('Unit Servis & Cuci')).toBeVisible();
    await expect(page.getByText('Omset Bulan Ini')).toBeVisible();

    // 4. Verify Content Sections
    await expect(page.getByText('Sebaran Aset Inventaris')).toBeVisible();
    await expect(page.getByText('Transaksi & Reservasi Terbaru')).toBeVisible();

    // Take screenshot of live dashboard
    await page.screenshot({ path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/dashboard_functional.png', fullPage: true });

    // 5. Test Sidebar Navigation across ALL Admin Modules
    // Inventaris
    await page.click('aside >> text=Inventaris Alat');
    await expect(page).toHaveURL(/.*inventory\/items/);
    await expect(page.getByRole('heading', { name: 'Inventaris Alat & Katalog' }).first()).toBeVisible();

    // Pelanggan
    await page.click('aside >> text=Data Pelanggan');
    await expect(page).toHaveURL(/.*customers/);
    await expect(page.getByRole('heading', { name: 'Manajemen Pelanggan' }).first()).toBeVisible();

    // Serah Terima
    await page.click('aside >> text=Serah Terima (QC)');
    await expect(page).toHaveURL(/.*operations\/handover/);
    await expect(page.getByRole('heading', { name: 'Serah Terima & Checklist QC Operasional' }).first()).toBeVisible();

    // Kalender Booking
    await page.click('aside >> text=Kalender Booking');
    await expect(page).toHaveURL(/.*operations\/calendar/);
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat' }).first()).toBeVisible();

    // Gudang Kanban
    await page.click('aside >> text=Gudang (Kanban)');
    await expect(page).toHaveURL(/.*maintenance\/kanban/);
    await expect(page.getByRole('heading', { name: 'Papan Kerja (Kanban) Maintenance & Gudang' }).first()).toBeVisible();

    // Laporan & Analitik
    await page.click('aside >> text=Analitik & Laporan');
    await expect(page).toHaveURL(/.*analytics\/dashboard/);
    await expect(page.getByRole('heading', { name: 'Dashboard Analitik & Laporan Bisnis' }).first()).toBeVisible();

    // Pengaturan
    await page.click('aside >> text=Pengaturan');
    await expect(page).toHaveURL(/.*settings/);
    await expect(page.getByRole('heading', { name: 'Pengaturan Sistem' }).first()).toBeVisible();

    // Kelola Akun
    await page.click('aside >> text=Kelola Akun');
    await expect(page).toHaveURL(/.*admin\/users/);
    await expect(page.getByRole('heading', { name: 'Manajemen Akun Pengguna' }).first()).toBeVisible();

    // Log Audit
    await page.click('aside >> text=Log Audit');
    await expect(page).toHaveURL(/.*audit-logs/);
    await expect(page.getByRole('heading', { name: 'Log Audit Sistem' }).first()).toBeVisible();

    // Back to Dashboard
    await page.click('aside >> text=Dashboard');
    await expect(page).toHaveURL(/.*dashboard/);
  });
});
