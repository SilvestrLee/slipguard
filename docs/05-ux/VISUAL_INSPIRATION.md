# Visual Inspiration

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Language](DESIGN_LANGUAGE.md), [Design Tokens](DESIGN_TOKENS.md), [Component Principles](COMPONENT_PRINCIPLES.md) |

---

What SlipGuard intentionally borrows from, and what it deliberately rejects — with the reasoning, not just the list. Reference brands are named for their **visual characteristics only**. Nothing here means "copy this layout" or "use this component" — see `COMPONENT_PRINCIPLES.md` and `DESIGN_TOKENS.md` for what SlipGuard actually builds.

## Reference Points

- **Stripe** — restrained colour used purposefully (one accent, everything else neutral), generous marketing-page spacing, typography-led hierarchy.
- **Linear** — calm, fast-feeling micro-interactions; dark-mode-first polish; minimal chrome around content.
- **Notion** — progressive disclosure done well (collapsed by default, expand on demand); plain, unpretentious typography.
- **Apple (product pages)** — one idea per section, scroll-driven storytelling, huge amounts of white space, no visual competition between sections.
- **OddStorm** — proof that a betting-adjacent product *can* look premium and analytical rather than casino-like; referenced for tone, not for any specific screen.

None of these are betting products except OddStorm, and OddStorm is referenced for what it avoids as much as what it does. SlipGuard's actual competitive contrast is the sportsbook and tipster-app aesthetic — see Explicitly Rejected below.

## Approved

| Characteristic | Why |
|---|---|
| Generous spacing | Signals calm and confidence; cramped layouts read as urgent or cheap (`DESIGN_LANGUAGE.md`'s White-Space Philosophy). |
| Premium typography | Typography is SlipGuard's primary "this is trustworthy" signal, since colour and imagery are deliberately restrained. |
| Subtle motion | Confirms an action happened without demanding attention — the opposite of a slot-machine's attention-grabbing animation (`MOTION_SYSTEM.md`). |
| Restrained colour palette | One functional accent colour, a small set of neutrals, and reserved risk-band colours only where they carry meaning (`DESIGN_TOKENS.md`). Colour that doesn't mean something is noise. |
| Strong hierarchy | Lets a stressed or uncertain user find "what should I do" in under a second, without reading everything. |
| Minimal borders | Structure comes from spacing and elevation, not boxes-within-boxes — heavy borders make a screen feel like a form to fill out, not a report to read. |
| Layered surfaces | A subtle elevation system (see `DESIGN_TOKENS.md`) shows what's foreground vs. background without needing borders or colour blocks. |
| Scroll storytelling | The homepage and report screens read top-to-bottom as a narrative, not a dashboard to scan in every direction (`HOMEPAGE_STORYBOARD.md`). |
| Calm interactions | Hover and press states confirm interactivity without startling or distracting — nothing bounces, pulses, or shakes for attention. |

## Explicitly Rejected

| Characteristic | Why |
|---|---|
| Sportsbook appearance | SlipGuard analyses risk; it does not sell bets. Looking like a sportsbook implies the same incentives as one, which directly contradicts `docs/00-governance/VISION_AND_PRINCIPLES.md`'s "SlipGuard Is Not: a bookmaker." |
| Casino colours (saturated reds/golds/greens used decoratively) | Casino palettes exist to excite and encourage more play. SlipGuard's job is the opposite: to slow the user down enough to make a clear decision. |
| Gambling imagery (chips, cards, dice, slot reels) | Reinforces the "this is a game of chance to enjoy" framing SlipGuard exists to push back against. See `IMAGE_GUIDELINES.md`. |
| Flashing indicators | Flashing is an attention-hijacking pattern borrowed from slot machines; it has no place signalling risk information a user needs to read calmly. |
| Dense dashboards | Density optimises for power-users scanning many numbers at once. Tunde needs one clear answer, not a trading terminal (`DESIGN_LANGUAGE.md`'s Cognitive Load Reduction). |
| Neon gradients | Reads as "app," specifically as a casino or crypto app — the opposite of the calm, analytical tone SlipGuard needs. |
| Unnecessary charts | A chart implies a number is worth exploring visually. Most risk-engine outputs are better served by one sentence and a band label than a graph (`DESIGN_LANGUAGE.md`'s Data Explanation Philosophy). |
| Noisy hero sections | A homepage hero crowded with motion, badges, and competing claims undermines the very calmness the product is trying to sell. |
