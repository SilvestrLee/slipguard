<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Models\BettingSlip;
use App\Models\SlipAnalysis;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function reportLegAttributes(array $overrides = []): array
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

function readySlipForReport(array $legAttributesList): BettingSlip
{
    $slip = BettingSlip::factory()->for(User::factory())->create();

    foreach ($legAttributesList as $index => $attributes) {
        $slip->legs()->create(reportLegAttributes(['display_order' => $index, ...$attributes]));
    }

    $slip->markReady();

    return $slip->fresh('legs');
}

test('a Full analysis report shows the score, band, and no edit action', function () {
    $slip = readySlipForReport([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    expect($analysis->availability)->toBe(AnalysisAvailability::Full);

    $this->actingAs($slip->user)
        ->get(route('analyze.report', $slip))
        ->assertOk()
        ->assertSee('Analysis complete')
        ->assertSee((string) $analysis->structural_score)
        ->assertSee($analysis->risk_band->label())
        ->assertSee('Analyse another slip')
        ->assertSee('Return to dashboard')
        ->assertDontSee('Edit this slip');
});

test('a Limited analysis report shows the limitation notice and no edit action', function () {
    // 1 genuinely unrecognized market among 5 legs = 20% (>0%, <=25% Tier 2 limit) — Tier 2 caps this at the Limited band.
    $slip = readySlipForReport([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Match Result', 'selection_name' => 'Liverpool', 'decimal_odds' => '1.80'],
        ['event_name' => 'Man City vs Spurs', 'market_name' => 'Match Result', 'selection_name' => 'Man City', 'decimal_odds' => '1.50'],
        ['event_name' => 'Newcastle vs Villa', 'market_name' => 'Match Result', 'selection_name' => 'Newcastle', 'decimal_odds' => '1.70'],
        ['event_name' => 'Fulham vs Brentford', 'market_name' => 'First Goal Between 10 and 20 Minutes', 'selection_name' => 'Yes', 'decimal_odds' => '1.60'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    expect($analysis->availability)->toBe(AnalysisAvailability::Limited);

    $this->actingAs($slip->user)
        ->get(route('analyze.report', $slip))
        ->assertOk()
        ->assertSee('Limited analysis available')
        ->assertSee((string) $analysis->structural_score)
        ->assertSee('Analyse another slip')
        ->assertSee('Return to dashboard')
        ->assertDontSee('Edit this slip');
});

test('an Unavailable analysis report shows the reason and no score, band, or edit action', function () {
    $slip = readySlipForReport([
        ['sport' => 'Tennis', 'event_name' => 'Djokovic vs Alcaraz', 'market_name' => 'Match Winner', 'selection_name' => 'Djokovic', 'decimal_odds' => '1.40'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    expect($analysis->availability)->toBe(AnalysisAvailability::Unavailable);

    $this->actingAs($slip->user)
        ->get(route('analyze.report', $slip))
        ->assertOk()
        ->assertSee("We couldn't analyze this slip")
        ->assertSee('Analyse another slip')
        ->assertSee('Return to dashboard')
        ->assertDontSee('Edit this slip')
        ->assertDontSee($analysis->risk_band?->label() ?? '__none__');
});

test('a report is forbidden to a user who does not own the slip', function () {
    $slip = readySlipForReport([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    $this->actingAs(User::factory()->create())
        ->get(route('analyze.report', $slip))
        ->assertForbidden();
});

test('a report 404s for a slip with no analysis yet', function () {
    $slip = BettingSlip::factory()->for($user = User::factory()->create())->create();

    $this->actingAs($user)
        ->get(route('analyze.report', $slip))
        ->assertNotFound();
});

test('guests are redirected to login', function () {
    $slip = readySlipForReport([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    $this->get(route('analyze.report', $slip))->assertRedirect(route('login'));
});

test('the Analyze action on a Ready slip creates the analysis and redirects to its report', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create(reportLegAttributes());
    $slip->markReady();

    Livewire::actingAs($user)
        ->test('betting-slips.index')
        ->call('analyzeSlip', $slip->id)
        ->assertRedirect(route('analyze.report', $slip->fresh()));

    expect($slip->fresh()->status->value)->toBe('analysed');
    expect($slip->fresh()->analysis)->not->toBeNull();
});

test('viewing a report never changes the persisted analysis (no re-invocation of the Risk Engine)', function () {
    $slip = readySlipForReport([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
    ]);
    $analysis = (new AnalyzeBettingSlip)->execute($slip);
    $originalScore = $analysis->structural_score;
    $originalUpdatedAt = $analysis->updated_at;

    $this->actingAs($slip->user)->get(route('analyze.report', $slip));
    $this->actingAs($slip->user)->get(route('analyze.report', $slip));

    $fresh = $analysis->fresh();
    expect($fresh->structural_score)->toEqual($originalScore);
    expect($fresh->updated_at)->toEqual($originalUpdatedAt);
    expect(SlipAnalysis::where('betting_slip_id', $slip->id)->count())->toBe(1);
});

test('the report issues a small, fixed number of queries regardless of factor/leg count', function () {
    $slip = readySlipForReport([
        ['event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90'],
        ['event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65'],
        ['event_name' => 'Man City vs Spurs', 'market_name' => 'Match Result', 'selection_name' => 'Man City', 'decimal_odds' => '1.50'],
    ]);
    (new AnalyzeBettingSlip)->execute($slip);

    DB::enableQueryLog();
    $this->actingAs($slip->user)->get(route('analyze.report', $slip))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});
