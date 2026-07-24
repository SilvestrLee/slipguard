<?php

namespace App\Domain\Risk\Normalization;

use App\Domain\Risk\Taxonomy\NormalizationStatus;

final readonly class NormalizedSport
{
    public function __construct(
        public ?string $sportCode,
        public NormalizationStatus $status,
        public string $rawInput,
    ) {}
}
