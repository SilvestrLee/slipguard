# AO-MVP-005 — Reference Data Ownership and Capability B Boundary

| Field | Value |
|---|---|
| Commission | `AO-MVP-005` (Product Office → Architecture Office) |
| Predecessor | `PO-MVP-004` — Guided Manual Intake Clarification |
| Type | Architecture review only — no code, migrations, flags, or UX documents changed |
| Core question | Does Capability B own raw football reference data (competitions/teams/fixtures), or only intelligence derived from it — and can the Manual Builder consume reference data without depending on Capability B? |

---

## 1. Evidence Reviewed

- **ADRs** (all 12 that exist): ADR-001 (Modular Monolith), 002 (Deterministic Risk), 003 (Constrained AI), 004 (UI Separation), 005 (Auth), 006 (Slip Versioning), 007 (Analysis Persistence Boundary), 008 (Planner Engine Boundary), 009 (Planner Lifecycle/Regeneration), 010 (Journal Entry Reference Boundary), 012 (Product Boundaries/Operator Independence), 013 (Capability B ↔ Planner Integration Boundary).
- **`app/Domain/MarketIntelligence/*`** — models, value objects, the provider contract and its one real implementation, the `Construction/` subfolder.
- **U-17 programme documents** — `U-17.1` (architecture discovery, Proposed), `U-17.4` (Compliance review, Delivered/real), `U-17.5` (Engineering Foundation Package).
- **`docs/offices/PARSER_OFFICE.md`** and `docs/00-governance/DECISION_LOG.md`'s Parser/Compliance entries.
- **`FootballMarketTaxonomyV1`**, the Manual Builder (`betting-slips/builder.blade.php`) and Capability B's Builder (`market-intelligence/builder.blade.php`).
- **`docs/product/MVP_CURRENT_STATE_AUDIT.md`** and the in-progress `MVP_SCOPE_LOCK.md` draft.

Ownership was not inferred from naming — every conclusion below cites a specific file, migration, or document.

---

## 2. Current-State Architecture

**No ADR defines a "reference data" bounded context.** ADR-013 is the closest related decision, and it explicitly scopes itself narrower than this question: it governs only the single handoff point where an *already-accepted* Capability B candidate becomes an ordinary `BettingSlip`/`PlannerSession` — it says outright that it does not touch "Capability B's own upstream questions (evidence sourcing, ranking mathematics, construction algorithm)." **This commission's core question is genuinely open, not pre-answered.**

At the persistence layer, a real separation already exists:
- `market_intelligence_fixtures` — `provider_event_id` (unique), `competition_key`, `home_team`, `away_team`, `commence_time`, `retrieved_at`, `cache_expires_at`. **No odds/market columns.**
- `market_intelligence_market_quotes` — foreign key to the fixture, `canonical_market`, `provider_market_key`, `bookmaker_key`, `outcomes` (JSON), `evidence_quality`. Odds/market data only.

At the provider-integration layer, this separation does **not** currently exist: `MarketEvidenceProvider::bulkAcquire(string $competitionKey, array $markets): array` (`app/Domain/MarketIntelligence/MarketEvidenceProvider.php`) is implemented once, by `OddsApiProvider`, as a single HTTP call returning fixture identity and bookmaker odds together — deliberately not split into a fixtures-only and a markets-only call, specifically so a caller can't accidentally double-request data one response already contained.

Candidate construction (`Construction/` — eligibility gates, slot ranking, outcome mapping) is cleanly separate code from fixture/quote ingestion, confirming the domain already internally distinguishes "evidence" from "derived decisions" in its class structure, even though no document says so explicitly.

The Manual Builder (`betting-slips/builder.blade.php`) and Capability B's Builder (`market-intelligence/builder.blade.php`) are **completely independent implementations today** — zero shared component code, zero shared data dependency. The Manual Builder is pure free text.

---

## 3. Ownership Analysis

### 3.1 Static Internal Taxonomy (Sport, Market)

SlipGuard-owned, static, deterministic. `FootballMarketTaxonomyV1` already exists, has no external dependency, and is not touched by any of the gates discussed below.

### 3.2 Reference Sports Data (Competition, Team, Venue, Fixture Schedule)

This is the crux of the commission, and the evidence points in two directions that must both be honoured, not averaged:

