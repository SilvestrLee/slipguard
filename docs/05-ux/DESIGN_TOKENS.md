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

**Provenance note (added U-02, 2026-07-25; accepted as canonical by Product Office, 2026-07-25):** this document named every colour token from 2026-07-24 onward but specified no concrete values — the first screen that needed to render anything (the Customer Dashboard) surfaced the gap. The values below were proposed by Engineering, using well-established, pre-vetted shade/background pairings (a saturated-700-on-tinted-50 pattern for badges/indicators, not white-on-saturated-600, so contrast holds without per-pairing measurement) rather than arbitrary picks, approved by the founder before implementation proceeded, and formally accepted by Product Office on U-02 review as the canonical SlipGuard Design System values. They are not provisional — future UI work must reuse these tokens rather than introducing new colours, spacing values, typography scales, or animation timings; revise here (with Product Office sign-off), never in component code, if a specific pairing is later found lacking.

**Amended `U-08.1` (`PO-U08.1-AC-001` §12/§13):** the dark neutral scale below is now "smoked graphite" — a genuinely desaturated, warm-neutral scale, not the prior blue-tinted slate (deliberately avoiding "generic dark navy" per the memo's own wording); anchored on the memo's own directional values (`#1B1C20`/`#141518`) and extended into a full scale. `--color-accent` is now Rich Indigo in both themes, replacing the prior restrained blue — values corroborated via UI UX Pro Max's colour domain rather than picked freehand (an indigo-500/600 pairing recurs across its own design-system/SaaS references). Light theme's neutral scale is unchanged — §12 was specifically about the dark-theme foundation, and light theme was already a low-saturation, non-navy slate.

| Token | Light | Dark | Purpose |
|---|---|---|---|
| `--color-neutral-50` | `#f8fafc` | `#1b1c20` | Page background (light) / smoked-graphite surface (dark). |
| `--color-neutral-100` | `#f1f5f9` | `#232428` | Subtle surface tint, hover backgrounds. |
| `--color-neutral-200` | `#e2e8f0` | `#2e2f34` | Borders, dividers. |
| `--color-neutral-300` | `#cbd5e1` | `#3c3d42` | Stronger borders, disabled element outlines. |
| `--color-neutral-400` | `#94a3b8` | `#55565b` | Placeholder text, muted icons. |
| `--color-neutral-500` | `#64748b` | `#85868b` | Secondary text. |
| `--color-neutral-600` | `#475569` | `#b3b4b8` | Body text on light surfaces / secondary text on dark. |
| `--color-neutral-700` | `#334155` | `#d3d4d6` | Emphasised body text. |
| `--color-neutral-800` | `#1e293b` | `#e8e8ea` | Headings on light surfaces. |
| `--color-neutral-900` | `#0f172a` | `#f6f6f7` | Primary text (light) / primary surface text (dark). |
| `--color-neutral-950` | `#020617` | `#ffffff` | Reserved — highest-contrast text only. |
| `--color-accent` | `#6366f1` (text/icons/borders), `#4f46e5` (filled button background) | `#818cf8` | Primary buttons, links, active nav state, focus rings. One colour, used sparingly. |
| `--color-risk-low` | `#059669` (700-weight text: `#047857`) | `#34d399` | Risk band: Low. |
| `--color-risk-moderate` | `#d97706` (700-weight text: `#b45309`) | `#fbbf24` | Risk band: Moderate. |
| `--color-risk-high` | `#ea580c` (700-weight text: `#c2410c`) | `#fb923c` | Risk band: High. |
| `--color-risk-very-high` | `#b91c1c` | `#f87171` | Risk band: Very High. |
| `--color-quality-strong` | `#0f766e` | `#2dd4bf` | Data quality: Strong. Teal family — never overlaps a risk hue. |
| `--color-quality-good` | `#0284c7` | `#38bdf8` | Data quality: Good. Sky-blue family. |
| `--color-quality-limited` | `#6366f1` | `#a5b4fc` | Data quality: Limited. Indigo family. |
| `--color-quality-insufficient` | `#64748b` | `#94a3b8` | Data quality: Insufficient. Neutral grey — signals "not enough signal," not alarm. |
| `--color-labs-research` | `#7c3aed` (700-weight text: `#6d28d9`) | `#c4b5fd` | Labs feature status: Research. Violet family. |
| `--color-labs-planned` | `#0284c7` (700-weight text: `#0369a1`) | `#7dd3fc` | Labs feature status: Planned. Sky-blue family — reused hue family from Data Quality: Good is acceptable here since Labs status and data-quality bands never appear on the same screen or compete for meaning (unlike risk/quality bands, which do and must stay visually distinct from each other). |
| `--color-labs-designing` | `#c026d3` (700-weight text: `#a21caf`) | `#f0abfc` | Labs feature status: Designing. Fuchsia family. |
| `--color-labs-development` | `#4f46e5` (700-weight text: `#4338ca`) | `#a5b4fc` | Labs feature status: In Development. Indigo family. |
| `--color-labs-beta` | `#0d9488` (700-weight text: `#0f766e`) | `#5eead4` | Labs feature status: Beta. Teal family. |
| `--color-labs-released` | `#16a34a` (700-weight text: `#15803d`) | `#86efac` | Labs feature status: Released. Green family — the one hue on this whole list not otherwise used elsewhere in the product, deliberately: "released" is the one Labs status that should read as calmly positive, not merely categorical, distinct from every risk/quality colour by design. |

**Provenance note (added 2026-07-26, SD-002/Programme U-13):** the six Labs status tokens above follow the exact same saturated-700-on-tinted-50 pairing convention this document already established for risk/quality bands (§Applied pairing convention below) — no new colour methodology was invented, only new hue assignments for a structurally identical use case (`docs/05-ux/COMPONENT_PRINCIPLES.md`'s Badges component already covers "compact status labels... small tags" generically; Labs status is exactly that, not a new component). None of these six colours are risk or quality tokens, and none may be reused for risk/quality meaning later, per this document's own rule below.

**Applied pairing convention** (so every future component gets AA contrast without re-deriving it): a risk/quality band's saturated shade is used for text, icon, and border only, on that same token's `-50`-equivalent tint background (e.g. risk-Low text/icon/border on a pale emerald tint, never solid `risk-low` as a background with white text on top) — this is the pairing used throughout `COMPONENT_PRINCIPLES.md`'s Risk Indicators and Badges. The neutral scale supplies the actual page/card backgrounds in every case.

Rules:
- Risk-band and quality-band colours are **never** repurposed for anything else (no "success green" reused as a risk-Low colour to save a token — reuse implies false equivalence between "this is fine" and "this is Low risk," which is exactly the conflation `docs/09-compliance/PRODUCT_GUARDRAILS.md` warns against).
- All colour pairs (text-on-background) must meet `ACCESSIBILITY.md`'s contrast ratios in both light and dark mode, following the Applied pairing convention above.
- Risk, quality, and Labs-status band colours remain **flat, ungradiented, always** — see Gradients below for what changed and what didn't.

## Alert Tokens (added U-12.0, `PO-U12.0-001`)

Distinct from risk/quality/labs bands — used only for transient, non-risk system messages (`<x-alert>`, `COMPONENT_PRINCIPLES.md`'s Alerts). Replaces raw `bg-red-50`/`bg-green-50`-style Tailwind palette classes that had no dark-mode value.

| Token | Light | Dark | Purpose |
|---|---|---|---|
| `--color-alert-success` | `#16a34a` | `#4ade80` | Save/confirmation messages. |
| `--color-alert-error` | `#dc2626` | `#f87171` | Recoverable validation/error messages. |
| `--color-alert-caution` | `#ca8a04` | `#fbbf24` | Warnings that aren't errors. |
| `--color-alert-info` | `#2563eb` | `#60a5fa` | Neutral informational messages. |

## Confidence Tokens (added U-12.0, `PO-U12.0-001` — reserved, no current consumer)

`--confidence-high` (`#0f766e`), `--confidence-medium` (`#b45309`), `--confidence-low` (`#b91c1c`) — defined per `PO-U12.0-001` §12.2 ahead of any consumer, since Engineering hasn't yet built the OCR/PDF extraction confidence display these belong to (`PO-U11.5A-001` approved the capability; implementation awaits Architecture/Parser/Compliance Office review, `docs/00-governance/DECISION_LOG.md`). Not yet aliased into Tailwind's `@theme` (no utility class exists yet) — do so only once a real consumer exists, per Just-in-Time Documentation.

## Surfaces (added U-12.0, `PO-U12.0-001`)

A page and its cards previously shared the exact same background tone (`neutral-50`), differentiated only by a border — real, but thin, surface hierarchy. Three named tiers, all theme-aware:

| Token | Light | Dark | Purpose |
|---|---|---|---|
| `--color-surface-page` | `#f8fafc` (= `neutral-50`) | `#1b1c20` (= `neutral-50` dark) | The page background itself. Alias of `neutral-50` — no new value, just a semantic name. |
| `--color-surface-soft` | `#f1f5f9` (= `neutral-100`) | `#232428` (= `neutral-100` dark) | Recessed/secondary panels (empty-state wells, muted sections). Alias of `neutral-100`. |
| `--color-surface-card` | `#ffffff` | `#232428` | The standard card/panel surface — genuinely distinct from the page (not just bordered), giving cards a gentle lift. |
| `--color-surface-inverse` | `#1b1c20` (constant) | `#1b1c20` (constant) | A deliberately theme-independent dark band (e.g. the homepage Intelligence Section) — never flips, matching that section's pre-existing precedent. Updated `U-08.1` from navy to the same smoked-graphite tone as the dark-theme foundation, so this band no longer sits as a mismatched navy island. |

## Gradients (added U-12.0, `PO-U12.0-001` — supersedes this document's prior "No gradients. Flat colour only" rule)

**Constitutional amendment, recorded explicitly, not silent** (`docs/00-governance/DECISION_LOG.md`): the Product Office's U-12.0 commission explicitly approves a restrained, theme-aware gradient system as "a significant part of the SlipGuard visual language" (§13), while itself prohibiting exactly what `VISUAL_INSPIRATION.md`'s "Neon gradients" rejection already warned against (saturated, decorative, casino-style effects) — the two are compatible once read precisely, and `VISUAL_INSPIRATION.md` is amended alongside this document to state the distinction rather than silently contradict it.

| Token | Use |
|---|---|
| `--gradient-page` | A vertical tint across large page backgrounds (`neutral-50` → `neutral-200`-equivalent, smoked-graphite in dark theme since `U-08.1`). Applied to every layout shell (authenticated, guest, public, Labs) — and, in the public/Labs-guest layouts specifically, moved onto `.atmosphere` itself rather than the content wrapper (see Fixed Atmospheric Layer below). |
| `--gradient-hero` | A soft accent-tinted radial glow behind hero content only — never a visible "blob," a barely-there atmosphere. Indigo since `U-08.1` (was blue). |
| `--gradient-inverse` | A subtle lighter-indigo glow on the constant dark `surface-inverse` band. Indigo since `U-08.1` (was blue). |
| `--gradient-cta` | Added `U-16.2` (`PO-U16.2-CR-001` §8.4) — a bold, solid (not atmospheric) diagonal gradient, deliberately constant across both themes like `--surface-inverse`, reused behind every public CTA section. Indigo since `U-08.1`, specifically deep/saturated indigo-500→700 rather than dark theme's own lighter `--accent` (`#818cf8`), which would fail white-text contrast if used as a solid fill. |
| `--gradient-button` | Added `U-16.2` (founder direct instruction) — `linear-gradient(180deg, var(--accent), var(--accent-strong))`, referencing the accent variables directly so it re-themes automatically (indigo since `U-08.1`, with no separate update needed here). Applied to `<x-primary-button>` and every public ad hoc primary CTA button. |

**Amended `U-16.2` (`PO-U16.2-CR-001`):** `--gradient-page`'s second stop strengthened from a `neutral-100`-equivalent to a `neutral-200`-equivalent. Product Office rejected the prior value as visually ineffective — verified directly: it was mathematically correct but perceptually redundant once several homepage sections' fully opaque backgrounds also covered it, and even where visible, two adjacent shades this close were only reliably perceptible with developer tools. Still two fully neutral, non-colourful, adjacent shades from the same scale — not a colour, not a saturation increase — but now meant to read in an ordinary screenshot, per the corrective directive's own explicit acceptance standard (§9).

**Amended `U-08.1` (`PO-U08.1-AC-001` §13):** every gradient above that was previously a blue tint is now indigo, matching the accent colour change — no new gradient methodology, only new hue assignments.

**Rules, unchanged in spirit from the prior "flat colour only" rule's intent:**
- Gradients are a **surface/atmosphere treatment only** — never applied to a risk, quality, or Labs-status colour, and never to the Risk Indicator component, which remains flat colour + icon + label (`COMPONENT_PRINCIPLES.md`, unchanged). This still excludes `<x-card>` and every other discrete component — the `U-16.2` corrective's authorisation to adjust *section*-level (full-bleed page) surfaces does not extend to card-by-card gradients, which remain rejected exactly as before.
- No more than one gradient token per surface; no card-by-card gradient variation.
- No saturated multi-colour or animated gradients — every gradient here is a single-hue tint at low opacity, static.
- Gradients must hold correct contrast against any text placed on top, in both themes.

## Fixed Atmospheric Layer (added `U-08.1`, `PO-U08.1-AC-001` §14/§15)

A `.atmosphere` container (`resources/css/app.css`) holding 3-5 large, heavily blurred, `border-radius: 9999px` shapes, `background: var(--accent-strong)`, `position: fixed`, `pointer-events: none`. Light theme: `opacity: 0.16`; dark theme: `opacity: 0.28`, with a wider blur radius (`110px` vs `90px`) — raised from an initial `0.05`/`0.12` after founder visual feedback that the first pass read as too dim to register as a real design element, not merely "restrained." Carries `--gradient-page` as its own background — the content wrapper it sits behind must **not** also carry an opaque background, or that wrapper's own paint sits between the fixed shapes and every section, hiding the atmosphere entirely regardless of any section's translucency (a real bug found and fixed while first wiring this up — see `docs/00-governance/DECISION_LOG.md`). Applied across the entire product, per a sequence of founder direct instructions on 2026-07-28 that progressively widened this token's scope from its original `PO-U08.1-AC-001` public-only design: public layout and the Labs guest branch (4-shape variant); the guest (single-card) authentication layout, login/register/forgot-password (3-shape variant); and — after an initial narrower "just the Dashboard" instruction was itself explicitly widened to "all the pages in the portal/dashboard" — every authenticated portal screen rendered through `layouts/app.blade.php` (Dashboard, History, Journal, Planner Workspace, Risk Reports, Settings, Profile) and the authenticated branch of `layouts/labs.blade.php` (also a 3-shape variant, unconditional, matching its own guest branch). The Filament-based internal Operations panel is a structurally separate rendering stack and was never in scope.

See `MOTION_SYSTEM.md`'s own Fixed Atmospheric Layer section for the motion-language framing (why it's static, why it's fixed, theme parity reasoning) — this entry is the token/implementation record.

## Premium Light Sweep (added `U-08.1`, `PO-U08.1-AC-001` §11)

A `.light-sweep` class (`resources/css/app.css`) — a `background-position`-animated diagonal gradient overlay via `::after`, 16s cycle, ~87% dwell before one slow pass. Applied via explicit opt-in only, currently to two named high-value surfaces: the homepage hero's sample-report preview, and every public CTA section's `bg-gradient-cta` surface. See `MOTION_SYSTEM.md`'s own Premium Light Sweep section for the full motion-language rationale and the forbidden-character list (no sparkle/glare/lens-flare/metallic-shine).
- **Section surfaces may now be translucent, not only fully opaque** (`U-16.2`), specifically so a page-level gradient can show through them — the translucency itself must still resolve to a token-derived colour (e.g. `surface-page/80`), never an arbitrary opacity value invented ad hoc.

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

### Type Roles (added U-12.0, `PO-U12.0-001`)

The type scale above always existed; this maps each *role* a screen actually needs to the token that already covers it — so no screen invents a one-off size. No new sizes were added.

| Role | Token | Notes |
|---|---|---|
| Marketing display | `text-4xl` `font-semibold` | Homepage hero headline only, per the existing scale's own note. |
| Page title | `text-3xl` `font-semibold` | One per authenticated screen. |
| Section heading | `text-2xl` `font-semibold` | |
| Card heading | `text-lg` `font-semibold` | |
| Product body | `text-base` | Default in-app copy. |
| Supporting body | `text-sm` `text-neutral-600` | Secondary/explanatory line beneath a heading. |
| Navigation | `text-sm` `font-medium` | |
| Button | `text-sm` `font-semibold` | |
| Input | `text-sm` | |
| Form label | `text-sm` `font-medium` | |
| Caption | `text-xs` | Timestamps, disclaimers. |
| Table header | `text-xs` `font-semibold` `uppercase` `tracking-wide` `text-neutral-500` | |
| Table body | `text-sm` | |
| Numeric metric | `text-2xl`/`text-3xl` `font-semibold` `font-tabular` | Dashboard/Intelligence Section stat values. |
| Odds value | `text-sm` `font-semibold` `font-tabular` | |
| Risk label | `text-sm` `font-bold` | The one place bold is used, per the rule above. |
| Status badge | `text-xs` `font-medium` | |

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

**Amended by founder direct instruction (2026-07-27): no shadows anywhere on the site.** Elevation is now built entirely from a contrasting border line — increasing border weight/contrast for each level — plus the existing background lightness change (`--surface-page`/`--surface-soft`/`--surface-card`), never from shadow. This supersedes the prior "shadow + lightness change, never borders alone" rule below it, which is retained struck through in spirit only for the historical record of *why* `shadow-elevation-*` classes still appear throughout the codebase (see immediately below).

| Token | Use |
|---|---|
| `elevation-0` | Flat — page background, non-interactive flat sections (timeline, plain text blocks). |
| `elevation-1` | Default card/panel resting state. Border: `border-neutral-200/60`. |
| `elevation-2` | Hovered interactive cards, the risk report's headline section, active nav bar. Border: `border-neutral-300`. |
| `elevation-3` | Modals, drawers, dropdowns — anything floating above page content. Border: `border-2 border-neutral-300`/`border-l-2` (thicker, the highest-contrast step). |

Never skip a level (no `elevation-0` element jumping straight to `elevation-3` on hover) — elevation changes step by one, consistent with the calm motion system (`MOTION_SYSTEM.md`).

**`shadow-elevation-{1,2,3}` utility classes remain in markup site-wide, deliberately, rather than being mechanically stripped from every one of the ~40 call sites that carry them** — `--shadow-1/2/3` now resolve to `none` in all themes, so the classes are inert (cost nothing, render nothing) rather than removed. Any component that previously relied on shadow *alone* for contrast (no accompanying border) had an explicit border added directly at that call site — Card's `elevated`/`interactive` variants, Modal, Dropdown, the theme-toggle's sliding thumb, the mobile nav drawer, and the homepage hero's report preview were the specific places this applied; everywhere else already had a border alongside its (now-inert) shadow class from earlier passes.

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

## Container Widths (revised `PO-U12.0-CP-002`, 2026-07-27)

Superseded the prior three-tier `container-narrow`/`container-standard`/`container-wide` naming (640/960/1200px) — those were documented as intent back in U-02 but never implemented as real, named utilities; every screen picked its own approximate `max-w-Nxl` independently, and container widths drifted into an undocumented mix of `max-w-2xl` through `max-w-7xl` across the product (found during `U-12.0`'s own completion-pass audit). Implemented for the first time as literal Tailwind `@utility` classes (`resources/css/app.css`), matching this section's token names exactly:

| Token | Value | Purpose | Screens |
|---|---|---|---|
| `container-reading` | 720px (45rem) | Long-form reading, single-column documents. | Risk Report, Journal Entry. |
| `container-standard` | 960px (60rem) | General authenticated workspace. | Dashboard, History, Journal, Profile, Slip List, intake shell. |
| `container-analytics` | 1200px (75rem) | Decision-intelligence workspaces — intentionally wider than Standard while remaining comfortably readable. | Builder, Planner Workspace, Planning History. |
| `container-marketing` | 1280px (80rem) | Public website. | Homepage — navigation, hero, feature sections, Intelligence Section, trust sections, footer. |

Each token controls `max-width` only — `mx-auto` and responsive horizontal padding (`px-4 sm:px-6 lg:px-8` in-app, `px-6` on the public site) remain separate utility classes at each usage site, unchanged.

**Marketing width selection, stated plainly:** no OddStorm reference artifact (file, screenshot, or URL) has ever been supplied anywhere in this repository or conversation — every description of OddStorm across this project's history has been prose characterization (typography hierarchy, restrained gradients, spacing discipline, SaaS-maturity tone), not an inspectable design file or live site this process could actually measure. **1280px was not adopted by assumption** — it was chosen because: (1) `VISUAL_INSPIRATION.md`'s own, more authoritative reference set (Stripe, Linear) favours comfortably-wide-but-restrained marketing containers over an edge-pushing 1440–1536px; (2) UI UX Pro Max's own guideline database, consulted directly for this pass, confirms a "minimal single-column, lots of whitespace" pattern as the fit for SlipGuard's positioning, not a maximal-width layout; (3) it reuses an already-familiar value (Tailwind's own `max-w-7xl`), avoiding a fifth arbitrary width alongside the three workspace tiers.

**Risk Report deviation, flagged explicitly:** `PO-U12.0-CP-002`'s own migration table places the Risk Report under `container-analytics` (1200px) alongside Builder and Planner. This was **not followed as written** — the Risk Report instead uses `container-reading` (720px), preserving this document's own pre-existing, deliberate principle (below) that the commission's migration table doesn't address or override for this specific screen. Recorded as a flagged deviation in `docs/00-governance/DECISION_LOG.md`, not a silent substitution.

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
