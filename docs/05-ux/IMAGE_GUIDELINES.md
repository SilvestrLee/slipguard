# Image Guidelines

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-25 |
| Related Documents | [Iconography](ICONOGRAPHY.md), [Visual Inspiration](VISUAL_INSPIRATION.md), [Motion System](MOTION_SYSTEM.md) |

---

## Brand Mark

**Added 2026-07-25, founder-approved.** Every previous customer-facing surface used the wordmark "SlipGuard" as plain styled text (`welcome.blade.php`, `layouts/guest.blade.php`, `layout/navigation.blade.php`) — no image-based logo existed anywhere in the repository until this entry. `MOTION_SYSTEM.md`'s pre-existing "Logo Rotation" rule governed motion for a mark that had no visual specification; this closes that gap.

The approved brand mark is the hexagon/shield "S" monogram (already consistent with this document's "Shield graphics" approved-imagery category, §Approved Imagery below) paired with the "SlipGuard" wordmark, in the same dark-navy tone as `DESIGN_TOKENS.md`'s existing accent scale. Source files live in `public/brand/`.

- **Full lockup** (`slipguard-logo-*-transparent.svg`/`.png`): mark + wordmark together. Use wherever there's room for the full width — desktop navigation, the homepage header, authentication layouts.
- **Icon only** (`slipguard-icon-*-transparent.svg`/`.png`): the mark alone, no wordmark. Use only where the full lockup cannot fit without cramping — the mobile navigation drawer header, compact mobile chrome.
- **Light variant**: for light backgrounds (dark navy artwork). **Dark variant**: for dark backgrounds (white artwork). Both are transparent-background PNG artwork wrapped in an SVG container — never recoloured, stretched, cropped, or redrawn; preserve aspect ratio at every size (`h-* w-auto`, never fixed `w-*` + `h-*` together for the full lockup).
- Every use requires `alt="SlipGuard"` for a standalone identity mark, or a decorative `aria-hidden="true"` treatment when the mark sits inside an already-labelled link (e.g. a "Go to dashboard" link wrapping the mark) — never `alt="logo"` and never two redundant accessible names for the same destination.

SlipGuard avoids generic sports and gambling imagery entirely. Every image on the site should reinforce "analytical decision-support tool," never "sportsbook" or "casino" (`VISUAL_INSPIRATION.md`, `docs/00-governance/VISION_AND_PRINCIPLES.md`'s "SlipGuard Is Not" list).

## Approved Imagery

- **Minimal illustrations** — simple, geometric, limited-palette illustrations (consistent with `DESIGN_TOKENS.md`'s restrained colour system) to represent abstract concepts like "risk" or "a decision," never literal betting scenes.
- **Abstract shapes** — soft geometric compositions used as background texture or section dividers, at low visual weight, never competing with foreground content.
- **Ticket mockups** — a stylised, generic representation of a betting slip (legs, odds, selections) used to illustrate the product without depicting a real bookmaker's actual branding or layout.
- **Shield graphics** — SlipGuard's own trust/protection motif, used sparingly (not as a repeated background pattern).
- **Data cards** — stylised previews of the actual UI (e.g. a sample Risk Report card, per `HOMEPAGE_STORYBOARD.md`'s Example Report section) rather than invented "dashboard" imagery that doesn't match the real product.
- **Product screenshots** — real, current screenshots of the actual application (kept up to date as the UI evolves) rather than idealised mockups that overpromise what the product looks like.

All imagery should be static or very subtly animated at most (`MOTION_SYSTEM.md`) — no auto-playing video, no looping animated heroes.

## Rejected Imagery

- **Football players / any sport action photography** — reinforces a sportsbook framing and implies outcome-prediction, which SlipGuard explicitly does not do.
- **Celebrations** (fist pumps, crowd cheering, trophy imagery) — implies a guaranteed or likely win, contradicting `docs/09-compliance/PRODUCT_GUARDRAILS.md`'s prohibited-claims list.
- **Fans / crowds / stadiums** — same reasoning as sport action photography; positions SlipGuard as sports/entertainment media rather than a risk-analysis tool.
- **Bookmaker screenshots** — real or implied bookmaker branding creates confusion about SlipGuard's independence and role (`docs/00-governance/VISION_AND_PRINCIPLES.md`: "SlipGuard Is Not... a bookmaker").
- **Casino imagery** (chips, cards, dice, slot machines, roulette, neon signage) — see `ICONOGRAPHY.md`'s Icons to Avoid; the same prohibition applies to photography and illustration, not just icons.

## Practical Rules

- Every image needs a stated purpose tied to the surrounding copy — an image chosen purely for visual interest without reinforcing the message it sits beside should be cut, not kept "because it looks nice" (`DESIGN_LANGUAGE.md`'s Cognitive Load Reduction).
- Product screenshots used in marketing must reflect the real, current UI — never a designed-but-unbuilt concept shown as if real.
- All images require alt text (decorative images marked `aria-hidden`, per `ACCESSIBILITY.md`).
- Keep total imagery weight light — this is a fast, calm product, not an image-heavy marketing site; prefer typography and layout to carry a section before reaching for an image.
