# MVP Current State Audit

| Field | Value |
|---|---|
| Commission | `PO-MVP-001`, Phase 1 |
| Purpose | Repository truth only — what already exists, without opening the repository |
| Method | Direct repository inspection (routes, real implementation files, real test runs) plus cross-reference against `TASKS.md`/`DECISION_LOG.md`'s own recorded history; where the two disagreed, code was trusted over narrative |
| Date | 2026-08-02 |

---

## How to read this document

Four states, as commissioned:

- **COMPLETE** — implemented, verified, production quality.
- **COMPLETE WITH POLISH REQUIRED** — functionally complete, minor UX/quality refinement only.
- **PARTIALLY IMPLEMENTED** — architecture exists, not yet commercially usable as-is.
- **NOT STARTED** — no meaningful implementation exists.

Every entry states its evidence, dependencies, and a remaining-work estimate. Nothing here is an implementation plan — it is a snapshot.

---

## At a Glance

| # | Feature | Status | Blocks MVP? |
|---|---|---|---|
| 1 | Authentication | COMPLETE | No |
| 2 | Dashboard | COMPLETE | No |
| 3 | Slip Builder (manual entry) | COMPLETE | No |
| 4 | Slip Intake (screenshot / PDF / paste / bet-code) | COMPLETE WITH POLISH REQUIRED | Product Office call |
| 5 | Deterministic Risk Engine | COMPLETE | No |
| 6 | Risk Report screen | COMPLETE | No |
| 7 | History | COMPLETE | No |
| 8 | Decision Journal | COMPLETE WITH POLISH REQUIRED | No |
| 9 | Planner (Capability A) + Planning History | COMPLETE | No |
| 10 | Market Intelligence / "Build an Accumulator" (Capability B) | PARTIALLY IMPLEMENTED | Product Office scope decision needed |
| 11 | Demo Workspace | COMPLETE | No |
| 12 | SlipGuard Labs | COMPLETE (as a teaser page) | No |
| 13 | Subscription / Billing / Pricing | **NOT STARTED** | **Yes** |
| 14 | Public Marketing Site | COMPLETE, with two stub pages | **Privacy/Terms: Yes** |
| 15 | Operations (internal Filament panel) | COMPLETE | No |
| 16 | Profile / Settings / Help | Profile COMPLETE; **Settings/Help NOT STARTED** | Product Office call |
| 17 | Support notes / audit log (internal) | COMPLETE | No |

**The commission's own MVP definition asks "Can they subscribe?" and "Would they pay for it?" There is currently no mechanism to charge anyone anything (#13) and no real Privacy Policy or Terms of Service (#14) — these are the two genuine, unambiguous blockers found in this audit. Everything else is either done or a scope decision, not a missing capability.**

---

## 1. Authentication — COMPLETE

**Evidence:** Real Volt pages for register, login, forgot-password, reset-password (guest) and confirm-password (authenticated). Session/CSRF handling via Laravel's standard stack.
**Note, not a gap:** `MustVerifyEmail` is explicitly *not* applied to `User` (commented out in `app/Models/User.php`) — email verification is a deliberate non-requirement, not an oversight.
**Dependencies:** None.
**Remaining work:** None for MVP as currently scoped.

## 2. Dashboard — COMPLETE

**Evidence:** `resources/views/livewire/dashboard.blade.php`, ~457 lines, real content (not a stub).
**Dependencies:** None.
**Remaining work:** None.

## 3. Slip Builder (manual entry) — COMPLETE

**Evidence:** `resources/views/livewire/betting-slips/builder.blade.php`, ~490 lines. A real regression (wrong container width class, `container-standard` instead of the specified `container-analytics`) was found and fixed this session (commit `e72818e`) — current state verified correct.
**Dependencies:** None.
**Remaining work:** None.

## 4. Slip Intake — COMPLETE WITH POLISH REQUIRED

**Evidence:** Four intake methods are offered:
- **PDF upload** — real: `ExtractPdfText` + `CreateDraftSlipFromParsedText` actions genuinely extract and parse text.
- **Paste text** — real, same parsing action.
- **Screenshot upload** — the image is stored and shown back to the customer, but **no OCR exists**; the customer manually transcribes what they see. This is a known, disclosed, deliberate scope limitation, not new information — `U-11.3`/`U-11.5A` already recorded this in this repository's own history ("approved real OCR/PDF extraction as an MVP capability, but that extraction engine is not yet implemented — pending Architecture/Parser/Compliance Office review").
- **Bet code / share link** — explicitly deferred, shown as a disabled option with an honest "not supported yet" label.

