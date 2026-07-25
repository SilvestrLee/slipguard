# Design Tokens

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Language](DESIGN_LANGUAGE.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Motion System](MOTION_SYSTEM.md), [Accessibility](ACCESSIBILITY.md), [Responsive Rules](RESPONSIVE_RULES.md) |

---

The single source of truth for every visual value in SlipGuard. **Never hardcode a colour, spacing, radius, or timing value directly in a Blade view, Livewire component, or Filament resource** — reference a token defined here. If a value you need isn't listed, add it here first, then use it — don't invent an ad-hoc value in the markup.

Implemented as Tailwind v4 `@theme` CSS variables in `resources/css/app.css` (v4 uses CSS-based theme configuration — no `tailwind.config.js`). Token names below map directly to `--variable-name` entries and their generated Tailwind utility classes.

## Colours

One functional accent colour, a neutral scale, and reserved semantic colours for risk/status — nothing decorative (`VISUAL_INSPIRATION.md`: "restrained colour palette").

| Token | Purpose |
|---|---|
| `--color-neutral-{50…950}` | Backgrounds, text, borders, surfaces. The vast majority of the UI uses only this scale. |
| `--color-accent` | The one brand accent — primary buttons, links, active states. Used sparingly; if more than one prominent element per screen uses it, that's a hierarchy problem, not a colour problem. |
| `--color-risk-low` | Risk band: Low. |
| `--color-risk-moderate` | Risk band: Moderate. |
| `--color-risk-high` | Risk band: High. |
| `--color-risk-very-high` | Risk band: Very High. |
| `--color-quality-strong` / `--color-quality-good` / `--color-quality-limited` / `--color-quality-insufficient` | Data-quality bands — visually distinct from risk-band colours (different hue family) so the two concepts are never confused on screen (`docs/00-governance/PRODUCT_GLOSSARY.md`'s "Confidence" entry). |

Rules:
- Risk-band and quality-band colours are **never** repurposed for anything else (no "success green" reused as a risk-Low colour to save a token — reuse implies false equivalence between "this is fine" and "this is Low risk," which is exactly the conflation `docs/09-compliance/PRODUCT_GUARDRAILS.md` warns against).
- All colour pairs (text-on-background) must meet `ACCESSIBILITY.md`'s contrast ratios in both light and dark mode.
- No gradients. Flat colour only.

## Typography

- `--font-sans`: `"Figtree", ui-sans-serif, system-ui, sans-serif` (already in place, `resources/css/app.css`) — the single typeface family for the entire product. No second/display typeface — see `DESIGN_LANGUAGE.md`'s Typography Philosophy.
- `--font-tabular`: `"Figtree", ui-monospace` fallback with `font-variant-numeric: tabular-nums` — applied specifically to odds, scores, and any number that updates in place, so digits don't shift width.

Type scale (`--text-{token}`, paired with a fixed `--leading-{token}` line-height):

| Token | Use |
|---|---|
| `--text-xs` | Fine print, disclaimers, timestamps. |
| `--text-sm` | Secondary/supporting text, form labels. |
| `--text-base` | Body copy — the default. |
| `--text-lg` | Emphasised body copy, card headings. |
| `--text-xl` | Section subheadings. |
| `--text-2xl` | Section headings (`h2`). |
| `--text-3xl` | Page headings (`h1`), risk-report headline. |
| `--text-4xl` | Homepage hero headline only. |

Weights used: regular (body), medium (emphasis, labels), semibold (headings). Bold is reserved for the risk band label itself — the one place maximum weight carries real meaning.

## Spacing Scale

A single scale, referenced everywhere (`DESIGN_LANGUAGE.md`'s White-Space Philosophy) — Tailwind's default rem-based scale, used as-is rather than inventing a parallel one:

`space-1` (0.25rem) · `space-2` (0.5rem) · `space-3` (0.75rem) · `space-4` (1rem) · `space-6` (1.5rem) · `space-8` (2rem) · `space-12` (3rem) · `space-16` (4rem) · `space-24` (6rem)

Rule of thumb: `space-1`–`space-3` for internal component spacing (padding, icon gaps), `space-4`–`space-8` for spacing between related elements, `space-12`+ for spacing between distinct sections.

## Radius Scale

| Token | Value | Use |
|---|---|---|
| `radius-sm` | 0.25rem | Small inline elements (inline code, tiny tags). |
| `radius-md` | 0.5rem | Buttons, inputs, nav active-state pills. |
| `radius-lg` | 0.75rem | Cards, panels, modals, risk indicators. |
| `radius-full` | 9999px | Badges, avatar, progress dots. |

No sharp (0) corners anywhere in the product — even `radius-sm` is a deliberate softness, consistent with the calm, non-institutional tone (`DESIGN_LANGUAGE.md`).

## Elevation

Elevation is built from shadow + a one-step background lightness change, never from borders alone (`VISUAL_INSPIRATION.md`: "minimal borders," "layered surfaces").

| Token | Use |
|---|---|
| `elevation-0` | Flat — page background, non-interactive flat sections (timeline, plain text blocks). |
| `elevation-1` | Default card/panel resting state. |
| `elevation-2` | Hovered interactive cards, the risk report's headline section, active nav bar. |
| `elevation-3` | Modals, drawers, dropdowns — anything floating above page content. |

Never skip a level (no `elevation-0` element jumping straight to `elevation-3` on hover) — elevation changes step by one, consistent with the calm motion system (`MOTION_SYSTEM.md`).

## Opacity

| Token | Value | Use |
|---|---|---|
| `opacity-disabled` | 0.4 | Disabled buttons/inputs. |
| `opacity-muted` | 0.6 | Secondary/supporting text over a coloured background, placeholder text. |
| `opacity-overlay` | 0.5 | Modal/drawer backdrop scrim. |

## Animation Timing

Mirrors `MOTION_SYSTEM.md` exactly — defined once here as tokens so no component hand-rolls a duration:

`duration-instant` (100ms) · `duration-fast` (150ms) · `duration-standard` (200ms) · `duration-deliberate` (300ms) · `duration-max` (400ms, hard ceiling)

`ease-entrance` (`ease-out`) · `ease-exit` (`ease-in`) · `ease-state` (`ease-in-out`)

All animation tokens must resolve to instant, non-transform swaps under `prefers-reduced-motion: reduce` (`MOTION_SYSTEM.md`'s Reduced Motion Accessibility) — implemented once via a shared Tailwind variant/utility, not per component.

## Container Widths

| Token | Value | Use |
|---|---|---|
| `container-narrow` | 640px | Risk report, journal, single-column reading content. |
| `container-standard` | 960px | Default in-app workspace content. |
| `container-wide` | 1200px | Homepage sections, marketing pages. |

The Risk Report intentionally uses the *narrower* container even on large screens — it's a document to read, not a dashboard to fill the screen (`COMPONENT_PRINCIPLES.md`'s Reports).

## Grid

12-column grid on desktop/tablet, single-column (implicit) on mobile. Gutter = `space-6` (desktop), `space-4` (mobile). Most in-app screens use 1 or 2 of the 12 columns' worth of nesting at most — SlipGuard rarely needs true multi-column grid layouts (`DESIGN_LANGUAGE.md`'s Layout Philosophy).

## Breakpoints

Matches Tailwind's defaults, used as the single source (`RESPONSIVE_RULES.md` for behaviour at each):

`sm` 640px · `md` 768px · `lg` 1024px · `xl` 1280px

## Icon Sizes

| Token | Value | Use |
|---|---|---|
| `icon-sm` | 16px | Inline with text, form field icons. |
| `icon-md` | 20px | Buttons, nav items, badges. |
| `icon-lg` | 24px | Standalone icons, risk indicator icon. |
| `icon-xl` | 32px | Empty states, feature illustration icons. |

See `ICONOGRAPHY.md` for style rules beyond sizing.

## Button Heights

| Token | Value | Use |
|---|---|---|
| `button-sm` | 32px | Inline/tertiary actions. |
| `button-md` | 40px | Default. |
| `button-lg` | 48px | Primary marketing-page CTAs, primary mobile actions (also satisfies the 44px minimum touch target — `ACCESSIBILITY.md`). |
