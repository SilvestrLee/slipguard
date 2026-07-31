> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. Requires real Product Office commissioning and the Frontend Work Rule's actual sequence (already partially applied here — see §0) before any implementation may begin. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# U-18.3.1 — UX Exploration & Experience Validation (response to `PO-U18.3.1-001`)

```text
Identifier:            R-01.5 (continuation)
Scope:                 Strategic
Status:                Exploratory — genuinely checked against real docs/05-ux/ and both
                       specialist design tools, per CLAUDE.md's Mandatory Specialist Design
                       Capability Invocation; not yet Product-Office-commissioned
Owner:                 UX Studio (role, not a standing individual)
Product Office Decision: None
Repository Status:     None
```

---

## 0. Specialist Design Capability Usage (required for qualifying work, `CLAUDE.md`)

**Reviewed first, per the Frontend Work Rule**: `docs/05-ux/COMPONENT_PRINCIPLES.md`, `UX_RULES.md`, `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md` (full text, all three) — this mattered a great deal to the outcome below; see §1.

**UI UX Pro Max — invoked**: searched `ux` domain for progressive-disclosure/wizard/form guidance, and `product` domain for SaaS-dashboard patterns.
- **Adopted**: step-count disclosure ("Step 2 of 4"), inline validation on blur, visible persistent labels — all High/Medium severity findings. **Not new guidance** — `COMPONENT_PRINCIPLES.md`'s existing Forms and Progress Indicators sections already require exactly this; the tool confirmed rather than added anything.
- **Rejected outright**: the tool's generic "SaaS (General)" and "Analytics Dashboard" product-type recommendations — Glassmorphism, Data-Dense layouts, cool→hot gradient heat-map colour, "Real-Time Monitoring" dashboard style. These are precisely `VISUAL_INSPIRATION.md`'s and `CLAUDE.md`'s named standing-rejection categories ("chart-heavy/dense-financial-terminal dashboards... decorative SaaS trends that weaken clarity"). A generic tool doesn't know SlipGuard rejected these on purpose; rejecting them here is the evaluation actually happening, not a rubber stamp.

**21st.dev — invoked**: searched for progressive-disclosure/wizard/expandable-card components.
- **Adopted as *concept-confirmation* only, nothing installed**: "Disclosure" (plain expand/collapse) and "Stepper" (step sequence) — both match patterns `COMPONENT_PRINCIPLES.md` already defines (Risk Indicators' existing expand/collapse; Progress Indicators' step pattern). Confirms the existing SGDS already has the right shape; no gap found.
- **Rejected explicitly**: "Stacking Cards," "Reveal" (3D card-flip), "Blur Reveal Deck" — decorative, motion-heavy, "immersive" framing. Direct conflict with `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md`'s "Evidence Before Theatre" and "Product Before Decoration" principles. Named specifically so this rejection is a real evaluation, not an assumption.
- Per `CLAUDE.md`'s Tool Invocation Policy: nothing from either tool is a source of committed code — all results are React/shadcn install commands, which would silently introduce a second frontend framework if installed. Nothing was installed; only the underlying pattern was evaluated.

**Browser verification**: **did not occur, and this states that explicitly rather than implying otherwise.** This work package is exploration/documentation only (§10 of the commission: "not authorised to... implement production interfaces") — there is no running interface to verify. Per `PO-GOV-UX-002`, this is named directly, not glossed over.

---

## 1. The load-bearing finding: much of this is already decided

Before generating new concepts, checking real `docs/05-ux/` content first (as the Frontend Work Rule requires) found that several of the commission's "exploration areas" already have a real, approved answer — re-exploring them from scratch would be redesign for its own sake, which `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md`'s own Engineering Guidance explicitly prohibits ("does not authorise redesign for its own sake... replacing functioning components... without a stated customer benefit").

| Commission's exploration area | Real status |
|---|---|
| **B. Navigation Philosophy** | Already locked: `COMPONENT_PRINCIPLES.md`'s Navigation section fixes "maximum 5 primary destinations," active-state-by-weight-not-colour, `aria-current`, collapse-to-bottom-bar below tablet. Nothing to re-derive. |
| **A. Workspace Entry Experience** | Already substantially answered: `PO-U02-DASH-002` (real, delivered) built "Continue Working" and "Needs Your Attention," directly implementing `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md`'s "Human Continuity" principle ("Continue where you stopped" over a generic list). The commission's own "continuation-first" option is not a novel concept to explore — it's already built. |
| The commission's "Every screen answers a customer question" framing (§ Executive Summary, throughout) | Identical, not merely similar, to `UX_RULES.md`'s existing "Every Screen Must Answer: What is happening? Why does it matter? What should I do next?" — already Approved, dated 2026-07-24. |
| **E. Report Consumption** | Already fixed and locked: `UX_RULES.md`'s Risk Report Hierarchy (headline → band → weakest leg → main reasons → per-leg detail → methodology/disclaimer → next action) is a specific, ordered, non-negotiable sequence, restated in `COMPONENT_PRINCIPLES.md`'s Reports section with an explicit anti-pattern against reordering it "for visual variety." |

