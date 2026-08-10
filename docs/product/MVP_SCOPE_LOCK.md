# MVP Scope Lock — SlipGuard v1.0

| Field | Value |
|---|---|
| Commission | `PO-MVP-002`, Phase 2 |
| Predecessor | `PO-MVP-001` Phase 1 — `docs/product/MVP_CURRENT_STATE_AUDIT.md` |
| Purpose | Define, freeze, and document the exact feature set of SlipGuard MVP v1.0 |
| Status of this document | **FROZEN — `PO-MVP-FREEZE-001`, 2026-08-10.** Product Office has formally frozen the SlipGuard MVP v1.0 capability baseline described in this document. `AO-MVP-005` (Architecture Office review — a genuinely different document from the `docs/architecture/AO-MVP-005-REFERENCE-DATA-OWNERSHIP.md` proposal this line previously referenced; the actual review is recorded in `TASKS.md`/`CHANGELOG.md` and `docs/00-governance/DECISION_LOG.md`) returned **B — ARCHITECTURE CLEARED WITH CONDITIONS**; both conditions (`AO-D1`, `AO-D3`) were closed by `PO-RC1-013` before this freeze. Risk Rule Set 2026.1 is formally signed off as of this freeze (§9 below) — see `docs/00-governance/DECISION_LOG.md`. See **Section 12 — MVP Freeze Record** for the complete freeze declaration, what remains as Launch Staging work, and the enforcement rule for future changes. Historical text throughout the rest of this document that describes earlier "Decision Required"/pending states is preserved as the real record of how each item was resolved, not rewritten — per this freeze's own "do not rewrite historical decisions" instruction. |

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
| 21 | Public Marketing Website | **INCLUDED** — decided (Privacy/Terms live, `PO-CO-002`) | No — external legal sign-off is a Launch Staging item, not a Product Office content decision |
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
- **Evidence:** Real, substantial implementation (`market-intelligence/builder.blade.php`, 1,329 lines), gated behind `MARKET_WIDE_PLANNER_ENABLED`. **Corrected at freeze (`AO-D3`/`PO-RC1-013`):** this row previously stated the flag defaults `false` in `.env.example` — stale; both `.env` and `.env.example` now correctly set it `true`, and `config/slipguard-market-intelligence.php`'s own comment was corrected in the same pass (the `env(..., false)` code-level fallback remains, deliberately, as a safe default for any environment that omits the variable entirely — unrelated to this launch-visibility decision). Its own code comment previously stated it was "internal-only" and, per `U-17.4`, "not authorized for public launch"; that restriction is superseded by `PO-MVP-004`.
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

- **Classification:** INCLUDED
- **Evidence:** Home (~1,050 lines), `/analyse`, `/reports`, `/planner`, `/about`, `/faq`, `/release-notes` are all real, substantive content, extensively verified this session. **Corrected at freeze:** this row previously stated `/privacy` and `/terms` route to `public-coming-soon.blade.php` — stale since `PO-CO-002` (2026-08-04, `docs/00-governance/DECISION_LOG.md`), which shipped real, jurisdiction-neutral Terms of Service (`resources/views/pages/terms.blade.php`, 156 lines) and Privacy Policy (`resources/views/pages/privacy.blade.php`, 132 lines), both live at their real routes — confirmed directly in `routes/web.php` and both files at freeze. `PO-CO-002`'s own recommendation was **CERTIFIED WITH CONDITIONS**, not unconditional: qualified external legal counsel sign-off, a Product Office launch-market decision (or explicit market-agnostic confirmation), and a real support contact channel remain genuine external items — a **Launch Staging gate**, not an MVP engineering gap (§36 of this freeze's own commissioning instruction). `/pricing` and `/contact` — `/contact` is real and complete (`PO-U22-001`, live form, persistence, internal triage); `/pricing` remains a stub, downstream of the pricing decision (§4.22 below).
- **Dependencies:** Qualified legal counsel review of the live Terms/Privacy content remains outstanding (Launch Staging, not MVP engineering).
- **Launch visibility:** Public.
- **Remaining work:** None from Engineering on Privacy/Terms — content is live. `/pricing` content remains downstream of §4.22's commercial decision.
- **Product Office Decision Required:** None on Privacy/Terms content itself (delivered, `PO-CO-002`) — external legal sign-off and a launch-market decision remain, carried forward as Launch Staging items (Section 12).

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

