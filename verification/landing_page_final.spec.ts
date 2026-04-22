import { test } from '@playwright/test';

test('capture screenshots of landing page', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000');

  // Hero
  await page.screenshot({ path: 'verification/screenshots/hero_final.png' });

  // Funcionalidades
  await page.locator('#funcionalidades').scrollIntoViewIfNeeded();
  await page.screenshot({ path: 'verification/screenshots/funcionalidades_final.png' });

  // Paineis
  await page.locator('#paineis').scrollIntoViewIfNeeded();
  await page.screenshot({ path: 'verification/screenshots/paineis_final.png' });

  // Sobre
  await page.locator('#sobre').scrollIntoViewIfNeeded();
  await page.screenshot({ path: 'verification/screenshots/sobre_final.png' });
});
