<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function readySlipForHistory(User $user, array $legAttributesList): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();

    foreach ($legAttributesList as $index => $attributes) {
        $slip->legs()->create(array_merge([
            'sport' => 'Football',
            'competition' => 'Premier League',
            'event_name' => "Match {$index}",
            'market_name' => 'Match Result',
            'selection_name' => 'Home',
            'decimal_odds' => '1.90',
            'display_order' => $index,
        ], $attributes));
    }

    $slip->markReady();

    return $slip->fresh('legs');
}

test('History shows the empty state when no analyses exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('history'))
        ->assertOk()
        ->assertSee('Nothing here yet.')
        ->assertSee('Analyze a slip');
});

test('History lists completed analyses, newest first, and links to the existing report', function () {
    $user = User::factory()->create();
    $slip = readySlipForHistory($user, [['decimal_odds' => '1.90'], ['decimal_odds' => '6.00']]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    $this->actingAs($user)
        ->get(route('history'))
        ->assertOk()
        ->assertSee($slip->displayLabel())
        ->assertSee($analysis->risk_band->label())
        ->assertSee('Open risk report')
        ->assertSee(route('analyze.report', $slip));
});

test('History never shows another customer\'s analyses', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $slip = readySlipForHistory($owner, [['decimal_odds' => '1.90']]);
    (new AnalyzeBettingSlip)->execute($slip);

    $this->actingAs($otherUser)
        ->get(route('history'))
        ->assertOk()
        ->assertSee('Nothing here yet.')
        ->assertDontSee($slip->displayLabel());
});

test('guests are redirected to login', function () {
    $this->get(route('history'))->assertRedirect(route('login'));
});

test('U-09 §3.7: History issues a small, fixed number of queries regardless of analysis count', function () {
    $user = User::factory()->create();
    foreach (range(1, 5) as $i) {
        readySlipForHistory($user, [['decimal_odds' => '1.90']]);
    }
    foreach ($user->bettingSlips as $slip) {
        (new AnalyzeBettingSlip)->execute($slip);
    }

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('history'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});

test('Sprint 11 History renders persisted summary metrics and meaningful record metadata', function () {
    $user = User::factory()->create();
    $slip = readySlipForHistory($user, [
        ['event_name' => 'Arsenal vs Chelsea', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'decimal_odds' => '2.00'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    $this->actingAs($user)
        ->get(route('history'))
        ->assertOk()
        ->assertSee('Analysis History')
        ->assertSee('Total analyses')
        ->assertSee('Average selections')
        ->assertSee('Premier League')
        ->assertSee('3.80')
        ->assertSee('Full report')
        ->assertSee($analysis->risk_band->label())
        ->assertDontSee('Untitled slip');
});

test('Sprint 11 History search and filters are ownership scoped', function () {
    $user = User::factory()->create();
    $matching = readySlipForHistory($user, [['competition' => 'Champions League', 'event_name' => 'Arsenal vs Inter']]);
    $matching->update(['name' => 'Midweek European Review']);
    $matchingAnalysis = (new AnalyzeBettingSlip)->execute($matching);
    $matchingAnalysis->update([
        'risk_band' => RiskBand::High,
        'availability' => AnalysisAvailability::Limited,
        'limited_analysis' => true,
    ]);

    $other = readySlipForHistory($user, [['competition' => 'La Liga', 'event_name' => 'Real Madrid vs Sevilla']]);
    $other->update(['name' => 'Spanish Evening Slip']);
    (new AnalyzeBettingSlip)->execute($other);

    Livewire::actingAs($user)
        ->test('history.index')
        ->set('search', 'European')
        ->assertSee('Midweek European Review')
        ->assertDontSee('Spanish Evening Slip')
        ->set('search', '')
        ->set('risk', 'high')
        ->set('availability', 'limited')
        ->assertSee('Midweek European Review')
        ->assertDontSee('Spanish Evening Slip')
        ->assertSee('Limited report');
});

test('Sprint 11 History paginates the archive instead of rendering every report', function () {
    $user = User::factory()->create();

    foreach (range(1, 11) as $index) {
        $slip = readySlipForHistory($user, [['event_name' => "Fixture {$index}"]]);
        $slip->update(['name' => sprintf('Archive record %02d', $index)]);
        (new AnalyzeBettingSlip)->execute($slip);
    }

    $this->actingAs($user)
        ->get(route('history'))
        ->assertOk()
        ->assertSee('Ten reports per page')
        ->assertSee('Archive record 11')
        ->assertDontSee('Archive record 01');
});

test('Sprint 11 History allows an owner to rename an analysis', function () {
    $user = User::factory()->create();
    $slip = readySlipForHistory($user, [['event_name' => 'Arsenal vs Chelsea']]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    Livewire::actingAs($user)
        ->test('history.index')
        ->call('beginRename', $analysis->id)
        ->set('renameValue', 'Premier League Weekend Accumulator')
        ->call('renameAnalysis')
        ->assertHasNoErrors()
        ->assertSee('Analysis renamed.');

    expect($slip->fresh()->name)->toBe('Premier League Weekend Accumulator');
});

test('Sprint 11 removal archives rather than destroying a deterministic analysis', function () {
    $user = User::factory()->create();
    $slip = readySlipForHistory($user, [['event_name' => 'Arsenal vs Chelsea']]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);

    Livewire::actingAs($user)
        ->test('history.index')
        ->call('confirmRemoval', $analysis->id)
        ->call('removeFromHistory')
        ->assertSee('deterministic record has been retained');

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Archived)
        ->and($analysis->fresh())->not->toBeNull();

    $this->actingAs($user)
        ->get(route('history'))
        ->assertOk()
        ->assertSee('Nothing here yet.');
});

test('Sprint 11 History actions cannot target another customer analysis', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $analysis = (new AnalyzeBettingSlip)->execute(
        readySlipForHistory($owner, [['event_name' => 'Private fixture']])
    );

    expect(fn () => Livewire::actingAs($other)
        ->test('history.index')
        ->call('beginRename', $analysis->id))
        ->toThrow(ModelNotFoundException::class);
});

test('the History journal action can preselect only an owned analysis', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $analysis = (new AnalyzeBettingSlip)->execute(
        readySlipForHistory($owner, [['event_name' => 'Journal fixture']])
    );

    $this->actingAs($owner)
        ->get(route('journal.create', ['analysis' => $analysis->id]))
        ->assertOk()
        ->assertSee($analysis->bettingSlip->displayLabel());

    $this->actingAs($other)
        ->get(route('journal.create', ['analysis' => $analysis->id]))
        ->assertNotFound();
});
