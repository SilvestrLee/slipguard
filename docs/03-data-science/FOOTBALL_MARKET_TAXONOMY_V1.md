# Football Market Taxonomy — Version 1.0

**Status:** Approved for engine preparation. Does not itself score risk.

## Purpose

Converts free-text sport, market, and selection input from the manual slip builder into stable, deterministic internal codes the future Risk Engine can consume. This is normalization, not prediction: it never answers which selection will win, whether a market is safe, or what a risk score should be.

Canonical source of truth for the exact alias lists is code, not this document: `App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1` and `App\Domain\Risk\Normalization\NormalizeSport`. This document is the policy record — what the taxonomy covers and why — not a duplicate copy of every alias, to avoid the two drifting apart.

## Version

`football_market_taxonomy_version: 1.0` (`FootballMarketTaxonomyV1::VERSION`). Taxonomy behavior must not change without incrementing this version.

## Sport Support

Only **football** (aliases: football, soccer, association football) is fully supported in v1. A fixed list of other real sports (tennis, basketball, cricket, baseball, horse racing, esports, virtual sports) is recognized by name but marked `unsupported` — distinct from `unrecognized`, which means the input didn't match anything at all. Matching is exact (case/whitespace-normalized), never fuzzy.

## Market Families (v1)

| Family | Market Code | Complexity |
|---|---|---|
| Match Result | `football.match_result.1x2` | Simple |
| Double Chance | `football.match_result.double_chance` | Simple |
| Draw No Bet | `football.match_result.draw_no_bet` | Simple |
| Total Goals | `football.total_goals.over_under` | Simple |
| Both Teams to Score | `football.goals.both_teams_to_score` | Moderate |
| Team Total Goals | `football.team_goals.over_under` | Moderate |
| Correct Score | `football.score.correct_score` | Complex |
| Half-Time Result | `football.half_time.result` | Moderate |
| Half-Time / Full-Time | `football.match_result.half_time_full_time` | Complex |

Total Goals also recognizes any `over`/`under` + decimal line embedded directly in the market text (e.g. "Over 3.5 Goals") via a fixed pattern, not just the literal example alias — the line itself is a selection fact, not part of the market code.

### Deliberately deferred (not built in v1)

- **Handicap markets** (Asian/European). Alias text alone cannot reliably tell the two apart, and this taxonomy does not guess — recognizing this family is deferred until a reliable disambiguation approach exists.
- **Team Total Goals** sub-facts (team scope, direction, line) — the market family is recognized, but per-fact extraction (home/away/named team, over/under, line) is deferred; no fixture in this sprint required it, and guessing team scope from free text risked exactly the kind of inference this taxonomy is meant to avoid.
- Player props, corners, cards, bet builders, winning margin, method of victory, and other niche markets remain `unrecognized`/`unsupported` by design — see Engine Contract §11.

## Normalization Status

- **complete** — every required fact for the recognized family was deterministically extracted.
- **partial** — the family was recognized, but a secondary fact (e.g. the total-goals line) could not be parsed.
- **unrecognized** — the input matched nothing in the approved catalogue.
- **unsupported** — the input names a real, known sport that this taxonomy version intentionally doesn't cover.

## Known Interim Limitation

Every leg entered today via the manual slip builder produces free text only — there is no sport/market picker. This means normalization runs entirely at analysis-preparation time, and until a market is one of the families above, its `market_complexity` is `unknown` and `data_quality` (once implemented) will reflect that. This is expected and policy-compliant (see Engine Contract §27), not a bug — worth remembering when interpreting early data-quality metrics.

## Matching Pipeline

Trim → collapse internal whitespace → lowercase → normalize hyphens to spaces (market text only, not selections, to preserve scores like "2-1") → exact alias lookup → controlled pattern extraction (total goals line, BTTS yes/no, correct score) → return complete/partial/unrecognized/unsupported. No fuzzy matching, no Levenshtein distance, no machine learning, no AI, at any step.
