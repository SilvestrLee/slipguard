# Iconography

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Tokens](DESIGN_TOKENS.md), [Image Guidelines](IMAGE_GUIDELINES.md), [Visual Inspiration](VISUAL_INSPIRATION.md) |

---

## Preferred Icon Style

- **Outline (stroke-based)**, not filled — outline icons read as analytical and precise; filled icons read as playful or brand-heavy, closer to app-store iconography than a diagnostics tool.
- **Stroke weight:** 1.5px at `icon-md`/`icon-lg` sizes (`DESIGN_TOKENS.md`), scaling proportionally at `icon-sm`/`icon-xl`. Consistent stroke weight across every icon in the product — never mix stroke weights from different icon sets.
- **Corner radius:** soft, rounded joins and caps (not sharp mitres) — consistent with the product's overall soft-radius language (`DESIGN_TOKENS.md`'s Radius Scale).
- **Filled vs. outline exception:** a filled variant of the *same* icon may be used for an active/selected state (e.g. an active nav item) — never a different icon shape between the two states, only the fill.
- Use a single, consistent icon set/library throughout — never mix icons from multiple libraries, which shows up immediately as inconsistent stroke weight and corner treatment.

## Approved Metaphors

| Icon | Use |
|---|---|
| Shield | Trust, protection, SlipGuard's own mark-adjacent imagery. |
| Magnifier | Analysis, inspection, "look closer" / methodology detail. |
| Warning (triangle or circle, outline) | High/Very High risk indicators, important cautions — never used to alarm, just to inform. |
| Check (circle, outline) | Completed states, Low risk, confirmation — never implies "safe to bet," only "structurally lower risk" (`docs/09-compliance/PRODUCT_GUARDRAILS.md`). |
| Document/list | Slip, report, journal entry. |
| Clock/history | Timeline, past analyses. |
| Chevron | Expand/collapse for progressive disclosure (`MOTION_SYSTEM.md`). |

Every icon must be pairable with a text label somewhere accessible (`ACCESSIBILITY.md`'s colour independence applies to icon-only meaning too) — an icon is a visual accelerator for a label, never a replacement for one.

## Icons to Avoid

- **Football** (or any single-sport imagery) — SlipGuard is not sport-specific in framing, even though v1 only supports football markets; a football icon overclaims scope and undersells the risk-analysis framing.
- **Money bags** — implies profit-focus, contradicts "SlipGuard Is Not... a guaranteed winning system" (`docs/00-governance/VISION_AND_PRINCIPLES.md`).
- **Casino chips** — gambling-as-game imagery, directly contradicts `VISUAL_INSPIRATION.md`'s rejected list.
- **Slot machines** — the single clearest visual signal of "casino product"; never appears anywhere, including empty states or illustrations.
- **Roulette wheels** — same reasoning as slot machines; implies random-chance framing SlipGuard exists to counter.
- **Confetti** — implies celebration of a bet or win; SlipGuard never celebrates placing or winning a bet (`MOTION_SYSTEM.md`'s Forbidden Animation Patterns; `docs/09-compliance/PRODUCT_GUARDRAILS.md`).

If a screen seems to need one of these to communicate an idea, the idea needs a different, non-gambling metaphor — not a softened version of the rejected icon.
