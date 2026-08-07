<?php

namespace App\Domain\AccumulatorConversation;

use App\Domain\MarketIntelligence\Construction\PlanningBrief;

/**
 * `PO-U23-001` §19 — the application-level abstraction the rest of
 * SlipGuard depends on, never a specific provider SDK directly. Per
 * `PO-U23-001` Decision 1: no live provider is wired for this commission
 * — the bound implementation (`DeterministicAccumulatorIntentInterpreter`)
 * is real, rule-based, and makes no external request, so this interface
 * exists for the substitution/testing value now and the live-provider
 * swap later, not as unused scaffolding.
 */
interface AccumulatorIntentInterpreter
{
    /**
     * @param  string  $message  The customer's natural-language request.
     * @param  PlanningBrief|null  $existingPlanningBrief  A brief already
     *                                                     established earlier in the same exchange (e.g. after a
     *                                                     clarification question), to be refined rather than
     *                                                     discarded. Multi-turn conversation history/memory beyond
     *                                                     this single refinement step is explicitly deferred
     *                                                     (`PO-U23-001`).
     */
    public function interpret(string $message, ?PlanningBrief $existingPlanningBrief = null): InterpretationResult;
}
