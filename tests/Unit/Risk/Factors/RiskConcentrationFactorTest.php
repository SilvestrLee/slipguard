<?php

use App\Domain\Risk\Factors\RiskConcentrationFactor;
use App\Domain\Risk\Results\ReasonCode;

test('a single-leg slip contributes exactly zero — concentration has no meaning for one leg', function () {
    $result = (new RiskConcentrationFactor)->calculate(slipOf([['50.00', 'complex']]));

    expect((string) $result->baseContribution)->toBe('0.0000');
    expect($result->reasonCodes)->toBe([]);
});

test('equal odds produce a normalized concentration of exactly zero', function () {
    $result = (new RiskConcentrationFactor)->calculate(slipOf(array_fill(0, 4, ['1.50', 'simple'])));

    expect($result->trace->normalizedValues['normalized_concentration'])->toBe('0.000000');
    expect((string) $result->baseContribution)->toBe('0.0000');
});

test('one dominant leg among modest ones produces a high normalized concentration', function () {
    $result = (new RiskConcentrationFactor)->calculate(slipOf([
        ['1.10', 'simple'], ['1.12', 'simple'], ['1.15', 'simple'], ['3.50', 'moderate'],
    ]));

    expect($result->baseContribution->isGreaterThan(5))->toBeTrue((string) $result->baseContribution);
});

test('never exceeds its documented maximum of 15', function () {
    // Maximally concentrated: one huge leg among many tiny ones.
    $result = (new RiskConcentrationFactor)->calculate(slipOf([
        ...array_fill(0, 19, ['1.01', 'simple']), ['100.00', 'simple'],
    ]));

    expect($result->adjustedContribution->isLessThanOrEqualTo(15))->toBeTrue();
});

test('reason codes trigger at the approved normalized-concentration thresholds', function () {
    $concentrated = (new RiskConcentrationFactor)->calculate(slipOf([
        ['1.10', 'simple'], ['1.12', 'simple'], ['1.15', 'simple'], ['3.50', 'moderate'],
    ]));
    expect($concentrated->reasonCodes)->toContain(ReasonCode::RiskConcentrated);

    $highlyConcentrated = (new RiskConcentrationFactor)->calculate(slipOf([
        ...array_fill(0, 19, ['1.10', 'simple']), ['20.00', 'simple'],
    ]));
    expect($highlyConcentrated->reasonCodes)->toContain(ReasonCode::RiskHighlyConcentrated);
});
