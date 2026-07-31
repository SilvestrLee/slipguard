> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# U-20.4 — Public Website Motion, Interaction & Storytelling System (response to `PO-U20.4-001`)

```text
Identifier:            R-01.10
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic (public-site motion language)
Status:                Exploratory — five direct conflicts found against the real, Approved
                       MOTION_SYSTEM.md; corrected below, not silently adopted or dropped
Owner:                 UX Studio (role, not a standing individual)
Product Office Decision: None
Repository Status:     None
```

## 0. A citation gap, named plainly

This commission's Strategic Authority lists `PO-U20.3-001` alongside `PO-U20.1-001`/`PO-U20.2-001` as though all three are settled prerequisites. **`U-20.3` was never actually delivered.** I received the commission text, raised a sequencing question about it (rejected), and received `U-20.2`'s real content next — `U-20.3`'s own section-by-section blueprint was never built, reviewed, or approved. Several items below (§2's "Hero Behaviour," §3's "Workspace Reveals") reference sections `U-20.3` defined but this repository has no reviewed version of. Not a blocker for the parts of this document that don't depend on it (most of it doesn't); named so it isn't mistaken for a settled foundation.

## 1. The real conflicts — the largest set found this session, checked against `MOTION_SYSTEM.md` directly, not assumed

`docs/05-ux/MOTION_SYSTEM.md` is `Approved`, locked, dated 2026-07-24, amended 2026-07-31 (the same day, for the exact rotation question below). Read in full before drafting anything else.

### Conflict One — logo rotation

**Original §8**: *"Rotating SlipGuard icon."*

**Real rule, `MOTION_SYSTEM.md`'s own "Static Product Mark" section** (added 2026-07-31, the same day): *"The approved SlipGuard icon and wordmark are static... They do not spin, rotate, bounce, loop, or respond to scroll position."* Confirmed a second way, independently: the real, currently-committed `public-nav.blade.php` already states "the former Signature Motion rotation is superseded." **Corrected**: Hero establishes tone via the already-approved page-load fade only (opacity, 250–350ms, `ease-out`) — no rotation, no exception.

### Conflict Two — parallax

**Original §12**: *"Parallax should exist only where it strengthens atmosphere... Recommended: Background gradients. Light fields. Atmospheric blobs."*

**Real rule**: Forbidden Animation Patterns names *"Parallax and scroll-jacking"* explicitly; Scroll Behaviour: *"No scroll-jacking, no parallax."* **The visual effect being reached for already exists, under a different name and a stricter, already-built mechanism** — the real "Fixed Atmospheric Layer" (three to five blurred indigo forms, scroll-linked depth via batched `requestAnimationFrame`, capped displacement, `prefers-reduced-motion` strips every transform, `pointer-events: none` always). **Corrected**: reuse the Fixed Atmospheric Layer by name, not "parallax" — the word itself is what's forbidden, and the real mechanism it would have described is already live.

### Conflict Three — screenshot hover zoom

**Original §10**: *"Subtle zoom"* among the recommended screenshot hover behaviours.

**Real rule, Hover Behaviour**: *"Hover states change opacity, background colour, or elevation only — never size, position, or shape."* A zoom is a size change, forbidden by name. **Corrected**: elevation + soft shadow only (both already on the original list and both compliant) — zoom dropped entirely, not softened.

### Conflict Four — "Reward" as a motion purpose

**Original §4**: motion principles listed as *"Reveal, Guide, Connect, Explain, Confirm, Reward."*

**Real rule, the document's own opening line**: *"Every animation... exists to confirm, guide, or clarify — never to delight, brand, or hold attention for its own sake."* "Reward" is the one word in the list that risks becoming exactly that — motion for the feeling of the interaction rather than for confirming a real state change. **Corrected**: dropped. "Confirm" (already real, already on both lists) already covers the legitimate case ("this action succeeded") without inviting the illegitimate one ("this felt good, do it again") — a distinction worth holding precisely given this product's own standing rule against anything reward-shaped in a betting-adjacent context (`ADR-012`'s "risk-awareness over excitement," the existing anti-gamified-streak rule).

### Conflict Five — the multi-stage workspace reveal

**Original §9**: *"Section enters → Copy becomes readable → Workspace appears → Important panel highlights → Callout explains → Section hands over."*

**Real rule, Reveal Animations**: what's actually built is `data-reveal` — a single, one-time fade + 8px rise, 300ms, `ease-out`, on first scroll into view, applied at the section level. The proposed sequence — per-element staged entrance, an "important panel" highlight step, a callout system — is materially richer and is not a restatement of the existing pattern. **This needs the Frontend Work Rule's own Gap Rule treatment** (documented in `COMPONENT_PRINCIPLES.md`/`MOTION_SYSTEM.md` before being built, not specified here as if it already has a home) — not adopted, not silently dropped, named as genuinely new and not yet designed.

## 2. What's real and compatible — kept as-is

- **Duration discipline** (§17's "fast enough to feel responsive, slow enough to feel intentional") is directionally correct and should cite the real numbers rather than stay vague: Instant 100ms, Fast 150ms, Standard 200ms, Deliberate 300ms, **400ms hard ceiling** — already exact, already approved, nothing to invent.
- **Easing** (not addressed explicitly in the original commission) should use the real rule: `ease-out` for entrances, `ease-in` for exits, `ease-in-out` for in-place state changes, never `linear`, **never bounce/spring/elastic anywhere** ("SlipGuard doesn't play").
- **§14 Microinteractions** (button compression, card elevation, theme toggle transition) match the real Micro-Interactions section almost exactly (button press: scale 0.98, 100ms, `:active`) — no conflict, already correct.
- **§18 Reduced Motion** matches the real rule closely: entrance/exit become instant opacity swaps, loading states drop decoration, reveals show final state immediately — already implemented once, globally, in `resources/css/app.css` (confirmed directly this session while building the mobile-section device-float animation), not something to re-implement per component.
- **§19 Engineering Principles** (GPU-friendly, interruptible, never blocks interaction) — sound, consistent, no conflict.
- **§7 Scroll Storytelling / §11 Section Rhythm** (alternating immersive/quiet sections, smooth handoffs) — this is presentation pacing, not a new motion mechanism; doesn't conflict with anything in `MOTION_SYSTEM.md` as long as "immersive" is read as the Fixed Atmospheric Layer (already permitted) rather than parallax (Conflict Two).
- **§15 Navigation Behaviour, §16 Page Transitions** — no conflict found; both are compatible with existing `wire:navigate`/Livewire transition conventions already in use sitewide.

## 3. Real achievement worth naming: this document's own Loading Behaviour section (§ not explicitly present in the original) should defer entirely to the real, already-precise three-tier rule rather than restate it loosely: under 300ms, nothing; 300ms–2s, a scoped inline spinner/skeleton; over 2s, a calm explicit state, never a fake progress bar. Already built, already correct, already the exact pattern this whole product's "Evidence Before Theatre" principle depends on (the Builder's own honest-progress fix, delivered earlier this session, is this rule in practice).

## 4. Recommendation

Adopt this document's corrected version in place of the original: five real conflicts resolved (three dropped/renamed, one deferred as genuinely new and undesigned, one grounded in exact existing numbers rather than restated vaguely). Nothing here contradicts `MOTION_SYSTEM.md`; everything that did has been named and fixed rather than passed through.
