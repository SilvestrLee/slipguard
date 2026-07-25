<?php

namespace App\Domain\Risk\Trace;

/**
 * Rule Set 2026.1 §10 — "No hidden calculations." Every factor's raw and
 * normalized intermediate values, kept separate from its scoring result
 * (FactorResult) so the two concerns — "what did it contribute" and "how
 * was that number produced" — stay distinct.
 */
final readonly class FactorTrace
{
    /**
     * @param  array<string, string|int|null>  $rawInputs  e.g. ['leg_count' => 3]
     * @param  array<string, string>  $normalizedValues  e.g. ['combined_decimal_odds' => '3.3600']
     * @param  array<int, string>  $notes  Free-form, human-readable calculation notes.
     */
    public function __construct(
        public array $rawInputs,
        public array $normalizedValues,
        public array $notes,
    ) {}
}
