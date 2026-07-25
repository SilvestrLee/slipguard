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

## Logo Rotation

The logo does not rotate, spin, or animate as a decorative flourish. The only permitted logo motion is a subtle opacity fade on page load (200ms) consistent with everything else on the page — the logo is a mark of trust, not a mascot.

## Micro-Interactions

- Button press: scale to 0.98, 100ms, on `:active`.
- Checkbox/toggle: 150ms colour + position transition.
- Form field focus: instant (100ms) border/ring colour change — see `ACCESSIBILITY.md` for the required focus-ring contrast.
- Copy-to-clipboard / save confirmation: a brief (1.5s) inline checkmark or label change, not a toast stack.
- Risk band appearing after analysis completes: fade + 8px rise, 300ms, `ease-out` — the one moment in the product allowed a slightly more deliberate reveal, because it's the product's core moment of truth and deserves a breath of weight, not urgency.

## Reveal Animations

Reserved for: homepage section entrances (once, on first scroll into view) and the risk report's headline/band appearing after analysis. Reveal animations are **never** used for routine UI (list items, table rows, repeated cards) — staggering the entrance of every row in a list looks impressive once and becomes friction on every subsequent visit.

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
