<?php

use App\Domain\Risk\Normalization\NormalizeFootballMarket;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use App\Models\BettingSlip;
use App\Models\User;
use Livewire\Volt\Volt;

/**
 * `PO-UX-001` (scoped implementation): the Manual Builder's Sport and
 * Market fields become controlled selectors, and Selection becomes a
 * guided control for the three market families `NormalizeFootballMarket`
 * already parses structured facts for (Both Teams to Score, Total Goals,
 * Correct Score) — all sourced from the existing, already-approved
 * `FootballMarketTaxonomyV1`, with zero new external dependency.
 */
test('every market option value round-trips through the real normalizer back to its own family', function () {
    $component = Volt::actingAs(User::factory()->create())->test('betting-slips.builder');

    $options = $component->instance()->marketOptions();
    $definitions = (new FootballMarketTaxonomyV1)->definitions();

    expect($options)->toHaveCount(count($definitions));

    foreach ($definitions as $definition) {
        $matchingValue = collect($options)->search($definition->displayName);

        expect($matchingValue)->not->toBeFalse();

        $normalized = (new NormalizeFootballMarket)->normalize($matchingValue, '');

        expect($normalized->marketFamily)->toBe($definition->family)
            ->and($normalized->status)->not->toBe(NormalizationStatus::Unrecognized);
    }
});

test('a new leg defaults Sport to Football, requiring no typing', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->assertSet('legs.0.sport', 'Football')
        ->assertSee('the only sport currently supported');
});

test('the Market field is a dropdown listing every taxonomy market by its display name', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->assertSee('Match Result')
        ->assertSee('Both Teams to Score')
        ->assertSee('Correct Score')
        ->assertSee('Half-Time / Full-Time')
        ->assertDontSee('e.g. Match Result');
});

test('choosing Both Teams to Score renders a Yes/No selection control and saves correctly', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.event_name', 'Arsenal vs Chelsea')
        ->set('legs.0.decimal_odds', '1.80')
        ->set('legs.0.market_name', 'Both Teams to Score')
        ->assertSee('Yes')
        ->assertSee('No')
        ->set('legs.0.selection_name', 'Yes')
        ->call('save')
        ->assertHasNoErrors();

    $leg = BettingSlip::sole()->legs->sole();

    expect($leg->market_name)->toBe('Both Teams to Score');
    expect($leg->selection_name)->toBe('Yes');

    $normalized = (new NormalizeFootballMarket)->normalize($leg->market_name, $leg->selection_name);
    expect($normalized->marketFamily)->toBe(MarketFamily::BothTeamsToScore);
    expect($normalized->status)->toBe(NormalizationStatus::Complete);
    expect($normalized->selectionFacts['selection_code'])->toBe('yes');
});

test('choosing Total Goals composes the selection from a direction select and a line input', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.event_name', 'Arsenal vs Chelsea')
        ->set('legs.0.decimal_odds', '1.85')
        ->set('legs.0.market_name', 'Total Goals')
        ->set('legs.0.total_goals_direction', 'over')
        ->assertSet('legs.0.selection_name', '')
        ->set('legs.0.total_goals_line', '2.5')
        ->assertSet('legs.0.selection_name', 'Over 2.5 Goals')
        ->call('save')
        ->assertHasNoErrors();

    $leg = BettingSlip::sole()->legs->sole();

    expect($leg->selection_name)->toBe('Over 2.5 Goals');

    $normalized = (new NormalizeFootballMarket)->normalize($leg->market_name, $leg->selection_name);
    expect($normalized->marketFamily)->toBe(MarketFamily::TotalGoals);
    expect($normalized->status)->toBe(NormalizationStatus::Complete);
    expect($normalized->selectionFacts)->toBe(['direction' => 'over', 'line' => '2.5']);
});

test('choosing Correct Score composes the selection from two score inputs', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.event_name', 'Arsenal vs Chelsea')
        ->set('legs.0.decimal_odds', '9.00')
        ->set('legs.0.market_name', 'Correct Score')
        ->set('legs.0.correct_score_home', '2')
        ->set('legs.0.correct_score_away', '1')
        ->assertSet('legs.0.selection_name', '2-1')
        ->call('save')
        ->assertHasNoErrors();

    $leg = BettingSlip::sole()->legs->sole();

    expect($leg->selection_name)->toBe('2-1');

    $normalized = (new NormalizeFootballMarket)->normalize($leg->market_name, $leg->selection_name);
    expect($normalized->marketFamily)->toBe(MarketFamily::CorrectScore);
    expect($normalized->status)->toBe(NormalizationStatus::Complete);
    expect($normalized->selectionFacts)->toBe(['score_home' => '2', 'score_away' => '1']);
});

test('markets without a parsed selection taxonomy keep the free-text Selection field', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.market_name', 'Match Result');

    expect($component->instance()->selectionInputMode(0))->toBe('freetext');

    $component->set('legs.0.selection_name', 'Arsenal to win')
        ->assertSet('legs.0.selection_name', 'Arsenal to win');
});

test('changing the market resets a previously chosen selection', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.market_name', 'Both Teams to Score')
        ->set('legs.0.selection_name', 'Yes')
        ->assertSet('legs.0.selection_name', 'Yes')
        ->set('legs.0.market_name', 'Total Goals')
        ->assertSet('legs.0.selection_name', '')
        ->assertSet('legs.0.total_goals_direction', '');
});

test('editing an existing Total Goals leg pre-fills the guided sub-fields from its saved selection text', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([
        'sport' => 'Football',
        'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Total Goals',
        'selection_name' => 'Over 2.5 Goals',
        'decimal_odds' => '1.85',
        'display_order' => 0,
    ]);

    Volt::actingAs($user)
        ->test('betting-slips.builder', ['bettingSlip' => $slip])
        ->assertSet('legs.0.total_goals_direction', 'over')
        ->assertSet('legs.0.total_goals_line', '2.5');
});
