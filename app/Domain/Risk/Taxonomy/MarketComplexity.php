<?php

namespace App\Domain\Risk\Taxonomy;

/**
 * A structural classification of how many distinct outcomes or conditions a
 * market involves — not an outcome prediction, and never inferred from odds.
 */
enum MarketComplexity: string
{
    case Simple = 'simple';
    case Moderate = 'moderate';
    case Complex = 'complex';

    /**
     * Applies only to unrecognized or unsupported markets.
     */
    case Unknown = 'unknown';
}
