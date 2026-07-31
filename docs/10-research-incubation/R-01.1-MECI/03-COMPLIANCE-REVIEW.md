> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not a legal opinion. Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. Requires a real, evidenced Product Office commissioning and Architecture Office/Engineering Office graduation review before any implementation may begin. See `docs/10-research-incubation/README.md`.

# Compliance Review — MECI Provider Licensing, Usage Rights & Commercial Suitability

| Field | Value |
|---|---|
| Scope | Licensing/usage-rights review of the Parser Office's shortlisted candidates: SportMonks (primary), football-data.org (secondary, standings only), Visual Crossing and OpenWeatherMap (weather) |
| Evidentiary basis | Real terms text fetched/searched. Where a fetch returned a partial or indexed-only result rather than the full document, this is disclosed per-provider. |

---

## Executive finding

SportMonks and Visual Crossing both present a licensing posture broadly compatible with SlipGuard's product shape (derived intelligence, not raw-data resale). **OpenWeatherMap's self-service tier is materially different and riskier**: it is licensed under the Open Database License (ODbL), a share-alike/copyleft framework — a different legal mechanism than the "don't resell as a data product" clauses found everywhere else in this review, and one that could, on a strict reading, require SlipGuard to release its own adapted weather-derived data under the same open terms if a permanent snapshot is shown to customers. **Recommendation: exclude OpenWeatherMap's self-service tier entirely**; use Visual Crossing, or OpenWeatherMap's separately-contracted Enterprise tier only.

## Provider-by-provider

**SportMonks** — Cleared, with one open question. Real terms (`sportmonks.com/terms-of-service/`): reselling data without approval is prohibited, but building apps/products using the data — and earning from those derivative works — is explicitly permitted, provided the data itself isn't resold as a competing product. This maps directly onto SlipGuard's own shape. **Historical storage after subscription changes is not addressed in the terms** — silence, not prohibition, requiring a written enquiry (below) before production reliance. Attribution is required only for third-party logos/photos, not general data. Account sharing across developers is prohibited; pricing adjusts for multiple domains. Governing law: Netherlands.

**Visual Crossing** — Cleared, contingent on one written clarification. Real terms (`visualcrossing.com/weather-service-terms/`): *"the data produced by the Service Components may only be stored in a database or other storage and retrieval system if specifically permitted by your license level"* — **permanent storage is not a universal right; it's tier-gated**, and which tier grants it wasn't stated in the page fetched. This is the load-bearing open question for this whole programme's immutable-snapshot design. Redistribution of *derived* results is explicitly permitted, provided raw data isn't exposed. Attribution required at Pay-as-you-go/Pro; waived at Enterprise. Non-compete clause (can't build a competing weather API) is irrelevant to SlipGuard's product.

**football-data.org** — Secondary candidate only (standings), not deep-reviewed. Confirmed: mandatory attribution ("Football data provided by the Football-Data.org API"); API-key confidentiality obligations. Full redistribution/storage terms not confirmed in this pass — requires the same direct-fetch treatment before any contract if elevated beyond its current narrow role.

**OpenWeatherMap** — Self-service (ODbL) tier not recommended. ODbL's share-alike condition requires that if an adapted database is made available *outside your organization*, the adapted database must be offered back under the same ODbL terms — explicitly stated to survive format/schema changes and added calculations, provided the result "remains fundamentally weather data derived from" the source. A `LegContextSnapshot` containing OpenWeatherMap-derived weather facts, shown to a customer, is plausibly "made available outside the organization" under this reading. This has not been tested by anyone qualified to give that opinion. OpenWeatherMap's Enterprise tier is described in its own materials as a separate commercial agreement, reasonably (not confirmed) understood not to carry this obligation.

## Historical storage — the hardest open question

No provider reviewed grants an unconditional right to store data permanently regardless of subscription status. SportMonks is silent. Visual Crossing explicitly gates storage by license tier without naming which tier qualifies. OpenWeatherMap's self-service tier introduces a structurally different (share-alike) risk rather than a simple storage question. **The architecture's `LegContextSnapshot` immutability design is sound and shouldn't be redesigned** — but it can't be relied upon for either primary candidate's data until answered in writing.

One partial mitigation: SlipGuard's own *derived* structural-risk and context findings (not the provider's raw response) are more defensibly SlipGuard's own retained product data than the raw payload is — the same reasoning that generally clears indefinite retention of derived output while raw responses get only short-lived caching.

## Outstanding written enquiries required before production reliance

1. **To SportMonks**: does data retrieved under an active subscription remain lawfully storable/retrievable if the subscription later lapses or is cancelled — specifically, may previously-retrieved injury, suspension, standings, and venue records be retained as part of an immutable historical customer-facing record after that point?
2. **To Visual Crossing**: which license tier(s) specifically permit database storage of retrieved weather data, and does that extend to permanent retention as part of an immutable customer-facing historical record, or only time-bounded operational storage?

**Neither has been sent.** Both are prerequisites before either provider can be relied upon for real production storage.

## Compliance risk register (summary)

| Risk | Provider | Severity |
|---|---|---|
| Share-alike obligation could require releasing SlipGuard's own weather-derived data | OpenWeatherMap (self-service) | High — standout finding |
| Permanent snapshot storage right unconfirmed | SportMonks, Visual Crossing | Medium — blocks confident production reliance, doesn't block internal design work |
| Raw data exposure to customers | All | Low if the architecture's raw/derived separation is enforced in code |
| football-data.org full ToS not yet reviewed | football-data.org | Low while secondary/standings-only |

## Recommended licence considerations

Primary football-data provider: SportMonks, pending the written retention clarification. Weather provider: Visual Crossing, pending the written tier-storage clarification — not OpenWeatherMap's self-service tier.

## Outstanding legal questions (not resolved by this document)

1. Does ODbL's share-alike condition actually reach a customer-facing derived evidence snapshot, or does SlipGuard's transformation fall outside it? Genuinely open, not resolved here.
2. Once answered, do SportMonks'/Visual Crossing's storage terms support *permanent* retention, the specific right the immutable-snapshot design needs?
3. None of this substitutes for qualified external counsel before any public launch.

## Sources

- [Sportmonks Terms of Service](https://www.sportmonks.com/terms-of-service/)
- [Visual Crossing Weather License Terms](https://www.visualcrossing.com/weather-service-terms/)
- [football-data.org — About](https://www.football-data.org/about) / [Pricing](https://www.football-data.org/pricing)
- [OpenWeatherMap — Self-Service Pricing](https://openweathermap.org/price) / [License explainer PDF](https://old.openweathermap.org/storage/app/media/documents/License_explainer_25%20Feb_25.pdf)
- [Open Data Commons Open Database License (ODbL) v1.0](https://opendatacommons.org/licenses/odbl/1-0/)
