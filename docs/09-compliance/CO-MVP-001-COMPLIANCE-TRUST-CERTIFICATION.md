# SlipGuard Compliance & Trust Certification

| Field | Value |
|---|---|
| Commission | `CO-MVP-001`, Compliance Office, Critical priority, requested by Product Office, direct founder instruction, 2026-08-04 |
| Type | **Governance and product compliance certification — not a legal opinion.** Identifies what must exist before launch and what requires jurisdiction-specific legal review; does not itself author binding legal documents. |
| Owner | Compliance Office (`docs/offices/COMPLIANCE_OFFICE.md`, established `G-02`, jointly owns `docs/09-compliance/PRODUCT_GUARDRAILS.md` with Product Office) |
| Grounding | Builds on `docs/product/MVP_LAUNCH_READINESS_AUDIT.md`'s Compliance findings (`R-01a`, `R-01b`, `R-02`) and the already-existing `docs/09-compliance/PRODUCT-COMPLIANCE-OPERATING-MODEL.md` (adopted `PO-PD011-001`, 2026-07-29) rather than re-deriving either. |
| Status | Delivered. Awaiting Product Office review. |

---

# 0. Provenance & Scope Corrections

Two claims in this commission's own framing were checked against real repository state before proceeding, consistent with this project's established practice of verifying a citation before treating it as authoritative (the same standard `G-03.1`, `AO-MVP-005`, and `PO-MVP-005` already applied to their own commissioning documents).

**0.1 — "The previously approved affiliate strategy" (§4) does not exist.** A direct search of `docs/00-governance/DECISION_LOG.md`, `TASKS.md`, `docs/09-compliance/*`, and `docs/adr/ADR-012*` found no affiliate strategy ever proposed, drafted, or approved anywhere in this repository. `PO-MVP-005`'s own audit independently confirmed (§8.7) zero affiliate-tracking code, bookmaker-referral links, or commercial-partnership code exists anywhere in the running application. **C-04 below is therefore not a review of an existing strategy — it is the compliance framework that would govern one if and when Product Office proposes it**, consistent with `docs/09-compliance/PRODUCT-COMPLIANCE-OPERATING-MODEL.md`'s own Compliance Trigger Matrix, which already names "affiliate systems" as a Mandatory Compliance Review trigger.

**0.2 — A Jurisdiction Matrix cannot be produced without a named target market, and none exists.** No document anywhere in this repository (`PROJECT.md`, `docs/00-governance/PROJECT_CHARTER.md`, `docs/00-governance/PRODUCT_POSITIONING.md`, `docs/01-product/PRODUCT_BLUEPRINT.md`) names an intended launch country or region. This is not an oversight this certification can fill in — `docs/09-compliance/PRODUCT-COMPLIANCE-OPERATING-MODEL.md` §"Deferred until market expansion" already made this an explicit, standing decision: *"International jurisdiction matrix, country-specific gambling regulation, regional advertising compliance, country-specific legal reviews — explicitly out of scope until Product Office approves entry into a specific market."* Inventing a plausible-sounding jurisdiction to populate a matrix would fabricate a decision Product Office hasn't made — exactly the failure mode this repository's own governance culture exists to prevent. **C-05 below delivers the matrix's methodology, ready to populate, and formally requests the one input this certification cannot supply itself: which market(s) SlipGuard intends to launch in first.**

**0.3 — Terminology note.** This commission's Executive Objective refers to SlipGuard as a "sports intelligence platform." Per `docs/00-governance/DECISION_LOG.md`'s `U-18.4.2` commission entry (2026-08-03), that framing was explicitly scoped to guide one architecture commission only — "the current SlipGuard product identity remains unchanged... football-first implementation remains an implementation strategy rather than a permanent product identity." This certification uses SlipGuard's actual, current, locked identity throughout — **a betting decision intelligence platform** (`SD-001`, `CLAUDE.md`) — and does not treat the commission's descriptive phrasing as a redefinition.

---

# 1. Executive Summary — Answering the Acceptance Criteria Directly

