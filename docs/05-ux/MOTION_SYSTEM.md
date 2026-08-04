# Motion System

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Language](DESIGN_LANGUAGE.md), [Design Tokens](DESIGN_TOKENS.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Accessibility](ACCESSIBILITY.md) |

---

Nothing moves without a reason. Every animation in SlipGuard exists to confirm, guide, or clarify — never to delight, brand, or hold attention for its own sake (`DESIGN_LANGUAGE.md`). If a motion can't be justified by one of the purposes below, remove it.

## Animation Duration

| Speed | Duration | Use |
|---|---|---|
| Instant | 100ms | Hover state changes, focus rings, colour transitions. |
| Fast | 150ms | Button press feedback, toggles, small state changes. |
| Standard | 200ms | Default for most transitions — panel open/close, tooltip appearance, tab switches. |
| Deliberate | 300ms | Modal/drawer entrance, page-section reveals. |
| Maximum | 400ms | Hard ceiling. Nothing in the product animates longer than this. A slower animation makes the product feel heavy, not premium. |

Loading states that exceed ~2 seconds are not "animations" — they're a loading pattern (see Loading Behaviour below), not a motion-duration problem.

## Animation Easing

- **Entrances** (something appearing): `ease-out` — starts fast, settles gently. Feels responsive.
- **Exits** (something disappearing): `ease-in` — starts slow, accelerates away. Feels like it's getting out of the way.
- **State changes in place** (hover, colour, toggle): `ease-in-out`.
- Never use `linear` for anything user-facing — it reads as mechanical, closer to a progress bar than an interface responding to a person.
- Never use a bounce, spring-overshoot, or elastic easing anywhere. SlipGuard doesn't play.

## Allowed Transitions

- Opacity fades (menus, tooltips, modals appearing/disappearing).
- Height/max-height expand-collapse (progressive disclosure — accordion-style reveal of per-leg detail, methodology).
- Colour transitions (hover states, focus states, risk-band colour appearing once data loads).
- Position/translate for panel and drawer entrances (slide in from the edge they're anchored to, nothing else).
- Subtle scale (0.98 → 1.0) on press feedback for buttons and cards, to confirm a tap registered.

Anything not on this list (rotation for decoration, parallax, particle effects, confetti, bouncing, shaking, pulsing to attract attention) is forbidden — see Forbidden Animation Patterns.

## Hover Behaviour

Hover states change opacity, background colour, or elevation only — never size, position, or shape (a card that grows on hover shifts surrounding layout and feels unstable). Duration: Instant (100ms). Every interactive element must have a visible hover state; interactive elements that look identical to static ones violate `ACCESSIBILITY.md`'s "colour independence" principle just as much as a purely colour-based signal does.

## Loading Behaviour

- **Under ~300ms**: no loading indicator at all — showing a spinner for a near-instant action is more distracting than the wait itself.
- **300ms–2s**: a subtle inline spinner or skeleton state on the specific element that's loading (a button, a card) — never a full-page overlay for a partial update (Livewire's `wire:loading` states, scoped to the affected element).
- **Over 2s** (e.g. slip analysis in progress): a calm, explicit state — "Analysing your slip…" with a static or gently-pulsing (opacity only, never scale) indicator. Never a fake progress bar that doesn't reflect real progress — that's a trust violation, not just a UI nicety.
- Skeleton screens (not spinners) are preferred wherever the eventual layout is already known, since they reduce perceived layout shift.

## Scroll Behaviour

No scroll-jacking, no parallax, no pinned/sticky sections that hijack the scroll wheel. The homepage's section-by-section storytelling (`HOMEPAGE_STORYBOARD.md`) is achieved through layout and content pacing, not through intercepting scroll. Content may **fade/slide in gently** (opacity 0→1, translate-y 8px→0, 300ms, `ease-out`) as it enters the viewport — once, never re-triggering on scroll-back, and only for section-level reveals, never per-paragraph or per-word.

## Static Product Mark (supersedes the former Signature Motion treatment, founder direct instruction, 2026-07-31)

The approved SlipGuard icon and wordmark are static across the public marketing site and authenticated portal. They do not spin, rotate, bounce, loop, or respond to scroll position.

The public header may retain its restrained page-load fade: opacity only, 250–350ms, `ease-out`. No rotation, bounce, zoom, blur, or slide.

**Icon/wordmark architecture:** the brand mark remains an icon and genuine-text “SlipGuard” wordmark sharing one accessible link. Neither element receives a transform binding.

**Sticky/condensed headers:** scroll observation remains permitted for header presentation only. The public header may condense and the authenticated header may add `shadow-elevation-1` after the established threshold. Neither behaviour transforms the product mark.

No logo-specific scroll state or transform binding may be introduced.

## Fixed Atmospheric Layer (added `U-08.1`, `PO-U08.1-AC-001` §14/§15; scope widened twice by founder direct instruction, 2026-07-28 — first to the guest auth screens, then to the Dashboard specifically, then corrected/widened again to the entire authenticated portal)

Three to five large, heavily blurred indigo forms sit fixed behind scrolling content across the **entire product**: the public marketing site, the Labs guest branch, the guest authentication screens, and every authenticated portal screen. The only exclusion is the Filament-based internal Operations panel, which is a structurally separate rendering stack. They read as ambient environmental light rather than identifiable graphic shapes.

