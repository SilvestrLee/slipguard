<?php

use App\Domain\Risk\Factors\IndividualOddsFactor;
use App\Domain\Risk\Results\ReasonCode;

test('a single-leg slip cannot be a relative outlier against itself', function () {
    $result = (new IndividualOddsFactor)->calculate(slipOf([['9.00', 'simple']]));

    expect($result->trace->normalizedValues['max_to_median_ratio'])->toBe('1.0000');
    expect($result->reasonCodes)->not->toContain(ReasonCode::LegOddsOutlier);
});

test('absolute elevation anchor points match the approved table', function (string $maxOdds, string $expectedAbsolute) {
    $result = (new IndividualOddsFactor)->calculate(slipOf([[$maxOdds, 'simple']]));

    expect($result->trace->normalizedValues['absolute_elevation'])->toBe($expectedAbsolute);
})->with([
    ['1.00', '0.0000'],
    ['2.00', '2.0000'],
    ['3.00', '5.0000'],
    ['5.00', '8.0000'],
    ['8.00', '10.0000'],
    ['15.00', '12.0000'],
]);

test('the combined absolute + relative contribution never exceeds the 20-point cap', function () {
    // 1.05 vs 50.00 — deliberately extreme to try to break the cap.
    $result = (new IndividualOddsFactor)->calculate(slipOf([['1.05', 'simple'], ['50.00', 'simple']]));

    expect($result->adjustedContribution->isLessThanOrEqualTo(20))->toBeTrue();
});

test('deterministic median — even leg count averages the two middle values', function () {
    // Sorted: 1.10, 1.20, 1.30, 1.40 -> median of (1.20, 1.30) = 1.25
    $result = (new IndividualOddsFactor)->calculate(slipOf([
        ['1.40', 'simple'], ['1.10', 'simple'], ['1.30', 'simple'], ['1.20', 'simple'],
    ]));

    expect($result->trace->normalizedValues['median_leg_odds'])->toBe('1.2500');
});

test('deterministic median — odd leg count uses the true middle value', function () {
    // Sorted: 1.10, 1.20, 1.30 -> median = 1.20
    $result = (new IndividualOddsFactor)->calculate(slipOf([
        ['1.30', 'simple'], ['1.10', 'simple'], ['1.20', 'simple'],
    ]));

    expect($result->trace->normalizedValues['median_leg_odds'])->toBe('1.2000');
});

test('equal odds produce a ratio of exactly 1.00 and no outlier reason code', function () {
    $result = (new IndividualOddsFactor)->calculate(slipOf(array_fill(0, 4, ['1.50', 'simple'])));

    expect($result->trace->normalizedValues['max_to_median_ratio'])->toBe('1.0000');
    expect($result->reasonCodes)->toBe([]);
});

test('reason codes trigger at the approved anchor points (odds 3.00, ratio 2.0)', function () {
    $elevated = (new IndividualOddsFactor)->calculate(slipOf([['3.00', 'simple']]));
    expect($elevated->reasonCodes)->toContain(ReasonCode::LegOddsElevated);

    $notElevated = (new IndividualOddsFactor)->calculate(slipOf([['2.99', 'simple']]));
    expect($notElevated->reasonCodes)->not->toContain(ReasonCode::LegOddsElevated);

    // Sorted: 1.10, 1.10, 3.00 -> median (odd count) = 1.10; ratio = 3.00/1.10 ≈ 2.73.
    $outlier = (new IndividualOddsFactor)->calculate(slipOf([['1.10', 'simple'], ['1.10', 'simple'], ['3.00', 'simple']]));
    expect($outlier->reasonCodes)->toContain(ReasonCode::LegOddsOutlier);
});
