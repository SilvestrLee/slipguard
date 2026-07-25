<?php

use App\Domain\Risk\Factors\CombinedOddsFactor;
use App\Domain\Risk\Results\ReasonCode;

test('every combined-odds anchor point matches the approved contribution exactly', function (string $odds, string $expected) {
    $slip = slipOf([[$odds, 'simple']]);

    $result = (new CombinedOddsFactor)->calculate($slip);

    expect((string) $result->baseContribution)->toBe($expected);
})->with([
    ['1.00', '0.0000'],
    ['2.00', '3.0000'],
    ['4.00', '7.0000'],
    ['7.00', '10.0000'],
    ['10.00', '14.0000'],
    ['20.00', '17.0000'],
    ['50.00', '20.0000'],
]);

test('combined odds above 50.00 stays capped at the maximum of 20', function () {
    $slip = slipOf([['99.99', 'simple']]);
    $result = (new CombinedOddsFactor)->calculate($slip);

    expect((string) $result->baseContribution)->toBe('20.0000');
});

test('combined odds interpolates smoothly between anchors — no boundary jump', function () {
    $below = (new CombinedOddsFactor)->calculate(slipOf([['3.99', 'simple']]));
    $atBoundary = (new CombinedOddsFactor)->calculate(slipOf([['4.00', 'simple']]));

    // 3.99 and 4.00 are adjacent by one cent — the contribution must differ
    // by a small, proportionate amount, not a discrete jump.
    $difference = $atBoundary->baseContribution->minus($below->baseContribution)->abs();
    expect($difference->isLessThan('0.05'))->toBeTrue((string) $difference);
});

test('combined odds is computed as the product of all leg odds, not summed', function () {
    $slip = slipOf([['2.00', 'simple'], ['3.00', 'simple']]);
    $result = (new CombinedOddsFactor)->calculate($slip);

    expect($result->trace->normalizedValues['combined_decimal_odds'])->toBe('6.0000');
});

test('combined odds reason codes use the approved anchor points as boundaries', function (string $odds, array $expectedCodes) {
    $result = (new CombinedOddsFactor)->calculate(slipOf([[$odds, 'simple']]));

    expect($result->reasonCodes)->toBe($expectedCodes);
})->with([
    'below 2.00 — none' => ['1.50', []],
    'exactly 2.00 — moderate' => ['2.00', [ReasonCode::CombinedOddsModerate]],
    'exactly 10.00 — high' => ['10.00', [ReasonCode::CombinedOddsHigh]],
    'exactly 50.00 — extreme' => ['50.00', [ReasonCode::CombinedOddsExtreme]],
]);

test('combined odds contribution is monotonic across the approved range', function () {
    $samples = ['1.00', '1.50', '2.00', '3.00', '5.00', '8.00', '15.00', '25.00', '40.00', '60.00'];
    $previous = null;

    foreach ($samples as $odds) {
        $result = (new CombinedOddsFactor)->calculate(slipOf([[$odds, 'simple']]));

        if ($previous !== null) {
            expect($result->baseContribution->isGreaterThanOrEqualTo($previous))->toBeTrue("odds={$odds}");
        }
        $previous = $result->baseContribution;
    }
});
