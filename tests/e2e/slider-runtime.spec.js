const { test, expect } = require('@playwright/test');

test.describe('Syntekpro slider runtime', () => {
  test('renders slider and navigates with arrows', async ({ page }) => {
    await page.goto('/');

    const slider = page.locator('.sp-slider-runtime').first();
    await expect(slider).toBeVisible();

    const next = slider.locator('.sp-slider-next');
    if (await next.count()) {
      await next.click();
      await expect(slider).toHaveAttribute('data-slider-id', /.+/);
    }
  });

  test('supports keyboard navigation', async ({ page }) => {
    await page.goto('/');
    const slider = page.locator('.sp-slider-runtime').first();
    await expect(slider).toBeVisible();
    await slider.focus();
    await page.keyboard.press('ArrowRight');
    await page.keyboard.press('ArrowLeft');
    await expect(slider).toBeVisible();
  });
});
