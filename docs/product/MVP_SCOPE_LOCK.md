# MVP Scope Lock — SlipGuard v1.0

| Field | Value |
|---|---|
| Commission | `PO-MVP-002`, Phase 2 |
| Predecessor | `PO-MVP-001` Phase 1 — `docs/product/MVP_CURRENT_STATE_AUDIT.md` |
| Purpose | Define, freeze, and document the exact feature set of SlipGuard MVP v1.0 |
| Status of this document | **Draft for Product Office decision — NOT FROZEN.** See the "Product Office Decision Required" markers throughout. Per this commission's own "Separate Observation from Decision" principle, Engineering has not silently resolved the items that are genuine business/product calls; every remaining classification below is Engineering's evidence-grounded *proposal*, not a self-authorized final answer. **Additionally pending `AO-MVP-005` (Architecture Office review, `docs/architecture/AO-MVP-005-REFERENCE-DATA-OWNERSHIP.md`) on the Manual Intake / Capability B reference-data boundary** — per direct Product Office instruction, this document must not be frozen until that review is accepted. **One item is no longer open:** Capability B's launch visibility (§4.14) has been decided by direct Product Office/founder instruction — `PO-MVP-004`, 2026-08-03, `docs/00-governance/DECISION_LOG.md` — and this document has been updated throughout to match. This document becomes the binding MVP Contract only once every remaining marked item is resolved. |

---

# Section 1 — Executive Summary

SlipGuard MVP v1.0 is a deterministic betting decision intelligence platform. A customer uploads or manually enters a betting slip; SlipGuard runs it through a fixed, versioned, mathematically deterministic rule set; the customer receives a structural risk assessment with a plain-language explanation of exactly which factors drove the result, backed by evidence they can inspect. The customer decides what to do next — SlipGuard never predicts an outcome, never promises a safe bet, and never places, adjusts, or automates a wager.

**Intended launch audience:** individual recreational accumulator bettors (the primary persona, "Tunde," per `PROJECT.md`) who currently build multi-leg slips with limited statistical background and want to understand risk before committing, not a prediction of the result.

**Expected user value:** the ability to see, in plain language, why a slip is structurally risky (or not), which single leg contributes the most to that risk, and to record and revisit their own reasoning over time (Journal, History) — without being sold a tip, a system, or a guarantee.

**Launch philosophy:** ship the deterministic core completely and honestly rather than a broader but shallower feature set. Per Phase 1's audit, that core — the risk engine, report, builder, planner, history, journal, demo workspace — is genuinely complete and independently verified. MVP does not attempt to be a full-service betting companion, a social platform, a prediction tool, or a multi-market intelligence terminal in this release.

**What MVP intentionally does not attempt:** outcome prediction of any kind; automated bet placement or bookmaker account integration (permanently excluded by `ADR-012`, not merely deferred — see Section 6); community/social features; a mobile application; automated slip reading (OCR); live, continuously-updated market intelligence beyond Capability B's existing, already-scoped implementation (launch-enabled — see Section 4.14); multi-tier commercial packaging (no billing exists yet — see Section 8).

---

# Section 2 — MVP Mission

The first public release exists to prove one thing: that deterministic, evidence-based structural analysis can make a betting slip's risk understandable to an ordinary customer without predicting anything. Concretely, MVP delivers:

- **Deterministic intelligence** — the same slip, evidence, and rule-set version always produce the same result (`app/Domain/Risk`, independently verified against a fresh MySQL database this session).
- **Structural analysis** — leg count, combined odds, individual-odds elevation, risk concentration, and market complexity, each a named, versioned factor (RF-001–RF-005; RF-006 relationship detection is real but permanently held inactive under Rule Set 2026.1 pending a verifiable event-identity source — an honest, disclosed limitation, not a bug).
- **Risk explanation** — every score ships with the specific factor that drove it, in plain language, not just a number (Risk Report screen).
- **Decision support, not decision-making** — the weakest-leg attribution (Planner) and Journal exist to help the customer reason about their own slip; nothing in MVP chooses, ranks, or executes on the customer's behalf.
- **User confidence and trust** — achieved through explainability and evidence, not through UI polish alone; this is why Phase 1 classified the deterministic core as complete before anything commercial.

This section deliberately does not discuss anything beyond v1.0 — see Section 9 for the deferred roadmap.

---

# Section 3 — Complete Feature Inventory

