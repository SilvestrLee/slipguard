<?php

use App\Domain\Risk\Engine\CalculateStructuralRisk;

/**
 * Property-style tests, per the E-06B sprint: order independence, determinism,
 * bounds, monotonicity, and symmetry. These check invariants that must hold
 * across many inputs, complementing the exact-vector tests in
 * CalculateStructuralRiskVectorsTest.
 */
test('reordering legs never changes the result — the engine has no positional bias', function () {
    $legs = [['1.20', 'simple'], ['4.50', 'complex'], ['1.80', 'moderate'], ['2.30', 'simple']];
    $reversed = array_reverse($legs);

    $original = (new CalculateStructuralRisk)->calculate(slipOf($legs));
    $shuffled = (new CalculateStructuralRisk)->calculate(slipOf($reversed));

    expect($shuffled->structuralScore)->toBe($original->structuralScore);
    expect($shuffled->riskBand)->toBe($original->riskBand);
    expect($shuffled->dataQuality->score)->toBe($original->dataQuality->score);
});

test('the same input always produces the same output — no hidden state, no randomness, no clock', function () {
    $legs = [['1.30', 'simple'], ['9.00', 'complex'], ['1.55', 'moderate']];

    $first = (new CalculateStructuralRisk)->calculate(slipOf($legs));
    $second = (new CalculateStructuralRisk)->calculate(slipOf($legs));

    expect($second->structuralScore)->toBe($first->structuralScore);
    expect($second->riskBand)->toBe($first->riskBand);
    expect($second->reasonCodes)->toBe($first->reasonCodes);
    foreach ($first->factorResults as $code => $factorResult) {
        expect((string) $second->factorResults[$code]->adjustedContribution)->toBe((string) $factorResult->adjustedContribution);
    }
});

test('the structural score is always within 0 and 100, across a wide sample of slips', function (array $legs) {
    $result = (new CalculateStructuralRisk)->calculate(slipOf($legs));

    expect($result->structuralScore)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
})->with([
    'single tiny odds' => [[['1.01', 'simple']]],
    'single huge odds' => [[['1000.00', 'complex']]],
    'many mixed legs' => [[['1.10', 'simple'], ['2.00', 'moderate'], ['5.00', 'complex'], ['1.05', 'simple'], ['3.30', 'moderate']]],
    'twenty legs at the cap' => [array_fill(0, 20, ['1.10', 'complex'])],
]);

test('every factors adjusted contribution always stays within 0 and its own cap', function (array $legs) {
    $result = (new CalculateStructuralRisk)->calculate(slipOf($legs));

    foreach ($result->factorResults as $factorResult) {
        expect($factorResult->adjustedContribution->isGreaterThanOrEqualTo(0))->toBeTrue($factorResult->factorCode);
        expect($factorResult->adjustedContribution->isLessThanOrEqualTo($factorResult->cap))->toBeTrue($factorResult->factorCode);
    }
})->with([
    'single tiny odds' => [[['1.01', 'simple']]],
    'single huge odds' => [[['1000.00', 'complex']]],
    'many mixed legs' => [[['1.10', 'simple'], ['2.00', 'moderate'], ['5.00', 'complex'], ['1.05', 'simple'], ['3.30', 'moderate']]],
    'twenty legs at the cap' => [array_fill(0, 20, ['1.10', 'complex'])],
]);

test('adding another leg to an accumulator never decreases the leg-count contribution (monotonicity)', function () {
    $previous = null;
    for ($count = 1; $count <= 20; $count++) {
        $result = (new CalculateStructuralRisk)->calculate(slipOf(array_fill(0, $count, ['1.50', 'simple'])));
        $current = $result->factorResults['RF-001']->baseContribution;

        if ($previous !== null) {
            expect($current->isGreaterThanOrEqualTo($previous))->toBeTrue("leg count {$count}");
        }
        $previous = $current;
    }
});

test('raising the combined odds of a slip never decreases the combined-odds contribution (monotonicity)', function () {
    $samples = ['1.50', '2.00', '3.00', '5.00', '10.00', '25.00', '50.00'];
    $previous = null;

    foreach ($samples as $odds) {
        $result = (new CalculateStructuralRisk)->calculate(slipOf([[$odds, 'simple']]));
        $current = $result->factorResults['RF-002']->baseContribution;

        if ($previous !== null) {
            expect($current->isGreaterThanOrEqualTo($previous))->toBeTrue("odds {$odds}");
        }
        $previous = $current;
    }
});

test('legs with identical odds contribute identical, symmetric shares to concentration — no leg is arbitrarily favored', function () {
    $result = (new CalculateStructuralRisk)->calculate(slipOf(array_fill(0, 5, ['2.00', 'simple'])));

    // Perfectly equal odds must normalize to exactly zero concentration — nothing is "more concentrated" than anything else.
    expect((string) $result->factorResults['RF-004']->baseContribution)->toBe('0.0000');
});