PW-03 establishes one shared, scroll-linked depth mechanism. Public pages read document scroll; authenticated routes read the established workspace scroll region. Each form has a restrained independent depth, displacement is capped, and transforms are batched through `requestAnimationFrame`. Motion occurs only during real scrolling and stops on the final scroll frame—there are no timers, pointer tracking, easing, inertia, or idle drift. `prefers-reduced-motion: reduce` removes every atmosphere transform. `pointer-events: none` remains mandatory throughout.

**Provenance note**: this section originally read "never on authenticated screens... prioritise clarity over brand expression," reflecting `PO-U08.1-AC-001`'s own original scope. The founder directly and explicitly widened that scope twice in the same session (first to one named Dashboard exception, then to "all the pages in the portal/dashboard") — recorded here as a correction to the prior text, not a silent contradiction of it.

**Theme parity:** the same architecture and the same four shapes exist in both themes; only intensity changes — dark theme uses a richer opacity and a slightly wider blur than light theme, per `PO-U08.1-AC-001` §16's own "light theme must not feel like a completely separate product" requirement. Neither theme is the "real" premium version; both are the same signature, tuned for their own contrast needs.

## Premium Light Sweep (added `U-08.1`, `PO-U08.1-AC-001` §11)

A rare, slow, diagonal, low-contrast shimmer, reserved for a small, explicitly named set of high-value surfaces — currently the homepage's hero product preview and the accent-gradient CTA surface (`COMPONENT_PRINCIPLES.md`'s own Cards/Hero rules are otherwise unchanged; this is an explicit opt-in, never a default). It must never become something every card gets, per `PO-U08.1-AC-001` §11's own explicit warning.

**Mechanics:** a `background-position` sweep on a translucent diagonal gradient overlay, not an opacity pulse — reads as more mechanical/premium than a glow (corroborated by UI UX Pro Max's `gsap` domain, though implemented here in plain CSS; no animation dependency is introduced for this alone). A long dwell (the overlay sits at rest for the large majority of each cycle) precedes one slow pass, never a continuous back-and-forth — this is what makes it read as "rare," not as a loop running the whole time a visitor is on the page. The existing Reduced Motion Accessibility rule below already collapses this, along with every other animation in the product, to a single near-instant pass under `prefers-reduced-motion: reduce` — no separate handling needed per component.

**Forbidden character** (`PO-U08.1-AC-001` §11's own list, restated here since it's the most literal thing this effect could slide into if implemented carelessly): no sparkle, glare, lens flare, glossy "casino" shine, or metallic effect — the sweep must stay faint enough that a visitor never consciously registers "there's an animation here," only a faint sense of depth if they happen to be looking at the right moment.

## Micro-Interactions

- Button press: scale to 0.98, 100ms, on `:active`.
- Checkbox/toggle: 150ms colour + position transition.
- Form field focus: instant (100ms) border/ring colour change — see `ACCESSIBILITY.md` for the required focus-ring contrast.
- Copy-to-clipboard / save confirmation: a brief (1.5s) inline checkmark or label change, not a toast stack.
- Risk band appearing after analysis completes: fade + 8px rise, 300ms, `ease-out` — the one moment in the product allowed a slightly more deliberate reveal, because it's the product's core moment of truth and deserves a breath of weight, not urgency.

## Reveal Animations

Reserved for: page section entrances (once, on first scroll into view) across the public site, and the risk report's headline/band appearing after analysis. Originally scoped to the homepage only (`U-15.2`); widened sitewide across public pages by founder direct instruction (2026-07-28) — implementation moved from a per-page inline `<script>` (home.blade.php only) to a single shared handler in `resources/js/app.js` (`initScrollReveal`, bound to Livewire's `livewire:navigated` event so one listener covers the initial load and every subsequent `wire:navigate` transition), so adding the reveal to a new page is a `data-reveal` attribute, not a copy-pasted script. Every public page's non-hero, non-CTA sections carry `data-reveal` (hero sections are already visible on load and need no reveal; CTA sections keep their own distinct `light-sweep` treatment instead, matching home's original precedent). Reveal animations are **never** used for routine UI (list items, table rows, repeated cards) — staggering the entrance of every row in a list looks impressive once and becomes friction on every subsequent visit.

## Reduced Motion Accessibility

Every animation in this system must respect `prefers-reduced-motion: reduce`. When set:
- All entrance/exit animations become instant opacity swaps (no translate, no scale).
- Loading states drop to a static label instead of any pulsing/spinning indicator.
- Scroll-triggered reveals show content in its final state immediately, no fade.

This is not optional per-component — implement it once at the token/utility level (see `DESIGN_TOKENS.md`'s animation timing tokens) so every component inherits it automatically.

## Forbidden Animation Patterns

- Confetti, particle effects, or celebratory bursts of any kind — SlipGuard never celebrates a bet (`docs/09-compliance/PRODUCT_GUARDRAILS.md`).
- Flashing, strobing, or rapid pulsing to draw attention (slot-machine pattern).
- Bounce/spring/elastic easing anywhere.
- Countdown timers or urgency-driven animation (ticking numbers, shrinking bars implying scarcity).
- Auto-playing looped animations (spinning icons, breathing backgrounds) outside an active loading state.
- Parallax and scroll-jacking.
- Animating more than one unrelated element simultaneously to "liven up" a page — every animation must be tied to a specific state change, not decoration.