- **Architecture already conceptually separates it.** `U-17.1` (Proposed, not accepted, but real) draws the Evidence Acquisition → Evidence Store pipeline as feeding "the existing Planner" **and** "other future consumers (Risk Feed, Labs, Reports, public demonstrations)" — Architecture Office's own prior thinking already treated this as shared, multi-consumer infrastructure, not something owned exclusively by one feature. The DB schema (§2) independently arrived at the same separation.
- **Governance does not yet carve out reference data from Capability B's gate.** Parser Office's charter (`docs/offices/PARSER_OFFICE.md`) is Accountable/Responsible for "evidence-source viability, acquisition method, validation, and freshness/completeness governance" for *all* external evidence — its own gap statement is that no external evidence source of any kind has cleared that governance yet (a statement that appears to predate `U-17.5`'s real `OddsApiProvider` build; the discrepancy itself is worth Parser Office's attention, not resolved here). There is no sentence anywhere in the reviewed documents saying "reference lookup is exempt from Parser Office review." Extracting a shared layer would still need that review — it does not bypass governance, it changes *what* is being reviewed (a lighter-weight reference lookup rather than the full Capability B candidate-construction pipeline).

**Conclusion:** competition/team/fixture data is architecturally separable from Capability B's derived intelligence, and Architecture Office had already been heading in that direction — but it is not *currently* separated in the one place that matters operationally (the provider integration), and no governance carve-out exists yet to let it skip Parser Office review.

### 3.3 Live Operational Data (live odds, lineups, in-play events)

Out of scope for this commission — nothing in Manual Intake or the "No Typing" exploration asked for live data, and none exists in the repository today beyond the demo-seeded `market_intelligence_market_quotes`.

### 3.4 Derived Intelligence (candidate construction, weakest-leg attribution, market suitability)

SlipGuard-created, and the one area `U-17.4`'s real compliance concern is actually about — paraphrased accurately here, not a verbatim quote: UK Gambling Commission guidance indicates a tipster service *placing bets on a customer's behalf* triggers licensing; `ADR-012` already forbids exactly that (no autonomous betting, no fund custody). The genuinely open classification question `U-17.4` itself names is that candidate *construction* — not placement — is "new territory," distinct from Capability A (`U-17.4` §60, citing `U-17.1`). This is Capability B's genuine, undisputed exclusive territory.

---

## 4. Findings by Data Class

| Data Class | Proposed Owner | Source | Consumers | Governance Gate | MVP Availability |
|---|---|---|---|---|---|
| Static taxonomy (Sport, Market) | SlipGuard (internal) | `FootballMarketTaxonomyV1`, static | Manual Builder, Capability B | None | **Available now** |
| Competition reference data | Proposed: shared Reference Data capability (not yet built) | External provider (currently bundled with odds) | Manual Builder (future), Capability B | Parser Office source-viability review (not yet run for a standalone reference use); provider clarification letter (drafted, **not sent**) | Not available in MVP |
| Team reference data | Same as Competition | Same | Same | Same | Not available in MVP |
| Fixture reference data | Same as Competition | Same | Same | Same, plus: currently only obtainable bundled with odds via `bulkAcquire()` — no fixtures-only fetch path exists | Not available in MVP |
| Live market data (odds/quotes) | Capability B | `OddsApiProvider` | Capability B only | `U-17.4`: approved for bounded non-public MVP development; **not approved for public launch pending external legal opinion** | Internal-only, launch-disabled |
| Derived candidate intelligence | Capability B | Internal (`Construction/`) | Capability B only | Same as live market data — this is the actual subject of the regulatory concern | Internal-only, launch-disabled |

No row is ambiguous: three rows are genuinely blocked on the same unresolved dependency (provider clarification letter + Parser Office review), not on three independent problems.

---

## 5. Questions Resolved

