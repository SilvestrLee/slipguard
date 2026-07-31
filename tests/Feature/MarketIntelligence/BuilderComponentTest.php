<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Models\BettingSlip;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use App\Models\PlannerSession;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function builderFixture(string $providerEventId, string $homeTeam, string $awayTeam, array $outcomes): MarketIntelligenceFixture
{
    $fixture = MarketIntelligenceFixture::factory()->create([
        'provider_event_id' => $providerEventId,
        'competition_key' => 'soccer_epl',
        'home_team' => $homeTeam,
        'away_team' => $awayTeam,
    ]);

    MarketIntelligenceMarketQuote::factory()->for($fixture, 'fixture')->create([
        'outcomes' => $outcomes,
    ]);

    return $fixture;
}

test('the destination remains visible as an honest workflow preview while live evaluation is disabled', function () {
    config(['slipguard-market-intelligence.enabled' => false]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('builder'))
        ->assertOk()
        ->assertSee('Experience preview')
        ->assertSee('Live evaluation unavailable')
        ->assertSee('Illustrative preview · not a live candidate')
        ->assertSee('The fictional fixtures and values above demonstrate the review layout only.')
        ->assertSee('This preview contains no generated candidate');
});

test('the live Builder composes constraints beside a repository-backed request summary', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('builder'))
        ->assertOk()
        ->assertSee('Your planning request')
        ->assertSee('Fresh fixtures in selected window')
        ->assertSee('A repository-backed fixture count, not an estimate of suitable candidates.')
        ->assertSee('Higher leg counts require more compatible fixtures')
        ->assertSee('SlipGuard will search supported fixtures and return a candidate for your review.')
        ->assertSee('Build accumulator ideas from supported evidence for your review — never prediction.')
        ->assertSee('This window currently contains fewer fixtures than your requested leg count.')
        ->assertSee('role="switch"', false);
});

test('dependent target-odds validation explains an invalid populated range before submission', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('targetOddsEnabled', true)
        ->set('targetOddsMin', '4.00')
        ->set('targetOddsMax', '2.00')
        ->assertHasErrors(['targetOddsMax' => ['gt']])
        ->assertSee('Maximum decimal odds must be greater than the minimum.')
        ->assertDontSee('Enter a maximum decimal-odds value.');
});

test('invalid planning constraints are explicit and block candidate discovery', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', [])
        ->set('markets', [])
        ->set('targetOddsEnabled', true)
        ->set('targetOddsMin', '4.00')
        ->set('targetOddsMax', '2.00')
        ->call('findCandidate')
        ->assertHasErrors(['competitions', 'targetOddsMax'])
        ->assertSet('discovered', false);
});

test('the request summary responds to supported competition and market selections', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->call('toggleCompetition', 'soccer_epl')
        ->call('toggleMarket', 'h2h')
        ->set('windowDays', 3)
        ->set('legCount', 4)
        ->assertSee('Next 3 days')
        ->assertSee('4 legs');
});

test('the authenticated navigation keeps the Builder visible and labels it as a preview while the flag is off', function () {
    $user = User::factory()->create();

    config(['slipguard-market-intelligence.enabled' => false]);
    $this->actingAs($user)->get(route('analyze'))
        ->assertOk()
        ->assertSee('Build Accumulator')
        ->assertSee('Preview');

    config(['slipguard-market-intelligence.enabled' => true]);
    $response = $this->actingAs($user)->get(route('analyze'))->assertOk();
    $response->assertSee('Build Accumulator');
});

test('the live Builder exposes accessible staged candidate evaluation feedback', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('builder'))
        ->assertOk()
        ->assertSee('Candidate evaluation underway')
        ->assertSee('Retrieve or reuse fresh fixture evidence')
        ->assertSee('Check competition, market and planning-window eligibility')
        ->assertSee('Rank compatible fixture and market opportunities')
        ->assertSee('does not display a percentage')
        ->assertDontSee('candidateTimer')
        ->assertDontSee('setInterval')
        ->assertSee('aria-live="polite"', false);
});

test('the Builder landing experience shows only the customer recent planning sessions', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $recentSlip = BettingSlip::factory()->for($user)->create(['name' => 'Weekend structure review']);
    $otherSlip = BettingSlip::factory()->for($otherUser)->create(['name' => 'Another customer plan']);

    $recentSession = PlannerSession::factory()
        ->for($user)
        ->for($recentSlip, 'sourceBettingSlip')
        ->create();

    PlannerSession::factory()
        ->for($otherUser)
        ->for($otherSlip, 'sourceBettingSlip')
        ->create();

    $this->actingAs($user)->get(route('builder'))
        ->assertOk()
        ->assertSee('Planner available')
        ->assertSee('Recent planning sessions')
        ->assertSee('Weekend structure review')
        ->assertSee(route('planner.session', $recentSession), false)
        ->assertSee(route('planner.history'), false)
        ->assertDontSee('Another customer plan');
});

test('a brief with no matching fixtures shows the insufficient-fixtures empty state', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    Http::fake(['*' => Http::response([], 200)]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('legCount', 2)
        ->call('findCandidate')
        ->assertSet('discovered', false)
        ->assertSee('No accumulator candidate satisfies your current planning constraints.');
});

