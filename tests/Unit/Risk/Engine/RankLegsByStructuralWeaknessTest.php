<?php

use App\Domain\Risk\Engine\RankLegsByStructuralWeakness;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\Taxonomy\NormalizationStatus;

/**
 * The Marginal Structural Contribution (MSC) weakest-leg attribution model —
 * docs/03-data-science/WEAKEST_LEG_ATTRIBUTION_MODEL.md, accepted
 * PO-U06.2A-AC-001 (docs/00-governance/DECISION_LOG.md). Reproduces the
 * specification's own worked examples (§7) and validation vectors (§8)
 * exactly, per Data Science Lab's Handover Rules ("reproduces TV-001
 * through TV-0NN exactly" is the checkable acceptance criterion, not
 * "matches the formula").
 */
test('WLA-EX-01: ordinary mixed four-leg slip ranks and tie-breaks exactly as specified', function () {
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.30', 'simple'),
        footballLeg(2, 1, '1.50', 'simple'),
        footballLeg(3, 2, '2.20', 'moderate'),
        footballLeg(4, 3, '6.00', 'complex'),
    ]);

    $ranking = (new RankLegsByStructuralWeakness)->calculate($slip);

    expect($ranking->baselineAvailability)->toBe(AnalysisAvailability::Full);
    expect($ranking->baselineScore)->toBe(64);
    expect($ranking->baselineBand)->toBe(RiskBand::High);
    expect($ranking->attributions)->toHaveCount(4);

    // Ranking (weakest -> strongest): L4, L2, L1, L3 — L1/L2 tie at
    // integer MSC 4 and are resolved by tie-break step 2 (unrounded
    // precision), not step 3+ (odds/complexity/position).
    $byLegId = collect($ranking->attributions)->keyBy('bettingSlipLegId');

    expect(collect($ranking->attributions)->pluck('bettingSlipLegId')->all())->toBe([4, 2, 1, 3]);
    expect(collect($ranking->attributions)->pluck('rank')->all())->toBe([1, 2, 3, 4]);

    expect($byLegId[4]->msc)->toBe(37);
    expect($byLegId[4]->scoreWithoutLeg)->toBe(27);
    expect($byLegId[4]->bandWithoutLeg)->toBe(RiskBand::Moderate);
    expect($byLegId[4]->gateDependent)->toBeFalse();

    expect($byLegId[2]->msc)->toBe(4);
    expect($byLegId[1]->msc)->toBe(4);
    expect($byLegId[3]->msc)->toBe(3);

    // The tie-break fact the specification's worked example turns on: L2's
    // removal has the larger true (unrounded) effect than L1's, even
    // though both round to the same integer MSC.
    expect($byLegId[2]->mscPrecise->isGreaterThan($byLegId[1]->mscPrecise))->toBeTrue();
});

test('WLA-EX-02: an exact symmetric tie is resolved only by entry-sequence position', function () {
    $slip = normalizedSlip([
        footballLeg(1, 0, '2.00', 'moderate'),
        footballLeg(2, 1, '2.00', 'moderate'),
        footballLeg(3, 2, '2.00', 'moderate'),
    ]);

    $ranking = (new RankLegsByStructuralWeakness)->calculate($slip);

    expect($ranking->baselineScore)->toBe(30);
    expect($ranking->baselineBand)->toBe(RiskBand::Moderate);

    // All three legs are, by every risk-relevant measure, identical —
    // integer MSC, unrounded MSC, odds, and complexity all tie exactly.
    foreach ($ranking->attributions as $attribution) {
        expect($attribution->msc)->toBe(9);
        expect($attribution->scoreWithoutLeg)->toBe(21);
        expect($attribution->bandWithoutLeg)->toBe(RiskBand::Low);
    }

    $precise = collect($ranking->attributions)->pluck('mscPrecise');
    expect($precise[0]->isEqualTo($precise[1]))->toBeTrue();
    expect($precise[1]->isEqualTo($precise[2]))->toBeTrue();

    // Only entry-sequence position (tie-break step 5) can resolve this —
    // legs entered first rank as nominal "weakest," in original order.
    expect(collect($ranking->attributions)->pluck('bettingSlipLegId')->all())->toBe([1, 2, 3]);
    expect(collect($ranking->attributions)->pluck('rank')->all())->toBe([1, 2, 3]);
});

test('WLA-EX-03: MSC is genuinely negative for a filler leg once both group caps are saturated', function () {
    $legs = [];
    for ($i = 1; $i <= 12; $i++) {
        $legs[] = footballLeg($i, $i - 1, '1.05', 'simple');
    }
    $legs[] = footballLeg(13, 12, '50.00', 'complex');

    $ranking = (new RankLegsByStructuralWeakness)->calculate(normalizedSlip($legs));

    expect($ranking->baselineScore)->toBe(88);
    expect($ranking->baselineBand)->toBe(RiskBand::VeryHigh);

    $byLegId = collect($ranking->attributions)->keyBy('bettingSlipLegId');

    // The dominant leg is overwhelmingly the weakest — removing it drops
    // the slip out of Very High entirely.
    expect($ranking->weakestLeg()->bettingSlipLegId)->toBe(13);
    expect($byLegId[13]->msc)->toBe(58);
    expect($byLegId[13]->bandWithoutLeg)->toBe(RiskBand::Moderate);

    // Every filler leg: both group caps stay saturated at their exact cap
    // value before and after removal, so the integer score doesn't move —
    // but the unrounded precision is genuinely negative (removing this
    // "safe" leg would very slightly raise, not lower, the true score).
    for ($i = 1; $i <= 12; $i++) {
        expect($byLegId[$i]->msc)->toBe(0, "filler leg {$i} should show integer MSC 0");
        expect($byLegId[$i]->scoreWithoutLeg)->toBe(88);
        expect($byLegId[$i]->mscPrecise->isNegative())->toBeTrue("filler leg {$i}'s mscPrecise should be negative, got {$byLegId[$i]->mscPrecise}");
    }

    // Every filler leg is structurally identical (same odds, same
    // complexity, same effect) — the tie resolves by entry position only,
    // and the dominant leg still ranks first regardless.
    expect(collect($ranking->attributions)->pluck('rank')->all())->toBe(range(1, 13));
});

