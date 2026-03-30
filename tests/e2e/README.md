# E2E test suite

These Playwright tests validate key slider interactions:

- Arrow navigation
- Keyboard navigation
- Runtime mount sanity

## Run

1. Install dependencies in a JS workspace (`@playwright/test`).
2. Set `SLIDER_E2E_BASE_URL` to your local WordPress URL.
3. Run `npx playwright test`.