test('a valid brief discovers real opportunities and renders every surviving outcome', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    builderFixture('fixture-1', 'Arsenal', 'Chelsea', [
        ['name' => 'Arsenal', 'price' => '1.85'],
        ['name' => 'Draw', 'price' => '3.60'],
        ['name' => 'Chelsea', 'price' => '4.50'],
    ]);
    builderFixture('fixture-2', 'Liverpool', 'Everton', [
        ['name' => 'Liverpool', 'price' => '1.50'],
        ['name' => 'Draw', 'price' => '4.00'],
        ['name' => 'Everton', 'price' => '5.50'],
    ]);

    $component = Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('legCount', 2)
        ->call('findCandidate');

    $component->assertSet('discovered', true)
        ->assertSee('Arsenal vs Chelsea')
        ->assertSee('Liverpool vs Everton')
        ->assertSee('Arsenal to Win')
        ->assertSee('Draw')
        ->assertSee('Chelsea to Win');
});

test('Evaluate Candidate only proceeds once every active slot has a chosen outcome', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    builderFixture('fixture-1', 'Arsenal', 'Chelsea', [
        ['name' => 'Arsenal', 'price' => '1.85'],
        ['name' => 'Draw', 'price' => '3.60'],
        ['name' => 'Chelsea', 'price' => '4.50'],
    ]);
    builderFixture('fixture-2', 'Liverpool', 'Everton', [
        ['name' => 'Liverpool', 'price' => '1.50'],
        ['name' => 'Draw', 'price' => '4.00'],
        ['name' => 'Everton', 'price' => '5.50'],
    ]);

    $component = Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('legCount', 2)
        ->call('findCandidate')
        ->call('selectOutcome', 'fixture-1', 'Arsenal to Win')
        ->call('evaluateCandidate')
        ->assertSet('evaluationOutcome', null); // only one of two slots chosen — guard must hold

    $component->call('selectOutcome', 'fixture-2', 'Liverpool to Win')
        ->call('evaluateCandidate')
        ->assertSet('evaluationOutcome', 'constructed')
        ->assertSee('structural-risk assessment.')
        ->assertSee('Structural contribution describes each leg’s effect on slip structure, not its likelihood of winning.')
        ->assertSee('All requested conditions satisfied')
        ->assertSee('This is a candidate for review, not a predicted outcome or recommendation.');
});

test('accepting a constructed candidate creates a real Ready slip inside a real Planner session', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    builderFixture('fixture-1', 'Arsenal', 'Chelsea', [
        ['name' => 'Arsenal', 'price' => '1.85'],
        ['name' => 'Draw', 'price' => '3.60'],
        ['name' => 'Chelsea', 'price' => '4.50'],
    ]);
    builderFixture('fixture-2', 'Liverpool', 'Everton', [
        ['name' => 'Liverpool', 'price' => '1.50'],
        ['name' => 'Draw', 'price' => '4.00'],
        ['name' => 'Everton', 'price' => '5.50'],
    ]);

    $component = Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('legCount', 2)
        ->call('findCandidate')
        ->call('selectOutcome', 'fixture-1', 'Arsenal to Win')
        ->call('selectOutcome', 'fixture-2', 'Liverpool to Win')
        ->call('evaluateCandidate')
        ->assertSet('evaluationOutcome', 'constructed');

    $component->call('acceptCandidate');

    $session = PlannerSession::sole();
    expect($session->sourceBettingSlip->status)->toBe(BettingSlipStatus::Ready);
    expect($session->sourceBettingSlip->legs)->toHaveCount(2);

    $component->assertRedirect(route('planner.session', $session));
});

test('a risk ceiling breach surfaces Replacement Needed with the correct reason and leaves other choices intact', function () {
    config(['slipguard-market-intelligence.enabled' => true]);
    $user = User::factory()->create();

    foreach (range(1, 6) as $i) {
        builderFixture("fixture-{$i}", "Team{$i}A", "Team{$i}B", [
            ['name' => "Team{$i}A", 'price' => '5.00'],
            ['name' => 'Draw', 'price' => '5.00'],
            ['name' => "Team{$i}B", 'price' => '5.00'],
        ]);
    }
    builderFixture('fixture-spare', 'SpareA', 'SpareB', [
        ['name' => 'SpareA', 'price' => '2.00'],
        ['name' => 'Draw', 'price' => '2.00'],
        ['name' => 'SpareB', 'price' => '2.00'],
    ]);

    $component = Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('legCount', 6)
        ->set('riskCeiling', 'low')
        ->call('findCandidate');

    foreach (range(1, 6) as $i) {
        $component->call('selectOutcome', "fixture-{$i}", "Team{$i}A to Win");
    }

    $component->call('evaluateCandidate')
        ->assertSet('evaluationOutcome', 'replacement_needed');

    expect($component->get('replacementReasonLabel'))->not->toBeEmpty();

    // the other five choices survive — only the flagged fixture needs a new choice
    $remaining = collect($component->get('activeFixtureKeys'))->diff([$component->get('replacementNewFixtureKey')]);
    expect($remaining)->toHaveCount(5);
});
