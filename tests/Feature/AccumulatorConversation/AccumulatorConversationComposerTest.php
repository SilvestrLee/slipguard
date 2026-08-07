<?php

use App\Models\BettingSlip;
use App\Models\User;
use Livewire\Volt\Volt;

/**
 * `PO-U23-001` — the conversational entry layer. Decision 2: the module
 * inherits every existing Capability B restriction exactly; Class B
 * (explicit construction) does not depend on the flag at all, matching
 * every other Capability A intake method.
 */
test('a criteria-discovery request hands off to the existing Capability B Builder with the interpreted brief, never duplicating candidate discovery here', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', 'Give me three Premier League games tonight.')
        ->call('submit')
        ->assertRedirect(route('builder'));

    expect(session('conversational_planning_brief'))->toMatchArray([
        'competitions' => ['soccer_epl'],
        'legCount' => 3,
        'windowDays' => 1,
    ]);
});

test('the Builder applies a session-flashed conversational brief and runs discovery automatically on arrival', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    session()->flash('conversational_planning_brief', [
        'competitions' => ['soccer_epl'],
        'windowDays' => 3,
        'legCount' => 4,
        'markets' => ['h2h', 'totals'],
        'riskCeiling' => 'moderate',
    ]);
    session()->flash('conversational_summary', 'Got it — 4 selections from Premier League.');

    Volt::actingAs($user)
        ->test('market-intelligence.builder')
        ->assertSet('competitions', ['soccer_epl'])
        ->assertSet('legCount', 4)
        ->assertSet('riskCeiling', 'moderate');
});

test('a normal visit to the Builder with nothing flashed behaves exactly as before — no conversational side effect', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('market-intelligence.builder')
        ->assertSet('discovered', false)
        ->assertSet('competitions', ['soccer_epl', 'soccer_spain_la_liga', 'soccer_italy_serie_a']);
});

test('an explicit selection creates a real Draft BettingSlip via the existing SaveBettingSlip action and hands off to the existing Manual Builder', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', 'Arsenal to win.')
        ->call('submit');

    $slip = BettingSlip::sole();

    expect($slip->user_id)->toBe($user->id)
        ->and($slip->legs->sole()->market_name)->toBe('Match Result')
        ->and($slip->legs->sole()->selection_name)->toBe('Arsenal to win');
});

test('explicit construction works even when Capability B is disabled — it never depends on the flag', function () {
    config(['slipguard-market-intelligence.enabled' => false]);
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', 'Arsenal to win.')
        ->call('submit');

    expect(BettingSlip::count())->toBe(1);
});

test('criteria discovery is honestly short-circuited when Capability B is disabled, matching the Builder\'s own existing wording, never a silent failure', function () {
    config(['slipguard-market-intelligence.enabled' => false]);
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', 'Three Premier League selections.')
        ->call('submit')
        ->assertSee('not available in this workspace yet');

    expect(BettingSlip::count())->toBe(0);
});

test('the composer shows a Preview badge when Capability B is disabled, matching the existing dashboard card treatment exactly', function () {
    config(['slipguard-market-intelligence.enabled' => false]);
    $user = User::factory()->create();

    Volt::actingAs($user)->test('accumulator-conversation.composer')->assertSee('Preview');
});

test('an unsupported competition request shows the honest explanation and a way out to the Guided Builder, never a fabricated result', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', 'Three Champions League selections.')
        ->call('submit')
        ->assertSee('Champions League')
        ->assertSee('Use the Guided Builder instead');

    expect(BettingSlip::count())->toBe(0);
});

test('an ambiguous request asks a clarifying question instead of guessing', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', 'Four safer games.')
        ->call('submit')
        ->assertSee('Which competition');
});

test('a starter prompt fills the composer without submitting on its own', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->call('useStarterPrompt', 'Arsenal to win')
        ->assertSet('message', 'Arsenal to win');

    expect(BettingSlip::count())->toBe(0);
});

test('a guest cannot submit the composer', function () {
    Volt::test('accumulator-conversation.composer')
        ->set('message', 'Arsenal to win.')
        ->call('submit')
        ->assertUnauthorized();
});

test('an empty message is rejected with a visible validation error, matching the existing form-validation convention', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('accumulator-conversation.composer')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['message']);
});