**Resolved at freeze.** `DecisionJournalTest`'s intermittent archive-grouping flakiness did not reproduce across repeated runs during `PO-RC1-012`'s verification pass (2026-08-10) or since; downgraded from a tracked blocker to monitored, consistent with `PO-RC1-002`'s own earlier reconfirmation. No Engineering-owned implementation work blocking the core journey (Section 5) remains open at freeze.

## Product

| Item | Description | Owner | Impact | Severity | Resolution Required | Blocks Launch? |
|---|---|---|---|---|---|---|
| Pricing model | No pricing has ever been approved | Product Office | Blocks any commercial launch | Critical | Approve a pricing model (or confirm Free-only v1.0) | **Yes, for a paid launch. No, for a Free-only launch — carried forward to Launch Staging, not an MVP freeze blocker.** |
| Packaging | Premium boundary is currently empty (§7) | Product Office | Same as above | Critical | Same as above | Same as above |
| Capability B approval | Real feature, launch decision resolved by `PO-MVP-004` | Product Office | Resolved — §4.14 ships visible, subject to normal quality gates | Low | Resolved | No — decision made |
| Risk Rule Set 2026.1 sign-off | Mathematics implemented and verified | Product Office / Data Science | **Formally signed off at this freeze (`PO-MVP-FREEZE-001`, 2026-08-10) — see Section 12.** | — | None | No — resolved |

## Compliance

**Resolved at freeze, with genuine external conditions carried forward.** Real, jurisdiction-neutral Privacy Policy and Terms of Service content is live (`PO-CO-002`, 2026-08-04) — this row previously described both as honest stubs, which is now stale; corrected here rather than left contradictory. `PO-CO-002`'s own recommendation was **CERTIFIED WITH CONDITIONS**: qualified external legal counsel sign-off, a Product Office launch-market naming decision (or explicit market-agnostic confirmation), and a real support contact channel remain genuinely external and are carried forward as Launch Staging items (Section 12) — not MVP engineering gaps. Licensing/regulatory documentation beyond `CO-MVP-001`'s certification remains a Compliance Office/external-counsel matter, same carry-forward.

## Data

No external data-provider dependency exists in the core MVP journey (Section 5) — the deterministic engine operates on customer-entered/uploaded data only. Capability B (§4.14) is the only feature with external market-data dependencies, and it now ships launch-enabled per `PO-MVP-004`. No Data-owned blocker was found for the core MVP as scoped.

## Infrastructure

