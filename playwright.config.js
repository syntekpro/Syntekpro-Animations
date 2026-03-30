// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/e2e',
  timeout: 30000,
  retries: 1,
  use: {
    baseURL: process.env.SLIDER_E2E_BASE_URL || 'http://localhost',
    trace: 'retain-on-failure'
  }
});
