import { test, expect } from '@playwright/test';

test.use({ colorScheme: 'dark' });

test('test login in dark scheme', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/login');
  await page.waitForTimeout(1000);

  const debugInfo = await page.evaluate(() => {
    const htmlClass = document.documentElement.className;
    const bodyClass = document.body.className;
    const emailInput = document.querySelector('input[type="email"]');
    const emailLabel = document.querySelector('label');
    const fluxCard = document.querySelector('[data-flux-card], .flux-card, form');

    return {
      htmlClass,
      bodyClass,
      inputStyles: emailInput ? {
        color: window.getComputedStyle(emailInput).color,
        backgroundColor: window.getComputedStyle(emailInput).backgroundColor,
        borderColor: window.getComputedStyle(emailInput).borderColor,
        borderWidth: window.getComputedStyle(emailInput).borderWidth,
      } : null,
      labelStyles: emailLabel ? {
        color: window.getComputedStyle(emailLabel).color,
      } : null,
      cardStyles: fluxCard ? {
        backgroundColor: window.getComputedStyle(fluxCard).backgroundColor,
        color: window.getComputedStyle(fluxCard).color,
      } : null
    };
  });

  console.log('DARK_SCHEME_DEBUG_INFO:', JSON.stringify(debugInfo, null, 2));
  await page.screenshot({ path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27/scratch/login_dark_mode.png' });

  // Now test login PIN page
  await page.goto('http://127.0.0.1:8000/login/pin');
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27/scratch/login_pin_dark_mode.png' });
});
