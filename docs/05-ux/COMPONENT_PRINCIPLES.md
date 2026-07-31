# Component Principles

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Tokens](DESIGN_TOKENS.md), [Motion System](MOTION_SYSTEM.md), [Accessibility](ACCESSIBILITY.md), [UX Rules](UX_RULES.md), [Explainability System](EXPLAINABILITY_SYSTEM.md), [Empty States](EMPTY_STATES.md), [Trust Signals](TRUST_SIGNALS.md) |

---

Every reusable component's purpose, spacing, and rules. Values reference `DESIGN_TOKENS.md` — never hardcode a spacing, radius, or colour value directly in a component; if the token you need doesn't exist yet, add it there first.

Only components the product currently needs are defined here. Add a new component's principles here *when it's first built*, not speculatively (`CLAUDE.md`'s Just-in-Time Documentation applies to components too).

---

## Buttons

**Purpose:** the single primary action on a screen, plus secondary/tertiary supporting actions.

- **Spacing:** horizontal padding `space-4` (primary/secondary), `space-3` (small/inline). Vertical padding sized to reach the minimum touch target (`ACCESSIBILITY.md`).
- **Radius:** `radius-md` (see `DESIGN_TOKENS.md`) — soft, not pill-shaped, not sharp.
- **Elevation:** none at rest. Primary buttons may take `elevation-1` on hover only.
- **Interaction:** hover = background shift, active = scale 0.98 (`MOTION_SYSTEM.md`), disabled = reduced opacity + `cursor-not-allowed`, never removed from layout.
- **Accessibility:** minimum 44×44px touch target, visible focus ring, disabled buttons remain in the tab order with `aria-disabled`, not `disabled`, when the reason for disablement needs to be discoverable.
- **Responsive:** full-width on mobile for the primary action in a form or card; auto-width from tablet up.
- **Usage rules:** exactly one primary (filled, accent-coloured) button per screen or per card. Every other action is secondary (outline) or tertiary (text-only).
- **Anti-patterns:** two primary-styled buttons competing on one screen; a button whose label doesn't state the action ("Submit," "OK") instead of the actual outcome ("Save slip," "Mark as ready").

## Cards

**Purpose:** group one coherent unit of content (a slip summary, a journal entry, an analysis result) as a single visually distinct surface.

**Implemented U-12.0 (`PO-U12.0-001`) as `<x-card>`** — `variant="standard"` (default, bordered), `elevated`, `interactive` (pass `href`, renders as a single `<a>` per the Accessibility rule below), or `soft` (recessed, `surface-soft`). Uses the new `surface-card`/`shadow-elevation-*` tokens (`DESIGN_TOKENS.md`'s Surfaces/Elevation sections) instead of matching the page background exactly — a genuine, distinct lift, not just a border.

- **Spacing:** internal padding `space-6` (desktop), `space-4` (mobile). Gap between stacked cards: `space-4`.
- **Radius:** `radius-lg`.
- **Elevation:** `elevation-1` at rest, `elevation-2` on hover only if the card is itself clickable/navigable.
- **Interaction:** if interactive, the whole card is the click target (not just a nested link) with a visible hover state; if not interactive, no hover state at all — a static card that reacts to hover is misleading.
- **Accessibility:** if the card is a link, it is a single semantic `<a>`, not a `<div>` with a click handler.
- **Responsive:** cards never sit side-by-side on mobile unless they're intentionally short (badges, tags) — default to a single column.
- **Usage rules:** one clear heading per card, one primary piece of information, supporting detail below.
- **Anti-patterns:** nesting a card inside a card; putting more than one unrelated action inside a single card's footer.
- **Homepage capability card (`U-15.2`):** the same `<x-card variant="interactive">` primitive, composed with an icon inside a small soft-accent circle (`bg-accent-strong/10 text-accent-strong`, not a bare icon) and a trailing arrow glyph that shifts on hover (`translate-x` only, `MOTION_SYSTEM.md` micro-interaction budget) — a content composition using the existing component, not a new base component.

## Badges

**Purpose:** compact status labels — slip lifecycle status (Draft/Ready/Analysed/Archived), data-quality band, small tags. **Not** used for risk bands — see Risk Indicators below, which are a distinct, higher-stakes component.

**Implemented U-12.0 (`PO-U12.0-001`) as `<x-badge>`** — `tone="neutral"` (default, lifecycle status) or `tone="quality-{strong,good,limited,insufficient}"` (data-quality band, using the existing quality tokens). Deliberately has no `risk-*` tone, so a badge can never be reached for a risk band by mistake.

- **Spacing:** horizontal padding `space-2`, vertical `space-1`.
- **Radius:** `radius-full` (pill) — badges are the one place a pill shape is appropriate, since they're compact labels, not action targets.
- **Elevation:** none.
- **Interaction:** static, non-interactive. A badge is never a button.
- **Accessibility:** status conveyed by label text, not colour alone (see `ACCESSIBILITY.md`'s colour independence).
- **Responsive:** no change across breakpoints.
- **Usage rules:** one badge per status; never stack multiple badges to describe one thing.
- **Anti-patterns:** using badge colour intensity to imply severity — that's the Risk Indicator's job, not this component's.

## Risk Indicators

**Purpose:** communicate a risk band (Low/Moderate/High/Very High) or data-quality band clearly, calmly, and without colour as the only signal. The single most important component in the product — every other component defers to this one in visual priority on any analysis screen.

- **Spacing:** generous internal padding (`space-4`+) — a risk indicator is never a cramped chip. It has room to breathe because it's the answer the user came for.
- **Radius:** `radius-lg`.
- **Elevation:** `elevation-1`, slightly more prominent than surrounding cards.
- **Interaction:** static at the headline level; may be expandable (progressive disclosure) to reveal the reasons behind the band, using the standard expand/collapse motion (`MOTION_SYSTEM.md`).
- **Accessibility:** every band pairs a colour with an icon *and* a text label (e.g. a subtle warning icon + the word "High" + the colour) — removing colour must not remove meaning.
- **Responsive:** full-width on mobile, sized to content on desktop but never cramped.
- **Usage rules:** always paired with a one-sentence plain-language explanation directly beneath it (`DESIGN_LANGUAGE.md`'s Data Explanation Philosophy). Never shown without at least the top reason.
- **Anti-patterns:** a bare colour swatch or number with no label; reusing sportsbook-style "red = danger, green = safe" without also stating that Low risk is not "safe to bet" (`docs/09-compliance/PRODUCT_GUARDRAILS.md`) — SlipGuard never implies a band is permission to bet.

## Alerts

**Purpose:** a transient, page-level message — save confirmation, validation summary, a recoverable error. New component, added U-12.0 (`PO-U12.0-001`), consolidating flash-message blocks that previously used the raw Tailwind palette directly (`bg-red-50`, `bg-green-50`) with no dark-mode value.

- **Spacing:** `space-4` horizontal/vertical padding.
- **Radius:** `radius-md`.
- **Elevation:** none — an alert sits flush in the content flow, not floating above it.
- **Interaction:** static; dismissal (where offered) is a plain text/icon action, never the only way to stop seeing it (an alert tied to `session()->flash()` clears itself on the next request regardless).
- **Accessibility:** `role="alert"`; colour is paired with the message text itself, never colour alone, consistent with every other status pattern in this document.
- **Responsive:** full width of its container at every breakpoint.
- **Usage rules:** implemented as `<x-alert variant="success|error|caution|info">` using the dedicated `alert-*` tokens (`DESIGN_TOKENS.md`) — never a risk/quality/labs token, and never the raw Tailwind palette directly.
- **Anti-patterns:** stacking more than one alert at once; using an alert for a permanent, non-transient message (that's a `soft` Card, not an Alert).

## Empty States

**Purpose:** the "nothing here yet" state for a list screen (History, Journal, Planning History, Slip Index) — copy itself is `EMPTY_STATES.md`'s own authority, unchanged; this only unifies the markup.

**Implemented U-12.0 (`PO-U12.0-001`) as `<x-empty-state>`** (`icon`, `title`, `description`, optional `action` slot) — a `surface-soft` panel with a dashed border, replacing four near-identical hand-written blocks.

## Page Headers

**Purpose:** the title + one-line description + one action pattern at the top of every in-app list screen.

**Implemented U-12.0 (`PO-U12.0-001`) as `<x-page-header>`** (`title`, `description`, optional `action` slot) — mirrors `Section Headers`' rules below but for the top of a whole screen rather than a marketing-page section.

## Metric Cards

**Purpose:** a single numeric statistic with its label — the homepage Intelligence Credibility Section's metric grid, reusable anywhere else a system-level stat needs the same treatment.

**Implemented U-12.0 (`PO-U12.0-001`) as `<x-metric-card>`** (`label`, `value`, `inverse` for use on a `surface-inverse` band).

- **Spacing:** centred, `space-1` between value and label.
- **Radius:** n/a — text only, no container.
- **Elevation:** n/a.
- **Interaction:** static.
- **Accessibility:** the numeric value and its label are both always in the accessible text flow (never an image); count-up animation (where used) respects `prefers-reduced-motion` (`MOTION_SYSTEM.md`).
- **Responsive:** wraps into the surrounding grid's own column count; no independent breakpoint behaviour.
- **Usage rules:** always shows a real, verified value — never a fabricated or illustrative number (`docs/00-governance/DECISION_LOG.md`'s U-11.4 entry).
- **Anti-patterns:** using this component to imply a customer-volume/adoption statistic before real data exists — see `EMPTY_STATES.md`'s "no misleading dashboard" principle.

## Navigation

**Purpose:** let the user move between the workspace's core areas (Dashboard, Analyze, History, Journal) without becoming a visual distraction from the content itself.

- **Spacing:** generous horizontal padding between nav items (`space-6`+ on desktop).
- **Radius:** `radius-md` on the active-state background pill, if used.
- **Elevation:** the nav bar itself sits at `elevation-1` (a subtle separation from content), never higher.
- **Interaction:** active state shown by weight + a subtle background, not colour alone.
- **Accessibility:** current page indicated with `aria-current="page"`; fully keyboard-navigable in DOM order.
- **Responsive:** collapses to a bottom bar or hamburger menu below tablet width (`RESPONSIVE_RULES.md`) — never a dense horizontal scroll of nav items on mobile.
- **Usage rules:** maximum 5 primary destinations; anything else lives in an account/settings menu.
- **Anti-patterns:** a nav bar that changes height or position on scroll in a way that shifts page content (layout jump).

## Section Headers

**Purpose:** introduce a page section (marketing page or in-app) with one clear idea.

- **Spacing:** `space-8`–`space-12` above, `space-4` below, before body content begins.
- **Radius:** n/a (typography, not a container).
- **Elevation:** n/a.
- **Interaction:** static.
- **Accessibility:** correct semantic heading level (`h1`–`h3`), never skipped for visual sizing reasons — use type-scale tokens to resize, not a wrong heading level.
- **Responsive:** heading size steps down one level in the type scale below tablet width.
- **Usage rules:** one headline + optional one-line supporting text. No more.
- **Anti-patterns:** a section header with two competing headlines, or one that restates the page title instead of introducing what follows.

## Hero Blocks

**Purpose:** the homepage's opening statement — see `HOMEPAGE_STORYBOARD.md`'s Hero section.

- **Spacing:** the most generous spacing in the product — `space-16`+ vertical padding.
- **Radius:** n/a.
- **Elevation:** n/a (background may use a subtle layered surface, never a bold graphic).
- **Interaction:** exactly one CTA button; optional secondary text link ("See how it works").
- **Accessibility:** the hero's headline is the page's single `h1`.
- **Responsive:** headline drops one or two type-scale steps on mobile; never truncates or wraps awkwardly — write copy short enough to wrap cleanly at 375px.
- **Usage rules:** one sentence of positioning, one sentence of supporting explanation, one primary action. No stat carousel, no logo wall, no auto-playing video.
- **Anti-patterns:** anything from `VISUAL_INSPIRATION.md`'s Explicitly Rejected list — no flashing badges, no countdown, no "X people analysing slips right now."
- **Report-preview treatment (`U-15.2`):** the hero's sample-report card carries a slim title-bar strip above its content (`SlipGuard — Risk Report`, one small icon) at `elevation-2` — one step above a standard card — so it reads as a piece of the actual product rather than a decorative mockup. No traffic-light window-chrome, no browser frame, no device bezel — those are literal-screenshot clichés `VISUAL_INSPIRATION.md` already rejects the spirit of ("noisy hero sections... competing claims").
- **Native-platform device mockup exception (`PO-U19.1-001`, direct founder instruction, 2026-07-31):** the "no device bezel" rule immediately above remains in force everywhere else in the product without exception — every workspace screenshot, Risk Report, Planner screen, History/Journal view, and dashboard preview (including this section's own report-preview treatment) stays bezel-free, unchanged by this entry. One narrow, explicitly scoped exception: where a section's entire purpose is to communicate that a *native mobile app* exists or is coming — not merely to preview a screen — a restrained iPhone/Android device frame around a real screenshot is permitted, because in that one context the frame itself carries real semantic meaning ("this runs as a native app," not "here is a website") rather than being decorative chrome around content already understood to be a website. This is a constitutional exception, not a repeal of the rule above: the software is the hero everywhere else, and a device frame is never used merely because it "looks premium." Still governed by every other Hero Blocks rule (restrained, no exaggerated motion, real screens only, never inventing UI) and by `MOTION_SYSTEM.md`'s existing motion budget — a device frame does not license spinning, floating, or otherwise theatrical treatment beyond what any other hero content is already permitted.

## Forms

**Purpose:** slip entry, account settings, journal entries — anywhere the user provides input.

- **Spacing:** `space-4` between fields, `space-2` between a label and its field.
- **Radius:** `radius-md` on inputs, matching buttons.
- **Elevation:** none — inputs are defined by a border/background shift, not elevation.
- **Interaction:** instant focus-ring on `:focus`, inline validation messages appear below the field they belong to, never as a disconnected summary only.
- **Accessibility:** every input has a visible, associated `<label>` (never placeholder-as-label); error messages use `aria-describedby` and are announced to screen readers; validation is in plain language, not the raw rule name (`docs/06-engineering` validation conventions).
- **Responsive:** single-column field stacking below tablet width; multi-column only for genuinely paired short fields (e.g. odds + a small unit label).
- **Usage rules:** group related fields (all of one leg's fields together) with clear visual separation between groups.
- **Anti-patterns:** submitting a form with no loading/disabled state on the submit button, allowing a double-submit.

## Upload Areas

**Purpose:** the drop-target control for Screenshot/PDF slip intake. Built U-11.3 as an honest shell (file selection only, no processing); `PO-U11.5A-001` has since approved real OCR/PDF extraction as an MVP capability, but that extraction engine is **not yet implemented** — pending Architecture/Parser/Compliance Office review, per `docs/00-governance/DECISION_LOG.md`. This section covers the control's visual grammar only, unaffected by whether extraction exists yet.

**Implemented U-12.0 (`PO-U12.0-001`) as `<x-file-drop>`** (`inputId`, `accept`, `label`, `hint`) — extracted from the intake shell's two near-identical Screenshot/PDF blocks, now using `surface-soft` for the hover state instead of a flat neutral shade.

- **Spacing:** generous internal padding (`space-8`+), large enough to feel like an obvious drop target.
- **Radius:** `radius-lg`, dashed border to signal "drop zone" without needing colour.
- **Elevation:** `elevation-1` on drag-over only.
- **Interaction:** drag-over state, upload-progress state (skeleton, not spinner, per `MOTION_SYSTEM.md`), clear success/failure state. Genuine drag-over/upload-progress states are not yet built — the current control accepts a click-to-browse file selection only, per U-11.3's scope.
- **Accessibility:** a visible, keyboard-operable "choose file" fallback — drag-and-drop must never be the only path. Implemented as a `<label>` wrapping a `type="file"` input, not a `<div>` with a drop handler.
- **Responsive:** full-width on mobile; opens the native camera/file picker.
- **Usage rules:** state file-type/size limits before the user attempts an upload, not only after rejection.
- **Anti-patterns:** implying automated extraction is active before it exists — every unsupported method must still show the honest "not available yet" notice (`docs/00-governance/DECISION_LOG.md`'s U-11.3 entries), unchanged by this component's styling.

## Analysis Cards

**Purpose:** summarise one completed risk analysis in a list (e.g. on the dashboard or history screen).

- **Spacing:** `space-6` internal padding, matching the base Card component.
- **Radius:** `radius-lg`.
- **Elevation:** `elevation-1`, `elevation-2` on hover (the card links to the full report).
- **Interaction:** whole-card click target to the full report.
- **Accessibility:** risk band communicated via label + icon, not colour alone, even in this condensed form.
- **Responsive:** stacks to full-width single column below tablet.
- **Usage rules:** shows, at minimum: slip identifier/date, risk band, one-line top reason. Never shows the full per-leg breakdown at this level — that's progressive disclosure into the full report.
- **Anti-patterns:** cramming the full Risk Report Hierarchy (`UX_RULES.md`) into a list card — a card is a summary, not a shrunk report.

## Journal Cards

**Purpose:** show one journal entry — what the user decided and what they later recorded as the outcome.

- **Spacing:** matches Analysis Cards.
- **Radius:** `radius-lg`.
- **Elevation:** `elevation-1`.
- **Interaction:** editable inline or via a simple edit action; no destructive action without confirmation.
- **Accessibility:** clearly distinguishes the user's own recorded note from any system-generated text (different label/weight, not colour alone).
- **Responsive:** single column always — journal entries are personal, reflective content, not a scannable dense list.
- **Usage rules:** always shows the linked slip/analysis reference; never presents journal content as if it were a system-generated insight.
- **Anti-patterns:** turning the journal into a gamified streak tracker — directly contradicts `docs/09-compliance/PRODUCT_GUARDRAILS.md`'s prohibition on manipulative streaks.

## Reports (Risk Report screen)

**Purpose:** the product's core deliverable — the full explainable risk analysis for one slip.

- **Spacing:** the most generous body-content spacing in the app outside the homepage — each section of the Risk Report Hierarchy (`UX_RULES.md`) gets clear separation (`space-12`+ between major sections).
- **Radius:** section cards use `radius-lg`.
- **Elevation:** the headline/band section sits at the highest elevation on the page (`elevation-2`); everything else at `elevation-1` or flat.
- **Interaction:** progressive disclosure — main reasons expand to per-leg detail, methodology is collapsed by default.
- **Accessibility:** strict heading hierarchy following the Risk Report Hierarchy's order; the disclaimer is always in the accessible text flow, never an image or a faded footnote.
- **Responsive:** single-column, top-to-bottom on every breakpoint — this is a document to read, not a dashboard to scan, so it never becomes multi-column even on desktop.
- **Usage rules:** must always follow `UX_RULES.md`'s exact hierarchy: headline → band → weakest leg → main reasons → per-leg detail → methodology/disclaimer → next action.
- **Anti-patterns:** reordering the hierarchy for visual variety; hiding the disclaimer below the fold or in small print.
- **Print & Save as PDF (`U-15.1`):** a report is a document the customer may reasonably want to keep or show someone else, so it must be genuinely printable — this uses the browser's native "Print" (which already offers "Save as PDF" as a destination on every major platform) rather than a generated-PDF endpoint, since no server-side PDF renderer exists in this codebase and one is not justified by this need alone. On print: collapsed sections (Other Contributing Factors beyond the first two, Methodology, Report Details) render fully expanded — nothing a customer chose to keep should be missing because an accordion happened to be closed; the persistent navigation, footer, and the two Exit Action buttons are hidden (`@media print`); the report's own single-column, top-to-bottom structure is otherwise unchanged, since it was already print-shaped before this rule existed. The trigger is a plain, secondary-style button placed beside the Exit Actions, calling the browser's own `window.print()` — no new component, animation, or interaction pattern introduced.

## Timeline

**Purpose:** show a slip's lifecycle history or a sequence of journal/analysis events in order.

- **Spacing:** `space-4` between entries, with a connecting line at reduced opacity, not a bold rule.
- **Radius:** `radius-full` on the timeline node markers.
- **Elevation:** none — timelines are flat, structural elements.
- **Interaction:** static by default; entries may link to their related record.
- **Accessibility:** rendered as an ordered list (`<ol>`) semantically, not just visually sequential `<div>`s.
- **Responsive:** vertical orientation on every breakpoint — never switches to a horizontal scrolling timeline, which is hard to scan and harder to make accessible.
- **Usage rules:** each entry states what happened and when, in plain language ("Marked Ready," not a raw status code).
- **Anti-patterns:** using timeline styling for content that isn't genuinely sequential.

## Labs Feature Cards

**Purpose:** present one future SlipGuard capability on the SlipGuard Labs page (SD-002, Programme U-13) — informational, never a promise (`docs/00-governance/DECISION_LOG.md`'s SD-002 Principle 1).

- **Spacing:** matches the base Card component (`space-6` desktop / `space-4` mobile internal padding).
- **Radius:** `radius-lg`.
- **Elevation:** `elevation-1` at rest; no hover elevation change, since the card itself is not a navigation target (its buttons are the interactive elements, per the base Card component's own rule: a static-at-the-card-level surface doesn't get a misleading hover state).
- **Interaction:** the status badge is static (see Badges, using the six Labs status tokens in `DESIGN_TOKENS.md`); "Notify Me"/"Join Beta" are ordinary secondary (outline) buttons that toggle to a confirmed state (label changes to "✓ Notified"/"✓ Joined Beta") on activation — never a third, separate confirmation screen.
- **Accessibility:** status conveyed by label text plus colour, never colour alone, identical discipline to Risk Indicators and Badges elsewhere in this document.
- **Responsive:** single column always (`Cards` component's own rule: cards never sit side-by-side on mobile unless intentionally short — a Labs card is a full content unit, not a short tag).
- **Usage rules:** shows, at minimum: title, one status badge, a one/two-sentence summary, an optional "why this matters" line, and only the actions the feature actually enables (a feature with `beta_enabled: false` shows no "Join Beta" button at all, never a disabled one with no explanation).
- **Anti-patterns:** any vote count, ranking, or "N people want this" social-proof number on the card — `VISUAL_INSPIRATION.md`'s Explicitly Rejected list already prohibits exactly this pattern on the homepage hero, and SD-002 itself defers all voting mechanisms; a bare status badge with no summary text (status alone is not informative enough to justify a whole card).

## Progress Indicators

**Purpose:** show that something is in progress (analysis running, multi-step form) — see `MOTION_SYSTEM.md`'s Loading Behaviour for timing rules.

- **Spacing:** n/a (indicator itself is compact; surrounding content keeps normal spacing).
- **Radius:** `radius-full` for spinners/dots, `radius-md` for a linear bar.
- **Elevation:** none.
- **Interaction:** non-interactive, purely informational.
- **Accessibility:** `role="status"` with an accessible label ("Analysing your slip"), never conveyed by animation alone.
- **Responsive:** no change across breakpoints.
- **Usage rules:** only shown for genuinely indeterminate or long-running (>2s) waits; never a fake/simulated progress bar.
- **Anti-patterns:** a progress bar for an operation that completes in under 300ms (see Loading Behaviour) — that's motion without purpose.

## Dialogs (Modals)

**Purpose:** an interruptive, focused overlay for a single decision — confirming a destructive action (delete account, abandon a Planner session), never a routine information display (that's a Card or Alert).

**Documented `U-16.0`** — this component shipped with the original Breeze scaffold and was never brought into the SGDS surface/elevation system during `U-12.0`, unlike Card/Badge/Alert. Standardised now: backdrop `bg-neutral-900/50` (matching the mobile navigation drawer's own backdrop exactly, one component away — previously a raw, non-token `bg-gray-500`); panel `bg-surface-card shadow-elevation-3` (the highest elevation in the product, since a modal sits above everything else on screen — previously `bg-neutral-50 shadow-xl`, indistinguishable in surface terms from the page behind it).

- **Spacing:** panel padding is set by its own slot content (usually a `space-6` form), not the modal shell itself.
- **Radius:** `radius-lg`.
- **Elevation:** `elevation-3` — reserved for this component alone; nothing else in the product sits this high.
- **Interaction:** focus-trapped (Tab/Shift+Tab cycle within the panel), closes on `Escape`, backdrop click, or an explicit action; body scroll locked while open.
- **Accessibility:** focus moves to the first focusable element on open (where `focusable` is requested) and returns to the trigger on close; the backdrop is `aria-hidden`, the panel content carries its own heading.
- **Responsive:** full-width with margin on mobile, capped at the requested `maxWidth` from tablet up.
- **Usage rules:** exactly one modal open at a time; the action that opened it is the only way it closes besides Escape/backdrop — no modal auto-dismisses on a timer.
- **Anti-patterns:** stacking a second modal on top of an open one; using a modal for content long enough to need its own scroll — that's a dedicated page.
