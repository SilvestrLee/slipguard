<?php

namespace App\Domain\Risk\Results;

/**
 * Rule Set 2026.1 §16 — deliberately narrow scope: measures only how well
 * an all-football slip's markets normalized (partial-normalization
 * deductions). Sport-level problems are not scored here; they are the
 * Analysis Availability Gate's Tier 1 hard gate (§17), evaluated separately.
 */
final readonly class DataQualityResult
{
    /**
     * @param  array<int, ReasonCode>  $reasonCodes
     * @param  array<int, array{leg_id: int, reason: string, deduction: int}>  $deductions
     */
    public function __construct(
        public int $score,
        public DataQualityBand $band,
        public array $reasonCodes,
        public array $deductions,
    ) {}
}
