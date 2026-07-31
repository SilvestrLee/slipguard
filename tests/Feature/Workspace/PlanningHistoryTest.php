<?php

use App\Actions\Planner\StartPlannerSession;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function readySlipForPlanningHistory(User $user): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => 'Premier League', 'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90', 'display_order' => 0,
    ]);
    $slip->markReady();

    return $slip->fresh('legs');
}

test('Planning History shows the empty state when no sessions exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('planner.history'))
        ->assertOk()
        ->assertSee('Nothing here yet.');
});

test('Planning History lists past sessions with their status and links to the Planner Workspace', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanningHistory($user);
    $session = (new StartPlannerSession)->execute($user, $slip);

    $this->actingAs($user)
        ->get(route('planner.history'))
        ->assertOk()
        ->assertSee($slip->displayLabel())
        ->assertSee('Evaluated')
        ->assertSee(route('planner.session', $session));
});

test('Planning History never shows another customer\'s sessions', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $slip = readySlipForPlanningHistory($owner);
    (new StartPlannerSession)->execute($owner, $slip);

    $this->actingAs($otherUser)
        ->get(route('planner.history'))
        ->assertOk()
        ->assertSee('Nothing here yet.');
});

test('guests are redirected to login', function () {
    $this->get(route('planner.history'))->assertRedirect(route('login'));
});

test('U-09 §3.7: Planning History issues a small, fixed number of queries regardless of session count', function () {
    $user = User::factory()->create();
    foreach (range(1, 3) as $i) {
        $slip = readySlipForPlanningHistory($user);
        (new StartPlannerSession)->execute($user, $slip);
    }

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('planner.history'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});
