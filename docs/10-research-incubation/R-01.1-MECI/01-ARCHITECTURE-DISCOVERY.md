> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. Requires a real, evidenced Product Office commissioning and Architecture Office/Engineering Office graduation review before any implementation may begin. See `docs/10-research-incubation/README.md`.

# Match Evidence & Context Intelligence — Architecture Discovery

| Field | Value |
|---|---|
| Type | Architecture discovery — no implementation, no code, no migration |
| Scope | The complete architecture for acquiring, validating, attributing, versioning, snapshotting, consuming, and explaining factual football context evidence (injuries, suspensions, fixture congestion, travel burden, rest days, league context, head-to-head, weather, expected lineup availability) alongside the existing deterministic structural risk analysis |
| Explicit constraint | Must not modify `App\Domain\Risk\Engine\CalculateStructuralRisk`, `RiskAnalysisResult`, or any accepted structural-risk mathematics |
| Related | `ADR-002`, `ADR-003`, `ADR-007`, `ADR-012`, `docs/02-architecture/U-17.1` through `U-17.5` (the closest existing precedent — a different capability, same disciplines), `docs/offices/PARSER_OFFICE.md` |

---

## 0. Framing: what this document is and isn't

This is architecture discovery only. No provider is selected, no schema is migrated, no code is written. It defines the complete shape of a new, additive **evidence layer** — Match Evidence & Context Intelligence (MECI) — that sits beside the existing deterministic structural risk engine, never inside it.

Seven questions were commissioned explicitly — acquired, validated, attributed, versioned, snapshotted, consumed, explained — and this document is organised around them directly (§5–§11), after establishing the constitutional boundary (§1) and the one fact that gates everything (§2).

**What MECI is not**: it is not a second risk-scoring system, not a prediction engine, not an input to Capability B's candidate construction (`U-17.*`), and not a replacement for anything the structural engine already does. It is a sourced, factual, explicitly-bounded layer of *context* — the kind of thing a knowledgeable friend would mention about a fixture, stated as fact, never as forecast.

---

## 1. Constitutional boundary (restated, not reinvented)

