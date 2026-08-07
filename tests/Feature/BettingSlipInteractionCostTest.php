<?php

use App\Models\BettingSlip;
use App\Models\User;
use Livewire\Volt\Volt;

/**
 * `PO-UX-001` UX refinement pass (U-19.1) — interaction-cost reduction
 * within the already-shipped guided Manual Builder (`BettingSlipGuidedManualIntakeTest.php`).
 * No taxonomy, normalizer, or deterministic-analysis behaviour is touched
 * anywhere in this file; every assertion is about the Livewire component's
 * own UI-state logic.
 */
function guidedLeg(array $overrides = []): array
{
    return array_merge([
        'sport' => 'Football',
        'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win',
        'decimal_odds' => '1.90',
    ], $overrides);
}

test('nothing beyond Market is shown until a market is chosen', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->assertDontSee('Event')
        ->assertDontSee('Competition (optional)')
        ->assertDontSee('Decimal odds')
        ->set('legs.0.market_name', 'Match Result')
        ->assertSee('Event')
        ->assertSee('Competition (optional)')
        ->assertSee('Decimal odds');
});

test('adding a leg carries the previous leg\'s Competition forward, not its fixture or odds', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', guidedLeg())
        ->call('addLeg');

    expect($component->get('legs.1.competition'))->toBe('Premier League')
        ->and($component->get('legs.1.event_name'))->toBe('')
        ->and($component->get('legs.1.sport'))->toBe('Football');
});

test('Duplicate Previous Leg carries sport, competition and market forward but leaves fixture, selection and odds blank', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', guidedLeg(['market_name' => 'Both Teams to Score', 'selection_name' => 'Yes']))
        ->call('duplicatePreviousLeg');

    expect($component->get('legs'))->toHaveCount(2);
    expect($component->get('legs.1.sport'))->toBe('Football');
    expect($component->get('legs.1.competition'))->toBe('Premier League');
    expect($component->get('legs.1.market_name'))->toBe('Both Teams to Score');
    expect($component->get('legs.1.event_name'))->toBe('');
    expect($component->get('legs.1.selection_name'))->toBe('');
    expect($component->get('legs.1.decimal_odds'))->toBe('');
});

test('Duplicate Previous Leg does nothing when there is no leg to duplicate yet', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder');

    expect($component->get('legs'))->toHaveCount(1);

    $component->call('duplicatePreviousLeg');

    expect($component->get('legs'))->toHaveCount(1);
});

test('adding a new leg auto-collapses a previously completed leg, and it can be manually reopened', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', guidedLeg());

    expect($component->instance()->legIsCollapsed(0))->toBeFalse();

    $component->call('addLeg');

    expect($component->instance()->legIsCollapsed(0))->toBeTrue()
        ->and($component->instance()->legIsCollapsed(1))->toBeFalse();

    $component->call('toggleLegCollapse', 0);

    expect($component->instance()->legIsCollapsed(0))->toBeFalse();
});

test('an incomplete leg is never auto-collapsed by adding another one', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.market_name', 'Match Result')
        ->call('addLeg');

    expect($component->instance()->legIsCollapsed(0))->toBeFalse();
});

test('editing an existing multi-leg slip loads complete legs collapsed', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([...guidedLeg(), 'display_order' => 0]);
    $slip->legs()->create([...guidedLeg(['event_name' => 'Liverpool vs Everton']), 'market_name' => '', 'selection_name' => '', 'display_order' => 1]);

    $component = Volt::actingAs($user)->test('betting-slips.builder', ['bettingSlip' => $slip]);

    expect($component->instance()->legIsCollapsed(0))->toBeTrue()
        ->and($component->instance()->legIsCollapsed(1))->toBeFalse();
});

test('the leg summary line shows fixture, market, selection and odds together once complete', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', guidedLeg());

    expect($component->instance()->legSummaryLine(0))
        ->toBe('Arsenal vs Chelsea — Match Result: Arsenal to win @ 1.90');
});

test('the leg summary line is null while the leg is incomplete', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)->test('betting-slips.builder');

    expect($component->instance()->legSummaryLine(0))->toBeNull();
});

test('the Total Goals line stepper moves between half-integer values only', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.market_name', 'Total Goals')
        ->set('legs.0.total_goals_direction', 'over');

    expect($component->get('legs.0.total_goals_line'))->toBe('');

    $component->call('adjustTotalGoalsLine', 0, 1);
    expect($component->get('legs.0.total_goals_line'))->toBe('0.5');

    $component->call('adjustTotalGoalsLine', 0, 1);
    expect($component->get('legs.0.total_goals_line'))->toBe('1.5');

    $component->call('adjustTotalGoalsLine', 0, -1);
    expect($component->get('legs.0.total_goals_line'))->toBe('0.5');

    // Never goes below the lowest real line.
    $component->call('adjustTotalGoalsLine', 0, -1);
    expect($component->get('legs.0.total_goals_line'))->toBe('0.5');

    expect($component->get('legs.0.selection_name'))->toBe('Over 0.5 Goals');
});

test('the Correct Score steppers stay within a 0-9 range and compose the selection', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.market_name', 'Correct Score')
        ->call('adjustCorrectScoreHome', 0, 1)
        ->call('adjustCorrectScoreHome', 0, 1)
        ->call('adjustCorrectScoreAway', 0, 1);

    expect($component->get('legs.0.correct_score_home'))->toBe('2')
        ->and($component->get('legs.0.correct_score_away'))->toBe('1')
        ->and($component->get('legs.0.selection_name'))->toBe('2-1');

    // Cannot go below 0.
    $component->call('adjustCorrectScoreAway', 0, -1)->call('adjustCorrectScoreAway', 0, -1);
    expect($component->get('legs.0.correct_score_away'))->toBe('0');
});

test('the segmented radio and stepper components render for their guided market families', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0.market_name', 'Both Teams to Score')
        ->assertSee('role="radiogroup"', false)
        ->set('legs.0.market_name', 'Correct Score')
        ->assertSee('aria-label="Home team score"', false)
        ->assertSee('aria-label="Away team score"', false);
});
