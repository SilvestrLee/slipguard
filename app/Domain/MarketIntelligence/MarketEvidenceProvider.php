<?php

namespace App\Domain\MarketIntelligence;

interface MarketEvidenceProvider
{
    /** @return array{fixtures: array<int, CanonicalFixture>, quotes: array<int, CanonicalMarketQuote>} */
    public function bulkAcquire(string $competitionKey, array $markets): array;

    /** @return array<int, CanonicalMarketQuote> */
    public function eventMarketQuotes(string $competitionKey, string $providerEventId, array $markets): array;
}
