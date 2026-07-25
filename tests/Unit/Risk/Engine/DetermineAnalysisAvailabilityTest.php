<?php

use App\Domain\Risk\Engine\DetermineAnalysisAvailability;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

test('an all-football, fully-normalized slip with perfect data quality is Full', function () {
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.50'),
        footballLeg(2, 1, '1.60'),
    ]);

    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 100);

    expect($result->availability)->toBe(AnalysisAvailability::Full);
    expect($result->effectiveBand)->toBe(DataQualityBand::Strong);
    expect($result->reasonCodes)->toBe([]);
});

test('Tier 1 — any non-football leg makes the whole slip Unavailable, regardless of everything else', function () {
    $slip = normalizedSlip([
        unsupportedSportLeg(1, 0, '1.50'),
        footballLeg(2, 1, '1.60'),
    ]);

    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 100);

    expect($result->availability)->toBe(AnalysisAvailability::Unavailable);
    expect($result->effectiveBand)->toBe(DataQualityBand::Insufficient);
    expect($result->reasonCodes)->toBe([ReasonCode::SportUnsupported, ReasonCode::AnalysisUnavailable]);
});

test('Tier 2 — an unrecognized-market proportion over 25% makes the slip Unavailable', function () {
    // 2 of 4 legs unrecognized = 50%, over the 25% limit.
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.50', 'simple', NormalizationStatus::Unrecognized),
        footballLeg(2, 1, '1.50', 'simple', NormalizationStatus::Unrecognized),
        footballLeg(3, 2, '1.50'),
        footballLeg(4, 3, '1.50'),
    ]);

    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 100);

    expect($result->availability)->toBe(AnalysisAvailability::Unavailable);
    expect($result->effectiveBand)->toBe(DataQualityBand::Insufficient);
    expect($result->reasonCodes)->toBe([ReasonCode::MarketUnrecognized, ReasonCode::AnalysisUnavailable]);
});

test('Tier 2 — exactly 25% unrecognized does not trigger the hard gate, but caps the band at Limited', function () {
    // 1 of 4 legs unrecognized = exactly 25%, not over the limit.
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.50', 'simple', NormalizationStatus::Unrecognized),
        footballLeg(2, 1, '1.50'),
        footballLeg(3, 2, '1.50'),
        footballLeg(4, 3, '1.50'),
    ]);

    // Data quality is otherwise perfect, so the cap is what pulls the band down.
    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 100);

    expect($result->availability)->toBe(AnalysisAvailability::Limited);
    expect($result->effectiveBand)->toBe(DataQualityBand::Limited);
    expect($result->reasonCodes)->toBe([ReasonCode::AnalysisLimited, ReasonCode::MarketUnrecognized]);
});

test('Tier 3 — a data quality score below 40 makes the slip Unavailable with no unrecognized markets involved', function () {
    $slip = normalizedSlip([footballLeg(1, 0, '1.50')]);

    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 39);

    expect($result->availability)->toBe(AnalysisAvailability::Unavailable);
    expect($result->effectiveBand)->toBe(DataQualityBand::Insufficient);
    expect($result->reasonCodes)->toBe([ReasonCode::AnalysisUnavailable]);
});

test('Tier 3 — a data quality score in the Limited range alone produces Limited, without a market-unrecognized reason code', function () {
    $slip = normalizedSlip([footballLeg(1, 0, '1.50')]);

    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 50);

    expect($result->availability)->toBe(AnalysisAvailability::Limited);
    expect($result->effectiveBand)->toBe(DataQualityBand::Limited);
    expect($result->reasonCodes)->toBe([ReasonCode::AnalysisLimited]);
});

test('the effective band is always the worse of Tier 2s cap and Tier 3s score-derived band', function () {
    // Tier 2 cap is Limited (25% unrecognized), but Tier 3's own score (30) is already worse (Insufficient) —
    // the worse of the two must win, and Insufficient means Unavailable even though Tier 2 alone would only Limit.
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.50', 'simple', NormalizationStatus::Unrecognized),
        footballLeg(2, 1, '1.50'),
        footballLeg(3, 2, '1.50'),
        footballLeg(4, 3, '1.50'),
    ]);

    $result = (new DetermineAnalysisAvailability)->determine($slip, dataQualityScore: 30);

    expect($result->availability)->toBe(AnalysisAvailability::Unavailable);
    expect($result->effectiveBand)->toBe(DataQualityBand::Insufficient);
});

test('DataQualityBand::worseOf always returns the lower-ranked band, in either argument order', function () {
    expect(DataQualityBand::Strong->worseOf(DataQualityBand::Limited))->toBe(DataQualityBand::Limited);
    expect(DataQualityBand::Limited->worseOf(DataQualityBand::Strong))->toBe(DataQualityBand::Limited);
    expect(DataQualityBand::Insufficient->worseOf(DataQualityBand::Strong))->toBe(DataQualityBand::Insufficient);
    expect(DataQualityBand::Good->worseOf(DataQualityBand::Good))->toBe(DataQualityBand::Good);
});
