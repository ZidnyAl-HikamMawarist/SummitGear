import { test, expect } from '@playwright/test';

test.describe('Capture Redesigned UI Screenshots', () => {
  test('Verify and capture modern UI across admin pages', async ({ page }) => {
    // 1. Login
    await page.goto('/login');
    await page.fill('#email', 'admin@summitgear.com');
    await page.fill('#password', 'password123');
    await page.locator('#password').press('Enter');
    await expect(page).toHaveURL(/.*dashboard/, { timeout: 15000 });

    // 2. Dashboard
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_dashboard.png', 
      fullPage: true 
    });

    // 3. Kasir / Create Transaction
    await page.goto('/admin/transactions/create');
    await expect(page.getByRole('heading', { name: 'Kasir & Buat Transaksi Baru' })).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_kasir.png', 
      fullPage: true 
    });

    // 4. Inventaris
    await page.goto('/admin/inventory/items');
    await expect(page.getByRole('heading', { name: 'Master Katalog Barang' })).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_inventory.png', 
      fullPage: true 
    });

    // 5. Data Pelanggan
    await page.goto('/admin/customers');
    await expect(page.getByRole('heading', { name: 'Manajemen Data Pelanggan' })).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_customers.png', 
      fullPage: true 
    });

    // 6. Serah-Terima
    await page.goto('/admin/operations/handover');
    await expect(page.getByRole('heading', { name: 'Serah Terima & Checklist QC' }).first()).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_handover.png', 
      fullPage: true 
    });

    // 7. Kalender
    await page.goto('/admin/operations/calendar');
    await expect(page.getByRole('heading', { name: 'Kalender Visual Ketersediaan Alat (Grid)' })).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_calendar.png', 
      fullPage: true 
    });

    // 8. Kanban
    await page.goto('/admin/maintenance/kanban');
    await expect(page.getByRole('heading', { name: 'Papan Kerja (Kanban) Maintenance & Gudang' }).first()).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_kanban.png', 
      fullPage: true 
    });

    // 9. Analytics
    await page.goto('/admin/analytics/dashboard');
    await expect(page.getByRole('heading', { name: 'Dashboard Analitik & Laporan Bisnis' }).first()).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_analytics.png', 
      fullPage: true 
    });

    // 10. Users
    await page.goto('/admin/users');
    await expect(page.getByRole('heading', { name: 'Manajemen Akun Pengguna' }).first()).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_users.png', 
      fullPage: true 
    });

    // 11. Audit Logs
    await page.goto('/admin/audit-logs');
    await expect(page.getByRole('heading', { name: 'Log Audit Keamanan' }).first()).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_audit_logs.png', 
      fullPage: true 
    });

    // 12. Settings
    await page.goto('/admin/settings');
    await expect(page.getByRole('heading', { name: 'Pengaturan Sistem' }).first()).toBeVisible();
    await page.screenshot({ 
      path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/5614d546-edb0-49c0-acab-95b87b1e3ab9/redesign_settings.png', 
      fullPage: true 
    });
  });
});