**5.1 Ownership**
- Capability B does not *have to* own raw reference data architecturally — the schema and Architecture Office's own prior diagram already treat it as separable.
- Today, operationally, Capability B is the *only* thing that has ever fetched or persisted this data, so in practice it is the sole current source.
- There is an implicit shared-data intention in the repository (`U-17.1`'s diagram) but no implicit shared-data *implementation* — nothing is built.
- Extracting a Reference Data Service would reduce coupling going forward, but is new work, not a renaming of something that already exists.

**5.2 Access**
- The Manual Builder cannot *today* safely consume competitions/teams/fixtures without either (a) activating Capability B's flag — which would defeat the purpose of that flag by exposing its gated data through a second door, or (b) building a genuinely separate acquisition path, which does not exist yet.
- Read-only reference access is materially different from candidate generation in regulatory terms (`U-17.4`'s own concern is specifically about construction/tipster-adjacent activity, not fixture display) — but that difference is not yet reflected in any actual code boundary or governance sign-off. The distinction is real and defensible; it has not yet been *ratified*.

**5.3 Data Freshness**
- Competition and team identities change rarely — periodic sync (daily/weekly) would suffice.
- Fixture schedules need more frequent sync but not live/real-time for a reference-lookup use case (unlike Capability B's odds, which are inherently time-sensitive).
- No freshness threshold has been formally set for a reference-lookup use case because the use case doesn't exist yet — this is a design detail for whoever builds Model B, not a blocker to deciding the model.

**5.4 Compliance**
- `U-17.4`/`U-17.5` are explicit and unambiguous: **no bookmaker trademark/logo storage or display** — `bookmaker_key` is data only, never rendered as a branded element.
- Team/league logos or marks specifically are **not addressed anywhere** in the reviewed documents — a genuinely open sub-question, not a settled one. Recorded as an open item, not assumed either way.
- The provider-licensing distinction between "raw evidence (fixtures, markets, prices)" and derived intelligence already exists in `U-17.4`'s own reasoning, and finds no prohibition on using raw fixture/market data in a customer-facing product — the named restriction is narrower ("no resale as a standalone data product"). This directly supports treating reference data as a lighter compliance load than full candidate construction, though the confirming clarification letter to the provider has not been sent.

**5.5 Coupling**
- Using Capability B as the sole source (Model A) would create real, unnecessary coupling: two features with independently-managed launch timelines (Capability B's is explicitly "not approved for public launch pending legal opinion") would become launch-linked for no architectural reason.
- Duplicating a second, independent provider integration purely for reference lookup would waste effort and risk two sources of truth for the same fixtures — the existing `OddsApiProvider`/`MarketEvidenceProvider` should be evolved, not duplicated.
- The canonical model that should sit between providers and consumers is exactly what `U-17.1`'s own (unaccepted) diagram already proposed: a shared Evidence/Reference Store, with Capability B as one of several consumers layered on top, not the store's owner.

---

## 6. Candidate Architectural Models — Evaluation

### Model A — Capability B Owns Everything

- **Simplicity:** superficially simple (no new service), but only because it hides the real problem rather than solving it.
- **Coupling:** high — couples Manual Intake's launch timeline to Capability B's, which is explicitly gated on an external legal opinion Manual Intake has no reason to wait for.
- **Launch gating:** the worst of the three models — Manual Intake's guided fields would inherit Capability B's "not approved for public launch" status even though the actual regulatory concern (candidate construction) doesn't apply to fixture lookup.
- **Reuse:** poor — treats a general-purpose lookup as a side effect of a specific, narrower feature.
- **Operational risk:** real — any future change to Capability B's gating, flag, or licensing status would unintentionally affect an unrelated feature.
- **Verdict: rejected.**

### Model B — Shared Reference Data Layer

- **Separation of concerns:** strong — matches both the existing DB schema split and Architecture Office's own prior (unaccepted) diagram.
- **Provider abstraction:** requires evolving `MarketEvidenceProvider`/`OddsApiProvider` to expose a fixtures-only path (or a caching layer in front of the existing bundled call) — real but bounded engineering work, not a rewrite.
- **Consistency:** one source of truth for fixtures/competitions/teams, consumed by both features.
- **Implementation cost:** medium — a new bounded context/service, a schema decision (reuse `market_intelligence_fixtures` directly, or extract a genuinely new table), and the Parser Office review this data has never actually received.
- **MVP feasibility:** **not achievable within the current MVP timeline** — the provider clarification letter is drafted but unsent, and Parser Office has not reviewed reference data as its own use case. This is a real, near-term blocker, not a hypothetical one.
- **Verdict: recommended as the target architecture — for implementation after MVP v1.0, not before.**

### Model C — Minimal MVP Taxonomy Only

- **Time to launch:** fastest — zero new dependency, ships with what already exists (`FootballMarketTaxonomyV1`).
- **UX compromise:** real — Competition/Team/Fixture remain free text, which is exactly the gap `R-01.5`'s own exploration named.
- **Data quality:** unchanged from today — no regression, no improvement.
- **Future migration cost:** low, **provided** the eventual migration target is declared now (Model B) rather than left open — otherwise a future engineer could default to Model A by accident, exactly the coupling this review exists to prevent.
- **Scope integrity:** the only model that doesn't require any decision Product Office/Compliance/Parser Office hasn't already made.
- **Verdict: recommended as the MVP-immediate state, on the explicit condition that Model B is recorded now as the destination, not left ambiguous.**

---

## 7. Recommendation

**Adopt Model C for MVP v1.0, with Model B formally recorded now as the target architecture for the first post-MVP iteration that builds guided Competition/Team/Fixture selection.**

- **Rationale:** Model B is the architecturally correct answer — it is independently supported by the existing DB schema, by Architecture Office's own prior (if unaccepted) design intent, and by Compliance's own reasoning distinguishing raw evidence from derived intelligence. But it is not buildable today without first clearing two concrete, named blockers (below), and pretending otherwise would either delay MVP for no reason or quietly smuggle Capability B's gated data into a second surface (Model A) to hit a deadline.
- **Affected bounded contexts:** a new `ReferenceData` (or similarly named) domain, sitting alongside `MarketIntelligence`, not inside it. Capability B's domain would be refactored to *consume* this new context for fixture identity rather than owning it outright — a real but bounded refactor, not a rewrite of `Construction/` or the risk-scoring logic.
- **Data ownership:** the new Reference Data context owns competitions, teams, fixtures, venues, canonical identifiers/aliases. Capability B continues to own market quotes and all derived intelligence.
- **Service boundaries:** `MarketEvidenceProvider` would need a second, fixture-only method (or the Reference Data context wraps the existing bundled response and discards odds for its own callers) — evaluated as feasible, not requiring a new provider contract from scratch.
- **Required schema changes:** likely reuse of `market_intelligence_fixtures` as the new context's table (already odds-free) with an ownership/namespace move, rather than a new migration duplicating the same columns — an implementation detail for the work package that builds this, not decided further here.
- **Provider dependencies:** the existing `OddsApiProvider`/OddsAPI relationship — no new provider needed.
- **Compliance dependencies:** the drafted provider clarification letter (caching duration, attribution, derived-data retention) must actually be sent and answered; Parser Office must run its source-viability review against reference-data use specifically (not inherit Capability B's prior, narrower review).
- **Migration implications:** none for MVP v1.0 (nothing changes); a real but bounded migration when Model B is built.
- **MVP impact:** none — Manual Intake ships with Sport/Market structured selection only, exactly as much as is safely buildable today.
- **ADR required: Yes.** This decision — reference data as a distinct bounded context, separate from Capability B, with Capability B as a consumer rather than an owner — is exactly the kind of permanent architectural boundary `ADR-013` set precedent for recording. Recommend a new ADR (next number, `ADR-014` at time of writing) once Product Office accepts this review, so a future engineer cannot silently default to Model A. Writing that ADR is not done here, per this commission's own implementation constraint — it is a recommended next step, not part of this deliverable.

---

## 8. MVP Scope Consequence

**Outcome A: Manual Intake supports only structured Sport and Market selection in MVP. Competition, Team, and Fixture remain free text.**

Justification: every other outcome (B, C-as-written-in-the-commission is actually this document's "Model C", D) either requires work that cannot clear its own compliance/Parser Office prerequisites in time (B), or incorrectly couples Manual Intake to Capability B's unrelated launch gate (Outcome C/D as originally framed, which map to this document's Model A). Outcome A is the only one fully supported by evidence available *today*, and it does not foreclose Model B — it is the correct interim state pending that work.

---

## 9. Compliance Implications

- No new compliance exposure from shipping Outcome A — Sport/Market taxonomy is internal, static data with no external dependency.
- Building Model B later still requires: the provider clarification letter to actually be sent and answered, and a Parser Office source-viability review scoped to reference-data use (distinct from, and lighter than, Capability B's full review).
- Team/league logo/mark usage remains a genuinely open compliance question, not addressed by any reviewed document — flagged for Compliance Office attention whenever Model B or any team/competition-branded UI is considered, not assumed safe.

---

## 10. Implementation Estimate by Work Package (sizing only — no work started)

| Work Package | Scope | Estimate |
|---|---|---|
| WP1 — Sport/Market structured Manual Builder fields | Replace two free-text fields with taxonomy-driven selects, using existing `FootballMarketTaxonomyV1` | Small (days) |
| WP2 — Provider clarification letter sent/answered | Compliance-owned, not Engineering | Not an engineering estimate — a Compliance/Legal timeline dependency |
| WP3 — Parser Office reference-data source-viability review | Parser Office-owned | Not an engineering estimate |
| WP4 — `ReferenceData` bounded context extraction | New domain, fixture-only provider path, schema ownership move | Medium (weeks) — post-MVP |
| WP5 — Manual Builder Competition/Team/Fixture guided selection (consuming WP4) | UI work per `R-01.5`'s own recommended hybrid concept, gated on WP4 | Medium (weeks) — post-MVP, sequenced after WP4 |
| WP6 — `ADR-014` (or next available number) recording this decision | Governance documentation | Small — recommended immediately upon Product Office acceptance of this review |

---

## 11. Summary — Answering the Acceptance Standard Directly

- **Is Manual Intake genuinely dependent on Capability B?** No, not architecturally — but *today*, operationally, yes, because no independent reference-data path exists yet.
- **Which guided controls can ship in MVP?** Sport and Market only (taxonomy-driven), via WP1.
- **Who owns competition/team/fixture data?** Recommended: a new, not-yet-built `ReferenceData` context — not Capability B, and not the Manual Builder itself.
- **Is a shared Reference Data capability required?** Yes, as the target architecture — not required to exist before MVP ships.
- **What governance gate applies before implementation?** The provider clarification letter must be sent and answered, and Parser Office must review reference-data use specifically, before WP4 begins. Neither gate blocks MVP, since MVP does not attempt WP4.
