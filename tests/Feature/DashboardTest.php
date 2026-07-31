<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Actions\Journal\CreateJournalEntry;
use App\Actions\Planner\StartPlannerSession;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Results\RiskBand;
use App\Models\BettingSlip;
use App\Models\PlannerRegenerationEvent;
use App\Models\PlannerSession;
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

test('the dashboard makes screenshot and PDF intake primary, shows the future link intake, and keeps the accumulator destination visible', function () {
    config(['slipguard-market-intelligence.enabled' => false]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Upload screenshot')
        ->assertSee(route('analyze.intake', ['method' => 'screenshot']), false)
        ->assertSee('Upload PDF')
        ->assertSee(route('analyze.intake', ['method' => 'pdf']), false)
        ->assertSee('Bet Code or Share Link')
        ->assertSee('Coming soon')
        ->assertSee(route('analyze.intake', ['method' => 'betcode']), false)
        ->assertSee(route('builder'), false)
        ->assertSee('Explore the workflow');
});

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

test('a Draft or Ready slip appears in Continue Working, linking to its edit page', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Draft]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Continue Working')
        ->assertSee(route('analyze.edit', $slip), false);
});

test('a slip locked by an open Planner session is never offered in Continue Working', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Ready]);
    $session = PlannerSession::factory()->for($user)->for($slip, 'sourceBettingSlip')->create(['status' => PlannerSessionStatus::Draft]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    // The Planner session itself is the real "continue working" destination
    // for this slip — editing the locked slip directly would fail.
    $response->assertOk()
        ->assertSee('Continue Working')
        ->assertDontSee(route('analyze.edit', $slip), false)
        ->assertSee(route('planner.session', $session), false);
});

test('Continue Working shows whichever of a slip or a Planner session was touched more recently', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Draft, 'updated_at' => now()->subDay()]);
    $otherSlip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Ready]);
    $session = PlannerSession::factory()->for($user)->for($otherSlip, 'sourceBettingSlip')
        ->create(['status' => PlannerSessionStatus::Evaluated, 'updated_at' => now()]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(route('planner.session', $session), false)
        ->assertDontSee(route('analyze.edit', $slip), false);
});

test('a Complete Planner session appears in Needs Your Attention with its latest risk band', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Ready]);
    $session = PlannerSession::factory()->for($user)->for($slip, 'sourceBettingSlip')
        ->create(['status' => PlannerSessionStatus::Complete]);
    PlannerRegenerationEvent::factory()->for($session, 'plannerSession')->create(['risk_band' => RiskBand::Moderate]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Needs Your Attention')
        ->assertSee(route('planner.session', $session), false)
        ->assertSee(RiskBand::Moderate->label());
});

test('a Complete Planner session never appears in Continue Working', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Ready]);
    $session = PlannerSession::factory()->for($user)->for($slip, 'sourceBettingSlip')
        ->create(['status' => PlannerSessionStatus::Complete]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertDontSee('Continue Working')
        ->assertSee('Needs Your Attention')
        ->assertSee(route('planner.session', $session), false);
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
        ->assertSee($analysis->risk_band->label())
        ->assertSee(route('analyze.report', $slip), false);
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
        ->assertSee('Last analysis');
});

test('Progress no longer shows Completed this week — dropped, per the Metrics Philosophy audit, for not answering a decision question', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()->assertDontSee('Completed this week');
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

/**
 * U-11.2 Phase 2: previously hardcoded to always show the empty state
 * regardless of real data — a stale assumption from before Journal
 * existed (U-02, false since U-08.2).
 */
test('the Dashboard shows the real latest journal entry, not a hardcoded empty state', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    (new CreateJournalEntry)->execute($user, $analysis, 'Stuck to my staking plan this time.');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Stuck to my staking plan this time.')
        ->assertDontSee('Your journal is empty.');
});

test('the Dashboard presents an independent reflection without implying a report exists', function () {
    $user = User::factory()->create();
    (new CreateJournalEntry)->execute($user, null, 'An independent decision note.', 'A separate reflection');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Independent reflection')
        ->assertSee('A separate reflection')
        ->assertSee('An independent decision note.');
});

test('the Dashboard preserves a reflection whose former analysis is unavailable', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    (new CreateJournalEntry)->execute($user, $analysis, 'This reflection remains useful.', 'Review retained');
    $analysis->delete();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Analysis unavailable')
        ->assertSee('Review retained')
        ->assertSee('This reflection remains useful.');
});

test('the Dashboard still shows the empty state when there is genuinely no journal entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Your journal is empty.');
});

test('the Dashboard shows an open Planner session', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new StartPlannerSession)->execute($user, $slip);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Planner')
        ->assertSee($slip->displayLabel());
});

test('the Dashboard shows no Continue Working or Needs Your Attention sections when nothing is open', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Continue Working')
        ->assertDontSee('Needs Your Attention');
});

test('an unnamed slip in Recent Activity shows a repository-backed identity and real selection count', function () {
    $user = User::factory()->create();
    $slip = readySlipForDashboard($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);
    $slip->forceFill(['name' => null])->save();
    (new AnalyzeBettingSlip)->execute($slip);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Arsenal vs Chelsea + 1 more selection')
        ->assertSee('2 selections')
        ->assertDontSee('Untitled slip');
});