| Question | Answer |
|---|---|
| Are the launch blockers identified in `PO-MVP-005` resolved? | **No.** `R-01a`/`R-01b` (Terms/Privacy stubs) and `R-02` (Responsible Gambling determination) remain open — resolving them is this commission's own job; see C-02/C-03/C-06. |
| Are all required legal artefacts identified? | **Yes.** See C-02 — a complete register with mandatory/deferrable classification for every artefact type this commission named. |
| Is the business model still compliant with Product Office decisions? | **Yes**, re-confirmed against real running code, not re-asserted from memory. See §3 below. |
| Are affiliate plans compatible with the platform's trust model? | **No affiliate plans exist to evaluate** (§0.1). The framework that would govern one is defined (C-04) and is already compatible with `ADR-012`'s existing boundary. |
| Can RC1 proceed from a compliance perspective? | **Not yet — NOT CERTIFIED today; CERTIFIED WITH CONDITIONS is realistically close.** Every open item below is content/decision work, not a defect requiring redesign. |

**Certification status: NOT CERTIFIED.** Path to **CERTIFIED WITH CONDITIONS**: (1) commission real Terms of Service and Privacy Policy content [`⚠ requires legal advice`], (2) adopt this certification's recommended baseline Responsible Gambling signposting (low-cost, resolves `R-02` without waiting on a jurisdiction decision — see C-03), (3) Product Office names at least one intended launch market so C-05 can be completed [blocking specifically for any jurisdiction-specific claim; a market-agnostic soft-launch is a legitimate alternative Product Office may choose instead — see C-05], (4) add the two small, concrete gaps this certification found and `PO-MVP-005` didn't (an explicit age statement, a footer copyright/IP notice — both Minor, both cheap).

---

# 2. Business Model Compliance (Commission §3)

Re-verified directly against the current running application, not re-asserted from `ADR-012`'s text alone — largely reusing `PO-MVP-005`'s own one-day-old, code-level verification (§8.6/§8.7 of that audit) rather than re-deriving it, since no code has changed in this area since.

| Boundary | Status | Evidence |
|---|---|---|
| Does not accept bets | **Confirmed** | No wager-placement code path exists anywhere; `about.blade.php`/`faq.blade.php` state this directly to customers. |
| Does not process payments for bookmakers | **Confirmed** | No payment/billing package in `composer.json` (`stripe`, `cashier`, `paddle`, `paypal` — zero matches, re-confirmed). |
| Does not hold customer funds | **Confirmed** | No wallet/deposit/withdrawal model or code anywhere. |
| Does not automate wagering | **Confirmed** | Every wager requires a conscious customer action outside SlipGuard entirely — SlipGuard's own output stops at explanation, never execution. |
| Does not act as a betting intermediary | **Confirmed** | No code path connects a customer to a bookmaker account, order, or transaction. |
| Remains an intelligence platform | **Confirmed** | Explainability language throughout the product (`PO-MVP-005` §4/§8.3) consistently frames output as analysis, never instruction or execution. |

**Areas flagged for ongoing vigilance, not current violations:** Capability B ("Build an Accumulator") is the one feature with real external market-data dependency and the one place a future feature could most plausibly drift toward operator-like behaviour (e.g. a "one-click send to bookmaker" convenience feature would cross this line immediately) — already correctly named as a Mandatory Compliance Review trigger in the existing `PRODUCT-COMPLIANCE-OPERATING-MODEL.md`, not a new flag invented here. No current code approaches this boundary.

---

# C-01 — Launch Compliance Certification

**Certification status: NOT CERTIFIED — path to CERTIFIED WITH CONDITIONS is short and defined, per §1 above.**

This certification is a governance judgment, not a legal one. It certifies that Compliance Office has reviewed the product against SlipGuard's own locked boundaries (`ADR-012`, `CLAUDE.md`) and named every item that must exist before RC1 can be considered ready from a compliance standpoint. It does **not** certify that any jurisdiction's actual law has been satisfied — every item marked `⚠ requires jurisdiction-specific legal advice` below remains exactly that until real counsel reviews it, per this commission's own explicit constraint against authoring legal documents without that distinction.

**What is already solid, not merely assumed:** the business model (§2 above); the in-product disclaimer language (`PO-MVP-005` §8.3, independently verified genuine); the "no false jurisdiction claims" finding (`PO-MVP-005` §8.5); the existing audit-trail/security posture (`PO-MVP-005` §7, no compliance-relevant gap found there).

**What remains open, consolidated in C-06's Risk Register:** real legal content for Terms/Privacy [`⚠`]; a Responsible Gambling decision (this certification recommends a specific, low-cost resolution — C-03); the jurisdiction question itself (C-05); two small documentation gaps (age statement, IP notice).

---

# C-02 — Required Legal Documents Register

