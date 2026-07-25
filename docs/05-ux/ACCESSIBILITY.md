# Accessibility

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [UX Rules](UX_RULES.md), [Design Tokens](DESIGN_TOKENS.md), [Motion System](MOTION_SYSTEM.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Responsive Rules](RESPONSIVE_RULES.md) |

---

Accessibility is a design requirement in SlipGuard, not a post-launch pass. A screen that isn't accessible isn't finished, regardless of how it looks (`DESIGN_LANGUAGE.md`). This document is implementation-level detail; `UX_RULES.md`'s Accessibility section states the same principles at a higher level — this is what satisfies them concretely.

## Contrast Ratios

- Body text (`text-base` and smaller): minimum **4.5:1** against its background (WCAG AA).
- Large text (`text-xl`/`h3` and above, bold): minimum **3:1**.
- Non-text UI elements that carry meaning (icons, focus rings, risk-band indicator borders): minimum **3:1** against adjacent colours.
- Every colour pair defined in `DESIGN_TOKENS.md` (including every risk-band and data-quality-band colour against its text/background) must be verified at both light and dark mode before it ships — a colour that only passes in one mode is not usable.

## Keyboard Navigation

- Every interactive element (buttons, links, form fields, cards that act as links) is reachable and operable via `Tab`/`Shift+Tab`, in a logical order matching visual reading order.
- No keyboard trap — a modal or drawer must return focus cleanly to the element that opened it on close.
- Custom interactive components (expandable per-leg detail, progressive-disclosure sections) must be operable with `Enter`/`Space`, not mouse-only.
- Skip-to-content link on every page for keyboard/screen-reader users to bypass repeated navigation.

## Focus States

- Every interactive element has a visible focus ring — never `outline: none` without a replacement.
- Focus ring uses `duration-instant` (100ms, `MOTION_SYSTEM.md`) colour/opacity transition — appears immediately, no delay.
- Focus ring colour must meet the 3:1 non-text contrast minimum above, in both light and dark mode.
- Focus is never trapped, never silently moved without user action, and always visible — never hidden behind `elevation-3` overlays without being brought along.

## Screen Reader Expectations

- Every image conveys meaning through alt text or is marked `aria-hidden` if purely decorative (see `IMAGE_GUIDELINES.md`).
- Risk bands and data-quality bands are announced as text ("High risk," "Strong data quality") — never conveyed by colour or icon alone to assistive technology.
- Loading states use `role="status"` with a real label ("Analysing your slip"), not just a visual spinner (`MOTION_SYSTEM.md`'s Loading Behaviour).
- Form errors are programmatically associated with their field (`aria-describedby`) and announced on submission, not just shown visually.
- Headings follow a correct, unbroken hierarchy (`h1` → `h2` → `h3`) reflecting actual document structure — never chosen for font size (`COMPONENT_PRINCIPLES.md`'s Section Headers).

## Motion Reduction

Every animation in `MOTION_SYSTEM.md` has a `prefers-reduced-motion: reduce` fallback: entrances/exits become instant opacity swaps, loading indicators drop pulsing/spinning in favour of a static label, scroll-triggered reveals show final state immediately. Implemented once at the token/utility level (`DESIGN_TOKENS.md`'s Animation Timing) so it can't be forgotten per component.

## Touch Targets

- Minimum 44×44px for every tappable element (buttons, checkboxes, nav items, icon-only buttons) — matches `DESIGN_TOKENS.md`'s `button-lg` height.
- Minimum 8px spacing between adjacent touch targets to prevent mis-taps, especially in the leg-entry form and slip index actions.

## Readable Typography

- Body text minimum `text-base` (never smaller than `text-sm` for anything the user must read to complete a task, not just decorative fine print).
- Line length capped around 70–80 characters for body copy — enforced by `DESIGN_TOKENS.md`'s `container-narrow` on reading-heavy screens (Risk Report, Journal).
- Line height (`--leading-*`) generous enough that dense risk-report paragraphs remain easy to scan, never condensed to save vertical space.
- No text set entirely in uppercase for anything longer than a short label — hurts readability and screen-reader pronunciation in some contexts.

## Colour Independence

No information is conveyed by colour alone, anywhere:
- Risk bands: colour + icon + text label together (`COMPONENT_PRINCIPLES.md`'s Risk Indicators).
- Slip status badges: colour + text label.
- Form validation: colour + icon + explicit message text, not a red border alone.
- Links within body text: underlined or otherwise distinguished by more than colour, so colour-blind users and grayscale/print contexts can still identify them.

## Dark Mode Considerations

- Every token in `DESIGN_TOKENS.md` has a defined dark-mode value — dark mode is not an inverted-filter afterthought.
- Elevation in dark mode is communicated more by background lightness steps than by shadow (shadows read poorly on dark backgrounds) — each `elevation-*` token defines its own light and dark treatment.
- Risk-band and data-quality colours are re-tuned for dark backgrounds (not simply reused at the same lightness) to preserve the same contrast ratios.
- Respect the system `prefers-color-scheme` by default; a manual toggle may be added later but is not required for MVP.
