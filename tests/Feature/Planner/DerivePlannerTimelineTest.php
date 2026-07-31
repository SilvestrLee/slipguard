<?php

use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Planner\Presentation\DerivePlannerTimeline;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Models\PlannerSelection;
use App\Models\PlannerSession;

test('a fresh session with no revisions has only the started entry', function () {
    $session = PlannerSession::factory()->create();

    $entries = (new DerivePlannerTimeline)->derive($session->fresh(['selections', 'regenerationEvents']));

    expect($entries)->toHaveCount(1);
    expect($entries[0]['type'])->toBe('started');
});

test('the first revision reports every selection as neither added nor removed', function () {
    $session = PlannerSession::factory()->create();
    $selections = PlannerSelection::factory()->count(2)->create(['planner_session_id' => $session->id]);

    $session->regenerationEvents()->create([
        'sequence_number' => 1,
        'availability' => AnalysisAvailability::Full,
        'structural_score' => 40,
        'risk_band' => RiskBand::Moderate,
        'attributions' => $selections->map(fn ($s) => ['planner_selection_id' => $s->id])->all(),
    ]);

    $entries = (new DerivePlannerTimeline)->derive($session->fresh(['selections', 'regenerationEvents']));

    expect($entries)->toHaveCount(2);
    expect($entries[1]['detail']['added'])->toBe([]);
    expect($entries[1]['detail']['removed'])->toBe([]);
    expect($entries[1]['detail']['kept'])->toBe(2);
});

test('a subsequent revision correctly identifies an added and a removed selection', function () {
    $session = PlannerSession::factory()->create();
    $kept = PlannerSelection::factory()->create(['planner_session_id' => $session->id, 'event_name' => 'Kept Event', 'selection_name' => 'Kept Pick']);
    $removedSelection = PlannerSelection::factory()->create(['planner_session_id' => $session->id, 'event_name' => 'Removed Event', 'selection_name' => 'Removed Pick']);

    $session->regenerationEvents()->create([
        'sequence_number' => 1,
        'availability' => AnalysisAvailability::Full,
        'structural_score' => 50,
        'risk_band' => RiskBand::High,
        'attributions' => [
            ['planner_selection_id' => $kept->id],
            ['planner_selection_id' => $removedSelection->id],
        ],
    ]);

    $removedSelection->delete();
    $added = PlannerSelection::factory()->create(['planner_session_id' => $session->id, 'event_name' => 'Added Event', 'selection_name' => 'Added Pick']);

    $session->regenerationEvents()->create([
        'sequence_number' => 2,
        'availability' => AnalysisAvailability::Full,
        'structural_score' => 30,
        'risk_band' => RiskBand::Moderate,
        'attributions' => [
            ['planner_selection_id' => $kept->id],
            ['planner_selection_id' => $added->id],
        ],
    ]);

    $entries = (new DerivePlannerTimeline)->derive($session->fresh(['selections', 'regenerationEvents']));

    expect($entries)->toHaveCount(3);
    $secondRevision = $entries[2];
    expect($secondRevision['detail']['added'])->toBe(['Added Event — Added Pick']);
    expect($secondRevision['detail']['removed'])->toBe(['A selection that was later removed']);
    expect($secondRevision['detail']['kept'])->toBe(1);
});

test('a confirmed session appends a confirmed entry using the session\'s own timestamp', function () {
    $session = PlannerSession::factory()->create(['status' => PlannerSessionStatus::Complete]);

    $entries = (new DerivePlannerTimeline)->derive($session->fresh(['selections', 'regenerationEvents']));

    expect(collect($entries)->pluck('type'))->toContain('confirmed');
    expect(collect($entries)->pluck('type'))->not->toContain('exported');
});

test('an exported session appends only the exported entry, never a stale confirmed entry', function () {
    $session = PlannerSession::factory()->create(['status' => PlannerSessionStatus::Exported]);

    $entries = (new DerivePlannerTimeline)->derive($session->fresh(['selections', 'regenerationEvents']));

    expect(collect($entries)->pluck('type'))->toContain('exported');
    expect(collect($entries)->pluck('type'))->not->toContain('confirmed');
});
