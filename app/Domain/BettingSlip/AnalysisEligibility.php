<?php

namespace App\Domain\BettingSlip;

/**
 * Answers one question for the future Risk Engine: can this slip be
 * analysed right now, and if not, why? Keeps that logic out of scattered
 * conditionals in controllers, Livewire components, or the engine itself.
 */
final class AnalysisEligibility
{
    /**
     * @param  array<int, AnalysisIneligibilityReason>  $reasons
     */
    private function __construct(
        public readonly bool $eligible,
        public readonly array $reasons = [],
    ) {}

    public static function eligible(): self
    {
        return new self(true);
    }

    public static function ineligible(AnalysisIneligibilityReason ...$reasons): self
    {
        return new self(false, $reasons);
    }

    /**
     * @return array<int, string>
     */
    public function messages(): array
    {
        return array_map(fn (AnalysisIneligibilityReason $reason) => $reason->message(), $this->reasons);
    }
}
