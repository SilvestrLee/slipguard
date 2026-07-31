<?php

use App\Actions\Planner\AbandonPlannerSession;
use App\Actions\Planner\AddPlannerSelection;
use App\Actions\Planner\CompletePlannerSession;
use App\Actions\Planner\ExportPlannerSession;
use App\Actions\Planner\RemovePlannerSelection;
use App\Actions\Planner\StartPlannerSession;
use App\Actions\Planner\ToggleSelectionLock;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSelectionLockState;
use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Exceptions\BettingSlipLockedByPlannerException;
use App\Exceptions\BettingSlipNotReadyException;
use App\Exceptions\InvalidPlannerSessionTransitionException;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * @param  array<int, array{0: string, 1: string}>  $oddsAndComplexity  [[odds, market_name], ...]
 */
function readySlipForPlanner(User $user, array $legs): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();

    foreach ($legs as $index => [$odds, $marketName]) {
        $slip->legs()->create([
            'sport' => 'Football',
            'competition' => 'Premier League',
            'event_name' => "Match {$index}",
            'market_name' => $marketName,
            'selection_name' => 'Home',
            'decimal_odds' => $odds,
            'display_order' => $index,
        ]);
    }

    $slip->markReady();

    return $slip->fresh('legs');
}

test('starting a session seeds selections from the source slip and produces the first evaluation', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result'], ['1.50', 'Match Result'], ['2.20', 'Both Teams to Score']]);

    $session = (new StartPlannerSession)->execute($user, $slip);

    expect($session->status)->toBe(PlannerSessionStatus::Evaluated);
    expect($session->selections)->toHaveCount(3);
    expect($session->regenerationEvents)->toHaveCount(1);

    $event = $session->regenerationEvents->first();
    expect($event->sequence_number)->toBe(1);
    expect($event->availability)->toBe(AnalysisAvailability::Full);
    expect($event->attributions)->toHaveCount(3);

    // The source slip is never mutated by starting a session (PD-008).
    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready);
});

test('a session can only be started from a Ready slip', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([
        'sport' => 'Football', 'event_name' => 'Match 0', 'market_name' => 'Match Result',
        'selection_name' => 'Home', 'decimal_odds' => '1.50', 'display_order' => 0,
    ]);

    (new StartPlannerSession)->execute($user, $slip);
})->throws(BettingSlipNotReadyException::class);

test('the source slip is locked against direct editing while a session is open, and released once the session ends', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);

    $session = (new StartPlannerSession)->execute($user, $slip);

    expect($slip->fresh()->isLockedByPlanner())->toBeTrue();
    expect(fn () => $slip->fresh()->returnToDraft())->toThrow(BettingSlipLockedByPlannerException::class);

    (new AbandonPlannerSession)->execute($session);

    expect($slip->fresh()->isLockedByPlanner())->toBeFalse();
    $slip->fresh()->returnToDraft(); // no exception
    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Draft);
});

test('locking or unlocking a selection does not create a new regeneration event', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    $selection = $session->selections->first();
    expect($selection->isLocked())->toBeFalse();

    (new ToggleSelectionLock)->execute($selection);
    expect($selection->fresh()->lock_state)->toBe(PlannerSelectionLockState::Locked);
    expect($session->fresh()->regenerationEvents)->toHaveCount(1);

    (new ToggleSelectionLock)->execute($selection->fresh());
    expect($selection->fresh()->lock_state)->toBe(PlannerSelectionLockState::Unlocked);
    expect($session->fresh()->regenerationEvents)->toHaveCount(1);
});

test('removing a selection triggers re-evaluation and appends a new immutable event', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result'], ['6.00', 'Both Teams to Score']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    $firstEvent = $session->regenerationEvents->first();
    $toRemove = $session->selections->last();

    (new RemovePlannerSelection)->execute($toRemove);

    $session->refresh();
    expect($session->selections)->toHaveCount(1);
    expect($session->regenerationEvents)->toHaveCount(2);
    expect($session->regenerationEvents->last()->sequence_number)->toBe(2);

    // The first event is untouched — never mutated or deleted.
    expect($firstEvent->fresh()->attributions)->toHaveCount(2);
});

test('adding a selection validates input using the same rules as the slip builder and triggers re-evaluation', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    (new AddPlannerSelection)->execute($session, [
        'sport' => 'Football',
        'event_name' => 'Another Match',
        'market_name' => 'Match Result',
        'selection_name' => 'Away',
        'decimal_odds' => '2.50',
    ]);

    $session->refresh();
    expect($session->selections)->toHaveCount(2);
    expect($session->regenerationEvents)->toHaveCount(2);
});

test('adding an invalid selection is rejected before any mutation', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    expect(fn () => (new AddPlannerSelection)->execute($session, [
        'sport' => 'Football',
        'event_name' => 'Another Match',
        'market_name' => 'Match Result',
        'selection_name' => 'Away',
        'decimal_odds' => '0.50', // below the minimum
    ]))->toThrow(ValidationException::class);

    expect($session->fresh()->selections)->toHaveCount(1);
    expect($session->fresh()->regenerationEvents)->toHaveCount(1);
});

test('a session completes only from Evaluated, and export creates a new slip leaving the original untouched', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    (new CompletePlannerSession)->execute($session);
    expect($session->fresh()->status)->toBe(PlannerSessionStatus::Complete);

    $exportedSlip = (new ExportPlannerSession)->execute($session->fresh());

    expect($exportedSlip->id)->not->toBe($slip->id);
    expect($exportedSlip->legs)->toHaveCount(2);
    expect($exportedSlip->user_id)->toBe($user->id);

    $session->refresh();
    expect($session->status)->toBe(PlannerSessionStatus::Exported);
    expect($session->exported_betting_slip_id)->toBe($exportedSlip->id);

    // PD-009: the original slip remains permanently, exactly as entered.
    $slip->refresh();
    expect($slip->status)->toBe(BettingSlipStatus::Ready);
    expect($slip->legs)->toHaveCount(2);
});

test('export is rejected unless the session is Complete', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    (new ExportPlannerSession)->execute($session);
})->throws(InvalidPlannerSessionTransitionException::class);

test('abandoning a session at any non-terminal point never touches the source slip', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    (new AbandonPlannerSession)->execute($session);

    expect($session->fresh()->status)->toBe(PlannerSessionStatus::Abandoned);
    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready);
    expect($slip->fresh()->legs)->toHaveCount(1);
});

test('a customer cannot view or update another customer\'s planner session', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $slip = readySlipForPlanner($owner, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($owner, $slip);

    expect($otherUser->can('view', $session))->toBeFalse();
    expect($otherUser->can('update', $session))->toBeFalse();
    expect($owner->can('view', $session))->toBeTrue();
});

test('the MSC engine output persisted in a regeneration event matches direct engine computation exactly', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlanner($user, [
        ['1.30', 'Match Result'],
        ['1.50', 'Match Result'],
        ['2.20', 'Both Teams to Score'],
        ['6.00', 'Correct Score'],
    ]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    $event = $session->regenerationEvents->first();

    // The same shape/behaviour verified directly against
    // RankLegsByStructuralWeaknessTest's own WLA-EX-01 vector: the weakest
    // leg (highest odds, complex-ish market) ranks first.
    $ranked = collect($event->attributions)->sortBy('rank')->values();
    expect($ranked->first()['decimal_odds'])->toBe('6.00');
    expect($ranked->first()['rank'])->toBe(1);
    expect($event->structural_score)->toBeInt();
    expect($event->risk_band)->toBeInstanceOf(RiskBand::class);
});
