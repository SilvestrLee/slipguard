<?php

namespace App\Domain\MarketIntelligence\Construction;

use App\Models\MarketIntelligenceFixture;
use Brick\Math\BigDecimal;

/**
 * U-17.6 §4 — Option C, conservative-lowest available quote, chosen because
 * `U-17.3`'s real trial data showed a different bookmaker present for
 * nearly every market family (Pinnacle for h2h, 1xBet for btts/double_chance,
 * Coral for draw_no_bet) — no single reference bookmaker covers every
 * family. Alphabetical `bookmaker_key` is the stable, arbitrary-but-
 * reproducible tiebreak on an exact price tie, mirroring `U-17.6A`'s own
 * later use of the identical pattern for the same reason: a rule that
 * carries zero informational content is exactly the kind of thing safe to
 * use for tie-breaking, unlike an odds-based rule that carries real
 * (and therefore prediction-adjacent) information.
 */
final class SelectConservativeQuote
{
    /**
     * @return array<string, SelectedOutcomeQuote> keyed by outcome name
     */
    public function forFixtureMarket(MarketIntelligenceFixture $fixture, string $canonicalMarket): array
    {
        $quotes = $fixture->marketQuotes()->where('canonical_market', $canonicalMarket)->get();
        $byOutcome = [];

        foreach ($quotes as $quote) {
            foreach ($quote->outcomes as $outcome) {
                if (! isset($outcome['name'], $outcome['price'])) {
                    continue;
                }

                $name = $outcome['name'];
                $price = BigDecimal::of((string) $outcome['price']);
                $existing = $byOutcome[$name] ?? null;

                $isBetter = $existing === null
                    || $price->isLessThan(BigDecimal::of($existing->price))
                    || ($price->isEqualTo(BigDecimal::of($existing->price)) && $quote->bookmaker_key < $existing->bookmakerKey);

                if (! $isBetter) {
                    continue;
                }

                $byOutcome[$name] = new SelectedOutcomeQuote(
                    outcomeName: $name,
                    price: (string) $outcome['price'],
                    point: isset($outcome['point']) ? (string) $outcome['point'] : null,
                    bookmakerKey: $quote->bookmaker_key,
                    retrievedAt: $quote->retrieved_at,
                    providerLastUpdate: $quote->provider_last_update,
                    evidenceQuality: $quote->evidence_quality,
                    cacheExpiresAt: $quote->cache_expires_at,
                );
            }
        }

        return $byOutcome;
    }
}