**Dependencies:** OCR would require an Architecture/Parser/Compliance Office review before implementation (per the existing governance record) — not a quick engineering add.
**Remaining work:** None if screenshot-without-OCR is accepted as the MVP behaviour (it is honest, not broken — the customer isn't misled). If Product Office decides real OCR is required for MVP, this becomes a multi-office initiative, not an engineering task alone — flagged as a scope decision, not sized here.

## 5. Deterministic Risk Engine — COMPLETE

**Evidence:** `app/Domain/Risk/*` — a genuinely deep, mathematically real implementation (six factor classes RF-001–RF-006, `BigDecimal`-based deterministic arithmetic, the accepted Marginal Structural Contribution weakest-leg model). Independently verified multiple times this session, including against a fresh MySQL database with real inserts.
**Dependencies:** None.
**Remaining work:** None for MVP. (Risk Rule Set 2026.1 itself still awaits formal Product Office/Data Science sign-off per `TASKS.md`'s own Blocked section — a governance formality, not a code gap.)

## 6. Risk Report Screen — COMPLETE

**Evidence:** `resources/views/livewire/betting-slips/report.blade.php`, 482 lines — headline/band, main contributing factor, supporting factors, data quality, methodology, report details, all genuinely wired to persisted analysis data. Verified directly against real MySQL-backed data this session (a full create-slip → analyse → view-report cycle).
**Dependencies:** None.
**Remaining work:** None.

## 7. History — COMPLETE

**Evidence:** `resources/views/livewire/workspace/history/index.blade.php`, 672 lines, real.
**Dependencies:** None.
**Remaining work:** None.

## 8. Decision Journal — COMPLETE WITH POLISH REQUIRED

**Evidence:** `journal/index.blade.php` (407 lines) + `journal/entry.blade.php` (262 lines), real, functional.
**Known issue:** `DecisionJournalTest`'s archive-grouping assertion has failed intermittently across this session's test runs (order- or timing-dependent, not consistent) — flagged during the MySQL verification work as a pre-existing flakiness, not yet root-caused.
**Dependencies:** None.
**Remaining work:** Small — investigate and fix the test flakiness. Not a customer-facing defect as far as this audit found.

## 9. Planner (Capability A) + Planning History — COMPLETE

**Evidence:** `planner/session.blade.php` (871 lines) + `planner/history.blade.php` (83 lines). Real weakest-leg attribution; regeneration-event unique/cascade constraints independently verified with real inserts against MySQL this session.
**Dependencies:** None.
**Remaining work:** None.

## 10. Market Intelligence / "Build an Accumulator" (Capability B) — PARTIALLY IMPLEMENTED

**Evidence:** A large, real implementation exists (`market-intelligence/builder.blade.php`, 1,329 lines) but is gated behind `MARKET_WIDE_PLANNER_ENABLED`, default `false` — the route itself isn't registered when the flag is off. Its own code comments state it is "not authorized for public launch" (`U-17.4`).
**This is not an engineering gap — it is a deliberate, already-recorded product decision** to keep this internal-only pending a public-launch authorization.
**Dependencies:** A genuine Product Office decision on whether Capability B is in MVP v1 scope at all. If yes: needs the public-launch authorization and compliance review its own code already names as required. If no: it is correctly and formally Phase 8 (Post-MVP), and no further engineering action is needed now.
**Remaining work:** Zero engineering work if deferred; a scope decision either way.

## 11. Demo Workspace — COMPLETE

**Evidence:** `php artisan slipguard:demo` — verified end-to-end against real MySQL this session (30 slips, 12 journal entries, 18 Market Intelligence fixtures, confirmed idempotent on re-run).
**Dependencies:** None.
**Remaining work:** None.

## 12. SlipGuard Labs — COMPLETE (as a teaser/feature-interest page)

**Evidence:** `labs/index.blade.php`, guest-visible per its own routing, "Notify Me"/"Join Beta" gated to authenticated customers inside the component. Real, not a stub.
**Dependencies:** None.
**Remaining work:** None — this is a positioning/community-interest page by design, not a commerce feature.

## 13. Subscription / Billing / Pricing — NOT STARTED

**Evidence:** No billing/subscription package anywhere in `composer.json` (no Cashier, no Stripe SDK). No `Subscription`, `Plan`, or `Billing` model exists anywhere in the codebase. `/pricing` is an explicit, honest stub: "No pricing model has ever been approved by Product Office... No price has been set."
**This is the single largest gap against the commission's own MVP definition** — "supporting paying customers" and "Can they subscribe?" have no implementation to point to at all.
**Important distinction, not a blocker to solving this:** `ADR-012`'s Locked Decision that "customer money never enters SlipGuard" governs *betting funds* (deposits, wallets, stakes, winnings) between customer and bookmaker — it does not prohibit SlipGuard from charging its own honest SaaS subscription fee for the analysis product itself. A real subscription-billing integration (e.g. a standard third-party processor for SlipGuard's own fee) is architecturally compatible with the existing Locked Decisions; it simply hasn't been designed or built yet.
**Dependencies:** A real, Product-Office-approved pricing model (a business decision, not an engineering one) must exist before any billing architecture can be designed. Then: an Architecture Office decision on the billing integration, likely a Compliance Office review, then implementation.
**Remaining work:** Large. Not sizeable in engineering days alone until the pricing model and billing architecture decisions exist upstream of engineering.

## 14. Public Marketing Site — COMPLETE, with two real stub pages

**Evidence:** Homepage (~1,050 lines, real deterministic-engine-sourced content, verified extensively this session), `/analyse`, `/reports`, `/planner`, `/about`, `/faq`, `/release-notes` are all real, substantive content — not placeholders.
**Two pages are explicit, honest, self-declared stubs** (`public-coming-soon.blade.php`): `/privacy` and `/terms`. `/pricing` and `/contact` are also stubs but are downstream of the pricing decision above and a support-channel decision, respectively.
**This is a genuine pre-launch blocker, already known to this repository's own governance** — `docs/09-compliance/PRODUCT_GUARDRAILS.md`'s standing note ("final legal and regulatory wording requires review before public launch") is referenced explicitly in `DECISION_LOG.md`'s `U-13.0` entry as an unresolved item, not new information surfaced by this audit.
**Dependencies:** Real legal review (Compliance Office / external counsel) for Privacy and Terms content — engineering cannot write binding legal text unilaterally.
**Remaining work:** Small from engineering once real legal text exists (swap the stub for real content); the blocking dependency is legal/compliance authorship, not implementation effort.

## 15. Operations (internal Filament panel) — COMPLETE

**Evidence:** `CustomerResource`, `LabsFeatureResource`, `AuditLogResource`, with `SupportNotesRelationManager`, `PlannerSessionsRelationManager`, `BettingSlipsRelationManager`, `AnalysesRelationManager` all genuinely implemented — 18 PHP files under `app/Filament`, not stubs.
**Dependencies:** None.
**Remaining work:** None for MVP (this is internal tooling, not customer-facing).

## 16. Profile / Settings / Help

- **Profile — COMPLETE.** `/profile` is a real, functional Breeze-derived view (name/email/password management).
- **Settings — NOT STARTED.** `routes/web.php` routes `/settings` to the generic, unimplemented `coming-soon` view.
- **Help — NOT STARTED.** Same generic `coming-soon` view.

**Dependencies:** A real scope decision on what "Settings" contains beyond what Profile already covers (notification preferences? theme default? data export?) — currently undefined, so not yet sizeable.
**Remaining work:** Small–Medium once scope is defined; currently blocked on that scope decision, not on implementation difficulty.

## 17. Support Notes / Audit Log (internal) — COMPLETE

**Evidence:** Real, Filament-only (internal), confirmed under Operations above.
**Dependencies:** None.
**Remaining work:** None — not customer-facing, doesn't affect customer-facing MVP scope.

---

## Test Evidence

A targeted spot-check across the highest-risk customer-facing areas (Dashboard, Slip Analysis Report, Analyze Betting Slip, Betting Slip Intake, Planning History, Decision Journal, Operations):

```
106/106 passing, 416 assertions
```

This is a targeted sample, not the full suite (the full suite's own current state — 680+/689, a small number of known pre-existing, unrelated failures — is tracked separately in this repository's own recent verification history, most recently `docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md`).

## Divergences Found Between `TASKS.md` and Actual Code

One: the Slip Builder's container-width regression (item 3) — already found and fixed earlier this session, confirmed resolved as of commit `e72818e`. No other stale "complete" claims surfaced in this pass, though this was a targeted audit, not an exhaustive line-by-line reconciliation of every historical `TASKS.md` entry.

## Summary for Product Office

Without opening the repository: **the deterministic intelligence product itself — the part the commission's own Core Product Principle says matters most — is genuinely complete and verified** (engine, report, planner, history, journal, demo workspace, dashboard, builder, all real MySQL-verified this session). The public marketing site honestly represents the product. Internal operations tooling is real.

**Two things stand between this and a commercially chargeable MVP, and neither is an engineering gap to be closed by more code alone:**

1. **No billing/subscription mechanism exists** — needs a Product Office pricing decision before an Architecture Office billing design, before implementation.
2. **Privacy Policy and Terms of Service are honest stubs** — needs real legal/Compliance Office authorship before a straightforward engineering swap.

Two further items are **scope decisions**, not gaps: whether Capability B (Market Intelligence / Build an Accumulator) is in MVP v1 at all, and whether Settings/Help need real content for MVP or can remain deferred alongside their current honest "coming soon" state.
