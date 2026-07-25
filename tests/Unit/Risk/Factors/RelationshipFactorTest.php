<?php

use App\Domain\Risk\Factors\RelationshipFactor;
use App\Domain\Risk\Results\ReasonCode;

test('relationship always contributes exactly zero, regardless of input', function () {
    $result = (new RelationshipFactor)->calculate(slipOf([
        ['1.10', 'simple'], ['1.12', 'simple'], ['1.15', 'simple'], ['3.50', 'complex'],
    ]));

    expect((string) $result->baseContribution)->toBe('0.0000');
    expect((string) $result->adjustedContribution)->toBe('0.0000');
});

test('relationship is always visibly marked not-evaluated, never silently omitted', function () {
    $result = (new RelationshipFactor)->calculate(slipOf([['1.50', 'simple']]));

    expect($result->reasonCodes)->toBe([ReasonCode::RelationshipFactorNotEvaluated]);
});

test('relationship has a maximum contribution of exactly zero', function () {
    expect((new RelationshipFactor)->maximumContribution())->toBe(0);
});
