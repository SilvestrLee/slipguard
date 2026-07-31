# ADR-012 — Product Boundaries, Operator Independence & Regulatory Positioning

**Status:** Accepted (Product Office, direct founder ruling, 2026-07-27). Recorded as `ADR-012` rather than the commissioning document's own "ADR-010" label, and rather than Architecture Office's recommended `SD`-series classification — both resolved by explicit Product Office ruling; see `docs/00-governance/DECISION_LOG.md`'s `ADR-012` entry for the full reasoning trail.

## Context

The Product Office commissioned a permanent statement of what SlipGuard is, is not, and will never become — covering customer-fund custody, decision authority, autonomous betting, operator independence, user control, explainability, and regulatory positioning. The commissioning document self-identified as "ADR-010" and named "Safeguard Labs" as a constitutional entity subject to it.

Two things were verified directly against the repository before recording, and resolved by direct Product Office ruling rather than assumed:

1. **`ADR-010` already exists** — `ADR-010-JOURNAL-ENTRY-REFERENCE-BOUNDARY.md`, Accepted, an unrelated subject. `ADR-011` is also already reserved (Operations Console authorization, cited inline in `U-10.1`/`U-10.3`, not yet its own standalone file). Architecture Office additionally recommended this content matched the repository's `SD`-series (`SD-001`, `SD-002` — permanent product-identity/business-model decisions) more closely than the `ADR`-series (narrow, technical, internal-architecture boundaries this series has consistently held to). **Product Office ruled directly: record as `ADR-012`**, reusing neither `ADR-010` nor `ADR-011`, maintaining repository ADR continuity — overriding the `SD`-series recommendation.
2. **"Safeguard Labs" does not exist anywhere in this repository.** Confirmed directly as a naming error for **SlipGuard Labs** (`SD-002`, Programme U-13 — already built, accepted, and closed). **`SD-002` itself is preserved exactly as originally accepted, not broadened or rewritten** — the Constitutional Research Gate below (§8) is recorded as a new constraint on SlipGuard Labs' *future evolution*, operating within `SD-002`'s existing, unchanged charter, not an amendment to `SD-002`'s own text, scope, or history. SlipGuard Labs is a product programme, not an eighth constitutional office; its own "Applies To" listing in the commissioning document (alongside the seven real governance offices) is corrected accordingly — the research-gating obligation is owned by Product Office, per `docs/offices/PRODUCT_OFFICE.md`.

## Decision

### 1. Product Identity
SlipGuard is an independent betting decision intelligence platform. It is not a bookmaker, betting exchange, payment platform, wallet, investment product, or automated betting robot. Its purpose is intelligence, explainability, and disciplined decision support.

### 2. Nine Permanent Principles
1. **Customer money never enters SlipGuard** — no deposits, wallets, withdrawals, fund movement between bookmakers, stake/winnings receipt, escrow, or bet settlement. Every financial transaction stays exclusively between customer and licensed operator.
2. **SlipGuard never becomes the decision maker** — it presents evidence (structural risk, market characteristics, volatility, correlation, evidence quality, deterministic outputs); every report reinforces "the final decision remains yours."
3. **No outcome prediction** — reaffirms the existing Locked Decision (`CLAUDE.md`); not altered by this ADR.
4. **No autonomous betting** — no auto-placement, auto-stake-increase, auto-loss-chasing, auto-cashout, auto-rebuilding of failed slips, auto-acceptance of odds changes, or auto-executed strategies. Every wager requires a conscious customer action.
5. **Operator independence** — no affiliate agreement, bookmaker partnership, commercial incentive, or promotional campaign may bias a deterministic calculation, risk classification, customer-facing recommendation, or explainability.
6. **User control** — stake amount, bookmaker selection, timing, warning acceptance/rejection, betting frequency, and bankroll strategy remain the customer's alone.
7. **Explainability first** — every meaningful conclusion is explainable: why a warning exists, why risk increased, why selections interact, why evidence is weak or strong.
8. **Risk awareness over excitement** — no pressure mechanics, urgency manipulation, "bet now" messaging, artificial countdowns, loss-chasing prompts, or exaggerated success claims.
9. **Regulatory simplicity by design** — SlipGuard deliberately avoids operating models requiring it to function as a bookmaker, gambling operator, wallet provider, payment processor, or betting exchange.

