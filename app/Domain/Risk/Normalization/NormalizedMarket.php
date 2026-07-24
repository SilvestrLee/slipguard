<?php

namespace App\Domain\Risk\Normalization;

use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

final readonly class NormalizedMarket
{
    /**
     * @param  array<string, string|null>  $selectionFacts  Family-specific facts, e.g. ['direction' => 'over', 'line' => '2.5'].
     */
    public function __construct(
        public ?string $marketCode,
        public ?MarketFamily $marketFamily,
        public MarketComplexity $complexity,
        public NormalizationStatus $status,
        public array $selectionFacts,
        public string $rawMarketInput,
        public string $rawSelectionInput,
        public string $taxonomyVersion,
    ) {}

    /**
     * A leg whose sport was not recognized as football never gets a market
     * opinion at all — no taxonomy applies, so none is guessed. $sportStatus
     * must be Unsupported or Unrecognized (mirrored directly onto the market
     * result, since "the market is unclassifiable" and "the sport is
     * unclassifiable" are the same fact here).
     */
    public static function notClassifiedForSport(string $rawMarket, string $rawSelection, NormalizationStatus $sportStatus, string $taxonomyVersion): self
    {
        return new self(
            marketCode: null,
            marketFamily: null,
            complexity: MarketComplexity::Unknown,
            status: $sportStatus,
            selectionFacts: [],
            rawMarketInput: $rawMarket,
            rawSelectionInput: $rawSelection,
            taxonomyVersion: $taxonomyVersion,
        );
    }
}
