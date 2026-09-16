import { test, expect } from '@playwright/test';

test.describe('Verify Fixes for User Issues', () => {

    test('1 & 2 & 3: Catalog plus button, category filter, add to cart, and cart drawer', async ({ page }) => {
        const consoleErrors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });

        await page.goto('/booking');
        await page.waitForLoadState('networkidle');

        // Verify there is no 'count is not defined' in console
        const countErrors = consoleErrors.filter(e => e.includes('count is not defined'));
        expect(countErrors).toHaveLength(0);

        // 1. Verify button text has single '+' and clean 'Tambah' text
        const firstAddBtn = page.locator('button:has-text("Tambah")').first();
        await expect(firstAddBtn).toBeVisible();
        const btnText = await firstAddBtn.innerText();
        expect(btnText.trim()).toBe('Tambah');
        expect(btnText).not.toContain('+ +');

        // 2. Test Category filter pills
        const tendaPill = page.locator('button:has-text("Tenda")').first();
        await tendaPill.click();
        const visibleTendaBadge = page.locator('button[title*="Filter kategori Tenda"]').first();
        await expect(visibleTendaBadge).toBeVisible();

        // Test Category "Sepatu"
        const sepatuPill = page.locator('button:has-text("Sepatu")').first();
        await sepatuPill.click();
        const visibleSepatuBadge = page.locator('button[title*="Filter kategori Sepatu"]').first();
        await expect(visibleSepatuBadge).toBeVisible();

        // Reset to Semua Kategori
        const allPill = page.locator('button:has-text("Semua Kategori")').first();
        await allPill.click();
        await expect(page.locator('button[title*="Filter kategori Tenda"]').first()).toBeVisible();

        // 3. Test Add to Cart
        const addBtn = page.locator('button:has-text("Tambah")').first();
        await addBtn.click();
        await page.waitForTimeout(1000);

        // Verify button changed to "Tambah Lagi"
        await expect(page.locator('button:has-text("Tambah Lagi")').first()).toBeVisible();

        // Verify Navbar Cart button shows count
        const navCartBtn = page.locator('header button:has-text("Keranjang")');
        await expect(navCartBtn).toContainText('1');

        // Verify Floating Bottom Bar appears
        const floatingBar = page.locator('button:has-text("Lihat & Reservasi")');
        await expect(floatingBar).toBeVisible();

        // 4. Test Opening Cart Drawer
        await navCartBtn.click();
        await page.waitForTimeout(600);

        // Verify Drawer is visible
        const drawerHeader = page.locator('h2:has-text("Keranjang Sewa")');
        await expect(drawerHeader).toBeVisible();

        // Verify item is listed in drawer
        const drawerItem = page.locator('div[wire\\:key^="cart-item-"]');
        await expect(drawerItem).toBeVisible();

        // Check console errors again - should have no Alpine errors
        const lateCountErrors = consoleErrors.filter(e => e.includes('count is not defined'));
        expect(lateCountErrors).toHaveLength(0);
    });

    test('4. Admin Login and Logout flow', async ({ page }) => {
        await page.goto('/login');
        await page.waitForLoadState('networkidle');

        await page.fill('input[type="email"]', 'admin@summitgear.com');
        await page.fill('input[type="password"]', 'password123');
        await page.click('button:has-text("Masuk ke Akun")');

        await page.waitForURL('**/dashboard');
        await expect(page.locator('h3:has-text("Selamat Datang, Super Admin!")')).toBeVisible();

        // Test Logout trigger
        const logoutBtn = page.locator('button:has-text("Keluar (Logout)")');
        await logoutBtn.click();
        await page.waitForTimeout(500);

        // Confirm logout in modal
        const confirmLogoutBtn = page.locator('button:has-text("Ya, Keluar Akun")');
        await expect(confirmLogoutBtn).toBeVisible();
        await confirmLogoutBtn.click();

        await page.waitForURL('**/login');
        await expect(page.locator('h1:has-text("SummitGear")')).toBeVisible();
    });

    test('4b. Cashier PIN Login flow', async ({ page }) => {
        await page.goto('/login/pin');
        await page.waitForLoadState('networkidle');

        // Click PIN numpad '1' six times
        for (let i = 0; i < 6; i++) {
            await page.locator('button:has-text("1")').first().click();
            await page.waitForTimeout(100);
        }

        // Wait for auto-submit or click submit
        await page.waitForTimeout(1500);
        if (!page.url().includes('transactions/create')) {
            const submitBtn = page.locator('button:has-text("Masuk ke Sistem")');
            if (await submitBtn.isVisible()) {
                await submitBtn.click();
            }
        }

        await page.waitForURL('**/admin/transactions/create', { timeout: 10000 });
        await expect(page.locator('text=Terminal Kasir POS')).toBeVisible();
    });

});
