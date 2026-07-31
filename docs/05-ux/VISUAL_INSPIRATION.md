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
| Restrained colour palette | One functional accent colour (Rich Indigo since `U-08.1`, `PO-U08.1-AC-001` §13 — replacing the prior blue; still exactly one, still deliberately narrow), a small set of neutrals (smoked graphite in dark theme since `U-08.1` §12, replacing the prior blue-tinted navy), and reserved risk-band colours only where they carry meaning (`DESIGN_TOKENS.md`). Colour that doesn't mean something is noise. |
| Strong hierarchy | Lets a stressed or uncertain user find "what should I do" in under a second, without reading everything. |
| Minimal borders | **Amended, founder direct instruction (2026-07-27):** this row's original reasoning is superseded, not merely narrowed — shadows were removed sitewide, and borders are now elevation's primary carrier (`DESIGN_TOKENS.md`'s Elevation section), not a minimised afterthought. The underlying goal (avoid a screen reading as "boxes-within-boxes," a form to fill out rather than a report to read) still governs *how much* border weight/contrast escalates per level — one contrast step per elevation level, never an arbitrary decorative border — but "minimal" no longer means "prefer shadow over border." |
| Layered surfaces | A subtle elevation system (see `DESIGN_TOKENS.md`) shows what's foreground vs. background — now via border weight/contrast rather than shadow, per the amendment above. |
| Scroll storytelling | The homepage and report screens read top-to-bottom as a narrative, not a dashboard to scan in every direction (`HOMEPAGE_STORYBOARD.md`). |
| Calm interactions | Hover and press states confirm interactivity without startling or distracting — nothing bounces, pulses, or shakes for attention. |
| Restrained glassmorphism (added, founder direct instruction, 2026-07-27) | The public header: a translucent, blurred surface (`backdrop-blur-md` + a partial-opacity surface colour, a hairline border for edge definition) — restrained specifically means no colour tint, no chromatic aberration, no iridescence, distinct from the "Liquid Glass" style this project's own specialist-tool consultations have repeatedly rejected (moderate-poor performance, contrast-risky, wrong typeface/accent). One surface, not a system — not extended to cards or other chrome without a separate, explicit decision. |
| Fixed atmospheric layer (added `U-08.1`, `PO-U08.1-AC-001` §14/§15; scope progressively widened by founder direct instruction, 2026-07-28, to the entire product) | Three to five large, heavily blurred indigo forms, fixed behind scrolling content everywhere: public pages, the Labs guest branch, the guest authentication screens, and every authenticated portal screen (Dashboard, History, Journal, Planner Workspace, Risk Reports, Settings, Profile, and Labs' own authenticated branch) — low-to-moderate opacity (raised once from an initial pass the founder found too dim — see `DESIGN_TOKENS.md`). Reads as ambient light, never as an identifiable shape; `pointer-events: none`. The internal Filament Operations panel is a separate rendering stack, not a scoping exclusion. |

## Explicitly Rejected

| Characteristic | Why |
|---|---|
| Sportsbook appearance | SlipGuard analyses risk; it does not sell bets. Looking like a sportsbook implies the same incentives as one, which directly contradicts `docs/00-governance/VISION_AND_PRINCIPLES.md`'s "SlipGuard Is Not: a bookmaker." |
| Casino colours (saturated reds/golds/greens used decoratively) | Casino palettes exist to excite and encourage more play. SlipGuard's job is the opposite: to slow the user down enough to make a clear decision. |
| Gambling imagery (chips, cards, dice, slot reels) | Reinforces the "this is a game of chance to enjoy" framing SlipGuard exists to push back against. See `IMAGE_GUIDELINES.md`. |
| Flashing indicators | Flashing is an attention-hijacking pattern borrowed from slot machines; it has no place signalling risk information a user needs to read calmly. |
| Dense dashboards | Density optimises for power-users scanning many numbers at once. Tunde needs one clear answer, not a trading terminal (`DESIGN_LANGUAGE.md`'s Cognitive Load Reduction). |
| Neon/saturated gradients | Reads as "app," specifically as a casino or crypto app — the opposite of the calm, analytical tone SlipGuard needs. **Narrowed U-12.0 (`PO-U12.0-001`):** this rejects saturated, multi-colour, decorative gradients specifically — it does not reject gradients as a category. A restrained, single-hue, low-opacity atmospheric tint (`DESIGN_TOKENS.md`'s Gradients section) is now approved precisely because it stays within the same calm, low-noise register this table already asks for everywhere else; it is never applied to risk/quality/status colours or used decoratively per-card. |
| Unnecessary charts | A chart implies a number is worth exploring visually. Most risk-engine outputs are better served by one sentence and a band label than a graph (`DESIGN_LANGUAGE.md`'s Data Explanation Philosophy). |
| Noisy hero sections | A homepage hero crowded with motion, badges, and competing claims undermines the very calmness the product is trying to sell. |
