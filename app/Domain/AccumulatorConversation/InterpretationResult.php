<?php

namespace App\Domain\AccumulatorConversation;

use App\Domain\MarketIntelligence\Construction\PlanningBrief;

/**
 * The interpreter's one output shape, for every `RequestClass`. Named
 * constructors keep each class's invariant explicit at the call site
 * (a `criteriaDiscovery()` result always carries a `PlanningBrief` and
 * nothing else; `unsupported()` always carries a plain-language reason,
 * never a fabricated one) rather than leaving every property nullable
 * with no enforced relationship between them.
 */
final readonly class InterpretationResult
{
    private function __construct(
        public RequestClass $requestClass,
        public ?PlanningBrief $planningBrief,
        public ?ExplicitSelectionDraft $explicitSelection,
        public ?string $summary,
        public ?string $clarificationQuestion,
        public ?string $explanation,
    ) {}

    public static function criteriaDiscovery(PlanningBrief $planningBrief, string $summary): self
    {
        return new self(RequestClass::CriteriaDiscovery, $planningBrief, null, $summary, null, null);
    }

    public static function explicitConstruction(ExplicitSelectionDraft $selection, string $summary): self
    {
        return new self(RequestClass::ExplicitConstruction, null, $selection, $summary, null, null);
    }

    public static function needsClarification(string $question): self
    {
        return new self(RequestClass::NeedsClarification, null, null, null, $question, null);
    }

    public static function unsupported(string $explanation): self
    {
        return new self(RequestClass::Unsupported, null, null, null, null, $explanation);
    }
}
