<?php

use App\Domain\Risk\Factors\LegCountFactor;
use App\Domain\Risk\Results\ReasonCode;

test('every leg-count table value matches the approved table exactly', function (int $legs, int $expected) {
    $slip = slipOf(array_fill(0, $legs, ['1.50', 'simple']));

    $result = (new LegCountFactor)->calculate($slip);

    expect((string) $result->baseContribution)->toBe(number_format($expected, 4, '.', ''));
    expect((string) $result->adjustedContribution)->toBe(number_format($expected, 4, '.', ''));
})->with([
    [1, 0], [2, 2], [3, 5], [4, 8], [5, 11], [6, 13], [7, 15], [8, 17], [9, 18], [10, 19],
    [11, 20], [12, 21], [13, 21], [14, 22], [15, 22], [16, 23], [17, 23], [18, 24], [19, 24], [20, 25],
]);

test('leg count never exceeds its documented maximum of 25', function () {
    expect((new LegCountFactor)->maximumContribution())->toBe(25);
});

test('leg count reason codes match the approved boundaries exactly', function (int $legs, array $expectedCodes) {
    $slip = slipOf(array_fill(0, $legs, ['1.50', 'simple']));
    $result = (new LegCountFactor)->calculate($slip);

    expect($result->reasonCodes)->toBe($expectedCodes);
})->with([
    '1 leg — none' => [1, []],
    '2 legs — none' => [2, []],
    '3 legs — moderate' => [3, [ReasonCode::LegCountModerate]],
    '6 legs — moderate' => [6, [ReasonCode::LegCountModerate]],
    '7 legs — high' => [7, [ReasonCode::LegCountHigh]],
    '12 legs — high' => [12, [ReasonCode::LegCountHigh]],
    '13 legs — extreme' => [13, [ReasonCode::LegCountExtreme]],
    '20 legs — extreme' => [20, [ReasonCode::LegCountExtreme]],
]);

test('leg count contribution is monotonic — more legs never scores lower', function () {
    $previous = null;
    foreach (range(1, 20) as $legs) {
        $slip = slipOf(array_fill(0, $legs, ['1.50', 'simple']));
        $result = (new LegCountFactor)->calculate($slip);

        if ($previous !== null) {
            expect($result->baseContribution->isGreaterThanOrEqualTo($previous))->toBeTrue("legs={$legs} should be >= previous");
        }
        $previous = $result->baseContribution;
    }
});
