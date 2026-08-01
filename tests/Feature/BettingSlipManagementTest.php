<?php

use App\Models\BettingSlip;
use App\Models\User;
use Livewire\Volt\Volt;

function validLeg(array $overrides = []): array
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

test('a user can create a slip with multiple legs', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('name', 'Saturday accumulator')
        ->set('legs.0', validLeg())
        ->call('addLeg')
        ->set('legs.1', validLeg(['event_name' => 'Liverpool vs Everton', 'selection_name' => 'Liverpool to win']))
        ->call('save')
        ->assertHasNoErrors();

    $slip = BettingSlip::sole();

    expect($slip->user_id)->toBe($user->id);
    expect($slip->name)->toBe('Saturday accumulator');
    expect($slip->legs)->toHaveCount(2);
    expect($slip->legs->first()->event_name)->toBe('Arsenal vs Chelsea');
});

test('manual entry uses the shared planning workspace and reports only repository-backed input facts', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->assertSee('Manual Slip Entry')
        ->assertSee('Manual entry is a fallback')
        ->assertSee('Slip summary')
        ->assertSee('Manual fallback')
        ->assertSee('0 complete')
        ->set('legs.0', validLeg())
        ->assertSee('1 complete')
        ->assertSee('1.90')
        ->assertSee('A direct multiplication of entered decimal odds, not a prediction.');

    $html = $this->actingAs($user)->get(route('analyze.create'))->assertOk()->getContent();

    expect($html)->toContain('workspace-grid items-start')
        ->toContain('workspace-record-surface')
        ->toContain('lg:sticky lg:top-24')
        ->toContain('Step 1')
        ->toContain('Step 2');
});

test('a user can update an existing slip and its legs are replaced', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([...validLeg(), 'display_order' => 0]);

    Volt::actingAs($user)
        ->test('betting-slips.builder', ['bettingSlip' => $slip])
        ->set('legs.0', validLeg(['selection_name' => 'Chelsea to win']))
        ->call('save')
        ->assertHasNoErrors();

    $slip->refresh();

    expect($slip->legs)->toHaveCount(1);
    expect($slip->legs->first()->selection_name)->toBe('Chelsea to win');
});

test('a user can delete their own slip', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();

    Volt::actingAs($user)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id);

    expect(BettingSlip::find($slip->id))->toBeNull();
});

test('a slip cannot be saved with zero legs', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->call('removeLeg', 0)
        ->call('save')
        ->assertHasErrors(['legs']);

    expect(BettingSlip::count())->toBe(0);
});

test('a slip cannot exceed the configured maximum legs', function () {
    config(['slipguard.max_legs' => 2]);

    $user = User::factory()->create();

    $component = Volt::actingAs($user)->test('betting-slips.builder');

    $component->call('addLeg')->call('addLeg');

    expect($component->get('legs'))->toHaveCount(2);
});

test('each leg requires the core fields and valid decimal odds', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', validLeg(['decimal_odds' => '0.50']))
        ->call('save')
        ->assertHasErrors(['legs.0.decimal_odds']);

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', validLeg(['event_name' => '']))
        ->call('save')
        ->assertHasErrors(['legs.0.event_name']);
});

test('guests cannot reach the slip builder or index', function () {
    $slip = BettingSlip::factory()->create();

    $this->get(route('analyze'))->assertRedirect(route('login'));
    $this->get(route('analyze.create'))->assertRedirect(route('login'));
    $this->get(route('analyze.edit', $slip))->assertRedirect(route('login'));
});

test('a user cannot view, edit, or delete another users slip', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $slip = BettingSlip::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('analyze.edit', $slip))
        ->assertForbidden();

    Volt::actingAs($intruder)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id)
        ->assertForbidden();

    expect(BettingSlip::find($slip->id))->not->toBeNull();
});

test('a user only sees their own slips on the index', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    BettingSlip::factory()->for($user)->create(['name' => 'Mine']);
    BettingSlip::factory()->for($otherUser)->create(['name' => 'Not mine']);

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('Mine')
        ->assertDontSee('Not mine');
});
