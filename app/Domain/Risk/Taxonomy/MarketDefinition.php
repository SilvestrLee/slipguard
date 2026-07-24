<?php

namespace App\Domain\Risk\Taxonomy;

final readonly class MarketDefinition
{
    /**
     * @param  string  $marketCode  Stable, permanent identifier, e.g. "football.total_goals.over_under".
     * @param  array<int, string>  $aliases  Normalized (lowercase, trimmed, single-spaced) alias strings.
     */
    public function __construct(
        public string $marketCode,
        public MarketFamily $family,
        public string $displayName,
        public array $aliases,
        public MarketComplexity $complexity,
    ) {}
}
