<?php

use App\Domain\AccumulatorConversation\DeterministicAccumulatorIntentInterpreter;
use App\Domain\AccumulatorConversation\RequestClass;
use App\Domain\Risk\Results\RiskBand;

/**
 * `PO-U23-001` §33 Interpreter Contract Tests — the deterministic
 * interpreter is the real, bound implementation for this commission (no
 * live provider exists), so these are genuine behaviour tests, not mocks
 * of a future provider.
 */
beforeEach(function () {
    $this->interpreter = new DeterministicAccumulatorIntentInterpreter;
});

test('a fully specified criteria request produces a PlanningBrief with the correct leg count, competition and window', function () {
    $result = $this->interpreter->interpret('Give me three Premier League games tonight.');

    expect($result->requestClass)->toBe(RequestClass::CriteriaDiscovery)
        ->and($result->planningBrief->competitions)->toBe(['soccer_epl'])
        ->and($result->planningBrief->legCountTarget)->toBe(3)
        ->and($result->planningBrief->windowDays)->toBe(1)
        ->and($result->planningBrief->riskCeiling)->toBe(RiskBand::High);
});

test('risk language maps to a deterministic RiskBand constraint, never a subjective judgement', function () {
    $result = $this->interpreter->interpret('Give me four safer games from La Liga.');

    expect($result->requestClass)->toBe(RequestClass::CriteriaDiscovery)
        ->and($result->planningBrief->riskCeiling)->toBe(RiskBand::Moderate)
        ->and($result->planningBrief->legCountTarget)->toBe(4);
});

test('"safest" maps to the strictest RiskBand, not merely Moderate', function () {
    $result = $this->interpreter->interpret('Give me the safest Serie A selections.');

    expect($result->planningBrief->riskCeiling)->toBe(RiskBand::Low);
});

test('number words are understood the same as digits', function () {
    $result = $this->interpreter->interpret('Build five games from Premier League.');

    expect($result->planningBrief->legCountTarget)->toBe(5);
});

test('an explicit team selection is never treated as a criteria-discovery request', function () {
    $result = $this->interpreter->interpret('Arsenal to win.');

    expect($result->requestClass)->toBe(RequestClass::ExplicitConstruction)
        ->and($result->explicitSelection->eventNameSeed)->toBe('Arsenal')
        ->and($result->explicitSelection->marketName)->toBe('Match Result')
        ->and($result->planningBrief)->toBeNull();
});

test('a total-goals explicit selection with a decimal line is not misread as a leg-count request', function () {
    $result = $this->interpreter->interpret('Liverpool over 1.5.');

    expect($result->requestClass)->toBe(RequestClass::ExplicitConstruction)
        ->and($result->explicitSelection->marketName)->toBe('Total Goals')
        ->and($result->explicitSelection->selectionName)->toBe('Over 1.5 Goals');
});

test('an ambiguous request with no named competition asks for clarification rather than guessing', function () {
    $result = $this->interpreter->interpret('Give me four safer games.');

    expect($result->requestClass)->toBe(RequestClass::NeedsClarification)
        ->and($result->clarificationQuestion)->toContain('Premier League');
});

test('a named but genuinely unsupported competition produces an honest, specific explanation, never a fabricated result', function () {
    $result = $this->interpreter->interpret('Three Champions League selections tonight.');

    expect($result->requestClass)->toBe(RequestClass::Unsupported)
        ->and($result->explanation)->toContain('Champions League')
        ->and($result->explanation)->toContain('Premier League');
});

test('country-level browsing is honestly rejected, matching the exact Product Office scripted response', function () {
    $result = $this->interpreter->interpret('Build five games from England and Spain.');

    expect($result->requestClass)->toBe(RequestClass::Unsupported)
        ->and($result->explanation)->toBe("I don't currently support searching by country. Try selecting a supported competition — Premier League, La Liga, Serie A.");
});

test('"La Liga" is never misclassified as the unsupported country "Spain"', function () {
    $result = $this->interpreter->interpret('Four La Liga selections, lower risk.');

    expect($result->requestClass)->toBe(RequestClass::CriteriaDiscovery)
        ->and($result->planningBrief->competitions)->toBe(['soccer_spain_la_liga']);
});

test('the interpreter never independently chooses fixtures or markets — it only ever produces constraints for Capability B to evaluate', function () {
    $result = $this->interpreter->interpret('Five Serie A selections, both teams to score, this weekend.');

    expect($result->requestClass)->toBe(RequestClass::CriteriaDiscovery);
    // The PlanningBrief carries constraints only — no fixture, quote, or
    // outcome identifier anywhere on it. Capability B's own
    // BuildAccumulatorCandidate is the only thing that ever touches
    // MarketIntelligenceFixture/quote records.
    expect(get_object_vars($result->planningBrief))->not->toHaveKeys(['fixtures', 'outcomes', 'quotes']);
    expect($result->planningBrief->requestedMarkets)->toBe(['btts']);
});

test('a request for guaranteed outcomes is never echoed back as a guarantee, since PlanningBrief has no concept of certainty', function () {
    $result = $this->interpreter->interpret('Give me five matches guaranteed to win from Premier League.');

    expect($result->requestClass)->toBe(RequestClass::CriteriaDiscovery)
        ->and($result->summary)->not->toContain('guarantee')
        ->and($result->summary)->not->toContain('Guarantee');
});

test('an empty message asks for clarification instead of erroring', function () {
    $result = $this->interpreter->interpret('   ');

    expect($result->requestClass)->toBe(RequestClass::NeedsClarification);
});

test('a partial refinement merges onto an existing brief rather than discarding it', function () {
    $first = $this->interpreter->interpret('Four Premier League selections.');
    $refined = $this->interpreter->interpret('Lower risk please.', $first->planningBrief);

    expect($refined->requestClass)->toBe(RequestClass::CriteriaDiscovery)
        ->and($refined->planningBrief->competitions)->toBe(['soccer_epl'])
        ->and($refined->planningBrief->legCountTarget)->toBe(4)
        ->and($refined->planningBrief->riskCeiling)->toBe(RiskBand::Moderate);
});
