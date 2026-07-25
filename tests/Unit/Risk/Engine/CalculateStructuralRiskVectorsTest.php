<?php

use App\Domain\Risk\Engine\CalculateStructuralRisk;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;

/**
 * Every canonical test vector from docs/03-data-science/RISK_RULE_SET_2026_1.md
 * §20, compared exactly against the approved expected values — no tolerance.
 * These numbers were independently verified with a BigDecimal reference
 * script during the rule set's design and review; this test proves the
 * production implementation reproduces them exactly.
 */
test('every canonical vector produces its exact approved score and band', function (string $name, array $legs, int $expectedScore, RiskBand $expectedBand) {
    $slip = slipOf($legs);

    $result = (new CalculateStructuralRisk)->calculate($slip);

    expect($result->structuralScore)->toBe($expectedScore, "{$name}: expected score {$expectedScore}, got {$result->structuralScore}");
    expect($result->riskBand)->toBe($expectedBand, "{$name}: expected band {$expectedBand->value}, got {$result->riskBand?->value}");
})->with([
    'TV-001 Conservative Single' => ['TV-001', [['1.40', 'simple']], 3, RiskBand::Low],
    'TV-002 Aggressive Single' => ['TV-002', [['9.00', 'complex']], 42, RiskBand::Moderate],
    'TV-003 Balanced Two-Leg' => ['TV-003', [['1.50', 'simple'], ['1.60', 'simple']], 9, RiskBand::Low],
    'TV-004 Balanced Three-Leg' => ['TV-004', [['1.40', 'simple'], ['1.50', 'simple'], ['1.60', 'simple']], 16, RiskBand::Low],
    'TV-005 High-Odds Outlier' => ['TV-005', [['1.20', 'simple'], ['1.25', 'simple'], ['1.30', 'simple'], ['4.50', 'simple']], 55, RiskBand::High],
    'TV-006 Balanced Five-Leg' => ['TV-006', [['1.35', 'simple'], ['1.40', 'simple'], ['1.45', 'simple'], ['1.50', 'simple'], ['1.55', 'simple']], 28, RiskBand::Moderate],
    'TV-007 Large Low-Odds Accumulator' => ['TV-007', array_fill(0, 10, ['1.20', 'simple']), 37, RiskBand::Moderate],
    'TV-008 Extreme Accumulator' => ['TV-008', array_fill(0, 20, ['1.90', 'simple']), 54, RiskBand::High],
    'TV-009 Complex-Market Slip' => ['TV-009', [['2.00', 'complex'], ['2.20', 'complex'], ['2.10', 'complex']], 40, RiskBand::Moderate],
    'TV-010 Concentrated Risk' => ['TV-010', [['1.10', 'simple'], ['1.12', 'simple'], ['1.15', 'simple'], ['3.50', 'simple']], 49, RiskBand::Moderate],
    'TV-014 Boundary (3.99)' => ['TV-014', [['3.99', 'simple']], 17, RiskBand::Low],
    'TV-014b Boundary (4.00)' => ['TV-014b', [['4.00', 'simple']], 17, RiskBand::Low],
    'TV-015 Reordered (= TV-004)' => ['TV-015', [['1.60', 'simple'], ['1.50', 'simple'], ['1.40', 'simple']], 16, RiskBand::Low],
    'TV-016 Group-Cap Activation' => ['TV-016', array_fill(0, 20, ['1.50', 'simple']), 53, RiskBand::High],
    'TV-017 Equal-Odds Tie' => ['TV-017', array_fill(0, 4, ['1.50', 'simple']), 22, RiskBand::Low],
    'TV-018 Maximum 20-Leg (mixed)' => ['TV-018', [...array_fill(0, 19, ['1.10', 'simple']), ['20.00', 'simple']], 87, RiskBand::VeryHigh],
    'TV-019 Ceiling Proof' => ['TV-019', [...array_fill(0, 19, ['1.10', 'complex']), ['20.00', 'complex']], 100, RiskBand::VeryHigh],
    'TV-020 Single-Leg Ceiling' => ['TV-020', [['50.00', 'complex']], 54, RiskBand::High],
    'TV-021 Two-Leg Very-High' => ['TV-021', [['1.05', 'complex'], ['50.00', 'complex']], 77, RiskBand::VeryHigh],
]);

test('TV-013 unsupported sport is unavailable, not scored (gate supersedes the old standalone value)', function () {
    // The approved document's TV-013 predates the E-06A gate redesign, which
    // made any non-football leg a hard Tier 1 block (§17). A slip that is
    // entirely a Tennis leg is therefore Unavailable, not scored at 5/Low —
    // this test documents that intentional supersession explicitly.
    $slip = normalizedSlip([unsupportedSportLeg(1, 0, '1.80')]);

    $result = (new CalculateStructuralRisk)->calculate($slip);

    expect($result->availability)->toBe(AnalysisAvailability::Unavailable);
    expect($result->structuralScore)->toBeNull();
    expect($result->riskBand)->toBeNull();
});

test('every vector stays within documented factor and score bounds', function (string $name, array $legs) {
    $slip = slipOf($legs);
    $result = (new CalculateStructuralRisk)->calculate($slip);

    expect($result->structuralScore)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);

    foreach ($result->factorResults as $factorResult) {
        expect($factorResult->adjustedContribution->isGreaterThanOrEqualTo(0))->toBeTrue("{$name}: {$factorResult->factorCode} below 0");
        expect($factorResult->adjustedContribution->isLessThanOrEqualTo($factorResult->cap))->toBeTrue("{$name}: {$factorResult->factorCode} above its own cap");
    }
})->with([
    'TV-005' => ['TV-005', [['1.20', 'simple'], ['1.25', 'simple'], ['1.30', 'simple'], ['4.50', 'simple']]],
    'TV-018' => ['TV-018', [...array_fill(0, 19, ['1.10', 'simple']), ['20.00', 'simple']]],
    'TV-019' => ['TV-019', [...array_fill(0, 19, ['1.10', 'complex']), ['20.00', 'complex']]],
    'TV-021' => ['TV-021', [['1.05', 'complex'], ['50.00', 'complex']]],
]);
