> **STATUS: EXPLORATORY — NOT CONSTITUTIONAL HISTORY.** Not cited by `TASKS.md`, `DECISION_LOG.md`, any ADR, or office documentation. Requires a real, evidenced Product Office commissioning and Architecture Office/Engineering Office graduation review before any implementation may begin. See `docs/10-research-incubation/README.md`.

# Parser Office Phase 0 — MECI Evidence Availability, Source Viability & Operational Assessment

| Field | Value |
|---|---|
| Scope | Real-world evidence-source viability for the nine Context Fact Taxonomy v1 families in `01-ARCHITECTURE-DISCOVERY.md` |
| Evidentiary basis | Live web search and direct page fetches. Where a fetch was blocked (`403`), the finding is drawn from search-indexed summaries of the same page, labelled accordingly. No live API trial was performed (requires real account creation, not authorized in this exercise). |

---

## Executive finding

Real, viable sources exist for most families. **SportMonks** is the strongest single candidate — Injuries/Suspensions, Standings, and geocoordinate-bearing Venue data are all included in its base **Starter** tier (€29/month, 5 leagues). Weather is architecturally independent of the football-data-provider decision — a separate, general-purpose meteorological API. **One real commercial product must not be adopted at all**: SportMonks' "Expected Lineups" is an algorithmic prediction, not a confirmed fact.

## The finding that matters most: predicted lineups are not evidence

SportMonks' "Premium Expected Lineups" — the mainstream commercial answer to "expected lineup availability" — is explicitly **an algorithmic prediction**: generated "using past data, squad lists, injuries/suspensions, and tactics," carrying a `lineup_confirmed: false` flag until the club's own official announcement replaces it. Its own published accuracy figures (Bundesliga/Serie A/Eredivisie 87–88%, EPL 84%, La Liga 75%) mean roughly one in eight to one in four predictions for major leagues does not match reality. Using this product for "Expected Lineup Availability" would mean presenting an algorithmic guess as fact — a direct conflict with the architecture's own non-goal for that family and with `ADR-003`.

**Recommendation**: derive this family only from (1) the Injuries & Suspensions family (confirmed-out players, already fact) and (2) genuinely official confirmed lineups once released (industry-typical timing ~60 minutes pre-kickoff, not independently reverified here). Direct consequence: for any snapshot finalised well before kickoff — the normal case — this family will legitimately show `Unavailable` beyond whatever confirmed absences are already known. That's correct behaviour for a family whose one non-predictive source is genuinely time-boxed, not a defect.

## Coverage, freshness, reliability (summary)

| Family | Best candidate | Plan tier | Reliability class |
|---|---|---|---|
| Injuries & Suspensions | SportMonks (`sidelined` entity) | All plans, Starter upward | Semi-official — domain-wide underreporting is a real ceiling, not vendor-specific |
| Weather | Visual Crossing or OpenWeatherMap | Both viable free tiers for MVP volume | Official-equivalent as *forecast*, never certainty |
| League Context (standings) | football-data.org / SportMonks / API-Football (any) | Varies | Official-equivalent |
| Fixture Congestion, Rest Days, Head-to-Head | Derived from any candidate's own fixture/result history | Same as base feed | Official-equivalent; head-to-head depth limited on entry tiers |
| Travel Burden | Derived, requires venue coordinates — **confirmed present on SportMonks**; unconfirmed on API-Football | Same as base feed | Official-equivalent, contingent on coordinate confirmation |

## Fixture identity — no cross-provider shortcut exists

No universal fixture identifier exists across providers; each maintains its own internally-stable ID. Sportradar sells a dedicated commercial "Mapping API" specifically for cross-provider ID translation — third-party evidence this is a recognized, monetized industry problem, not a gap specific to smaller providers. This confirms the architecture's own exact-alias-first, no-fuzzy-matching design is necessary, not merely cautious.

## Derived evidence — validated

Four of five derived families (Congestion, Rest Days, League Context, Head-to-Head) are high-confidence arithmetic over data any football-data candidate already returns. Travel Burden is conditional on venue-coordinate presence (confirmed for SportMonks only in this pass).

## Operational risks

- Predicted evidence mistaken for confirmed fact (see above) — the standout new risk.
- Injury/suspension underreporting — domain-wide, not fixable by provider choice.
- Geographic coverage: NPFL and most non-top-5-European coverage remains unconfirmed for every family in this document, not only odds.
- Fixture-identity fragmentation — real, requires exact-match-first design.

## Cost overview (indicative)

SportMonks Starter €29/month (Injuries/Suspensions/Standings/Venues, 5 leagues). API-Football Pro $19/month (injuries-endpoint tier unconfirmed — pricing/terms pages returned `403`). OpenWeatherMap free tier ~1M calls/month. Visual Crossing free 1,000 records/day, $0.0001/record paid.

## Recommendation (evaluate, not select — Parser Office does not choose the production provider)

SportMonks recommended as the primary football-context candidate; Visual Crossing/OpenWeatherMap as an independent weather decision. Provider *selection* remains Product Office's, informed by this evaluation and by Compliance Office's licensing review — matching the real `U-17.2 → U-17.4 → U-17.5` sequence already established in this repository's own history for a different capability.

## Sources

- [New Endpoint Injuries — API-FOOTBALL](https://www.api-football.com/news/post/new-endpoint-injuries)
- [Injuries and Suspensions — Sportmonks](https://www.sportmonks.com/glossary/injuries-and-suspensions/)
- [Premium Expected Lineups — Sportmonks](https://www.sportmonks.com/blogs/premium-expected-lineups/)
- [Sportmonks Football API plans & pricing](https://www.sportmonks.com/football-api/plans-pricing/)
- [football-data.org — Competition/standings docs](https://docs.football-data.org/general/v4/competition.html)
- [OpenWeatherMap pricing](https://openweathermap.org/price)
- [Visual Crossing weather API pricing](https://www.visualcrossing.com/weather-data-pricing/)
- [Sportradar — Soccer ID Handling / Mapping API](https://developer.sportradar.com/soccer/docs/soccer-ig-id-handling)
- [Sportmonks — Venues endpoints](https://docs.sportmonks.com/v3/endpoints-and-entities/endpoints/venues/get-all-venues)
