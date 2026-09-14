import { chromium } from '@playwright/test';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const artifactDir = 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27';

  const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
  const page = await context.newPage();

  console.log('Logging in as Kasir...');
  await page.goto('http://127.0.0.1:8000/login');
  await page.waitForLoadState('networkidle');

  await page.locator('input[type="email"]').first().fill('kasir@summitgear.com');
  await page.locator('input[type="password"]').first().fill('password123');
  await page.locator('button[type="submit"]').first().click();

  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1000);

  await page.goto('http://127.0.0.1:8000/admin/transactions/create');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1000);

  // Capture close-up of customer & schedule section
  const customerCard = page.locator('label[for="pos-customer"]').locator('..').locator('..');
  if (await customerCard.count() > 0) {
    console.log('Capturing customer card close-up...');
    await customerCard.first().screenshot({ path: `${artifactDir}/kasir_customer_dropdown_fixed.png` });
  }

  console.log('Capturing full Kasir dashboard...');
  await page.screenshot({ path: `${artifactDir}/dashboard_kasir_font.png` });

  console.log('Done capturing!');
  await browser.close();
})();