test('WLA-TV-04: removing a recognized leg from an already-Limited slip can make the remainder gate-dependent', function () {
    // 4 football legs, 1 unrecognized market (25%) — at, not over, Tier 2's
    // threshold, per Rule Set 2026.1 §17's own worked example row.
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.50', 'simple'),
        footballLeg(2, 1, '1.60', 'simple'),
        footballLeg(3, 2, '1.70', 'simple'),
        footballLeg(4, 3, '2.00', 'unknown', NormalizationStatus::Unrecognized),
    ]);

    $ranking = (new RankLegsByStructuralWeakness)->calculate($slip);

    expect($ranking->baselineAvailability)->toBe(AnalysisAvailability::Limited);
    expect($ranking->baselineScore)->not->toBeNull();

    $byLegId = collect($ranking->attributions)->keyBy('bettingSlipLegId');

    // Removing the unrecognized leg itself only ever improves the Tier 2
    // proportion (0/3 = 0%) — never gate-dependent.
    expect($byLegId[4]->gateDependent)->toBeFalse();
    expect($byLegId[4]->availabilityWithoutLeg)->toBe(AnalysisAvailability::Full);
    expect($byLegId[4]->rank)->toBe(1);

    // Removing any of the 3 *recognized* legs instead pushes the remaining
    // unrecognized-market proportion from 1/4 (25%, allowed) to 1/3 (33%,
    // over Tier 2's 25% threshold) — Unavailable, no numeric MSC, no rank.
    foreach ([1, 2, 3] as $legId) {
        expect($byLegId[$legId]->gateDependent)->toBeTrue("leg {$legId} should be gate-dependent");
        expect($byLegId[$legId]->availabilityWithoutLeg)->toBe(AnalysisAvailability::Unavailable);
        expect($byLegId[$legId]->rank)->toBeNull();
        expect($byLegId[$legId]->msc)->toBeNull();
    }

    // Gate-dependent legs are appended after every rankable leg, in
    // original slip order — never interleaved or given a default rank.
    expect(collect($ranking->attributions)->pluck('bettingSlipLegId')->all())->toBe([4, 1, 2, 3]);
});

test('ranking does not apply to a single-leg accumulator', function () {
    $slip = slipOf([['1.40', 'simple']]);

    $ranking = (new RankLegsByStructuralWeakness)->calculate($slip);

    expect($ranking->baselineAvailability)->toBe(AnalysisAvailability::Full);
    expect($ranking->baselineScore)->toBe(3);
    expect($ranking->attributions)->toBe([]);
    expect($ranking->weakestLeg())->toBeNull();
});

test('ranking does not apply when the baseline itself is Unavailable', function () {
    $slip = normalizedSlip([unsupportedSportLeg(1, 0, '1.80')]);

    $ranking = (new RankLegsByStructuralWeakness)->calculate($slip);

    expect($ranking->baselineAvailability)->toBe(AnalysisAvailability::Unavailable);
    expect($ranking->baselineScore)->toBeNull();
    expect($ranking->attributions)->toBe([]);
});

test('ranking is deterministic across repeated calls on the same slip', function () {
    $slip = slipOf([
        ['1.30', 'simple'],
        ['1.50', 'simple'],
        ['2.20', 'moderate'],
        ['6.00', 'complex'],
    ]);

    $ranker = new RankLegsByStructuralWeakness;
    $first = $ranker->calculate($slip);
    $second = $ranker->calculate($slip);

    expect(collect($first->attributions)->pluck('bettingSlipLegId')->all())
        ->toBe(collect($second->attributions)->pluck('bettingSlipLegId')->all());
    expect(collect($first->attributions)->pluck('msc')->all())
        ->toBe(collect($second->attributions)->pluck('msc')->all());
});

test('reason-code delta reflects what removing the weakest leg would change', function () {
    $slip = normalizedSlip([
        footballLeg(1, 0, '1.30', 'simple'),
        footballLeg(2, 1, '1.50', 'simple'),
        footballLeg(3, 2, '2.20', 'moderate'),
        footballLeg(4, 3, '6.00', 'complex'),
    ]);

    $ranking = (new RankLegsByStructuralWeakness)->calculate($slip);
    $weakest = $ranking->weakestLeg();

    expect($weakest->bettingSlipLegId)->toBe(4);
    // Removing the weakest leg (the 6.00/complex outlier) should lose at
    // least one reason code the baseline carries and gain none it didn't.
    expect($weakest->reasonCodesLostWithoutLeg)->not->toBeEmpty();
});