| Document | Mandatory for MVP? | Current status | Recommendation | Owner |
|---|---|---|---|---|
| **Terms of Service** | **Mandatory** | Stub (`/terms` → `public-coming-soon`, confirmed) | Commission real content `⚠` | Compliance/Legal |
| **Privacy Policy** | **Mandatory** | Stub (`/privacy` → same) | Commission real content `⚠` — SlipGuard collects real personal data (email, name, uploaded slip images/PDFs, analysis history), so this is required regardless of which market is eventually named | Compliance/Legal |
| **Cookie Policy** | **Not currently mandatory** | Does not exist as a standalone document | Direct verification found **zero** analytics, tracking, or third-party cookie code anywhere in the codebase (`gtag`, Google Analytics, Meta Pixel, Hotjar, Mixpanel, Segment, PostHog — all zero matches) — only Laravel's own strictly-necessary session/CSRF cookies are set. Under most cookie-consent regimes, strictly-necessary cookies require disclosure, not a standalone policy or consent banner. **Recommend folding a short cookies subsection into the Privacy Policy** rather than a separate document — avoids unjustified complexity, consistent with `docs/offices/OPERATIONS_OFFICE.md`'s own "avoid premature complexity" principle applied here to legal-document sprawl. Revisit if analytics/tracking is ever added. | Compliance/Legal |
| **Acceptable Use Policy** | **Not mandatory for MVP** | Does not exist | SlipGuard has no user-generated public content, no marketplace, no third-party-visible uploads — the risk surface an AUP typically covers barely exists yet. **Recommend folding core prohibited-use language (fraud, abuse, automated scraping) into the Terms of Service** instead of a separate document. Revisit if/when a social or sharing feature is ever introduced. | Compliance/Legal |
| **Disclaimer language** | **Already substantively satisfied** | Real, in-product, independently verified genuine (`PO-MVP-005` §8.3 — homepage, Risk Report, Manual Builder all carry real "no outcome prediction" language) | No new work required for the in-product experience. **Recommend the Terms of Service still contain a formal disclaimer clause** as a standard legal backstop even though the product experience already does the communicative job. | Compliance/Legal (ToS clause only) |
| **Intellectual Property notices** | **Footer notice already present; correction to this certification's own earlier finding** | **Correction, `PO-RC1-002`, 2026-08-04**: this certification originally reported no copyright/IP notice anywhere in the footer, based on a search for the literal `©` character and the words "copyright"/"all rights reserved" — a false negative. Direct re-verification found a real, working, dynamically-dated notice already present: `<p>&copy; {{ now()->year }} SlipGuard</p>` (`resources/views/components/public-footer.blade.php:102`, HTML-entity-encoded, which the original search missed). No footer change was needed. | An IP clause in the Terms of Service is still recommended once real ToS content is authored — the footer line alone doesn't cover trademark/content-licensing terms a ToS clause would. | Compliance/Legal (ToS clause only) |

---

# C-03 — Responsible Gambling Framework

**Framing constraint, stated directly rather than assumed away:** SlipGuard does not accept bets, hold funds, or facilitate wagering (§2) — it analyses slips the customer already intends to place elsewhere, entirely outside SlipGuard. This narrows, but does not eliminate, potential Responsible Gambling obligations — several regulatory and advertising-standards regimes extend responsible-gambling expectations to services *adjacent to* gambling (tipster/analysis services, odds comparison, etc.), not only to licensed operators themselves. **Whether a specific obligation applies is jurisdiction-dependent and cannot be finally determined until C-05's market question is answered `⚠`.**

**Recommendation, not contingent on the jurisdiction question:** adopt a baseline of low-cost, high-value signposting now, rather than waiting on a jurisdiction decision that may take time — this directly resolves `PO-MVP-005` finding **R-02** without blocking on C-05.