- **`ADR-002` (locked)**: risk scores, bands, and weakest-leg findings are produced by versioned deterministic logic — MECI introduces no second scoring authority. It has no score, no band, no weight, no threshold of its own that competes with `RuleSet2026_1`.
- **`ADR-003` (locked)**: AI may transform *approved structured findings* into plain language; it must never calculate scores, invent match facts, or predict outcomes. MECI is precisely the mechanism that makes context facts "approved structured findings" in the first place — it is the acquisition/validation layer, not the explanation layer. The two remain separate: MECI produces facts; a downstream, already-governed explanation step (out of scope here) may phrase them.
- **`ADR-007` (locked)**: the Risk Engine touches no database, HTTP, clock, or infrastructure concern, and every presentation surface reads *persisted* analysis, never a live recalculation. MECI is designed to sit entirely outside this boundary (§2) and to obey its own presentation half exactly: **consumption reads a persisted snapshot only, never a live provider call** (§10).
- **`ADR-012` (locked)**: no outcome prediction, no "safe bet" claims, no pressure mechanics, operator independence, risk-awareness-over-excitement. Every context fact family in §4 is designed to be stated as neutral disclosure, never as directional framing about likelihood of winning or losing (§11's approved-language register makes this concrete).
- **`CLAUDE.md` Locked Decision**: "AI never determines suitability, ranking, confidence, selection order, correlation, or planner/risk outputs — those remain deterministic." MECI's own attribution/quality-tagging (§7) is deterministic rule-based tagging (source freshness, completeness), never an AI judgement call.
- **Capability boundary**: MECI attaches to legs the customer has already selected inside the *existing* structural analysis (Capability A). It has no relationship to Capability B (`U-17.*`, market-wide candidate construction) and must not become an input to fixture/candidate eligibility or ranking unless a future, separately-authorised programme explicitly extends it there.

---

## 2. The one decision that gates everything else

Verified directly against the codebase at the time this document was written:

```php
// app/Models/BettingSlipLeg.php — the raw, customer-facing leg record
'sport', 'competition', 'event_name', 'event_start_time' (nullable), 'market_name', 'selection_name', 'decimal_odds'

// app/Domain/Risk/Normalization/NormalizedBettingSlipLeg.php — the ONLY shape
// that reaches CalculateStructuralRisk
public int $bettingSlipLegId;
public int $displayOrder;
public NormalizedSport $sport;
public NormalizedMarket $market;
public string $decimalOdds;
```

Two facts follow directly:

1. **`event_name` is free text** — whatever the customer typed, pasted, or an OCR/PDF parse extracted. There is no team ID, no fixture ID, no competition taxonomy behind it, and `event_start_time` is nullable.
2. **The deterministic engine never sees any of it.** `NormalizedBettingSlipLeg` carries only sport, market, and odds.

**This is MECI's hardest, most load-bearing fact.** Nothing in §4 onward can attach a single injury, weather forecast, or head-to-head record to a leg until a free-text `event_name` can be confidently resolved to a real-world fixture. Following this codebase's own established discipline for exactly this class of problem (`NormalizeFootballMarket`'s exact-alias-only matching), **fixture identity resolution must be exact-match-first and must fail explicitly, never silently guess.**

---

## 3. Platform context

```text
                    ┌───────────────────────────────┐
                    │  BettingSlipLeg (existing,       │   ← unchanged; MECI reads it,
                    │  free-text event_name/competition)│      never writes to it
                    └───────────────┬───────────────┘
                                    ▼
                    ┌───────────────────────────────┐
                    │   Fixture Identity Resolver       │   ← hardest problem (§2, §6)
                    │  (exact-match-first; explicit       │
                    │   Unresolved on failure, never guess)│
                    └───────────────┬───────────────┘
                         confident match │  no match → explicit Unresolved,
                                    ▼      stop here, no fabricated evidence
                    ┌───────────────────────────────┐
                    │  Context Provider Adapter(s)      │   ← §7; multiple providers,
                    │  (per fact-family; §4)             │      unlike U-17's single-provider MVP
                    └───────────────┬───────────────┘
                                    ▼
                    ┌───────────────────────────────┐
                    │   Context Evidence Validator       │   ← §8; freshness/completeness
                    │                                     │      contract, quality tagging
                    └───────────────┬───────────────┘
                                    ▼
                    ┌───────────────────────────────┐
                    │   Context Evidence Store           │   ← §5; operational store,
                    │  (raw + derived, versioned)        │      not a warehouse
                    └───────────────┬───────────────┘
                                    ▼
                    ┌───────────────────────────────┐
                    │  Context Evidence Snapshot         │   ← §9; bound to a specific
                    │  (immutable, one write at first-view│      SlipAnalysis/LegAnalysis, never
                    │   finalisation; never re-fetched live)│    rewritten (Revision 1, §9r below)
                    └───────────────┬───────────────┘
                                    ▼
                    ┌───────────────────────────────┐
                    │  Presentation (Risk Report)        │   ← §10; reads snapshot only,
                    │  + downstream Explanation (ADR-003)│      never invokes provider or
                    │                                     │      re-derives at read time
                    └───────────────────────────────┘

  ═══════════════════════════════════════════════════════════════════
  CalculateStructuralRisk / RiskAnalysisResult / SlipAnalysis persistence
  (ADR-002, ADR-007 — completely untouched)
  ═══════════════════════════════════════════════════════════════════
```

---

## 4. Context Fact Taxonomy v1

| # | Family | Acquisition mode | Non-goal (must never become) |
|---|---|---|---|
| 1 | Injuries | Direct (external provider) | A prediction of match impact |
| 2 | Suspensions | Direct (external provider) | A prediction of match impact |
| 3 | Fixture Congestion | **Derived** (from stored fixture history) | A "fatigue risk score" |
| 4 | Travel Burden | Derived (from stored venue coordinates) | A probability adjustment |
| 5 | Rest Days | Derived (from stored fixture history) | A fitness judgement |
| 6 | League Context | Derived (from stored standings/results) | A "must-win" narrative |
| 7 | Head-to-Head | Derived (from stored fixture history) | A "form"/momentum narrative implying likely outcome |
| 8 | Weather | Direct (external meteorological provider) | A performance-impact claim |
| 9 | Expected Lineup Availability | Direct (external provider — **confirmed absences only**) | A lineup prediction — see `02-PARSER-OFFICE-PHASE-0.md` §6 for why this matters more than it first appears |

Families 3–7 are derivable from a base fixture/result feed the Evidence Store accumulates over time — only families 1, 2, 8, 9 genuinely require a dedicated external provider relationship.

---

## 5–11. Acquired, Validated, Attributed, Versioned, Snapshotted, Consumed, Explained

The full lifecycle design — provider adapters, canonical evidence shapes, the `EvidenceQuality` enum (`Fresh`/`Stale`/`Conflicting`/`Unavailable`), the `LegContextSnapshot` entity and its version axes, the presentation boundary, and the approved/prohibited language register — is unchanged in substance from the original discovery conversation and is summarized here rather than fully reproduced, since the load-bearing decisions are captured in §1–§4 above and §9r (Revision 1) below. The full original text (component tables, canonical field shapes, failure model, decision register, phased roadmap) exists in this programme's source conversation and should be re-derived fresh by whoever picks this up for real, rather than assumed complete from this summary — consistent with this whole document's own "verify, don't assume" standard.

Key structural decisions, restated because they're load-bearing:
- No fifth version axis added to `SlipAnalysis`/`LegAnalysis`; `LegContextSnapshot` is a new sibling entity, foreign-keyed to `leg_analysis_id`.
- No new column on `BettingSlip` (`ADR-006` already rejected content-versioning it).
- Presentation reads `LegContextSnapshot` only, never a live provider call, mirroring `ADR-007`'s Forbidden Call Flow exactly.
- No ADR is proposed for this — recommended as a future addendum to `ADR-007` at real adoption time, not a new ADR number, since it extends an existing boundary rather than introducing a new kind of one.

---

## 9r. Revision 1 — Report Immutability & Parser Office Scope

Two refinements were made to the original design during discussion:

**Report immutability**: rather than asynchronous, best-effort snapshotting after structural persistence (which could let a customer watch a report change while reading it), the revised design gates on fast, local Fixture Identity Resolution first (near-instant — no external call), then runs a **bounded, time-limited** acquisition attempt only where resolution succeeds. Exactly one `LegContextSnapshot` is written per leg, at first-view finalisation, and never rewritten — `AnalyzeBettingSlip`'s own persistence timing (`ADR-007`) is completely unaffected; only the timing of when the *customer is shown* the result changes. Named tradeoff: this can add up to the bound's duration of latency versus pure structural analysis alone.

**Parser Office scope**: Parser Office evaluates (existence, candidate providers, licensing implications, update frequency, reliability, coverage, completeness, quality, cost) and shortlists; it does not select a production provider. Provider selection is Product Office's decision, informed by Parser Office (viability) and Compliance Office (licensing) findings — matching the real, already-established `U-17.2 → U-17.4 → U-17.5` sequence in this repository's own history.

---

## Summary

1. MECI is a parallel, additive evidence layer — `ADR-002`'s deterministic scoring authority is untouched, verified against the actual engine code, not assumed.
2. The current data model cannot support any of this yet — legs carry no structured fixture identity (§2) — and that gap, not provider selection, is the true first blocker.
3. Nine fact families are named and taxonomised (§4), split cleanly between four externally-sourced and five internally-derivable.
4. Nothing here is built, contracted, or decided. Every recommendation is exactly that — recommended, not decided.
