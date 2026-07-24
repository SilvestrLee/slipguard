<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Risk\Normalization\NormalizeBettingSlip;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use App\Exceptions\BettingSlipNotReadyException;
use App\Models\BettingSlip;

function normalizer(): NormalizeBettingSlip
{
    return app(NormalizeBettingSlip::class);
}

test('a ready slip normalizes every leg and carries the taxonomy version', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win', 'decimal_odds' => '1.90', 'display_order' => 0,
    ]);
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => 'Premier League',
        'event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals',
        'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65', 'display_order' => 1,
    ]);

    $normalized = normalizer()->execute($slip);

    expect($normalized->taxonomyVersion)->toBe(FootballMarketTaxonomyV1::VERSION);
    expect($normalized->bettingSlipId)->toBe($slip->id);
    expect($normalized->legs)->toHaveCount(2);

    expect($normalized->legs[0]->sport->sportCode)->toBe('football');
    expect($normalized->legs[0]->market->marketCode)->toBe('football.match_result.1x2');

    expect($normalized->legs[1]->market->marketCode)->toBe('football.total_goals.over_under');
    expect($normalized->legs[1]->market->selectionFacts)->toBe(['direction' => 'over', 'line' => '2.5']);
});

test('normalization preserves original free text alongside the classification', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => null,
        'event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win', 'decimal_odds' => '1.90', 'display_order' => 0,
    ]);

    $normalized = normalizer()->execute($slip);

    expect($normalized->legs[0]->market->rawMarketInput)->toBe('Match Result');
    expect($normalized->legs[0]->market->rawSelectionInput)->toBe('Arsenal to win');
    expect($normalized->legs[0]->sport->rawInput)->toBe('Football');
});

test('normalization rejects draft, analysed, and archived slips', function (BettingSlipStatus $status) {
    $slip = match ($status) {
        BettingSlipStatus::Draft => BettingSlip::factory()->create(),
        BettingSlipStatus::Analysed => BettingSlip::factory()->analysed()->create(),
        BettingSlipStatus::Archived => BettingSlip::factory()->archived()->create(),
        default => throw new LogicException('not used'),
    };

    expect(fn () => normalizer()->execute($slip))->toThrow(BettingSlipNotReadyException::class);
})->with([
    'draft' => [BettingSlipStatus::Draft],
    'analysed' => [BettingSlipStatus::Analysed],
    'archived' => [BettingSlipStatus::Archived],
]);

test('an unrecognized market on an otherwise valid leg still normalizes without failing the whole slip', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => null,
        'event_name' => 'Arsenal vs Chelsea', 'market_name' => 'First Goal Between 10 and 20 Minutes',
        'selection_name' => 'Yes', 'decimal_odds' => '3.00', 'display_order' => 0,
    ]);

    $normalized = normalizer()->execute($slip);

    expect($normalized->legs[0]->market->status)->toBe(NormalizationStatus::Unrecognized);
    expect($normalized->legs[0]->market->marketCode)->toBeNull();
});

test('normalization supports the maximum configured number of legs', function () {
    $slip = BettingSlip::factory()->ready()->create();

    foreach (range(0, config('slipguard.max_legs') - 1) as $index) {
        $slip->legs()->create([
            'sport' => 'Football', 'competition' => null,
            'event_name' => "Event {$index}", 'market_name' => 'Match Result',
            'selection_name' => 'Home', 'decimal_odds' => '1.90', 'display_order' => $index,
        ]);
    }

    $normalized = normalizer()->execute($slip->fresh());

    expect($normalized->legs)->toHaveCount(config('slipguard.max_legs'));
});