| Item | Recommendation | Rationale |
|---|---|---|
| Responsible Gambling information | **Include** — a short footer statement and a link to a recognised support resource (e.g. a BeGambleAware-style reference, adjusted once a real market is named) | Cheap, meaningfully reduces regulatory exposure across virtually every plausible target market, consistent with `ADR-012`'s "risk awareness over excitement" boundary the product already embodies functionally in its Risk Report copy. |
| Safer Gambling guidance | **Include**, folded into the same footer/support-resource reference above — no separate page needed at MVP scale | Avoids inventing new UI surface for something a link and a sentence already serve honestly. |
| Support resources | **Include** — link to a real, recognised helpline/resource once a market is named | Same rationale as above. |
| Deposit-loss disclaimers | **Do not include** | SlipGuard never processes deposits — including this language would be inaccurate and would misleadingly imply SlipGuard resembles a betting operator, directly working against `ADR-012`'s operator-independence positioning. |
| Reality checks | **Do not include** | Same rationale — a "reality check" mechanic (a timed reminder during active play) presupposes SlipGuard mediates active wagering sessions, which it structurally does not and must not (`ADR-012`, "no autonomous betting"). |
| Age restrictions | **Real gap, found this pass**: no age statement or age-confirmation step exists anywhere in registration (`routes/auth.php`, direct search — no age field, no gate). **Recommend an explicit 18+ (or higher where locally required `⚠`) statement in the Terms of Service at minimum**; a registration-time age checkbox is a low-cost addition worth considering alongside it. | Standard baseline across virtually every plausible market; currently entirely absent. |
| Regional wording differences | **Genuinely blocked on C-05** | Cannot specify regional wording without knowing the region. |

---

# C-04 — Affiliate Compliance Framework

**No affiliate strategy exists to review (§0.1).** This section defines the framework that governs one if and when Product Office proposes it — building directly on `ADR-012`'s existing Locked Decision ("no affiliate agreement, bookmaker partnership, commercial incentive, or promotional campaign may bias a deterministic calculation, risk classification, customer-facing recommendation, or explainability") and `docs/09-compliance/PRODUCT-COMPLIANCE-OPERATING-MODEL.md`'s existing Compliance Trigger Matrix, which already names "affiliate systems" as requiring Mandatory Compliance Review — that gate already exists; this framework specifies what it must check.

| Requirement | Detail |
|---|---|
| Required disclosures | Any affiliate/referral link must be clearly, visibly labeled as a commercial link at the point of display (e.g. "we may earn a fee if you sign up via this link") — standard advertising-disclosure practice, applies regardless of jurisdiction. |
| Advertising obligations | A bookmaker referral must never be framed as originating from, or endorsed by, the deterministic engine — it must be structurally and visually separated from analysis output. |
| User transparency | The existence and general nature of any affiliate relationship must be disclosed in the Terms of Service/Privacy Policy, even in cases where the relationship demonstrably does not bias any output — non-disclosure of a material commercial connection is its own distinct compliance risk, separate from the bias question `ADR-012` already governs. |
| Separation between intelligence and affiliate incentives | Structural, not just visual: any future affiliate link must live in clearly separate, clearly labeled placements (mirroring the existing "your licensed operator" pattern already used in `about.blade.php`/`faq.blade.php`) — never woven into risk scores, candidate rankings, or explainability text. |
| Conflict-of-interest risk | The highest-risk failure mode is bookmaker-selection bias — e.g. Capability B ever preferring one bookmaker's odds because of a commercial arrangement rather than the deterministic evidence. This would directly breach `ADR-012` and must be treated as a hard blocker, not a "manage carefully" item, on any future affiliate proposal touching Capability B specifically. |
| Approval gate | Any real affiliate proposal must go through the existing Mandatory Compliance Review trigger (`PRODUCT-COMPLIANCE-OPERATING-MODEL.md`) jointly with Product Office before any implementation — this framework does not itself approve a hypothetical future programme, only specifies what a real one would have to satisfy. |

**Bottom line:** no current risk — nothing exists to disclose or mitigate. This framework exists so a future affiliate proposal is evaluated against a written standard rather than improvised at the time.

---

# C-05 — Jurisdiction Readiness Matrix

**Cannot be populated yet — this is the certification's single largest genuine blocker (§0.2).** Delivered here: the matrix's methodology, ready to complete the moment Product Office names a market, plus the explicit request for that input.

## Matrix methodology (columns to complete per market, once named)

| Column | What it captures |
|---|---|
| Licensing implications | Whether operating a decision-intelligence service (not a betting operator) triggers any local licensing regime — varies significantly by market; some jurisdictions regulate tipster/analysis services adjacent to gambling, most do not, at least not identically to operators. `⚠ requires jurisdiction-specific legal advice` in every case. |
| Consumer protection considerations | General consumer-protection law applicable to any SaaS (cancellation rights, clear pricing once billing exists, data-subject rights) plus any gambling-adjacent-specific consumer protections. |
| Advertising considerations | Whether local advertising standards (e.g. ASA-style codes) extend responsible-gambling advertising rules to analysis/tipster-adjacent services — directly informs C-03's regional wording question. |
| Privacy obligations | Applicable data-protection regime (e.g. GDPR-class, or a market-specific equivalent) — determines Privacy Policy content requirements beyond the baseline already identified in C-02. |
| Gambling-specific considerations | Any market-specific rules governing services that inform, but don't execute, gambling decisions. |
| Launch recommendation | GO / GO WITH CONDITIONS / NO GO for that specific market, once the above is actually assessed by real counsel. |