Every implemented capability found in Phase 1's audit, plus the items this commission's own example list named that Phase 1 grouped together (Registration is part of Authentication's Volt flow; there is no "Administration" surface distinct from the Operations Filament panel — confirmed directly against `routes/web.php` for both).

1. Authentication (including Registration)
2. Customer Dashboard
3. Slip Builder (manual entry)
4. Slip Intake — Screenshot Upload
5. Slip Intake — PDF Upload
6. Slip Intake — Paste Text
7. Slip Intake — Bet Code / Share Link (UI present, functionality deferred)
8. Deterministic Rule Engine / Analysis Pipeline
9. Explainability (factor naming/explanation, methodology disclosure)
10. Risk Report
11. History
12. Decision Journal
13. Planner (Capability A — weakest-leg attribution, regeneration, Planning History)
14. Capability B — Market Intelligence / "Build an Accumulator"
15. Demo Workspace
16. SlipGuard Labs
17. Operations Panel (internal Filament — includes Administration, Support Notes, Audit Log)
18. User Profile
19. Settings
20. Help
21. Public Marketing Website (Home, Analyse, Reports, Planner, About, FAQ, Release Notes, Pricing, Contact, Privacy, Terms)
22. Subscription / Billing (not implemented — see Section 4 and Section 8)

Nothing implemented and found in Phase 1 is omitted from this list.

---

# Section 4 — Feature Classification

## Summary Table

| # | Feature | Classification | Product Office Decision Required? |
|---|---|---|---|
| 1 | Authentication (incl. Registration) | **INCLUDED** | No |
| 2 | Dashboard | **INCLUDED** | No |
| 3 | Slip Builder | **INCLUDED** | No |
| 4 | Intake — Screenshot Upload | **INCLUDED (Polish Required)** | No (rationale below) |
| 5 | Intake — PDF Upload | **INCLUDED** | No |
| 6 | Intake — Paste Text | **INCLUDED** | No |
| 7 | Intake — Bet Code / Share Link | **DEFERRED** | No |
| 8 | Deterministic Rule Engine | **INCLUDED** | No |
| 9 | Explainability | **INCLUDED** | No |
| 10 | Risk Report | **INCLUDED** | No |
| 11 | History | **INCLUDED** | No |
| 12 | Decision Journal | **INCLUDED (Polish Required)** | No |
| 13 | Planner (Capability A) | **INCLUDED** | No |
| 14 | Capability B (Market Intelligence) | **INCLUDED, launch-enabled** — decided | No — resolved by `PO-MVP-004` |
| 15 | Demo Workspace | **INTERNAL** | No |
| 16 | SlipGuard Labs | **INCLUDED** | No |
| 17 | Operations Panel | **INTERNAL** | No |
| 18 | Profile | **INCLUDED** | No |
| 19 | Settings | **INCLUDED (bounded RC1 completion)** — decided | No — resolved by direct Product Office instruction, 2026-08-08 |
| 20 | Help | **INCLUDED (bounded RC1 completion)** — decided | No — resolved by direct Product Office instruction, 2026-08-08 |
| 21 | Public Marketing Website | **INCLUDED**, two pages blocked | **Yes** (Privacy/Terms content) |
| 22 | Subscription / Billing | **DEFERRED** *proposed — MVP launches Free-only* | **Yes** |

---

## 1. Authentication (including Registration)

- **Classification:** INCLUDED
- **Evidence:** Real Volt pages — `pages.auth.register`, `pages.auth.login`, `pages.auth.forgot-password`, `pages.auth.reset-password`, `pages.auth.confirm-password` (`routes/auth.php`).
- **Dependencies:** None.
- **Launch visibility:** Public, unauthenticated entry point.
- **Remaining work:** None.
- **Product Office notes:** Email verification is deliberately not required (`MustVerifyEmail` intentionally not applied to `User`) — this is existing, working behaviour, not a gap; confirm this remains acceptable for a paying-customer product before Section 10 sign-off.

## 2. Customer Dashboard

- **Classification:** INCLUDED
- **Evidence:** `resources/views/livewire/dashboard.blade.php`, ~457 lines, real.
- **Dependencies:** None.
- **Launch visibility:** Authenticated customers.
- **Remaining work:** None.

## 3. Slip Builder (manual entry)

- **Classification:** INCLUDED
- **Evidence:** `resources/views/livewire/betting-slips/builder.blade.php`. A real container-width regression was found and fixed this session (commit `e72818e`); current state independently verified correct.
- **Dependencies:** None.
- **Launch visibility:** Authenticated customers.
- **Remaining work:** None.

## 4. Intake — Screenshot Upload

- **Classification:** INCLUDED (Polish Required)
- **Evidence:** The screenshot is stored and shown back to the customer; there is no OCR. The customer manually transcribes it into the Builder.
- **Dependencies:** Real OCR would require an Architecture/Parser/Compliance Office review before implementation — already recorded in this repository's own history (`U-11.3`/`U-11.5A`), not new.
- **Launch visibility:** Authenticated customers.
- **Remaining work:** None required to ship as-is — the current behaviour is honest (nothing claims automated reading that doesn't happen) rather than broken. Marked "Polish Required," not "Product Office Decision Required," because shipping without OCR does not misrepresent the product; it is a real product-quality question for a future version, not a launch blocker. Flagged for Product Office awareness in Section 6/9 regardless.

## 5. Intake — PDF Upload

- **Classification:** INCLUDED
- **Evidence:** `ExtractPdfText` + `CreateDraftSlipFromParsedText` actions genuinely extract and parse text.
- **Dependencies:** None.
- **Remaining work:** None.

## 6. Intake — Paste Text

- **Classification:** INCLUDED
- **Evidence:** Same parsing action as PDF upload, real.
- **Dependencies:** None.
- **Remaining work:** None.

## 7. Intake — Bet Code / Share Link

- **Classification:** DEFERRED
- **Evidence:** UI presents this as a disabled option with an honest "not supported yet" label — no backend implementation.
- **Dependencies:** A real design/build effort, not started.
- **Remaining work:** Full feature — see Section 9 for destination release.

## 8. Deterministic Rule Engine / Analysis Pipeline

- **Classification:** INCLUDED
- **Evidence:** `app/Domain/Risk/*` — six factor classes (RF-001–RF-006), `BigDecimal`-based deterministic arithmetic, the accepted Marginal Structural Contribution weakest-leg model. Independently verified against a fresh MySQL database with real inserts this session.
- **Dependencies:** Risk Rule Set 2026.1 itself still awaits formal Product Office/Data Science sign-off per `TASKS.md`'s own Blocked section — a governance formality on the already-implemented, already-verified mathematics, not a code gap.
- **Remaining work:** None from Engineering; the sign-off itself is the outstanding item (tracked in Section 8, Product).

## 9. Explainability

- **Classification:** INCLUDED
- **Evidence:** Factor naming/explanation and methodology disclosure are real, wired to persisted analysis data (`report.blade.php`'s Main Contributing Factor, Other Contributing Factors, and Methodology sections).
- **Dependencies:** None.
- **Remaining work:** None.

## 10. Risk Report

- **Classification:** INCLUDED
- **Evidence:** `resources/views/livewire/betting-slips/report.blade.php`, 482 lines — headline/band, main factor, supporting factors, data quality, methodology, report details. Verified end-to-end against real MySQL data this session.
- **Dependencies:** None.
- **Remaining work:** None.

## 11. History

- **Classification:** INCLUDED
- **Evidence:** `resources/views/livewire/workspace/history/index.blade.php`, 672 lines, real.
- **Dependencies:** None.
- **Remaining work:** None.

## 12. Decision Journal

- **Classification:** INCLUDED (Polish Required)
- **Evidence:** `journal/index.blade.php` + `journal/entry.blade.php`, real and functional.
- **Dependencies:** None launch-blocking.
- **Remaining work:** `DecisionJournalTest`'s archive-grouping assertion has failed intermittently this session (order/timing-dependent, not consistent) — needs investigation. Not a known customer-facing defect.

## 13. Planner (Capability A)

- **Classification:** INCLUDED
- **Evidence:** `planner/session.blade.php` (871 lines) + `planner/history.blade.php`. Weakest-leg attribution and regeneration-event constraints independently verified with real inserts against MySQL this session.
- **Dependencies:** None.
- **Remaining work:** None.

## 14. Capability B — Market Intelligence / "Build an Accumulator"

- **Classification:** INCLUDED, launch-enabled — **decided** (`PO-MVP-004`, 2026-08-03, direct Product Office/founder instruction, `docs/00-governance/DECISION_LOG.md`)
- **Evidence:** Real, substantial implementation (`market-intelligence/builder.blade.php`, 1,329 lines), currently gated behind `MARKET_WIDE_PLANNER_ENABLED` (default `false` in `.env.example`/`config/slipguard-market-intelligence.php`) — the route itself doesn't register when the flag is off. Its own code comment previously stated it was "internal-only" and, per `U-17.4`, "not authorized for public launch"; that restriction is superseded by `PO-MVP-004`.
- **Dependencies:** None further for the *inclusion* decision itself — the formal Product Office capability-approval decision the code's own comments named as a precondition has now been given. Flipping `MARKET_WIDE_PLANNER_ENABLED` to `true` for a real launch is gated on the normal MVP release quality gates (Section 10), not on a further product-approval step.
- **Launch visibility:** Public, subject to the same launch-readiness gates as every other INCLUDED feature (Section 10) — no longer permanently flag-suppressed.
- **Remaining work:** Engineering to perform a gap analysis between the current implementation and the approved **Intelligence Builder Workspace** design direction (`docs/01-product/PO-U18.4.1-001-INTELLIGENCE-BUILDER-WORKSPACE-HANDOVER.md`, adopted `PO-MVP-004`), bucketing findings into what is required before MVP launch, what ships as an immediate post-launch enhancement, and what is longer-term architecture work. The existing, already-built Builder is the shipping foundation — the workspace evolution is additive refinement, not a launch blocker by default; the gap analysis is what determines whether any part of it *is* launch-blocking.
- **Product Office Decision Required:** None — resolved.
- **Pending Architecture Review:** `AO-MVP-005` (`docs/architecture/AO-MVP-005-REFERENCE-DATA-OWNERSHIP.md`) resolves a related but distinct question — whether guided Competition/Team/Fixture selection for the *plain Manual Builder* (not this capability) is architecturally dependent on Capability B. Its recommendation: no, not architecturally, but no independent path exists yet either — Manual Intake ships MVP v1.0 with structured Sport/Market only (already reflected in §4), free text for Competition/Team/Fixture, with a shared Reference Data capability recommended as the future target rather than Capability B absorbing this scope. This does not change Capability B's own classification above.

## 15. Demo Workspace

- **Classification:** INTERNAL
- **Evidence:** `php artisan slipguard:demo` — verified end-to-end against MySQL this session (30 slips, 12 journal entries, 18 Market Intelligence fixtures, confirmed idempotent).
- **Dependencies:** None.
- **Launch visibility:** Not customer-facing — a seeding command used for sales/demo/screenshot purposes, gated by `SLIPGUARD_DEMO_ENABLED` in any non-local environment.
- **Remaining work:** None.

## 16. SlipGuard Labs

- **Classification:** INCLUDED
- **Evidence:** `labs/index.blade.php`, guest-visible; "Notify Me"/"Join Beta" gated to authenticated customers inside the component. Real, not a stub.
- **Dependencies:** None.
- **Launch visibility:** Public (guests and customers).
- **Remaining work:** None — functions correctly today as a feature-interest/positioning page, not a commerce surface.

## 17. Operations Panel (internal Filament — Administration, Support Notes, Audit Log)

- **Classification:** INTERNAL
- **Evidence:** `CustomerResource`, `LabsFeatureResource`, `AuditLogResource`, with `SupportNotesRelationManager`, `PlannerSessionsRelationManager`, `BettingSlipsRelationManager`, `AnalysesRelationManager` — 18 real PHP files under `app/Filament`, not stubs. This is the repository's only "Administration" surface; there is no separate customer-facing admin feature.
- **Dependencies:** None.
- **Launch visibility:** Internal staff only.
- **Remaining work:** None.

## 18. User Profile

- **Classification:** INCLUDED
- **Evidence:** `/profile`, a real, functional Breeze-derived view (name/email/password management).
- **Dependencies:** None.
- **Remaining work:** None.

## 19. Settings

- **Classification:** INCLUDED (bounded RC1 completion) — **decided**, superseding the deferral proposed here
- **Decision:** Direct Product Office instruction, 2026-08-08 (following `PO-RC1-009`'s acceptance): the deferral above is superseded because Settings is "already exposed as authenticated product destination[s]" — a real nav entry and route already reachable by every customer, not an unbuilt surface. Explicitly bounded: fill the existing destination with real content; **do not expand it into new product capability** (no notification preferences, theme default, data export, or other net-new functionality authorised by this decision alone).
- **Evidence:** `routes/web.php` routes `/settings` to the generic, unimplemented `coming-soon` view — still the case; content is a separate, not-yet-started bounded commission.
- **Remaining work:** Scoped and delivered as a separate bounded commission (not this document, not yet started as of this entry).

## 20. Help

- **Classification:** INCLUDED (bounded RC1 completion) — **decided**, superseding the deferral proposed here
- **Decision:** Direct Product Office instruction, 2026-08-08 (following `PO-RC1-009`'s acceptance): same reasoning and bound as Settings above — Help is already a real, reachable nav destination, so its stub content is now in scope to complete, but net-new capability beyond real content (e.g. FAQ linkage, which already exists publicly at `/faq`) is not authorised by this decision alone.
- **Evidence:** Same generic `coming-soon` view as Settings — still the case; content is a separate, not-yet-started bounded commission.
- **Remaining work:** Scoped and delivered as a separate bounded commission (not this document, not yet started as of this entry).

## 21. Public Marketing Website

- **Classification:** INCLUDED, with two pages blocked
- **Evidence:** Home (~1,050 lines), `/analyse`, `/reports`, `/planner`, `/about`, `/faq`, `/release-notes` are all real, substantive content, extensively verified this session. `/privacy` and `/terms` route to `public-coming-soon.blade.php` (confirmed directly in `routes/web.php`) — explicit, honest stubs, not broken pages. `/pricing` and `/contact` are also stubs, downstream of the pricing and support-channel decisions below.
- **Dependencies:** Real legal review (Compliance Office / external counsel) for Privacy/Terms content.
- **Launch visibility:** Public.
- **Remaining work:** Small from Engineering once real legal text exists (a content swap); the blocking dependency is legal authorship, not implementation.
- **Product Office Decision Required:** Commission real Privacy Policy and Terms of Service content. Already flagged in this repository's own `U-13.0` governance entry — not new information.

## 22. Subscription / Billing

- **Classification:** DEFERRED — **proposed: MVP launches Free-only, single tier** — **not decided**
- **Evidence:** No billing/subscription package exists anywhere in `composer.json` (no Cashier, no Stripe SDK). No `Subscription`/`Plan`/`Billing` model exists. `/pricing` is an explicit stub: "No pricing model has ever been approved by Product Office... No price has been set."
- **Important distinction, not a blocker to resolving this:** `ADR-012`'s "customer money never enters SlipGuard" Locked Decision governs betting funds (deposits, wallets, stakes, winnings) between customer and bookmaker. It does not prohibit SlipGuard from charging its own honest SaaS subscription fee for the analysis product itself — a standard third-party billing integration for that fee is architecturally compatible with existing Locked Decisions and simply hasn't been designed yet.
- **Dependencies:** A real, Product-Office-approved pricing model (a business decision, not an engineering one) must exist before any billing architecture is designed; then an Architecture Office decision on the billing integration; then likely a Compliance Office review; then implementation.
- **Remaining work:** Large — not sizeable in engineering days until the pricing model and billing architecture decisions exist upstream.
- **Product Office Decision Required:** Confirm MVP v1.0 launches as a single free tier with no payment collection at all (Engineering's proposed default, since nothing else is currently buildable without upstream decisions), or provide the pricing model needed to scope a Premium tier before launch.

---

# Section 5 — MVP User Journey

Every stage below references a feature classified in Section 4; none are hypothetical.

```
Visitor
  ↓  (Public Marketing Website — §4.21)
Landing Page
  ↓  (Authentication — §4.1)
Registration
  ↓
Dashboard
  ↓  (§4.2)
Slip Builder
  ↓  (§4.3)
Upload (Screenshot / PDF / Paste — §4.4–§4.6)
  ↓
Analysis (Deterministic Rule Engine — §4.8)
  ↓
Risk Report (Explainability — §4.9, §4.10)
  ↓
Decision Journal (§4.12)
  ↓
History (§4.11)
  ↓
Planner (§4.13)
  ↓
Return Session (Dashboard → History/Journal/Planner, all §4.2/§4.11–13)
```

Every stage in this journey is currently INCLUDED or INCLUDED (Polish Required) — there is no point in the core customer journey that depends on a DEFERRED or Product-Office-pending item. The pending items (Subscription, Privacy/Terms content) sit outside this core loop, not inside it. (Capability B, Settings, and Help are no longer pending — see §4.14/§4.19/§4.20; Settings/Help content delivery is scoped as separate, not-yet-started bounded commissions, not a gap in this core loop.)

---

# Section 6 — Not Included in MVP

- **OCR automation** for screenshot intake — deferred; manual transcription is the honest v1.0 behaviour (§4.4).
- **Bet Code / share-link ingestion** — deferred, no backend exists (§4.7).
- **Mobile applications** — no implementation exists anywhere in this repository; not part of this audit's scope because nothing was found to classify.
- **Browser extensions** — same; no implementation exists.
- **Live, continuously-updated market intelligence beyond Capability B's existing, already-scoped implementation** — Capability B itself now ships (§4.14), but this is the existing implementation, not an expanded live-data product; a broader live-intelligence product is a distinct, not-yet-scoped future item.
- **Automated bookmaker integrations** — **permanently excluded**, not merely deferred. This is a Locked Decision (`ADR-012`): "no autonomous betting — no auto-placement, auto-stake-increase, auto-loss-chasing, auto-cashout... every wager requires a conscious customer action." This item does not appear in Section 9's deferred roadmap because it is not a future release candidate under current governance — it would require a constitutional decision to reverse, not a roadmap slot.
- **Social/community features** — no implementation exists; not part of Phase 1's audit findings.
- **AI-assisted recommendations beyond approved scope** — `CLAUDE.md`'s Locked Decisions already bound this precisely: "AI may explain verified findings only; AI never determines suitability, ranking, confidence, selection order, correlation, or planner/risk outputs." Nothing in the current implementation exceeds this boundary, and MVP does not propose to.
- **Enterprise features** — no implementation exists; not part of Phase 1's audit findings.
- **Subscription/Premium tier** — proposed deferred, pending Product Office pricing decision (§4.22).

(Settings and Help real content are no longer in this section — decided INCLUDED, bounded RC1 completion, direct Product Office instruction 2026-08-08; see §4.19/§4.20. Delivery itself remains separate, not-yet-started bounded commissions.)

---

# Section 7 — Premium Boundary

No feature appears in more than one category below.

## Free

Every INCLUDED and INCLUDED (Polish Required) item from Section 4: Authentication, Dashboard, Slip Builder, all three working intake methods (Screenshot, PDF, Paste), the Deterministic Rule Engine, Explainability, Risk Report, History, Decision Journal, Planner (Capability A), Capability B (Market Intelligence, §4.14 — decided `PO-MVP-004`), SlipGuard Labs, Profile, Settings (§4.19 — decided, direct Product Office instruction 2026-08-08), Help (§4.20 — same), the Public Marketing Website.

## Premium

**None exist today.** No feature in this repository is currently gated behind a paid tier — there is no billing system to gate anything with (§4.22). This section is intentionally empty pending the Section 4.22 pricing decision; populating it before that decision would fabricate a commercial structure that does not exist.

## Internal

Demo Workspace (§4.15), Operations Panel including Administration/Support Notes/Audit Log (§4.17).

## Future

Bet Code/share-link ingestion (§4.7); OCR automation (§4.4); any eventual Premium tier once pricing is approved (§4.22); Intelligence Builder Workspace evolution phases the pending gap analysis (§4.14) buckets as post-launch or longer-term architecture. See Section 9 for named destination releases. (Settings and Help real content are no longer here — decided INCLUDED, bounded RC1 completion, §4.19/§4.20 — though delivery itself is a separate, not-yet-started bounded commission.)

---

# Section 8 — Launch Blockers

## Engineering

| Item | Description | Owner | Impact | Severity | Resolution Required | Blocks Launch? |
|---|---|---|---|---|---|---|
| `DecisionJournalTest` flakiness | Archive-grouping assertion fails intermittently | Engineering | Test-suite noise; no known customer-facing defect | Low | Root-cause and fix | No |

No other outstanding Engineering-owned implementation work was found blocking the core journey (Section 5).

## Product

| Item | Description | Owner | Impact | Severity | Resolution Required | Blocks Launch? |
|---|---|---|---|---|---|---|
| Pricing model | No pricing has ever been approved | Product Office | Blocks any commercial launch | Critical | Approve a pricing model (or confirm Free-only v1.0) | **Yes, for a paid launch. No, for a Free-only launch.** |
| Packaging | Premium boundary is currently empty (§7) | Product Office | Same as above | Critical | Same as above | Same as above |
| Capability B approval | Real feature, launch decision resolved by `PO-MVP-004` | Product Office | Resolved — §4.14 ships visible, subject to normal quality gates | Low | None outstanding on the approval itself; Engineering gap analysis (§4.14) may surface its own items | No — decision made |
| Risk Rule Set 2026.1 sign-off | Mathematics implemented and verified; formal sign-off outstanding | Product Office / Data Science | Governance formality on already-shipped mathematics | Low | Formal sign-off | No |

## Compliance

| Item | Description | Owner | Impact | Severity | Resolution Required | Blocks Launch? |
|---|---|---|---|---|---|---|
| Privacy Policy | Currently an honest stub | Compliance / Legal | Cannot legally launch to real customers without one | Critical | Real legal authorship, then an Engineering content swap | **Yes** |
| Terms of Service | Currently an honest stub | Compliance / Legal | Same | Critical | Same | **Yes** |
| Licensing/regulatory documentation | Not assessed in this audit — outside repository evidence | Compliance | Unknown | Unknown | Compliance Office to confirm scope | Unknown — flagged, not assumed |

## Data

No external data-provider dependency exists in the core MVP journey (Section 5) — the deterministic engine operates on customer-entered/uploaded data only. Capability B (§4.14) is the only feature with external market-data dependencies, and it is proposed launch-disabled. No Data-owned blocker was found for the core MVP as scoped.

## Infrastructure

Outside this document's evidence base — Phase 1/Phase 2 covered application-layer repository state, not deployment/monitoring/backup infrastructure. Not assessed here; flagged as an open category for Infrastructure/Operations Office input before Section 10 sign-off, not silently assumed complete or incomplete.

## Commercial

| Item | Description | Owner | Impact | Severity | Resolution Required | Blocks Launch? |
|---|---|---|---|---|---|---|
| Billing implementation | No package, no model exists | Engineering (after Product/Architecture decisions) | Cannot collect payment | Critical for a paid launch | Pricing decision → Architecture billing design → implementation | **Yes, for a paid launch. No, for a Free-only launch.** |
| Payment gateway | None selected or integrated | Architecture / Product | Same | Critical for a paid launch | Same | Same |
| Regional pricing / currencies | Not addressed — no pricing exists at all yet | Product | N/A until base pricing exists | N/A | Downstream of the base pricing decision | No, until pricing exists |

---

# Section 9 — Deferred Roadmap

**Settings and Help (real content) removed from this table** (direct Product Office instruction, 2026-08-08, following `PO-RC1-009`'s acceptance) — both were previously destined for Version 1.1 here; superseding §4.19/§4.20's deferral moved them to bounded RC1 completion instead, so they are no longer part of the *deferred* roadmap. Delivery remains a separate, not-yet-started bounded commission — not re-added to Section 4 as complete.

| Feature | Destination |
|---|---|
| OCR automation for screenshot intake | Version 1.1 |
| Bet Code / share-link ingestion | Version 1.1 |
| Intelligence Builder Workspace evolution items the §4.14 gap analysis defers | Version 1.1 or later, per the gap analysis's own bucketing |
| Subscription / Premium tier | Version 1.1 (pending Product Office pricing decision — this is a *proposed* destination, contingent on that decision existing at all) |
| Expanded live market intelligence beyond current Capability B scope | Version 1.2 |
| Mobile application | Version 2 |
| Browser extension | Version 2 |
| **Automated bookmaker integration** | **Not on this roadmap at any version** — permanently excluded by the `ADR-012` Locked Decision (see Section 6). Would require a constitutional decision reversal, not a version bump. |

---

# Section 10 — Launch Acceptance Checklist

## Engineering
- [x] Core MVP journey (Section 5) code complete
- [x] Targeted test coverage passing (106/106, 416 assertions, per Phase 1)
- [ ] Full suite green with zero unexplained failures (currently 2–3 pre-existing, tracked failures — see `DecisionJournalTest` above and the 2 logo-motion assertions from `I-01.2`'s verification)
- [ ] `DecisionJournalTest` flakiness resolved

## UX
- [ ] Final review complete for the frozen MVP scope specifically (not yet performed against this document)
- [ ] Accessibility verified for the frozen scope
- [ ] Responsive behaviour verified for the frozen scope
- [ ] Human-Designed Experience Standard's five release gates satisfied for every INCLUDED screen

## Product
- [ ] MVP scope frozen (this document approved, not merely drafted)
- [ ] Premium boundary approved (Section 7 — currently empty, pending pricing)
- [x] Capability B decision recorded (Section 4.14) — `PO-MVP-004`, 2026-08-03: INCLUDED, launch-enabled
- [ ] Capability B gap analysis complete (Section 4.14 — determines any remaining pre-launch items)
- [x] Settings/Help scope decision recorded (Section 4.19/4.20) — direct Product Office instruction, 2026-08-08: INCLUDED, bounded RC1 completion (content delivery itself remains a separate, not-yet-started bounded commission)

## Compliance
- [ ] Privacy Policy approved
- [ ] Terms of Service approved
- [ ] Required disclosures complete (scope to be confirmed by Compliance Office)

## Data
- [ ] N/A for core MVP (no external provider dependency in the frozen scope) — confirm this remains true if Capability B is enabled

## Infrastructure
- [ ] Production environment configured — not assessed in this audit
- [ ] Monitoring active — not assessed
- [ ] Logging active — not assessed
- [ ] Backups verified — not assessed
- [ ] Disaster recovery documented — not assessed

## Commercial
- [ ] Pricing approved (or Free-only v1.0 explicitly confirmed)
- [ ] Subscription model approved (or explicitly deferred)
- [ ] Billing implemented (or explicitly not required for v1.0)
- [ ] Upgrade flow verified (or explicitly not applicable to v1.0)

---

# Section 11 — Final MVP Contract

**Pending Product Office approval of the items marked "Decision Required" throughout this document, SlipGuard MVP v1.0 is defined as follows:**

**What ships:** Authentication and Registration; the Customer Dashboard; the Slip Builder; three working intake methods (Screenshot upload, PDF upload, Paste text — screenshot without automated OCR, disclosed honestly); the complete deterministic rule engine and its explainability layer; the Risk Report; History; the Decision Journal; the Planner (Capability A, including Planning History); Capability B (Market Intelligence / "Build an Accumulator" — launch-enabled per `PO-MVP-004`, subject to the same quality gates as every other INCLUDED feature); SlipGuard Labs; the customer Profile; and the public marketing website, with Privacy Policy and Terms of Service content pending real legal authorship.

**What is intentionally excluded:** Bet Code/share-link ingestion, OCR automation, mobile applications, browser extensions, social/community features, enterprise features, and any AI-assisted recommendation beyond the constitutionally-bound "explain verified findings only" boundary already in force.

**What is permanently excluded, not merely deferred:** automated bookmaker integration and any form of autonomous betting, per the `ADR-012` Locked Decision.

**What is disabled at launch:** Nothing feature-complete is disabled by product decision. Capability B's `MARKET_WIDE_PLANNER_ENABLED` flag is flipped to launch-enabled subject to the same release-quality gates (Section 10) as every other INCLUDED feature, and subject to whatever the pending gap analysis (§4.14) finds must land before launch versus post-launch.

**What is Premium:** nothing, currently — no billing mechanism exists, so no feature can be commercially gated yet. MVP v1.0 is proposed to launch as a single free tier unless the Product Office supplies a pricing model in time to scope, design, and build a billing integration before launch.

**Conditions that must be met before launch**, in addition to Product Office approval of this document:
1. A real Privacy Policy and Terms of Service (Compliance/Legal-authored).
2. A Product Office decision on pricing — even if that decision is "Free-only for v1.0."
3. ~~A Product Office decision on Capability B's launch visibility.~~ Resolved — `PO-MVP-004`, 2026-08-03: INCLUDED, launch-enabled.
4. ~~A Product Office decision on Settings/Help's MVP scope.~~ Resolved — direct Product Office instruction, 2026-08-08: INCLUDED, bounded RC1 completion (content delivery itself remains a separate, not-yet-started bounded commission).
5. Infrastructure/Operations readiness (production environment, monitoring, backups, disaster recovery) — not assessed in this document and required before public launch regardless of feature scope.
6. Resolution of the `DecisionJournalTest` flakiness.
7. Completion of the Engineering gap analysis between the current Capability B implementation and the approved Intelligence Builder Workspace design direction (§4.14), and delivery of whatever it buckets as pre-launch.

**Evidence demonstrating readiness of everything already decided:** `docs/product/MVP_CURRENT_STATE_AUDIT.md` (Phase 1), this document's Section 4 (per-feature evidence), and `docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md` (platform-layer verification the whole MVP journey was exercised against).

This document is Engineering's evidence-grounded proposal for the MVP Contract. It becomes binding — "no feature may be added to or removed from MVP without an explicit Product Office decision" — only once the Product Office confirms or amends the remaining items marked "Decision Required" above (two of the original six — Capability B's launch visibility and Settings/Help's MVP scope — are now resolved, per items 3 and 4 above).
