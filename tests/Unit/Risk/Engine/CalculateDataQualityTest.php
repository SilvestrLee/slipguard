<?php

use App\Domain\Risk\Engine\CalculateDataQuality;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

test('a fully-normalized slip scores 100, Strong', function () {
    $slip = slipOf([['1.50', 'simple'], ['1.60', 'moderate']]);

    $result = (new CalculateDataQuality)->calculate($slip);

    expect($result->score)->toBe(100);
    expect($result->band)->toBe(DataQualityBand::Strong);
    expect($result->reasonCodes)->toBe([]);
});

test('one partial market deducts 8 points and stays Strong', function () {
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.50', 'simple', NormalizationStatus::Partial),
        footballLeg(2, 1, '1.60', 'simple'),
    ]);

    $result = (new CalculateDataQuality)->calculate($slip);

    expect($result->score)->toBe(92);
    expect($result->band)->toBe(DataQualityBand::Strong);
    expect($result->reasonCodes)->toBe([ReasonCode::NormalizationPartial]);
});

test('every leg partial reaches Limited via the deduction score alone, capped at -40', function () {
    $legs = [];
    for ($i = 0; $i < 5; $i++) {
        $legs[] = footballLeg($i + 1, $i, '1.50', 'simple', NormalizationStatus::Partial);
    }
    $slip = normalizedSlip($legs);

    $result = (new CalculateDataQuality)->calculate($slip);

    expect($result->score)->toBe(60); // 100 - min(5*8, 40) = 60
    expect($result->band)->toBe(DataQualityBand::Limited);
});

test('partial-normalization deductions alone can never reach Insufficient in Rule Set 2026.1', function () {
    // The -40 category cap floors the worst achievable deduction-only score
    // at 60 (Limited) — Insufficient is only reachable via the Analysis
    // Availability Gate's proportion/sport tiers, not this score alone.
    $legs = [];
    for ($i = 0; $i < 20; $i++) {
        $legs[] = footballLeg($i + 1, $i, '1.50', 'simple', NormalizationStatus::Partial);
    }
    $slip = normalizedSlip($legs);

    $result = (new CalculateDataQuality)->calculate($slip);

    expect($result->score)->toBe(60);
    expect($result->band)->not->toBe(DataQualityBand::Insufficient);
});

test('a non-football leg is excluded from the deduction score entirely — sport issues are Tier 1s job, not this scores', function () {
    $slip = normalizedSlip([
        unsupportedSportLeg(1, 0, '1.50'),
        footballLeg(2, 1, '1.60', 'simple'),
    ]);

    $result = (new CalculateDataQuality)->calculate($slip);

    // Only the football leg is considered; it has no partial market, so the score is a perfect 100.
    expect($result->score)->toBe(100);
});
