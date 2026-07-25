<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function dashboardLegAttributes(array $overrides = []): array
{
    return array_merge([
        'sport' => 'Football',
        'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win',
        'decimal_odds' => '1.90',
        'display_order' => 0,
    ], $overrides);
}

function readySlipForDashboard(User $user, array $legAttributesList): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();

    foreach ($legAttributesList as $index => $attributes) {
        $slip->legs()->create(dashboardLegAttributes(['display_order' => $index, ...$attributes]));
    }

    $slip->markReady();

    return $slip->fresh('legs');
}

test('a first-time user with no slips sees the No Slips empty state and no progress section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('No slips yet.')
        ->assertDontSee('No analyses yet.')
        ->assertDontSee('Progress')
        ->assertDontSee('Continue previous slip');
});

test('a user with an editable slip but no analyses sees the No Analyses empty state', function () {
    $user = User::factory()->create();
    BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Draft]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('No analyses yet.')
        ->assertDontSee('No slips yet.');
});

test('a Draft or Ready slip offers a Continue previous slip action', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Draft]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Continue previous slip')
        ->assertSee(route('analyze.edit', $slip), false);
});

test('a completed Full analysis appears in Recent Analyses with its risk band and leg count', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('2 selections')
        ->assertSee($analysis->risk_band->label());
});

test('the Progress section shows once at least one analysis exists', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Total analyses')
        ->assertSee('Completed this week');
});

test('an Unavailable analysis renders without a risk band and does not error', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['sport' => 'Tennis', 'event_name' => 'Player A vs Player B', 'market_name' => 'Match Winner', 'selection_name' => 'Player A', 'decimal_odds' => '1.80'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()->assertSee('Unavailable');
});

test('the dashboard never shows another user\'s analyses', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();

    $slip = readySlipForDashboard($owner, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    $response = $this->actingAs($viewer)->get(route('dashboard'));

    $response->assertOk()->assertSee('No slips yet.')->assertDontSee('Arsenal vs Chelsea');
});

test('the dashboard issues a small, fixed number of queries regardless of history size', function () {
    $user = User::factory()->create();

    foreach (range(1, 8) as $i) {
        $slip = readySlipForDashboard($user, [
            ['event_name' => "Match {$i}", 'market_name' => 'Match Result', 'selection_name' => 'Home', 'decimal_odds' => '1.90'],
        ]);
        (new AnalyzeBettingSlip)->execute($slip);
    }

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});
