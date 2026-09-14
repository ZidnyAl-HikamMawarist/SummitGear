import { test, expect } from '@playwright/test';

test('verify text contrast and colors across public and admin pages', async ({ page }) => {
  // 1. Landing page
  await page.goto('http://127.0.0.1:8000/');
  await page.waitForTimeout(500);

  const landingTextSample = await page.evaluate(() => {
    const headings = Array.from(document.querySelectorAll('h1, h2, h3')).slice(0, 5).map(h => ({
      tag: h.tagName,
      text: h.innerText.substring(0, 30),
      color: window.getComputedStyle(h).color
    }));
    const bodyColor = window.getComputedStyle(document.body).color;
    return { bodyColor, headings };
  });

  // 2. Booking page
  await page.goto('http://127.0.0.1:8000/booking');
  await page.waitForTimeout(500);

  const bookingTextSample = await page.evaluate(() => {
    const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4')).slice(0, 5).map(h => ({
      tag: h.tagName,
      text: h.innerText.substring(0, 30),
      color: window.getComputedStyle(h).color
    }));
    const bodyColor = window.getComputedStyle(document.body).color;
    return { bodyColor, headings };
  });

  // 3. Admin Login & Dashboard
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[type="email"]', 'admin@summitgear.com');
  await page.fill('input[type="password"]', 'password123');
  await page.click('button[type="submit"]');
  await page.waitForURL(/.*dashboard/);
  await page.waitForTimeout(1000);

  const adminTextSample = await page.evaluate(() => {
    const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4')).slice(0, 5).map(h => ({
      tag: h.tagName,
      text: h.innerText.substring(0, 30),
      color: window.getComputedStyle(h).color
    }));
    const bodyColor = window.getComputedStyle(document.body).color;
    const tableHeaders = Array.from(document.querySelectorAll('th')).slice(0, 3).map(th => ({
      text: th.innerText,
      color: window.getComputedStyle(th).color
    }));
    return { bodyColor, headings, tableHeaders };
  });

  console.log('TEXT_CONTRAST_AUDIT:', JSON.stringify({
    landing: landingTextSample,
    booking: bookingTextSample,
    admin: adminTextSample
  }, null, 2));

  // Take screenshots of landing, booking, and admin
  await page.screenshot({ path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27/scratch/contrast_admin_dashboard.png' });
  await page.goto('http://127.0.0.1:8000/booking');
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27/scratch/contrast_booking.png' });
  await page.goto('http://127.0.0.1:8000/');
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'C:/Users/Thinkpad/.gemini/antigravity-ide/brain/06e609f4-e57f-4faa-88a3-4a10812c9e27/scratch/contrast_landing.png' });
});
