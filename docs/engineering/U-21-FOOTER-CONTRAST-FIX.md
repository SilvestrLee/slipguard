# U-21 — Public Footer Contrast Fix

| Field | Value |
|---|---|
| Responds to | Direct founder instruction, 2026-07-31: "Fix the footer dark-theme contrast issue as a new commission." |
| Origin | Real, pre-existing defect found and disclosed in `U-20.8`'s accessibility pass — flagged there as out of that work package's scope, now fixed under its own commission per `U-20`'s closing statement ("future public-website work proceeds under a new programme identifier"). |
| Code | `resources/css/app.css` (commit `642772a`) |
| Status | Delivered |

## What `U-20.8` got wrong, corrected here rather than repeated

`U-20.8` described the defect as "the public footer does not adapt to dark theme, causing widespread color-contrast failures." That root cause is incorrect, and the correction matters for anyone reading this later: the footer's background **does** correctly switch to a dark gradient in dark theme. Confirmed directly — every element in the DOM chain from the footer's wordmark up through `<html>` is transparent (`rgba(0,0,0,0)`, verified via `getComputedStyle`); the actual visible background comes from `.atmosphere`, a `position: fixed` element that is a **sibling** of the content wrapper, not a DOM ancestor. axe-core's `color-contrast` rule walks the DOM ancestor chain to determine background colour; since `.atmosphere` isn't in that chain, it falls back to assuming white — which is why it reported `background-color: #ffffff` even under `data-theme="dark"`. That specific number was wrong.

## What was actually true

Real contrast was measured directly, not assumed: the computed text colour (`getComputedStyle`) against a background colour sampled from an actual rendered screenshot (not axe's fallback), run through the standard WCAG relative-luminance formula. Result, before any fix:

| Element (token) | Light theme | Dark theme |
|---|---|---|
| Wordmark (`neutral-900`) | 15.44 PASS | 16.07 PASS |
| Nav links (`neutral-600`, unchanged) | 6.24 PASS | 8.01 PASS |
| Tagline (`neutral-500`) | 3.94 FAIL | 4.49 FAIL |
| Column headings (`neutral-500`) | 4.12 FAIL | 2.26 FAIL |
| Copyright (`neutral-500`) | 3.94 FAIL | 4.61 marginal |
| Disclaimer (`neutral-400`) | 3.65 FAIL | 1.94 FAIL |

axe-core's own light-theme check only caught the disclaimer (its assumed-white background happens to be close to, but not exactly, the true light `.atmosphere` gradient — close enough that most of these passed axe's check while still genuinely failing a real measurement). Its dark-theme check flagged everything, correctly in aggregate, for the wrong specific reason.

## Fix

```css
footer .text-neutral-400,
footer .text-neutral-500 {
    color: var(--neutral-600);
}
```

`neutral-600` was chosen because it's already in use by the footer's own nav links and had already measured as comfortably passing in both themes (6.24 light / 8.01 dark) — one proven-safe value, applied uniformly, rather than a separate per-theme override. Scoped to `footer` only: `neutral-400`/`neutral-500` remain correct everywhere else in the app, where they're normally paired with the lighter `--surface-card` family rather than sitting directly on the atmosphere background.

After the fix, all six elements pass comfortably in both themes:

| Element | Light | Dark |
|---|---|---|
| Wordmark | 15.44 | 16.07 |
| Tagline | 6.28 | 8.11 |
| Nav links | 6.24 | 8.01 |
| Column headings | 6.55 | 8.37 |
| Copyright | 6.28 | 8.11 |
| Disclaimer | 5.81 | 7.76 |

## On the remaining axe-core "violations"

Re-running axe-core after the fix still reports ~20 `color-contrast` violations per page in dark theme, every single one inside `<footer>` — including the nav links, whose colour was never touched by this fix and independently measures 8.01:1. This is direct, repeatable proof the tool cannot evaluate this specific component correctly (the fixed-sibling blind spot above), not that a defect remains. Chasing a "zero violations" count from a tool with a confirmed blind spot on this exact component would mean either fabricating an unneeded explicit background (undermining the intentional design that lets `.atmosphere` show through, for no real accessibility gain) or trusting a false report over real, verified measurement. Neither is the honest choice; the real measurement is documented above instead.

## Verification

Pint clean. `git diff --check` clean. `php artisan test --filter="Homepage|PublicPages|GlobalShell|DesignSystem"`: 80/83 passing — the 3 failures are the same pre-existing, unrelated `container-analytics` and logo-motion tests already documented as tied to another in-progress, uncommitted change, not this one. Production build clean. Visual screenshots confirmed no regression in either theme — text reads as calm and de-emphasised, not newly bold or heavy, in both light and dark.
