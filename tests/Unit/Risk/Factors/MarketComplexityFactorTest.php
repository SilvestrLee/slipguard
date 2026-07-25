<?php

use App\Domain\Risk\Factors\MarketComplexityFactor;
use App\Domain\Risk\Results\ReasonCode;

test('all-simple legs contribute exactly zero', function () {
    $result = (new MarketComplexityFactor)->calculate(slipOf(array_fill(0, 3, ['1.50', 'simple'])));

    expect((string) $result->baseContribution)->toBe('0.0000');
});

test('all-complex legs contribute exactly the maximum of 10', function () {
    $result = (new MarketComplexityFactor)->calculate(slipOf(array_fill(0, 3, ['1.50', 'complex'])));

    expect((string) $result->baseContribution)->toBe('10.0000');
});

test('a mix of complexities averages proportionally', function () {
    // complex(2) + complex(2) + moderate(1) = 5, / 3 legs = 1.6667 avg -> (1.6667/2)*10 = 8.3333
    $result = (new MarketComplexityFactor)->calculate(slipOf([
        ['2.00', 'complex'], ['2.20', 'complex'], ['2.10', 'moderate'],
    ]));

    expect((string) $result->baseContribution)->toBe('8.3333');
});

test('an unknown-complexity leg is excluded from the average, never treated as complex', function () {
    // Two simple legs + one unknown -> average is over the 2 simple legs only (0), not diluted by the unknown leg.
    $result = (new MarketComplexityFactor)->calculate(slipOf([
        ['1.50', 'simple'], ['1.60', 'simple'], ['1.70', 'unknown'],
    ]));

    expect((string) $result->baseContribution)->toBe('0.0000');
});

test('a slip with no recognized markets at all contributes exactly zero, not an invented default', function () {
    $result = (new MarketComplexityFactor)->calculate(slipOf(array_fill(0, 3, ['1.50', 'unknown'])));

    expect((string) $result->baseContribution)->toBe('0.0000');
    expect($result->reasonCodes)->toBe([]);
});

test('reason codes trigger at the approved average thresholds', function () {
    $present = (new MarketComplexityFactor)->calculate(slipOf([['1.50', 'moderate'], ['1.60', 'moderate']]));
    expect($present->reasonCodes)->toBe([ReasonCode::MarketComplexityPresent]);

    $high = (new MarketComplexityFactor)->calculate(slipOf([['1.50', 'complex'], ['1.60', 'moderate']]));
    expect($high->reasonCodes)->toBe([ReasonCode::MarketComplexityHigh]);
});