This isn't a criticism of the commission — it's the actual, useful output of doing the review step first. The genuinely open areas are narrower than the commission's six-area list implies, and that narrowing is itself real evidence, not opinion.

---

## 2. Genuinely open areas — concepts explored

### C. Builder Experience ("No Typing" / Intent-Driven)

Real gap: nothing in `COMPONENT_PRINCIPLES.md` yet defines a guided/progressive-narrowing construction flow — the current Builder is a form. Three concepts, evaluated against the tools' findings above and the existing SGDS:

1. **Staged single-column wizard** (Stepper-equivalent, composed from existing Progress Indicator + Card + Form components). Strengths: matches the tool-confirmed step-disclosure pattern; fits the Reports component's own "single-column, top-to-bottom" reading pattern already established elsewhere; no new primitive needed. Weaknesses: a rigid stage order may not match how customers actually think about accumulator construction (they might want to jump to odds before finishing markets).
2. **Progressive narrowing within one screen** (a single Card that reveals its next field group only once the prior one is valid, no page/step transitions). Strengths: fewer navigation events, feels more like the collaborative-assistance framing the commission itself asks for; composes from the existing Forms component's field-grouping rule ("group related fields... with clear visual separation") without inventing anything. Weaknesses: harder to show overall progress at a glance; risks feeling like a black box if narrowing logic isn't visible.
3. **Hybrid — staged overview + inline narrowing per stage** (a slim Stepper header for orientation, progressive narrowing within each stage's content). Strengths: gets both the orientation benefit of (1) and the low-friction feel of (2); still zero new primitives (Progress Indicator for the header, Forms/Cards for content). Weaknesses: the most implementation work of the three; needs a real interaction spec, not just this description.

**Recommendation: (3), evaluated but not decided** — best answers the commission's own "collaborative assistance, not form completion" framing while composing entirely from existing components. Flagged as `Strategic`-scope, not `Technical`: automated/intent-driven candidate suggestion is Capability B territory (`U-17.*`), already gated behind Parser/Compliance review in this repository's real history — this Builder concept must not silently start suggesting external-evidence-driven candidates before that gate clears, regardless of how the interaction itself is styled.

### D. Intelligence Presentation

Real gap: no component yet defines how a *new* evidence type (e.g. `R-01.1-MECI`'s context facts, if that ever graduates) would be presented alongside the existing Risk Indicator. Two concepts:

1. **Extend the existing Risk Indicator's own expand/collapse** (already defined, already tool-confirmed as the right pattern shape) to host a clearly visually-subordinate "Context" section beneath the structural reasons — same disclosure interaction, no new component.
2. **A separate, explicitly lower-elevation card** beneath the Risk Indicator, using the base Card component's `soft` variant (already defined, "recessed") to signal at a glance that context evidence is supplementary, never competing with the structural finding for visual priority.

**Recommendation: (2)** — the existing `soft` Card variant already exists specifically to signal "present but subordinate," and using it avoids overloading the Risk Indicator (`COMPONENT_PRINCIPLES.md`'s own "single most important component... every other component defers to this one in visual priority") with a second kind of content inside it.

### F. Learning Experience

Real gap: `Journal Cards` exist; "learning summaries" and "recurring behavioural patterns" don't. This is the one area with a genuine, unresolved constitutional question, not just a component question: any pattern that says "you tend to do X" edges toward behavioural profiling, which needs to be checked against `ADR-012`'s explainability and no-manipulation principles before any concept is chosen — this document doesn't resolve that question, it names it as the actual blocker, consistent with `R-01.1`'s own established pattern of naming blockers rather than designing around them.

---

## 3. Experience Recommendation (summary)

- Navigation and Workspace Entry: **no change** — already correctly built, re-affirmed rather than reopened.
- Report Consumption: **no change** — the Risk Report Hierarchy is locked and shouldn't be revisited without a specific, stated customer problem with the current order (none was given here).
- Builder: concept (3) above merits real design work, gated on the Capability B boundary being respected.
- Intelligence Presentation: concept (2) above — a `soft`-variant subordinate card — is small, composable, and ready to specify in real detail whenever `R-01.1-MECI` (or any future evidence type) is actually commissioned.
- Learning/behavioural summaries: **not ready** — a compliance question, not a component question, and shouldn't be designed further until that's answered.

## 4. What would actually need to happen before any of this graduates

Per `LIFECYCLE.md` §4: a real Product Office decision to commission Builder redesign specifically (not inferred from this exploration); this document's concepts turned into an actual `docs/05-ux/U-XX` interaction spec, reviewed and accepted the way `U-06.4`/`U-17.7` (cited in `HUMAN_DESIGNED_EXPERIENCE_STANDARD.md` as real precedent) were; and, for the Learning area specifically, a real Compliance Office answer before any concept work continues at all.
