# Responsive Rules

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Tokens](DESIGN_TOKENS.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Accessibility](ACCESSIBILITY.md) |

---

Mobile-first: every layout is designed at 375px wide first, then expanded (`DESIGN_LANGUAGE.md`'s Layout Philosophy). Breakpoints match `DESIGN_TOKENS.md`: `sm` 640px · `md` 768px · `lg` 1024px · `xl` 1280px.

## Desktop (`lg` and above)

- **Container behaviour:** content sits within `container-narrow`/`container-standard`/`container-wide` (`DESIGN_TOKENS.md`) depending on screen type — never full-bleed body text.
- **Spacing:** full spacing scale available; section gaps use the larger end (`space-12`–`space-24`).
- **Typography:** full type scale, up to `text-4xl` for the homepage hero only.
- **Stacking:** the Risk Report remains single-column even on desktop (`COMPONENT_PRINCIPLES.md`'s Reports) — desktop width is used for comfortable line length and margin, not for cramming more columns of content.
- **Navigation:** full horizontal nav bar with all primary destinations visible.
- **Cards:** may sit in a 2–3 column grid where content is genuinely parallel (e.g. a grid of past analyses).

## Tablet (`md`–`lg`)

- **Container behaviour:** `container-standard` becomes the effective maximum; `container-wide` marketing sections reduce their side margins proportionally.
- **Spacing:** section gaps step down one level from desktop (e.g. `space-24` → `space-16`).
- **Typography:** headings step down one type-scale level from desktop where they'd otherwise wrap awkwardly (e.g. hero `text-4xl` → `text-3xl`).
- **Stacking:** card grids reduce from 3 columns to 2.
- **Navigation:** horizontal nav bar retained if all primary destinations fit; otherwise collapses to the mobile pattern early rather than truncating labels.
- **Cards:** internal padding may step down from `space-6` toward `space-4`.
- **Touch spacing:** treat as a touch device by default (tablets are primarily touch) — apply the same touch-target and spacing minimums as mobile, not desktop's tighter hover-based spacing.

## Mobile (below `md`)

- **Container behaviour:** full-width content with consistent side padding (`space-4`); no fixed container width.
- **Spacing:** tightest end of the scale for section gaps (`space-8`–`space-12`); component-internal spacing stays as close to desktop as possible (don't compress card padding below `space-4` — cramped touch targets are worse than a longer scroll).
- **Typography:** smallest appropriate type-scale step per element; body text never drops below `text-base` for anything the user must read to act (`ACCESSIBILITY.md`).
- **Stacking:** everything is single-column. Card grids, nav items, and multi-field forms all stack vertically.
- **Navigation:** collapses to a bottom tab bar (preferred, keeps primary destinations one thumb-reach away) or a hamburger menu for secondary/account items — never a horizontally scrolling nav strip.
- **Cards:** full-width, `space-4` internal padding, `space-4` gap between stacked cards.
- **Touch spacing:** minimum 44×44px targets with 8px+ gaps (`ACCESSIBILITY.md`); the slip-leg entry form in particular needs deliberate spacing between Add/Remove/Reorder controls to prevent mis-taps.
- **Scrolling behaviour:** natural vertical scroll only — no horizontal scroll anywhere except an explicitly contained, clearly-affordanced carousel (and SlipGuard currently has no approved use for one — see `MOTION_SYSTEM.md`'s Scroll Behaviour, no scroll-jacking on any breakpoint).

## Cross-Breakpoint Rules

- No content is hidden on mobile that's available on desktop, or vice versa — responsive changes layout and density, never information available to the user.
- No breakpoint introduces a new interaction pattern for the same task (e.g. drag-to-reorder on desktop must have an equivalent tap-based reorder control on mobile, not be dropped).
- Test every screen at 375px width first before considering a layout complete — a layout that only works at 1440px is not finished (`DESIGN_LANGUAGE.md`).
