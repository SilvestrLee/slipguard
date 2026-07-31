> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# U-20.2 — Workspace Evidence & Product Showcase (response to `PO-U20.2-001`)

```text
Identifier:            R-01.9
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic (a permanent design principle + screenshot library spec)
Status:                Exploratory — principle/standards/library (§1-9, 16-20) reviewed and sound
                       pending one compliance fix; §10-13 visual/interaction spec deferred
                       to the visual pass, not reviewed as in-scope for this document
Owner:                 UX Studio (role, not a standing individual)
Product Office Decision: None
Repository Status:     None
```

## Provenance

This work package (`U-20.2`) was initially cited by `U-20.3` as an already-approved prerequisite before it had actually been supplied anywhere in this conversation — flagged directly rather than assumed. Its real content was supplied immediately after. Content reviewed on its own merits below; the citation-order issue doesn't affect the substance.

## Real conflict found

§9's screenshot library and §10's homepage implementation both include the **Market Intelligence Builder** as public-facing product evidence ("Builder page, Platform page, Technology section"). This directly conflicts with `U-17.4`'s binding, still-unresolved compliance constraint: `MARKET_WIDE_PLANNER_ENABLED` defaults `false`, internal/staff-only, no public launch until the standing external-legal-opinion gate clears. Presenting it publicly — even as a screenshot — would misstate its real availability, which is exactly what this same document's own "Evidence Before Theatre" principle (§6) and "avoid marketing renders" instruction (§16) argue against, just applied to a real, gated feature rather than a fabricated one.

**Recommendation**: remove Market Intelligence Builder from the public screenshot library until `U-17.4`'s launch gate actually clears. Every other listed screenshot (Workspace Overview/Dashboard, Analysis Report, Planner *[Capability A, already public]*, History, Decision Journal, Demo Workspace, Mobile Workspace) is real and already publicly reachable — no conflict found for any of those.

## Everything else — checked, sound

- §8's Authentic/Populated screenshot standards match exactly what was already done for the (currently deferred) mobile section: real Playwright captures against the real demo workspace, not invented UI.
- §19's "Future Evolution" list (Operations Console, Global Search, Risk Feed, Saved Collections, Workspace Collaboration) is correctly framed as not-yet-existing — no claim of current capability.
- §20's accessibility requirements (alt text, captions, "important information must never exist solely inside an image") are consistent with `ACCESSIBILITY.md`'s existing rules — nothing new needed there.
- The Workspace Evidence Principle itself (§4) doesn't conflict with anything locked — it's a real, defensible extension of "Evidence Before Theatre" to marketing presentation specifically.

## What's not decided here

Whether this becomes a formally adopted, permanent `DESIGN_LANGUAGE.md`/`COMPONENT_PRINCIPLES.md` principle (as §24 claims it already is — "this work package formally establishes... a permanent component") is not something this document can self-certify, per `LIFECYCLE.md`'s own rule. It stays a proposal until a real, separate governance-document edit actually happens, the same way the device-mockup exception required its own explicit amendment rather than being true by assertion.

## Review pass (2026-07-31) — a real scope gap missed on first read

The original commission (`PO-U20.1-001`) was explicitly sequenced as "copy and structure first, visuals later" after the mobile section's own visual pass didn't land — that boundary was applied to `R-01.8` but **not consistently applied here**, on the first pass, even though this document is part of the same broader initiative.

§10 (Homepage Implementation — per-section screenshot placement), §11 (Feature Cards — a proposed redesign of an existing real component pattern), §12 (Progressive Disclosure — a crop/zoom/hotspot interaction sequence), and §13 (Interactive Behaviour — parallax, hover magnification, scroll-linked movement) are all **visual/layout/interaction specification**, not principle. They belong in the deferred visual pass, not this one.

**What stays in scope for this pass**: §1–§9 (the Workspace Evidence Principle itself, why it matters, the screenshot standards, and the screenshot library definition — content/principle, not layout) and §16–§20 (avoid-marketing-renders, realistic data, ownership, accessibility — all real constraints, not visual specifics). §14 (Screenshot Callouts) and §15 (Screenshot Narrative) are borderline — they describe a *pattern* more than a literal layout, but lean toward the deferred pass too; flagged rather than silently included or excluded.

This narrows what's actually "reviewed and ready" from this document to: the principle, the standards, the library list (minus Market Intelligence Builder, per the conflict above), and the ownership/accessibility rules. The rest waits for the same visual pass `R-01.8`'s own visuals are waiting for.
