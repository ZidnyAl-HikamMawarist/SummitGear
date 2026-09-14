import { chromium } from '@playwright/test';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const artifactDir = 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27';

  // --- 1. ADMIN CONTEXT ---
  const adminContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
  const adminPage = await adminContext.newPage();

  console.log('Logging in as admin...');
  await adminPage.goto('http://127.0.0.1:8000/login');
  await adminPage.waitForLoadState('networkidle');

  await adminPage.locator('input[type="email"]').first().fill('admin@summitgear.com');
  await adminPage.locator('input[type="password"]').first().fill('password123');
  await adminPage.locator('button[type="submit"]').first().click();

  await adminPage.waitForURL(/.*dashboard/, { timeout: 15000 });
  await adminPage.waitForLoadState('networkidle');
  await adminPage.waitForTimeout(1000);

  // 1. Capture Admin Dashboard & Sidebar
  console.log('Capturing Admin Dashboard...');
  await adminPage.screenshot({ path: `${artifactDir}/dashboard_admin_font.png` });

  // Capture close-up of sidebar
  const sidebar = adminPage.locator('aside.sidebar');
  if (await sidebar.count() > 0) {
    await sidebar.first().screenshot({ path: `${artifactDir}/sidebar_admin_closeup.png` });
  }

  // 2. Capture Gudang Dashboard
  console.log('Capturing Gudang Dashboard...');
  await adminPage.goto('http://127.0.0.1:8000/gudang/dashboard');
  await adminPage.waitForLoadState('networkidle');
  await adminPage.waitForTimeout(1000);
  await adminPage.screenshot({ path: `${artifactDir}/dashboard_gudang_font.png` });

  // 3. Capture Incoming Booking
  console.log('Capturing Incoming Booking...');
  await adminPage.goto('http://127.0.0.1:8000/admin/operations/incoming-booking');
  await adminPage.waitForLoadState('networkidle');
  await adminPage.waitForTimeout(1000);
  await adminPage.screenshot({ path: `${artifactDir}/dashboard_incoming_booking_font.png` });

  await adminContext.close();

  // --- 2. KASIR CONTEXT ---
  console.log('Logging in as Kasir...');
  const kasirContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
  const kasirPage = await kasirContext.newPage();

  await kasirPage.goto('http://127.0.0.1:8000/login');
  await kasirPage.waitForLoadState('networkidle');
  await kasirPage.locator('input[type="email"]').first().fill('kasir@summitgear.com');
  await kasirPage.locator('input[type="password"]').first().fill('password123');
  await kasirPage.locator('button[type="submit"]').first().click();

  await kasirPage.waitForLoadState('networkidle');
  await kasirPage.waitForTimeout(1000);

  await kasirPage.goto('http://127.0.0.1:8000/admin/transactions/create');
  await kasirPage.waitForLoadState('networkidle');
  await kasirPage.waitForTimeout(1000);

  console.log('Capturing Kasir Dashboard...');
  await kasirPage.screenshot({ path: `${artifactDir}/dashboard_kasir_font.png` });

  await kasirContext.close();
  console.log('All screenshots captured successfully!');
  await browser.close();
})();
