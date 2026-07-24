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
}
