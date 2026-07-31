# SlipGuard Design Language System (SGDS)

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-27 |
| Related Documents | [Design Tokens](DESIGN_TOKENS.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Visual Inspiration](VISUAL_INSPIRATION.md), [Motion System](MOTION_SYSTEM.md), [Accessibility](ACCESSIBILITY.md) |

---

Programme U-12.0 (`PO-U12.0-001`). This is the one implementation-facing entry point for "how does SlipGuard build a screen" — it names the principle and the primitive, then points to the document that already owns the detail, rather than restating it. It does not replace `DESIGN_TOKENS.md` or `COMPONENT_PRINCIPLES.md`; it sits above them.

## 1. Core Principle

**Clarity before decoration. Intelligence before excitement. Trust before persuasion.** (`PO-U12.0-001` §4.) Every visual decision should help the customer understand where they are, what SlipGuard knows and doesn't know, and what to do next — never to make the product feel more exciting than the decision it's actually helping with.

## 2. Reference, Not Template

OddStorm (`VISUAL_INSPIRATION.md`) is referenced for structural maturity, typographic confidence, and restrained depth — never copied for layout, density, colour, or business model. Where U-12.0 draws directly on an OddStorm quality (gradients, surface hierarchy), the specific SlipGuard implementation is documented here and in `DESIGN_TOKENS.md`; nothing is taken from that reference sight-unseen.

## 3. Typography

Figtree, unchanged — already modern, legible, and slightly rounded; refined rather than replaced (`PO-U12.0-001`'s own Font Decision Rule: don't replace a working typeface unnecessarily). Role → token mapping: `DESIGN_TOKENS.md`'s new **Type Roles** table. No new font, no new sizes.

## 4. Colour & Surfaces

One accent colour, a neutral scale, reserved risk/quality/labs bands — unchanged (`DESIGN_TOKENS.md`). New this programme: a genuine **surface hierarchy** (`surface-page` / `surface-soft` / `surface-card` / `surface-inverse`) so a card is now a visibly distinct tier from the page behind it, not just a bordered box in the same tone. New **alert tokens**, distinct from risk/quality, for transient system messages. Reserved **confidence tokens**, unused until the OCR/PDF extraction feature (`PO-U11.5A-001`) is actually implemented.

## 5. Gradients

**Constitutional amendment** (`DESIGN_TOKENS.md`'s Gradients section, `docs/00-governance/DECISION_LOG.md`): the prior "no gradients" rule is superseded by a restrained, theme-aware exception — one low-opacity, single-hue tint per surface (`gradient-page`, `gradient-hero`, `gradient-inverse`), never per-card, never saturated, never animated, never applied to a risk/quality/status colour. Applied to: the authenticated app shell and guest layout backgrounds, the homepage hero, and the Intelligence Section's dark inverse band.

## 6. Elevation

`elevation-1/2/3` (`DESIGN_TOKENS.md`) now resolve to real, theme-aware shadow values for the first time — previously a documented concept with no implementation, so screens fell back to Tailwind's raw `shadow-sm`/`shadow-md`, which reads as a generic dark drop-shadow that visually disappears on a dark surface. Dark mode uses a subtle translucent-white hairline instead of a bigger black shadow, per `PO-U12.0-001` §16's own requirement.

## 7. Component Grammar

Seven primitives added, each consolidating a pattern that was previously hand-written per screen (`COMPONENT_PRINCIPLES.md` has the full principles for each):

| Component | Replaces |
|---|---|
| `<x-card>` | Ad hoc `bg-neutral-50 border rounded-lg p-6` repeated on nearly every screen. |
| `<x-badge>` | Ad hoc lifecycle-status pill markup (Slip Index, Planning History). |
| `<x-alert>` | Ad hoc `bg-red-50`/`bg-green-50` flash-message blocks with no dark-mode value. |
| `<x-empty-state>` | Four near-identical "nothing here yet" blocks (History/Journal/Planning History/Slip Index/Dashboard). |
| `<x-page-header>` | The repeated title + description + one action pattern at the top of every list screen. |
| `<x-metric-card>` | The homepage Intelligence Section's metric grid markup. |
| `<x-file-drop>` | The intake shell's two near-identical Screenshot/PDF dropzone blocks. |

Only components with a real, current consumer were built — no speculative abstraction (`PO-U12.0-001` §30.10). A `<x-confidence-badge>` was deliberately **not** built, since no OCR confidence data exists yet to display.

## 8. Risk Communication (unchanged, restated for completeness)

Risk, confidence, evidence-completeness, and outcome are never visually collapsed into one colour — `COMPONENT_PRINCIPLES.md`'s Risk Indicators remain flat colour + icon + label, the one component gradients and the new surface system never touch. A Risk Indicator is not a Badge, an Alert, or a Card variant; it stays its own thing.

## 9. Where SGDS Applies

Fully applied: shared layouts (`layouts/app.blade.php`, `layouts/guest.blade.php`, navigation), homepage, Dashboard, Betting Slip Index, Builder, intake shell, History, Journal, Journal Entry, Planning History, profile forms, **and, as of the `PO-U12.0-CP-002` completion pass, the Risk Report and Planner Workspace** (`report.blade.php`, `planner/session.blade.php`) — every ad hoc `bg-neutral-50 border` surface panel migrated to `surface-card`/`shadow-elevation-1`, with each screen's headline risk-band section specifically upgraded to `shadow-elevation-2` per `COMPONENT_PRINCIPLES.md`'s Reports rule ("the headline/band section sits at the highest elevation on the page"); two remaining hardcoded `gray-*` remnants found and fixed in the Planner Workspace's abandon-session modal. Neither screen's mathematics, workflow, or component structure was altered — a pure surface/token pass, per the commission's own §10/§11 instruction.

## 10. Semantic Container Hierarchy (added `PO-U12.0-CP-002`)

Four named widths (`DESIGN_TOKENS.md`'s Container Widths section) replace the ad hoc `max-w-2xl` through `max-w-7xl` mix the original completion report itself flagged as an audit gap: `container-reading` (720px — Risk Report, Journal Entry), `container-standard` (960px — Dashboard, History, Journal, Profile, Slip List, intake shell), `container-analytics` (1200px — Builder, Planner Workspace, Planning History), `container-marketing` (1280px — the public homepage). The public-site width was not adopted at 1440px by assumption — see `DESIGN_TOKENS.md` for the full reasoning (no OddStorm artifact exists to literally inspect; 1280px follows `VISUAL_INSPIRATION.md`'s own Stripe/Linear reference set and UI UX Pro Max's "minimal single-column, generous whitespace" guidance instead). **One flagged deviation**: the Risk Report uses `container-reading`, not the commission's own `container-analytics` table entry — preserving `COMPONENT_PRINCIPLES.md`'s pre-existing, deliberate "document to read, not a dashboard" principle for that one screen.

## 11. Anti-Patterns (from `PO-U12.0-001`, restated briefly)

No sportsbook/casino visual language (unchanged, `VISUAL_INSPIRATION.md`). No gradient variation per card. No animated or multi-colour gradients. No component built without a real consumer. No risk-band colour reused for a non-risk meaning, including the new alert/gradient systems. No screen chooses an arbitrary container width independently of the four semantic tiers above.
