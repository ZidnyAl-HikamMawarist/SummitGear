import { test, expect } from '@playwright/test';

const artifactDir = 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27';

test.describe('SummitGear Comprehensive Visual Regression & Layout Audit', () => {

  test('Desktop & Mobile Visual Audit across all core pages', async ({ page }) => {
    test.setTimeout(120000);
    const auditResults = {
      fontChecks: [],
      modalChecks: [],
      overflowChecks: [],
      iconChecks: [],
    };

    // -------------------------------------------------------------
    // 1. LOGIN PAGE (Desktop & Mobile)
    // -------------------------------------------------------------
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    // Verify Plus Jakarta Sans / Poppins font on body and heading
    const bodyFont = await page.evaluate(() => window.getComputedStyle(document.body).fontFamily);
    const loginHeadingFont = await page.evaluate(() => {
      const h1 = document.querySelector('h1');
      return h1 ? window.getComputedStyle(h1).fontFamily : null;
    });
    auditResults.fontChecks.push({ page: 'login', bodyFont, loginHeadingFont });
    expect(bodyFont.toLowerCase()).toMatch(/(plus jakarta sans|poppins)/);

    await page.screenshot({ path: `${artifactDir}/visual_01_login_desktop.png`, fullPage: true });

    // Check Login on 375px mobile
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(300);
    const mobileLoginOverflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth);
    auditResults.overflowChecks.push({ page: 'login_mobile_375px', hasHorizontalScroll: mobileLoginOverflow });
    expect(mobileLoginOverflow).toBe(false);
    await page.screenshot({ path: `${artifactDir}/visual_01_login_mobile_375px.png`, fullPage: true });

    // -------------------------------------------------------------
    // 2. LOGIN PIN PAGE
    // -------------------------------------------------------------
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/login/pin');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_02_login_pin_desktop.png`, fullPage: true });

    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(300);
    const mobilePinOverflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth);
    auditResults.overflowChecks.push({ page: 'login_pin_mobile_375px', hasHorizontalScroll: mobilePinOverflow });
    expect(mobilePinOverflow).toBe(false);
    await page.screenshot({ path: `${artifactDir}/visual_02_login_pin_mobile_375px.png`, fullPage: true });

    // -------------------------------------------------------------
    // 3. AUTHENTICATE AS ADMIN
    // -------------------------------------------------------------
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/login');
    // Using standard input selector for flux:input
    const emailInput = page.locator('input[type="email"]').first();
    await emailInput.fill('admin@summitgear.com');
    const passwordInput = page.locator('input[type="password"]').first();
    await passwordInput.fill('password123');
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/.*dashboard/, { timeout: 15000 });
    await page.waitForLoadState('networkidle');

    // -------------------------------------------------------------
    // 4. ADMIN DASHBOARD (Desktop 1280px & 1440px & Mobile 375px)
    // -------------------------------------------------------------
    const dashboardBodyFont = await page.evaluate(() => window.getComputedStyle(document.body).fontFamily);
    const dashboardH1Font = await page.evaluate(() => {
      const h1 = document.querySelector('h1') || document.querySelector('h2');
      return h1 ? window.getComputedStyle(h1).fontFamily : null;
    });
    expect(dashboardBodyFont.toLowerCase()).toMatch(/(plus jakarta sans|poppins)/);

    // Check SVG sizing on dashboard icons (must not be overridden to a single uniform size)
    const iconSizes = await page.evaluate(() => {
      const svgs = Array.from(document.querySelectorAll('main svg'));
      return svgs.slice(0, 5).map(s => {
        const rect = s.getBoundingClientRect();
        return { width: Math.round(rect.width), height: Math.round(rect.height) };
      });
    });
    auditResults.iconChecks.push({ page: 'dashboard', iconSizes });

    // Desktop 1280px screenshot
    await page.screenshot({ path: `${artifactDir}/visual_03_dashboard_desktop_1280.png`, fullPage: true });

    // Desktop 1440px screenshot
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${artifactDir}/visual_03_dashboard_desktop_1440.png`, fullPage: true });

    // Mobile 375px check
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(400);
    const mobileDashOverflow = await page.evaluate(() => {
      const content = document.querySelector('.content-area') || document.querySelector('main');
      return {
        docScroll: document.documentElement.scrollWidth > document.documentElement.clientWidth,
        mainScroll: content ? content.scrollWidth > content.clientWidth : false
      };
    });
    auditResults.overflowChecks.push({ page: 'dashboard_mobile_375px', mobileDashOverflow });
    await page.screenshot({ path: `${artifactDir}/visual_03_dashboard_mobile_375.png`, fullPage: true });

    // -------------------------------------------------------------
    // 5. TEST MODAL CENTERING (Logout Modal via Sidebar/Topbar)
    // -------------------------------------------------------------
    await page.setViewportSize({ width: 1280, height: 800 });
    // Click logout button on sidebar to trigger centered Flux modal
    const logoutBtn = page.locator('button:has-text("Logout"), button:has-text("Keluar")').first();
    if (await logoutBtn.isVisible()) {
      await logoutBtn.click();
      await page.waitForTimeout(500);

      // Measure modal position relative to viewport
      const modalMetric = await page.evaluate(() => {
        const dialog = document.querySelector('dialog[open], [data-flux-modal][open], [data-flux-modal]');
        if (!dialog) return null;
        const rect = dialog.getBoundingClientRect();
        const viewportW = window.innerWidth;
        const viewportH = window.innerHeight;
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        return {
          viewportW,
          viewportH,
          modalRect: { top: rect.top, left: rect.left, width: rect.width, height: rect.height },
          isCenteredHorizontally: Math.abs(centerX - viewportW / 2) < 20,
          isCenteredVertically: Math.abs(centerY - viewportH / 2) < 50,
        };
      });

      auditResults.modalChecks.push({ modal: 'logout_modal', modalMetric });
      await page.screenshot({ path: `${artifactDir}/visual_04_modal_centered.png` });

      // Close modal with Escape key
      await page.keyboard.press('Escape');
      await page.waitForTimeout(400);
    }

    // -------------------------------------------------------------
    // 6. INVENTARIS ITEM INDEX & UNIT INDEX
    // -------------------------------------------------------------
    await page.goto('/admin/inventory/items');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_05_inventory_items.png`, fullPage: true });

    // Check item form
    const editOrNewBtn = page.locator('a[href*="/inventory/items/create"]').first();
    if (await editOrNewBtn.isVisible()) {
      await editOrNewBtn.click();
      await page.waitForLoadState('networkidle');
      await page.screenshot({ path: `${artifactDir}/visual_06_item_form.png`, fullPage: true });
    }

    // -------------------------------------------------------------
    // 7. KALENDER BOOKING MATRIX
    // -------------------------------------------------------------
    await page.goto('/admin/operations/calendar');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_07_calendar_matrix.png`, fullPage: true });

    // -------------------------------------------------------------
    // 8. MAINTENANCE KANBAN
    // -------------------------------------------------------------
    await page.goto('/admin/maintenance/kanban');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_08_maintenance_kanban.png`, fullPage: true });

    // -------------------------------------------------------------
    // 9. INCOMING BOOKING
    // -------------------------------------------------------------
    await page.goto('/admin/operations/incoming-booking');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_09_incoming_booking.png`, fullPage: true });

    // -------------------------------------------------------------
    // 10. SERAH TERIMA (HANDOVER)
    // -------------------------------------------------------------
    await page.goto('/admin/operations/handover');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_10_handover.png`, fullPage: true });

    // -------------------------------------------------------------
    // 11. DATA PELANGGAN
    // -------------------------------------------------------------
    await page.goto('/admin/customers');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_11_customers.png`, fullPage: true });

    // -------------------------------------------------------------
    // 12. PENGATURAN (SETTINGS & QR CODE)
    // -------------------------------------------------------------
    await page.goto('/admin/settings');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_12_settings_and_qr.png`, fullPage: true });

    // -------------------------------------------------------------
    // 13. AUDIT LOGS
    // -------------------------------------------------------------
    await page.goto('/admin/audit-logs');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_13_audit_logs.png`, fullPage: true });

    // -------------------------------------------------------------
    // 14. GUDANG DASHBOARD
    // -------------------------------------------------------------
    await page.goto('/gudang/dashboard');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_14_gudang_dashboard.png`, fullPage: true });

    // -------------------------------------------------------------
    // 15. PUBLIC LANDING PAGE & BOOKING (Client View)
    // -------------------------------------------------------------
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_15_public_landing.png`, fullPage: true });

    await page.goto('/booking');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${artifactDir}/visual_16_public_booking.png`, fullPage: true });

    // Output JSON audit report for console verification
    console.log('AUDIT_RESULTS_JSON:' + JSON.stringify(auditResults, null, 2));
  });

});
