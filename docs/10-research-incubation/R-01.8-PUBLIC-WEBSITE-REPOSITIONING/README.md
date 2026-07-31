> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, `docs/05-ux/`, or office documentation. Requires real Product Office review and, per the agreed sequencing, a separate visual/layout pass before any page markup changes. See `docs/10-research-incubation/README.md` and `LIFECYCLE.md`.

# U-20.1 — Public Positioning & Website Evolution: Copy & Structure (response to `PO-U20.1-001`)

```text
Identifier:            R-01.8
Programme:             R-01 — Research & Incubation Framework
Scope:                 Strategic (public product identity/positioning, not a bounded technical question)
Status:                Exploratory — copy/structure drafted for review; no page markup changed
Owner:                 UX Studio (role, not a standing individual)
Product Office Decision: None
Repository Status:     None — no .blade.php file touched by this deliverable
```

## 0. Scope of this pass, as agreed directly (not inferred)

1. **Terminology changes are public marketing copy only.** "Intelligence Workspace," "Explainable Intelligence Reports," etc. below describe the website's own prose. Nothing about the real product changes — nav labels, routes, Livewire component names, existing docs/tests/ADRs that say "Dashboard"/"Planner"/"Analysis" are untouched.
2. **Copy and structure first, visuals later.** No homepage section, layout, motion, or the mobile "Coming Soon" integration is implemented here.
3. **The mobile section stays removed**, not re-integrated in this pass.

## 1. Review pass (2026-07-31) — real issues found on re-read

A genuine second read, not a re-summary, found six real problems in the first draft. All are corrected in the sections below, not left as a separate to-do list.

1. **A broken internal cross-reference** — Chapter Five originally pointed to "§5's compliance note" for the Market Intelligence flag; the actual note lives with the screenshot strategy. Fixed in §5.
2. **An unverified route left as a placeholder** — the original §3 cited `pages/how-it-works` with "confirm exact route." Checked directly against `routes/web.php`: **no such route exists.** §4 is rewritten against the real route list.
3. **The IA proposal silently replaced the real, currently-live public nav** (`/analyse`, `/planner`, `/reports`, `/pricing` — confirmed via the screenshot captured earlier this session) with invented destinations, without saying what happens to those four real pages. Corrected in §4.
4. **The messaging-hierarchy mapping is repetitive, not a clean single pass.** "Trust" is the stated position for four of seven chapters (One, Four, Six, Seven); "Deterministic Intelligence" for three (Two, Three, Six). True per-chapter, misleading about the overall shape — the chain gets revisited, not traversed once. Flagged here; not fully re-paced, since that's closer to a copyedit than a structural fix.
5. **Chapters Four and Six both make the same "engineering discipline / automated tests" point** — real repetition a visitor would notice. Recommend Chapter Six drop the test-count restatement (§5) and stay purely on the named principles.
6. **"Constitutional engineering governance" (the trust table) is internal jargon** that conflicts with `CLAUDE.md`'s own primary-user description — Tunde "has limited statistical knowledge... dislikes academic terminology." Replaced in §6 with plain language.

**Worth flagging now, even though it's `U-20.3`'s text, not this document's**: `U-20.3` frames the Hero as *"more like Bloomberg Terminal than SaaS."* `CLAUDE.md` names "trading terminal" and "chart-heavy/dense-financial-terminal dashboards" as **standing rejection examples**, verbatim. That's a direct conflict, not a style note — worth resolving before it shapes anything visual, and worth knowing while reading this document's own "sound more sophisticated" positioning goal, which is the same underlying direction.

## 2. Real conflicts found in the original commission

**Navigation exceeds the locked 5-item limit.** `COMPONENT_PRINCIPLES.md`: *"maximum 5 primary destinations."* The commission's proposed nav (Platform, Features, How It Works, Pricing, Resources, Labs, About, Login) is eight items. §4 proposes a compliant, and now route-accurate, alternative.

**"Multi-layer Risk Models" risks implying ML/neural-network layering**, contradicting the product's own no-AI/ML-in-scoring principle (`ADR-002`). §6 uses "Multi-Factor Structural Risk Model" instead — same real substance (`RF-001`–`RF-006` plus interaction adjustments), no accidental AI connotation.

**"Hundreds of automated tests" — verified.** Direct count: **548** `test()`/`it()` functions across `tests/`. Cited as "500+ automated tests" below.

## 3. Messaging hierarchy

```text
Trust → Evidence → Deterministic Intelligence → Explainability → Decision Support → Features
```

Each chapter in §5 states its position in this chain — see §1 item 4 for the honest caveat about how evenly that mapping actually lands.

## 4. Information architecture — corrected against real routes

Real public routes, checked directly against `routes/web.php`: `/analyse`, `/planner` (public), `/reports`, `/about`, `/faq`, `/release-notes`, `/pricing`, `/labs`, plus `/contact`/`/privacy`/`/terms` (all still "coming soon" placeholders). **No "How It Works," "Platform," or "Resources" page exists.**

Proposed primary nav (5 items, matching the locked limit, zero invented pages):

```text
Analyse · Planner · Reports · Pricing · Labs
```

Nothing new to build for this to work — it's the real current nav (`Analyse`, `Planner`, `Reports`, `Pricing`), with `Labs` added (already a real, live route) to complete the compliant 5. Per §0's own scope agreement, individual nav *labels* can still carry richer public-facing copy later (e.g. "Reports" shown as "Explainable Reports") without touching the route or the underlying page name — that's a copy decision, not a routing one.

Chapter Three's "How SlipGuard Thinks" content lives on the **homepage itself**, as one of the seven chapters in §5 — it was never meant to be a separate page, and doesn't need one.

