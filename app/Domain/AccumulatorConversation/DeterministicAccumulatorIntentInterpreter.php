<?php

namespace App\Domain\AccumulatorConversation;

use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Domain\MarketIntelligence\MapOddsApiMarket;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;

/**
 * `PO-U23-001` Decision 1 — the real, bound implementation for this
 * commission. Not a "fake for tests only": no live AI provider exists
 * anywhere in this codebase (confirmed during Phase 1 reconnaissance), so
 * this deterministic, rule-based parser is what actually runs when a
 * customer types a request. Same discipline as `ParseSlipText` (this
 * codebase's other deterministic natural-language parser): a bounded,
 * disclosed vocabulary, never a guess — an unrecognised request asks for
 * clarification or explains the real limitation rather than inventing
 * one.
 *
 * Explicitly out of scope for this parser (`PO-U23-001` Decision 3/4 and
 * the Deferred Work list): country-level browsing, team-level discovery,
 * multi-turn conversation memory beyond one refinement step, and the
 * mixed explicit-selection-plus-discovery request class.
 */
final class DeterministicAccumulatorIntentInterpreter implements AccumulatorIntentInterpreter
{
    /**
     * Phrases naming a real football competition/country this parser
     * recognises as *not* currently supported — used only to produce an
     * honest, specific explanation instead of silent failure or a guess.
     * Deliberately not exhaustive: an unrecognised phrase not in this
     * list still falls through to needsClarification() rather than being
     * assumed unsupported.
     *
     * @var array<int, string>
     */
    private const KNOWN_UNSUPPORTED_COMPETITIONS = [
        'champions league', 'europa league', 'conference league',
        'bundesliga', 'ligue 1', 'eredivisie', 'primeira liga',
        'championship', 'mls', 'premiership',
    ];

    /** @var array<int, string> */
    private const KNOWN_UNSUPPORTED_COUNTRIES = [
        'england', 'spain', 'italy', 'germany', 'france', 'portugal', 'netherlands', 'scotland',
    ];

    /** @var array<string, int> */
    private const NUMBER_WORDS = [
        'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
        'six' => 6, 'seven' => 7, 'eight' => 8,
    ];

    /** @var array<int, string> */
    private const LOWER_RISK_PHRASES = ['lower risk', 'lower-risk', 'safer', 'conservative', 'less risky', 'avoid risky', 'safest', 'very low risk'];

    /** @var array<int, string> */
    private const MARKET_PHRASES = [
        'h2h' => 'match result',
        'totals' => 'total goals',
        'double_chance' => 'double chance',
        'draw_no_bet' => 'draw no bet',
        'btts' => 'both teams to score',
    ];

    public function interpret(string $message, ?PlanningBrief $existingPlanningBrief = null): InterpretationResult
    {
        $normalized = mb_strtolower(trim($message));

        if ($normalized === '') {
            return InterpretationResult::needsClarification(
                __("I didn't catch that — tell me the competition, how many selections, and any risk preference."),
            );
        }

        if ($unsupported = $this->detectUnsupportedScope($normalized)) {
            return InterpretationResult::unsupported($unsupported);
        }

        if ($explicit = $this->detectExplicitSelection($message, $normalized)) {
            return $explicit;
        }

        return $this->interpretAsCriteriaDiscovery($normalized, $existingPlanningBrief);
    }