**Not an MVP freeze blocker — carried forward to Launch Staging in full** (Section 12), per this freeze's own explicit instruction that production environment, monitoring, backups, mail, HTTPS, and deployment procedure are Launch Staging matters, not reasons to reopen MVP engineering scope. `docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md` and `docs/08-operations/RC1_OPERATIONS_PROGRAMME.md` already record real, concrete findings here (e.g. zero queued jobs exist, one scheduled backup command exists and is production-safe but has no cron wiring yet) — not re-assessed in this freeze.

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
- [x] Targeted test coverage passing (806 tests, 799 passed, 7 self-skipped by design, 0 failed, at freeze — `PO-RC1-013`)
- [x] Full suite green with zero unexplained failures (the 7 skips are `MySqlIntegrationTest.php`'s deliberate MySQL-only self-skip, independently re-run and confirmed 7/7 passing against real MySQL during `PO-RC1-012`)
- [x] `DecisionJournalTest` flakiness — did not reproduce across repeated runs at freeze; monitored, not blocking

## UX
- [x] Final review complete for the frozen MVP scope — `PO-RC1-012` (Final MVP RC1 Release Verification), real end-to-end journeys driven live across every INCLUDED capability
- [x] Accessibility verified for the frozen scope — dark-theme axe-core pass across 7 authenticated screens plus the Product Office Dark Theme Text Legibility amendment; known `.atmosphere`/axe measurement limitation understood and excluded, not treated as a false defect
- [x] Responsive behaviour verified for the frozen scope — 390/430/768/1024/1440px, light/dark, across the authenticated shell and public website (`PO-U24-004`, `PO-U24-005`, Dark Theme Amendment)
- [x] Human-Designed Experience Standard's release gates — satisfied by the accumulated, individually-verified UX commissions this baseline is built from (`PO-U24-001` through `PO-U24-005`); not re-litigated as a separate pass at freeze

## Product
- [x] MVP scope frozen — this document, `PO-MVP-FREEZE-001`, 2026-08-10 (Section 12)
- [ ] Premium boundary approved (Section 7 — currently empty, pending pricing) — **Launch Staging item, not an MVP freeze blocker**
- [x] Capability B decision recorded (Section 4.14) — `PO-MVP-004`, 2026-08-03: INCLUDED, launch-enabled
- [x] Capability B MVP boundary frozen (Section 12) — current implementation is the MVP baseline; Intelligence Builder Workspace evolution is explicitly future work, not a freeze blocker
- [x] Settings/Help scope decision recorded (Section 4.19/4.20) — direct Product Office instruction, 2026-08-08: INCLUDED, bounded RC1 completion, content delivered (`PO-U24-002`, `PO-U24-003`)

## Compliance
- [x] Privacy Policy approved — live, `PO-CO-002` (external qualified-counsel sign-off remains a Launch Staging item, Section 12)
- [x] Terms of Service approved — live, `PO-CO-002` (same carry-forward)
- [x] Required disclosures complete — `CO-MVP-001`'s certification (age statement, Responsible Gambling signposting, copyright notice) confirmed live in the public footer, per `PO-RC1-002`

## Data
- [x] N/A for core MVP (no external provider dependency in the frozen core journey) — confirmed still true with Capability B enabled: its external market-data dependency (The Odds API) is isolated to that one capability, not the core deterministic analysis path

## Infrastructure
**Explicitly not an MVP freeze blocker — Launch Staging in full (Section 12).** Real findings exist (`docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md`, `RC1_OPERATIONS_PROGRAMME.md`: zero queued jobs, one production-safe scheduled backup command with no cron wiring yet, `s3` disk configured but unused), not re-verified here.
- [ ] Production environment configured — Launch Staging
- [ ] Monitoring active — Launch Staging
- [ ] Logging active — Launch Staging
- [ ] Backups verified — Launch Staging (command exists and is production-safe; a real restore drill is blocked on a DB admin credential not available in this environment)
- [ ] Disaster recovery documented — Launch Staging

## Commercial
**Explicitly not an MVP freeze blocker — Launch Staging in full (Section 12), per this freeze's own instruction that unresolved commercial packaging does not reopen MVP engineering scope.**
- [ ] Pricing approved (or Free-only v1.0 explicitly confirmed) — Launch Staging
- [ ] Subscription model approved (or explicitly deferred) — Launch Staging
- [ ] Billing implemented (or explicitly not required for v1.0) — Launch Staging
- [ ] Upgrade flow verified (or explicitly not applicable to v1.0) — Launch Staging

---

# Section 11 — Final MVP Contract

**This section is now historical record, superseded by Section 12's formal freeze declaration (`PO-MVP-FREEZE-001`, 2026-08-10) — preserved as-is below to show how each item was actually resolved, not rewritten.**

**What ships (frozen):** Authentication and Registration; the Customer Dashboard; the Slip Builder; three working intake methods (Screenshot upload, PDF upload, Paste text — screenshot without automated OCR, disclosed honestly); the complete deterministic rule engine and its explainability layer (Risk Rule Set 2026.1, formally signed off — Section 12); the Risk Report; History; the Decision Journal; the Planner (Capability A, including Planning History); Capability B (Market Intelligence / "Build an Accumulator", including its Conversational Entry Layer — launch-enabled per `PO-MVP-004`); SlipGuard Labs; the customer Profile; Settings; Help & Methodology; Contact; and the public marketing website, including live Privacy Policy and Terms of Service content (`PO-CO-002`).

**What is intentionally excluded:** Bet Code/share-link ingestion, OCR automation, mobile applications, browser extensions, social/community features, enterprise features, Risk Watch, the External Intelligence Platform, SlipGuard Live, and any AI-assisted recommendation beyond the constitutionally-bound "explain verified findings only" boundary already in force. See Section 12's Explicit MVP Exclusions for the complete list.

**What is permanently excluded, not merely deferred:** automated bookmaker integration and any form of autonomous betting, per the `ADR-012` Locked Decision.

**What is disabled at launch:** Nothing feature-complete. Capability B's `MARKET_WIDE_PLANNER_ENABLED` flag is launch-enabled (`true` in both `.env` and `.env.example`) subject to the same release-quality gates as every other INCLUDED feature.

**What is Premium:** nothing, currently — no billing mechanism exists. Final Free-vs-Paid packaging is an explicit Launch Staging decision (Section 12), not reopened MVP engineering scope.

**Conditions that were resolved before this freeze:**
1. ~~A real Privacy Policy and Terms of Service.~~ Resolved — `PO-CO-002`, 2026-08-04: live (external qualified-counsel sign-off carried forward as a Launch Staging item).
2. ~~A Product Office decision on Capability B's launch visibility.~~ Resolved — `PO-MVP-004`, 2026-08-03: INCLUDED, launch-enabled.
3. ~~A Product Office decision on Settings/Help's MVP scope.~~ Resolved — direct Product Office instruction, 2026-08-08: INCLUDED, bounded RC1 completion, content delivered (`PO-U24-002`, `PO-U24-003`).
4. ~~Resolution of the `DecisionJournalTest` flakiness.~~ Did not reproduce across repeated runs at freeze (`PO-RC1-012`) — monitored, not blocking.
5. ~~Formal Risk Rule Set 2026.1 sign-off.~~ Resolved at this freeze — Section 12.
6. ~~Architecture clearance.~~ Resolved — `AO-MVP-005` (B — cleared with conditions) + `PO-RC1-013` (both conditions closed).

**Carried forward as Launch Staging, explicitly not MVP freeze blockers** (Section 12): a Product Office pricing decision; infrastructure/Operations readiness (production environment, monitoring, backups, disaster recovery); qualified external legal counsel sign-off on Privacy/Terms; a launch-market naming decision; a real support contact channel; provisioning an isolated MySQL `_test` database for the seven MySQL integration tests; registration throttling.

**Evidence demonstrating readiness:** `docs/product/MVP_CURRENT_STATE_AUDIT.md` (Phase 1), this document's Section 4 (per-feature evidence), `docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md`, and this session's `PO-RC1-012` (live end-to-end verification) and `AO-MVP-005`/`PO-RC1-013` (architecture review and closure) — recorded in full in `TASKS.md`/`CHANGELOG.md` and `docs/00-governance/DECISION_LOG.md`.

This document is now the binding MVP Contract: no feature may be added to or removed from the frozen MVP baseline without an explicit new Product Office commission, per Section 12's enforcement rule.

---

# Section 12 — MVP Freeze Record (`PO-MVP-FREEZE-001`, 2026-08-10)

## 12.1 Freeze Declaration

**SlipGuard MVP v1.0's implemented capability set is formally frozen as of 2026-08-10, branch `develop`.** The exact commit hash is recorded in `TASKS.md`/`CHANGELOG.md` and `docs/00-governance/DECISION_LOG.md` for this entry, since a commit cannot cite its own hash inside itself. From this point, **changes to the frozen MVP baseline require a new explicit Product Office commission** — Launch Staging work (deployment, infrastructure, security hardening, legal/commercial configuration, launch-surface work, and genuine production defect/release-blocking corrections) is authorized without reopening this freeze; casual product expansion during Launch Staging is not.

Architecture Clearance: `AO-MVP-005` (Final MVP Architecture Review) returned **B — ARCHITECTURE CLEARED WITH CONDITIONS**. Both conditions were closed before this freeze:
- **`AO-D1`** (destructive MySQL integration-test isolation) — closed by `PO-RC1-013`. The fail-closed guard (`tests/Support/MySqlTestDatabaseGuard.php` + `tests/Support/GuardsMySqlTestDatabase.php`, wired directory-wide in `tests/Pest.php`) is present in the frozen baseline: a destructive MySQL integration test now hard-fails before `RefreshDatabase` runs unless `APP_ENV=testing` **and** the resolved database name ends in `_test`. Verified for real against the exact prior incident (`DB_DATABASE=slipguard` rejected with a clear error, dev/demo data confirmed byte-identical before/after) and via 11 DB-free unit tests. The live-MySQL *permit* path remains verified only at the unit-test level — carried forward as a Launch Staging item (§12.6).
- **`AO-D3`** (stale Capability B feature-gate documentation) — closed by `PO-RC1-013`. `config/slipguard-market-intelligence.php`'s comment corrected; runtime behaviour unchanged.

## 12.2 Risk Rule Set 2026.1 — Formal Sign-Off

**Risk Rule Set 2026.1 is the formally approved deterministic analysis baseline for SlipGuard MVP**, effective this freeze. Verified before sign-off, not redesigned or tuned:
- **Canonical version identifier:** `RuleSet2026_1::VERSION = '2026.1'` (`app/Domain/Risk/RuleSets/RuleSet2026_1.php`), independently tracked alongside `CalculateStructuralRisk::ENGINE_VERSION = '1.0'` — ADR-007's four traceability axes (engine, rule-set, taxonomy, input-schema), all four persisted per `SlipAnalysis` row.
- **Deterministic behaviour:** confirmed by code inspection (no random/time/session access anywhere in the engine or its factor classes; `Brick\Math\BigDecimal` throughout, not floats) and by live re-execution — a real MySQL-backed analysis run during `PO-RC1-013`'s evidence-gathering and `PO-RC1-012`'s live journeys produced consistent, persisted results; a historical report reopened twice showed byte-identical scores.
- **Historical report integrity:** enforced by `BettingSlipStatus::allowedTransitions()` — `Analysed => [Archived]` only, no path back to an editable state, so a `SlipAnalysis` row is never overwritten in the normal customer workflow.
- **Explainability linkage:** Main Contributing Factor, Other Contributing Factors, and methodology disclosure are wired to real persisted analysis data (`report.blade.php`), not separately maintained copy.
- **Absence of generative AI from deterministic scoring:** confirmed by repository-wide grep — zero AI-provider SDK matches anywhere in `app/`. The Conversational Entry Layer is a deterministic rule-based parser (`DeterministicAccumulatorIntentInterpreter`), never a source of `SlipAnalysis` output.
- **Representative deterministic tests:** 204/204 Risk Engine tests (`tests/Unit/Risk`, `tests/Feature/Risk`) passing at freeze.

**Future modifications to Risk Rule Set 2026.1 require:** a new version identifier; explicit Product Office approval; deterministic regression proving the new version's own mathematics; and preservation of every historical analysis under the version it was actually computed with. 2026.1 itself must not be silently modified after this freeze.

## 12.3 Deterministic Product Boundary (Frozen Principle)

```
Canonical structured slip + Risk Rule Set version + defined deterministic inputs
        ↓
Deterministic Analysis
        ↓
Structural Risk Score, Risk Band, Findings, Main Contributing Factor, Explainability
```

Generative AI must not become the source of these outputs. This is architectural, not aspirational — confirmed by `AO-MVP-005`'s code-level review, not merely stated.

## 12.4 Historical Analysis Principle (Frozen Rule)

Historical analyses represent the deterministic result produced under the rule set and input state applicable when that analysis was created. Future changes must not silently rewrite historical analytical truth. Where reanalysis is ever introduced, it must create or represent a new analysis state under the existing state-machine architecture (§12.1's `BettingSlipStatus`), never retroactively alter a historical result in place.

## 12.5 Frozen MVP Capability Matrix

| Capability | Classification | Notes |
|---|---|---|
| Authentication & Registration | MVP — Active | §4.1 |
| Customer Dashboard | MVP — Active | §4.2 |
| Slip Builder (manual entry) | MVP — Active | §4.3 |
| Intake — Screenshot Upload | MVP — Active | No OCR, disclosed honestly (§4.4) |
| Intake — PDF Upload | MVP — Active | §4.5 |
| Intake — Paste Text | MVP — Active | §4.6 |
| Intake — Bet Code / Share Link | Deferred | UI present, no backend (§4.7) |
| Deterministic Rule Engine (Risk Rule Set 2026.1) | MVP — Active | Formally signed off, §12.2 |
| Explainability | MVP — Active | §4.9 |
| Risk Report | MVP — Active | §4.10 |
| History | MVP — Active | §4.11 |
| Decision Journal | MVP — Active | §4.12 |
| Planner (Capability A) | MVP — Active | §4.13 |
| Build an Accumulator (Capability B) | MVP — Feature Gated | `MARKET_WIDE_PLANNER_ENABLED=true`, §4.14, §12.6 |
| Conversational Entry Layer | MVP — Feature Gated | Same gate as Capability B, §12.7 |
| Demo Workspace | MVP — Active (Internal) | Staff/sales tooling, not customer-facing, §4.15 |
| SlipGuard Labs | MVP — Active | §4.16 |
| Operations Panel (Filament) | MVP — Active (Internal) | §4.17 |
| Profile | MVP — Active | §4.18 |
| Settings | MVP — Active | §4.19 |
| Help & Methodology | MVP — Active | §4.20 |
| Contact | MVP — Active | §4.21 |
| Public Marketing Website (incl. Privacy/Terms) | MVP — Active | §4.21 |
| Subscription / Billing | Deferred | §4.22, Launch Staging commercial decision |
| OCR automation | Deferred | Version 1.1 destination |
| Intelligence Builder Workspace evolution | Future Research / Incubation | §12.6 — additive, not required for MVP |
| Expanded live market intelligence beyond current Capability B scope | Future Research / Incubation | Version 1.2 destination |
| Mobile application | Future Research / Incubation | Version 2 destination |
| Browser extension | Future Research / Incubation | Version 2 destination |
| Risk Watch | Future Research / Incubation | §12.8 |
| External Intelligence Platform | Future Research / Incubation | §12.8 |
| SlipGuard Live | Future Research / Incubation | §12.9 |
| Automated bookmaker integration / autonomous betting | Explicitly Prohibited | `ADR-012` Locked Decision — permanent, not a roadmap slot |
| Customer funds handling / wallet / stake custody | Explicitly Prohibited | `ADR-012` |
| Outcome prediction | Explicitly Prohibited | `SD-001`/`CLAUDE.md` Locked Decision |

## 12.6 Capability B — Frozen MVP Boundary

**What is frozen as MVP:** PlanningBrief (competitions, leg count, market constraints, risk ceiling); candidate discovery against real market evidence (The Odds API, 3 configured competitions — EPL, La Liga, Serie A); deterministic evaluation; candidate acceptance producing a real, ordinary `Ready` `BettingSlip` (not a parallel object); Planning History integration via the existing, unmodified Planner (`ADR-013` — no parallel session/lifecycle system); feature gating via `MARKET_WIDE_PLANNER_ENABLED` (`true` in `.env`/`.env.example`, launch-enabled per `PO-MVP-004`; when `false`, the route still registers and renders an honest "Experience preview" state, not a 404 — no gated route leaks partial functionality or fails uncleanly).

**What is explicitly NOT frozen as MVP — the Intelligence Builder Workspace:** the broader future intelligence/planning direction (`docs/01-product/PO-U18.4.1-001-INTELLIGENCE-BUILDER-WORKSPACE-HANDOVER.md`) is additive future work, not required for this freeze. The current Builder is the coherent, launchable MVP foundation; the Workspace direction does not block freezing it.

## 12.7 Conversational Entry Layer — Frozen Boundary

```
Natural-language request → Interpretation → Structured PlanningBrief → Capability B → Deterministic evaluation → User decision
```

Frozen as an entry layer only, never an independent risk engine. Confirmed architecturally incapable of: inventing Structural Risk Scores or Risk Bands; bypassing PlanningBrief validation or Capability B constraints; authoring deterministic findings independently; silently introducing unsupported markets; placing bets; modifying accumulators without user action; or making the final user decision. The implementation (`DeterministicAccumulatorIntentInterpreter`) is a bounded-vocabulary rule-based parser — its own docblock states no live AI provider exists anywhere in this codebase. AI assists interpretation only; SlipGuard's deterministic systems remain authoritative.

## 12.8 Risk Watch / External Intelligence Platform

Recorded as **Post-MVP / future platform capability**. The existing research/proposal material is not deleted and not implemented by this freeze — it remains outside the MVP baseline unless a future, separate Product Office commission explicitly brings it in.

## 12.9 SlipGuard Live

Recorded as **architecturally separate future work**, outside this MVP freeze. Not designed, scoped, or implemented by this commission.

## 12.10 Explicit MVP Exclusions

Confirmed excluded from the frozen baseline, by absence of any implementing code (verified via direct repository grep for bet-placement/wallet/deposit/withdrawal/payment-SDK code — zero matches anywhere in `app/`): Risk Watch; External Intelligence Platform; SlipGuard Live; prediction engines; betting tips; bookmaker optimisation; automatic bet placement; customer-funds handling; bookmaker account integration; unsupported OCR; rumours/social intelligence; automatic user decision-making; future Intelligence Builder Workspace expansion. These are future work, not RC1 or freeze defects.

## 12.11 Launch-Staging Carry-Forward Items (Not MVP Freeze Blockers)

| Item | Status at freeze |
|---|---|
| Isolated MySQL `_test` database | Not yet provisioned in this environment (no `CREATE DATABASE` privilege, no `mysql` CLI) — provision, then run the 7 MySQL integration tests against it; the `AO-D1` guard must remain active and must never be pointed at development/demo/staging/production |
| Registration throttling | Classified Launch Staging / Security Hardening by `AO-MVP-005` — not an MVP freeze blocker |
| Production environment (Hostinger or equivalent) | Not provisioned; `docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md` defines requirements |
| Backups | Command exists (`slipguard:backup-database`, production-safe, scheduled daily) — cron wiring and a real restore drill remain outstanding |
| Scheduler | One job scheduled (backup); no real cron → `schedule:run` wiring exists yet |
| Mail | `log` driver in dev; real provider needed for production |
| HTTPS / security configuration | Launch Staging |
| Monitoring / logging | Launch Staging |
| Legal | Privacy/Terms content live (`PO-CO-002`); qualified external counsel sign-off remains outstanding |
| Pricing / commercial decision | No pricing ever approved; Premium boundary (§7) remains empty by design until Product Office supplies one |
| Coming Soon / public launch staging | Not implemented; per the approved launch-staging model (§40 of this freeze's own commissioning instruction), this is `PO-LAUNCH-STAGING-001`'s to sequence, not this freeze's |