## What this certification can say without a named market

- The business model itself (§2) is structurally conservative — no funds custody, no autonomous execution, no operator role — which reduces, but does not eliminate, licensing exposure in most plausible markets. This is a real, favourable starting position, not a substitute for market-specific review.
- `PO-MVP-005`'s own finding stands: no false or overreaching jurisdiction/licensing claim exists anywhere in current copy (§8.5) — there is nothing to correct while this question remains open.

## Request to Product Office

**This certification formally requests that Product Office name at least one intended first launch market before C-05 can be completed.** Two legitimate paths forward, either acceptable from a compliance-governance standpoint:
1. **Name a specific first market** (or small set of markets) — C-05 is then completed against real jurisdictions, and specialist legal advice is engaged for exactly those, per this commission's own constraint against Compliance Office substituting for that advice.
2. **Explicitly choose a market-agnostic soft launch** (no geographic restriction, no jurisdiction-specific claim made anywhere in the product) as a deliberate interim strategy — this is a legitimate choice, but it should be a stated decision, not a silent default, and it does not remove the baseline Responsible Gambling/age-statement recommendations in C-03 (those are cheap enough to justify applying broadly regardless of the eventual market decision).

Either path is compatible with this certification; leaving the question unanswered is not — a market must be decided or explicitly deferred-with-acknowledgment, not left ambiguous into an actual launch.

---

# C-06 — Compliance Risk Register

| ID | Risk | Severity | Owner | Blocking? | Mitigation | Recommended completion stage |
|---|---|---|---|---|---|---|
| CO-01 | Terms of Service is a stub | Critical | Compliance/Legal | **Yes, before any real launch** | Commission real content `⚠` | Before RC1 |
| CO-02 | Privacy Policy is a stub | Critical | Compliance/Legal | **Yes** | Commission real content `⚠` | Before RC1 |
| CO-03 | No Responsible Gambling signposting exists | Major → **resolved by recommendation** | Engineering (implementation), Compliance (content) | Yes, until C-03's baseline is adopted | Adopt C-03's baseline signposting — does not require the jurisdiction question to be answered first | Before RC1 |
| CO-04 | No age statement or age-confirmation exists | Major | Engineering + Compliance | Yes, before real launch | Add 18+ (or locally required `⚠`) statement to ToS; consider a registration-time checkbox | Before RC1 |
| CO-05 | ~~No copyright/IP notice in the footer~~ — **corrected, `PO-RC1-002`**: a real notice already exists (`&copy; {{ now()->year }} SlipGuard`), the original finding was a search false-negative. A ToS IP clause is still recommended once real ToS content is authored. | Minor | Compliance (ToS clause) | No | Fold an IP clause into the Terms of Service content commission (`CO-01`) — no separate footer work needed | Reflected in CO-01's content, not a standalone item |
| CO-06 | No target launch market has ever been named | Major | Product Office | **Yes, for any jurisdiction-specific claim** | Product Office names a market, or explicitly adopts a market-agnostic soft launch (C-05) | Before RC1 |
| CO-07 | No affiliate strategy exists (informational, not a current risk) | Observation Only | N/A | No | Framework ready (C-04) for if/when one is proposed | N/A — dormant |
| CO-08 | Cookie Policy / AUP absent as standalone documents | Observation Only | Compliance/Legal | No | Deliberately folded into Privacy Policy / ToS instead (C-02) — not a gap, a scoping decision | Reflected in CO-01/CO-02's content |
| CO-09 | Capability B is the one feature most likely to drift toward operator-like behaviour if extended carelessly | Observation Only | Product Office / Compliance Office | No — no current violation | Already correctly gated by the existing Compliance Trigger Matrix; no new gate needed, named here for continuity with `PO-MVP-005`'s own Capability B attention | Ongoing vigilance, not a launch item |

---

*This document is Compliance Office's certification deliverable. Per `CO-MVP-001`'s own explicit constraints, it does not redesign the product, expand MVP scope, introduce payment processing, recommend sportsbook functionality, or change Product Office strategy — and every item marked `⚠` remains governance guidance only until reviewed by real jurisdiction-specific legal counsel.*