    /**
     * `PO-U23-001` Decision 3 — honest failure for a real football
     * competition/country this parser recognises by name but Capability B
     * genuinely does not support today (`config('slipguard-market-intelligence.allowed_competitions')`
     * is exactly three specific competitions, never a country).
     */
    private function detectUnsupportedScope(string $normalized): ?string
    {
        foreach (self::KNOWN_UNSUPPORTED_COMPETITIONS as $competition) {
            if (str_contains($normalized, $competition)) {
                return __("I can't currently build accumulators for :competition — SlipGuard's Builder supports :supported today.", [
                    'competition' => ucwords($competition),
                    'supported' => $this->supportedCompetitionsList(),
                ]);
            }
        }

        // A bare country name is only an unsupported signal if none of the
        // real supported competitions is also named — "Spain" alone means
        // country browsing (unsupported); "La Liga" always means the real,
        // supported competition, even though it's also a Spanish league.
        if ($this->detectCompetitionKeys($normalized) !== []) {
            return null;
        }

        foreach (self::KNOWN_UNSUPPORTED_COUNTRIES as $country) {
            if (str_contains($normalized, $country)) {
                return __("I don't currently support searching by country. Try selecting a supported competition — :supported.", [
                    'supported' => $this->supportedCompetitionsList(),
                ]);
            }
        }

        return null;
    }

    private function supportedCompetitionsList(): string
    {
        return implode(', ', array_values(config('slipguard-market-intelligence.allowed_competitions')));
    }

    /**
     * `PO-U23-001` Decision 4, Request Class B — a single named team
     * paired with a recognised market phrase ("Arsenal to win",
     * "Liverpool over 1.5"). Deliberately narrow: plural/leg-count
     * language ("games", "matches", "selections", a number of legs)
     * always routes to criteria discovery instead, even if a market
     * phrase also appears, since that combination means "find games
     * matching this kind of market," not one specific selection.
     */
    private function detectExplicitSelection(string $original, string $normalized): ?InterpretationResult
    {
        // A bare integer ("4 games") signals criteria discovery; a decimal
        // ("over 1.5") is an odds/goal-line value and must not trigger the
        // same guard — checked separately so "Liverpool over 1.5" isn't
        // misread as a leg-count request.
        if (preg_match('/\b(games?|matches?|selections?|legs?|fixtures?)\b/', $normalized)
            || preg_match('/(?<![\d.])\b\d+\b(?!\.\d)/', $normalized)
            || $this->detectLegCountWord($normalized) !== null) {
            return null;
        }

        if (preg_match('/^([\p{Lu}][\p{L}\' ]{1,30}?)\s+to\s+win\b/u', trim($original), $matches)) {
            $team = trim($matches[1]);

            return InterpretationResult::explicitConstruction(
                new ExplicitSelectionDraft($team, 'Match Result', __(':team to win', ['team' => $team])),
                __('Got it — :team to win. Opening the Builder so you can pick the fixture and confirm the odds.', ['team' => $team]),
            );
        }

        if (preg_match('/^([\p{Lu}][\p{L}\' ]{1,30}?)\s+(over|under)\s+(\d+(?:\.\d+)?)\b/iu', trim($original), $matches)) {
            $team = trim($matches[1]);
            $direction = ucfirst(mb_strtolower($matches[2]));
            $line = $matches[3];

            return InterpretationResult::explicitConstruction(
                new ExplicitSelectionDraft($team, 'Total Goals', "{$direction} {$line} Goals"),
                __('Got it — :direction :line goals. Opening the Builder so you can confirm which fixture and the odds.', [
                    'direction' => $direction, 'line' => $line,
                ]),
            );
        }

        foreach ((new FootballMarketTaxonomyV1)->definitions() as $definition) {
            foreach ($definition->aliases as $alias) {
                if (str_contains($normalized, $alias) && preg_match('/^([\p{Lu}][\p{L}\' ]{1,30})/u', trim($original), $matches)) {
                    $team = trim($matches[1]);

                    return InterpretationResult::explicitConstruction(
                        new ExplicitSelectionDraft($team, $definition->displayName, trim($original)),
                        __('Got it. Opening the Builder so you can confirm the fixture, market and odds.'),
                    );
                }
            }
        }

        return null;
    }

