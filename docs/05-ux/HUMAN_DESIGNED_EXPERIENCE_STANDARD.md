# Human-Designed Experience Standard

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | Every customer-facing SlipGuard interface |
| Owner | Product Office |
| Adopted | 2026-07-29, direct founder instruction |
| Related Documents | [Design Language](DESIGN_LANGUAGE.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Motion System](MOTION_SYSTEM.md), [Explainability System](EXPLAINABILITY_SYSTEM.md) |

---

## Provenance

This document was adopted directly from the founder's own instruction, delivered as a formal-style Product Office memo citing a "UX Studio submission (`UX-HD-001`)" that Product Office had reviewed and amended. **No such prior submission exists anywhere in this repository** — verified by direct search before writing anything here, consistent with this project's established discipline of checking a citation against real repository state before treating it as authoritative. The content itself, however, is coherent, doesn't contradict any Locked Decision, and directly extends principles `DESIGN_LANGUAGE.md` already states (a "decision-support instrument, not exciting" personality; hierarchy built from "size, weight, and spacing first, colour second") — so it is adopted here on its own merits, honestly attributed as new guidance issued today, not as a ratification of prior work that never happened.

---

## The core principle

> **Every SlipGuard interface must demonstrate deliberate human judgement rather than merely conforming to familiar software conventions.**

The interface should appear to have been intentionally designed around the customer's immediate objective, their current context, their likely questions, the information that genuinely matters, and the next useful action — not assembled from generic dashboard conventions. This sits alongside `DESIGN_LANGUAGE.md`'s existing Design Philosophy ("a decision-support instrument... not a betting product") as an equally-weighted constitutional principle, not a replacement for it.

## Four supporting principles

**Human Continuity** — the interface should help a customer naturally continue unfinished work, remembering context wherever doing so improves usability without creating confusion. Prefer "Continue where you stopped" over a generic "Recent analyses" list where the distinction genuinely helps; prefer "Last reviewed yesterday" over a bare "History" label. This is about recognizing what the customer was doing, not merely storing their records.

**Deliberate Omission** — every major interface should be able to justify why something appears, why it receives its visual priority, and why other information was intentionally left out. Information appears only when it contributes to customer understanding or action — never merely because it's available. This extends `DESIGN_LANGUAGE.md`'s existing Cognitive Load Reduction principle ("no chart, table, or number appears unless it changes what the user should do") rather than introducing a new one.

**Product Before Decoration** — visual treatment supports recognition, hierarchy, trust, continuity, and interaction. It is never pursued for its own sake or as a source of originality. Directly restates `DESIGN_LANGUAGE.md`'s existing White-Space Philosophy and Visual Hierarchy sections in this standard's own vocabulary — not a new rule.

**Evidence Before Theatre** — processing states always correspond to genuine system behaviour. Fake percentages, artificial scanning animations, invented statistics, meaningless progress bars, and decorative "thinking" animations are all prohibited. This is `MOTION_SYSTEM.md`'s existing Loading Behaviour rule ("never a fake progress bar that doesn't reflect real progress") elevated to a named, constitutional-weight principle rather than a new constraint — it was already binding; it is now named explicitly enough that it can't be quietly overlooked.

## What "human-designed" does not mean

Not asymmetrical, artistic, handcrafted-looking, unusual, or decorative. It means contextual, purposeful, recognisable, restrained, and thoughtfully prioritised — the same restraint `DESIGN_LANGUAGE.md`'s Product Personality section already names ("clear > calm > confident > warm... never playful, urgent, flashy, or salesy").

## Zero new components, clarified

"Zero new components" means **zero new primitive UI components** — no new base Card variant, no new Button style, no new Alert type invented outside `COMPONENT_PRINCIPLES.md`'s own Just-in-Time process. New product experiences may absolutely be created; they should be assembled from what `COMPONENT_PRINCIPLES.md` already defines (Cards, Forms, Badges, Alerts, Risk Indicators, Buttons, Empty States, disclosure patterns). Composition over expansion, matching every UX deliverable already produced this session (`U-06.4`, `U-17.7`) which independently reached this same conclusion before this standard existed to name it.

## Five release gates

No major customer-facing interface should be considered complete until it satisfies all five:

1. **Correct** — faithfully implements the approved product behaviour.
2. **Understandable** — a normal customer can explain what happened.
3. **Trustworthy** — honestly communicates evidence, limitations, uncertainty, and responsibility (`EXPLAINABILITY_SYSTEM.md`'s own Customer Trust section, restated here as a release gate rather than a design philosophy alone).
4. **Human-Designed** — feels intentionally designed rather than assembled, per this document.
5. **Efficient** — the customer can complete the intended task with minimal friction.

## Audit questions

Beyond the five gates, a Human-Designed Audit asks: does the interface remember what the customer was doing (Continuity)? Has unnecessary information been deliberately excluded (Omission)? Does the screen genuinely improve customer judgement, or does it merely display information (Decision Support)?

## Engineering guidance

This standard does not authorise redesign for its own sake, replacing functioning components, inventing new interactions, creating new component libraries, or visual experimentation without a stated customer benefit. It directs improving information hierarchy, recognition, context, and interaction quality using what already exists — removing unnecessary visual noise, not adding novelty.

## The one question every new screen should answer first

> **What decision is this screen helping the customer make?**

If that question can't be answered clearly, reconsider the interface before implementation begins — matching the Frontend Work Rule's own Gap Rule discipline (`CLAUDE.md`): stop, resolve the gap, then proceed.

## Scope note — dashboard priorities not commissioned here

The founder's original instruction also proposed specific dashboard implementation priorities (contextual slip naming, an unfinished-work card, richer recent-analysis summaries, theme refinement). **This document adopts the standard only** — it does not itself commission that implementation work. Per `CLAUDE.md`'s Just-in-Time Documentation and this project's own established practice of scoping implementation as its own explicit commission, dashboard changes against this standard should be requested separately when the founder is ready for them.
