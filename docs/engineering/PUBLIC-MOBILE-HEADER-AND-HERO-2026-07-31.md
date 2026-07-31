# Public Mobile Header and Homepage Hero Verification

Date: 2026-07-31  
Authority: direct founder approval  
Status: implemented and browser-verified

## Confirmed from repository

- The public header previously rendered its desktop navigation utilities and authentication actions in the mobile header row.
- The authenticated workspace already contained the repository's accepted accessible drawer interaction pattern.
- The homepage uses genuine light- and dark-theme dashboard screenshots rather than illustrative analysis data.
- The public atmosphere contains fixed, oversized blurred forms whose geometry can enlarge the document's measured scroll width on small viewports.

## Implemented

- The public desktop header remains unchanged from the `md` breakpoint upward.
- Mobile now presents the compact SlipGuard brand and one 44-pixel navigation trigger.
- The public drawer contains genuine destinations and account actions, manages initial and restored focus, traps keyboard focus, closes with Escape and after navigation, and locks background scrolling while open.
- The dashboard preview is a horizontally explorable region on mobile. It renders at no less than 200% of the available viewport width, so approximately one half is visible at a time, and maintains a minimum visible height of 50% of the small viewport.
- The full 1400:820 screenshot remains intact at its natural aspect ratio. It is not cropped or stretched.
- The preview bleeds from the content gutter to the physical right edge and includes a restrained “Drag to explore” affordance, making the continuation discoverable without a scrollbar.
- The primary and secondary hero actions use equal, full-width mobile alignment before adapting deliberately for tablet and desktop layouts.
- Tablet portrait centres both actions within the single-column hero. Tablet landscape gives the stacked actions an equal 224-pixel width and aligns them to the right-hand hero column. Intrinsic inline widths return at the wider `xl` breakpoint.
- The visible drag affordance is hidden by default and enabled only below 768 pixels, the same range in which the dashboard preview becomes a two-view horizontal canvas.
- Every authenticated sticky workspace header exposes a shared Home-icon “Return to Website” action linking to the public homepage.
- Touch users can drag the preview; keyboard users can focus it and use Left/Right Arrow. Scrollbars are visually suppressed without removing scrolling semantics.
- Public document overflow is clipped horizontally, preventing atmosphere geometry from creating a page-level horizontal scrollbar.

## Verification

- `php artisan test tests/Feature/GlobalShellTest.php tests/Feature/HomepageTest.php tests/Feature/PublicPagesTest.php tests/Feature/ThemeSystemTest.php`
  - 67 passed
  - 521 assertions
- `npm run build`
  - passed
- `git diff --check`
  - passed
- Playwright with system Chrome at 320, 375, 390 and 430 CSS pixels:
  - desktop navigation hidden;
  - drawer opens and close control receives focus;
  - background scrolling locks while open;
  - theme, sign-in and registration actions remain available;
  - Escape closes the drawer and restores trigger focus;
  - no browser page errors;
  - preview scroll width is exactly twice its visible width;
  - Right Arrow advances the preview by half a visible viewport.
- Follow-up browser inspection at 390 × 844 confirmed:
  - the preview reaches the physical right edge;
  - 378 CSS pixels are visible from a 756 CSS-pixel canvas;
  - the rendered screenshot ratio (`1.70733`) matches its natural ratio (`1.70732`);
  - the screenshot is 441.6 CSS pixels tall, exceeding 50% of the small viewport;
  - the two mobile hero actions are equally aligned and full width.
- Tablet browser inspection confirmed:
  - at 768 × 1024 the action group is centred as a pair;
  - at 1024 × 768 both stacked controls share the same `x` coordinate and 224-pixel width;
  - no hero content or desktop navigation structure changed.
- Responsive visibility verification confirmed:
  - 390px and 767px: two-view screenshot and drag affordance visible;
  - 768px and 1280px: full-width screenshot and drag affordance hidden.

## Scope preserved

- The authenticated-shell change is limited to the shared public-home return action.
- No desktop navigation redesign.
- No theme-token or atmosphere-composition change.
- No product copy, route, persistence, provider or deterministic-analysis change.
- Existing unrelated working-tree content was not reset, stashed, reverted, cleaned or overwritten.