    private function interpretAsCriteriaDiscovery(string $normalized, ?PlanningBrief $existingPlanningBrief): InterpretationResult
    {
        $competitions = $this->detectCompetitionKeys($normalized) ?: ($existingPlanningBrief?->competitions ?? []);

        if ($competitions === []) {
            return InterpretationResult::needsClarification(
                __('Which competition should I look at — :supported?', ['supported' => $this->supportedCompetitionsList()]),
            );
        }

        $legCount = $this->detectLegCount($normalized) ?? $existingPlanningBrief?->legCountTarget ?? 4;
        $windowDays = $this->detectWindowDays($normalized) ?? $existingPlanningBrief?->windowDays ?? 3;
        $riskCeiling = $this->detectRiskCeiling($normalized) ?? $existingPlanningBrief?->riskCeiling ?? RiskBand::High;
        $markets = $this->detectMarkets($normalized) ?: ($existingPlanningBrief?->requestedMarkets ?? config('slipguard-market-intelligence.default_markets'));

        $brief = new PlanningBrief(
            competitions: $competitions,
            windowDays: $windowDays,
            legCountTarget: $legCount,
            requestedMarkets: $markets,
            targetOddsMin: null,
            targetOddsMax: null,
            riskCeiling: $riskCeiling,
        );

        $competitionLabels = implode(', ', array_map(
            fn (string $key) => config("slipguard-market-intelligence.allowed_competitions.{$key}"),
            $competitions,
        ));

        $summary = $riskCeiling !== RiskBand::High
            ? __('Got it — :count selections from :competitions, aiming for a :risk risk ceiling. Checking available fixtures…', [
                'count' => $legCount, 'competitions' => $competitionLabels, 'risk' => mb_strtolower($riskCeiling->label()),
            ])
            : __('Got it — :count selections from :competitions. Checking available fixtures…', [
                'count' => $legCount, 'competitions' => $competitionLabels,
            ]);

        return InterpretationResult::criteriaDiscovery($brief, $summary);
    }

    /** @return array<int, string> */
    private function detectCompetitionKeys(string $normalized): array
    {
        $keys = [];

        foreach (config('slipguard-market-intelligence.allowed_competitions') as $key => $label) {
            if (str_contains($normalized, mb_strtolower($label))) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    private function detectLegCount(string $normalized): ?int
    {
        if (preg_match('/\b(\d+)\s*(games?|matches?|selections?|legs?)\b/', $normalized, $matches)) {
            return max(2, min(8, (int) $matches[1]));
        }

        return $this->detectLegCountWord($normalized);
    }

    private function detectLegCountWord(string $normalized): ?int
    {
        foreach (self::NUMBER_WORDS as $word => $value) {
            if (preg_match('/\b'.$word.'\b\s*(games?|matches?|selections?|legs?)?/', $normalized)) {
                return $value;
            }
        }

        return null;
    }

    private function detectWindowDays(string $normalized): ?int
    {
        return match (true) {
            str_contains($normalized, 'tonight') || str_contains($normalized, 'today') => 1,
            str_contains($normalized, 'tomorrow') => 2,
            str_contains($normalized, 'this weekend') || str_contains($normalized, 'weekend') => 3,
            str_contains($normalized, 'next week') => 7,
            default => null,
        };
    }

    private function detectRiskCeiling(string $normalized): ?RiskBand
    {
        foreach (self::LOWER_RISK_PHRASES as $phrase) {
            if (str_contains($normalized, $phrase)) {
                return str_contains($phrase, 'safest') || str_contains($phrase, 'very low')
                    ? RiskBand::Low
                    : RiskBand::Moderate;
            }
        }

        return null;
    }

    /** @return array<int, string> */
    private function detectMarkets(string $normalized): array
    {
        $map = new MapOddsApiMarket;
        $keys = [];

        foreach (self::MARKET_PHRASES as $providerKey => $phrase) {
            if (str_contains($normalized, $phrase) && in_array($providerKey, $map->supportedProviderKeys(), true)) {
                $keys[] = $providerKey;
            }
        }

        return $keys;
    }
}