**"About" stays a real, existing, secondary destination** (linked from the footer, not primary nav) — unchanged from today. **"Resources" has no existing page** — FAQ and Release Notes already exist separately; whether "Resources" ever becomes a real umbrella page is a genuinely open question, not decided here, and not required for anything else in this document to work.

## 5. Homepage copy — seven chapters

### Chapter One — The Problem
*(Hierarchy: Trust)*

> Most betting tools ask you to trust something you can't see — a hidden model, an opaque prediction, a probability nobody can explain. SlipGuard takes a different approach: every conclusion is something you can actually check.

### Chapter Two — The Solution
*(Hierarchy: Evidence → Deterministic Intelligence)*

> **Deterministic intelligence.** Every risk score comes from the same versioned rules every time — never a model that quietly changes its mind.
> **Evidence.** Every conclusion traces back to a specific, stated fact about your slip's structure.
> **Explainability.** Every result comes with the reason behind it, in plain language — never a black box.
> **Transparency.** You can see exactly what SlipGuard checked, and exactly what it didn't.

### Chapter Three — How SlipGuard Thinks
*(Hierarchy: Deterministic Intelligence → Explainability)*

A philosophy diagram, not a literal system diagram:

```text
Evidence → Verification → Deterministic Rules → Risk Intelligence → Explainable Conclusions → Your Decision
```

> This is the same sequence every time, for every slip. Nothing about it depends on who's asking, what mood the model is in, or which way the wind is blowing. That's what "deterministic" actually means — not a marketing word, a real constraint on how the engine is built.

### Chapter Four — Why Trust SlipGuard
*(Hierarchy: Trust, restated at the point of proof)*

- **Observable evidence** — every factor behind a score is named, not hidden.
- **Explainable conclusions** — a plain-language reason accompanies every finding, always.
- **Deterministic reasoning** — the same input produces the same output, every time, by construction.
- **Customer agency** — SlipGuard never decides for you. Every report ends with the decision still yours.
- **Continuous validation** — the engine's behaviour is checked by an extensive, real automated test suite (500+ tests) before anything ships.

### Chapter Five — The Intelligence Workspace
*(Hierarchy: Decision Support → Features)*

> One connected workspace, not a pile of disconnected screens: analyse a slip, plan an accumulator around real constraints, review the full explanation, come back to your history and journal whenever you want to.

Real, implemented surfaces referenced (screenshot strategy in §7): Dashboard, Analysis/Risk Reports, Planner, History, Decision Journal, Slip Intake, SlipGuard Labs. **Market Intelligence (Capability B) is deliberately not named or shown here** — currently feature-flagged internal-only; see §7's compliance note.

### Chapter Six — Engineering Principles
*(Hierarchy: Deterministic Intelligence → Trust, as commitments, not slogans)*

- **Evidence Before Theatre** — every "in progress" indicator reflects real work, or it doesn't appear at all.
- **Explain Before Conclude** — a customer sees the reasoning before, or alongside, any conclusion — never after.
- **Customer Outcome Sovereignty** — SlipGuard never chooses a side for you. Ever.
- **Deterministic Intelligence** — no AI/ML in scoring, ranking, or selection — a real architectural constraint, not a promise.
- **Transparent Reasoning** — every methodology is documented and available, not proprietary-and-hidden.

*(The original sixth bullet — "Continuous Validation," restating Chapter Four's test-count claim — is dropped here per §1 item 5. Chapter Four already owns that evidence; repeating it added nothing.)*

### Chapter Seven — Platform Roadmap
*(Hierarchy: Trust, forward-looking)*

> Native iPhone and Android apps are in active development. Additional evidence sources and new intelligence models are on the roadmap. We'll say so honestly when each one is ready — not before.

**Compliance note**: no App Store/Google Play badges until real public availability. No specific release dates.

## 6. Trust messaging framework — real achievements only

| Claim | Verified basis |
|---|---|
| Deterministic Intelligence Engine | `ADR-002` (Accepted, locked) |
| Explainable Analysis | `ADR-003` (Accepted, locked) |
| Evidence Validation | Real `AnalysisAvailability`/data-quality scoring in the engine |
| Multi-Factor Structural Risk Model | Real `RF-001`–`RF-006` + interaction adjustments — not "multi-layer," see §2 |
| Customer Outcome Sovereignty | Real, accepted constitutional principle (`PO-U17.6A-AC-001`) |
| 500+ automated tests | Verified directly: 548 `test()`/`it()` functions, counted this pass |
| Every decision is documented and reviewable | Replaces "constitutional engineering governance" per §1 item 6 — same real basis (the SGOS structure), plain language instead of naming the internal system |

**Explicitly not claimed anywhere**: customer counts, usage statistics, uptime numbers, or any figure not independently verified this pass.

## 7. Screenshot strategy

Reuses the real-capture approach already proven for the (currently deferred) mobile section: genuine Playwright captures of the actual running application with real demo data (`slipguard:demo`), never invented UI. For Chapter Five: capture Dashboard, Risk Report, Planner, and History at desktop width once this copy is approved. **Market Intelligence/Builder must not be screenshotted for public display** while `MARKET_WIDE_PLANNER_ENABLED` stays false — showing a feature-flagged-off, internal-only capability publicly would misstate its real availability.

## 8. SEO direction (as given, checked against Locked Decisions)

Consistent with the real product identity, no prediction-oriented language. No changes proposed.

## 9. Explicit confirmation of non-objectives

No rebrand. No product-positioning change beyond copy. No unapproved feature introduced. No capability promised that isn't real or already planned. No fabricated credibility (§6 enforces this directly). No AI-hype language. No betting-industry marketing tactics.

## 10. What's deliberately not in this document

Visual direction, desktop/mobile layouts, motion guidance, and the mobile section's own redesign — all deferred to a separate pass.