### 3. Permitted Future Integrations
Constitutionally compatible, subject to technical feasibility and legal review — **none commissioned, scoped, or architected by this ADR**:
- **Read-only account connections** (betting history, settled/open bets, staking history, account statistics) — improves insight, never controls transactions.
- **Slip transfer** (structured slips, booking codes where supported, deep links into bookmaker bet slips, exports) — final confirmation always belongs to the customer.
- **Odds validation** (movement, suspended markets, unavailable selections, pricing differences) — informational only.
- **Betting journal automation** (recording authorised activity to improve customer history/reporting/behavioural analysis).
- **Behavioural intelligence** (advisory-only pattern observation: increasing stakes, excessive accumulator length, repeated warning overrides, concentrated market exposure).

### 4. Permanently Prohibited
Absent a formal ADR/SD superseding this one: becoming a bookmaker; holding customer funds; operating a wallet; processing betting payments; guaranteeing profit; advertising guaranteed winning slips; selling certainty; autonomous wagering; hidden decision-making algorithms; manipulating customers into placing bets.

### 5. Product Positioning
*"SlipGuard is the independent intelligence layer between the bettor and the bookmaker. The bookmaker executes the wager. SlipGuard helps the customer understand it."*

### 6. Engineering, UX, Compliance Implications
Engineering optimises for deterministic analysis, explainable outputs, auditability, transparency, customer control, modular integrations — and builds no infrastructure for fund custody, payment processing, betting wallets, or autonomous wagering. UX reinforces informed decision-making, deliberate interaction, transparency, and calm visual language — never pressure. Compliance reviews every future capability against this ADR; any proposal introducing fund custody, autonomous execution, or bookmaker-like behaviour is escalated to Product Office before development begins.

### 7. SlipGuard Labs' Constitutional Research Gate
Every SlipGuard Labs (`SD-002`, Programme U-13) research initiative must satisfy, before entering roadmap consideration: (1) preserves operator independence; (2) avoids handling customer money; (3) avoids deciding for the user; (4) avoids predicting sporting outcomes; (5) improves explainability; (6) improves customer discipline; (7) maintains customer control; (8) reduces rather than increases behavioural risk; (9) has explainable reasoning; (10) strengthens SlipGuard's role as an intelligence platform rather than a gambling operator. Failure of any gate requires rejection or redesign before further investment. **This gate applies to how SlipGuard Labs evolves from here — it does not retroactively alter `SD-002`'s own accepted text, scope, or history.**

## Consequences

- Every future feature, experiment, integration, partnership, and commercial opportunity is evaluated against this ADR; a conflicting proposal is rejected unless this ADR is formally amended by Product Office.
- None of §3's five permitted-integration categories are implementable yet — each requires its own Architecture Office discovery, Parser Office viability assessment (most involve an external bookmaker/account data dependency Parser Office has never assessed), and Compliance Office review before Engineering begins, mirroring the sequence already established for the Planner and Operations Console.
- No compliance/legal review has been performed as part of this ADR. `docs/09-compliance/PRODUCT_GUARDRAILS.md`'s own standing note ("final legal and regulatory wording requires review before public launch") remains true and unaddressed.
- `CLAUDE.md`'s Locked Decisions gain five new entries (fund-custody prohibition, autonomous-betting prohibition, operator independence, risk-awareness-over-excitement, regulatory-simplicity-by-design); the pre-existing "no outcome prediction"/"no safe-bet claims"/deterministic-explainable/Planner-editability entries are reaffirmed, not replaced.
- `docs/09-compliance/PRODUCT_GUARDRAILS.md` gains an Operator & Custody Boundaries section; its existing content (prohibited claims, responsible design, transparency) is unchanged, since it already covered a subset of this ADR's ground.
