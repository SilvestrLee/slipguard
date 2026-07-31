<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Actions\Planner\AbandonPlannerSession;
use App\Actions\Planner\CompletePlannerSession;
use App\Actions\Planner\ExportPlannerSession;
use App\Actions\Planner\StartPlannerSession;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSessionStatus;
use App\Models\BettingSlip;
use App\Models\PlannerSession;
use App\Models\User;
use Livewire\Livewire;

/**
 * @param  array<int, array{0: string, 1: string}>  $oddsAndMarket
 */
function readySlipForPlannerUi(User $user, array $oddsAndMarket): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();

    foreach ($oddsAndMarket as $index => [$odds, $marketName]) {
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

test('a Ready slip with no prior planner session offers "Improve This Slip" alongside "Analyze"', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('Improve This Slip')
        ->assertSee('Analyze');
});

test('clicking "Improve This Slip" starts a session and redirects to the Planner', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);

    Livewire::actingAs($user)
        ->test('betting-slips.index')
        ->call('planThisAccumulator', $slip->id)
        ->assertRedirect();

    $session = PlannerSession::where('source_betting_slip_id', $slip->id)->sole();
    expect($session->status)->toBe(PlannerSessionStatus::Evaluated);
});

test('a second click resumes the already-open session instead of starting a new one', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);

    Livewire::actingAs($user)->test('betting-slips.index')->call('planThisAccumulator', $slip->id);
    Livewire::actingAs($user)->test('betting-slips.index')->call('planThisAccumulator', $slip->id);

    expect(PlannerSession::where('source_betting_slip_id', $slip->id)->count())->toBe(1);
});

test('a slip already locked by an open planner session offers "Continue Planning" instead of "Improve This Slip"', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);

    (new StartPlannerSession)->execute($user, $slip);

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('Continue Planning')
        ->assertDontSee('Improve This Slip');
});

test('the Planner Workspace shows the primary finding and the original slip stays untouched', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [
        ['1.30', 'Match Result'],
        ['1.50', 'Match Result'],
        ['6.00', 'Correct Score'],
    ]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    $this->actingAs($user)
        ->get(route('planner.session', $session))
        ->assertOk()
        ->assertSee('Primary finding')
        ->assertSee('6.00')
        ->assertSee('Your original slip');

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready);
});

test('keeping a selection does not create a new revision', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    $selection = $session->selections->first();

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->call('toggleLock', $selection->id);

    expect($session->fresh()->regenerationEvents)->toHaveCount(1);
    expect($selection->fresh()->isLocked())->toBeTrue();
});

test('removing a selection creates a new revision, and the last remaining selection cannot be removed', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['6.00', 'Correct Score']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    $weakest = $session->selections->firstWhere('decimal_odds', '6.00');

    $component = Livewire::actingAs($user)->test('planner.session', ['plannerSession' => $session]);
    $component->call('removeSelection', $weakest->id);

    expect($session->fresh()->selections)->toHaveCount(1);
    expect($session->fresh()->regenerationEvents)->toHaveCount(2);

    $lastOne = $session->fresh()->selections->first();
    $component->call('removeSelection', $lastOne->id);

    // The safeguard refused the removal — still 1 selection, no third revision.
    expect($session->fresh()->selections)->toHaveCount(1);
    expect($session->fresh()->regenerationEvents)->toHaveCount(2);
});

test('adding a valid replacement selection creates a new revision', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->set('newLeg.sport', 'Football')
        ->set('newLeg.event_name', 'Another Match')
        ->set('newLeg.market_name', 'Match Result')
        ->set('newLeg.selection_name', 'Away')
        ->set('newLeg.decimal_odds', '2.50')
        ->call('addSelection')
        ->assertHasNoErrors();

    expect($session->fresh()->selections)->toHaveCount(2);
    expect($session->fresh()->regenerationEvents)->toHaveCount(2);
});

test('adding an invalid replacement selection is rejected with a validation error and no mutation', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->set('newLeg.sport', 'Football')
        ->set('newLeg.event_name', 'Another Match')
        ->set('newLeg.market_name', 'Match Result')
        ->set('newLeg.selection_name', 'Away')
        ->set('newLeg.decimal_odds', '0.50')
        ->call('addSelection')
        ->assertHasErrors(['newLeg.decimal_odds']);

    expect($session->fresh()->selections)->toHaveCount(1);
    expect($session->fresh()->regenerationEvents)->toHaveCount(1);
});

test('confirming and exporting produces a new slip while the original remains exactly as entered', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->call('confirmSelection');

    expect($session->fresh()->status)->toBe(PlannerSessionStatus::Complete);

    $this->actingAs($user)
        ->get(route('planner.session', $session))
        ->assertOk()
        ->assertSee('Create a betting slip from this selection');

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->call('exportSelection');

    $session->refresh();
    expect($session->status)->toBe(PlannerSessionStatus::Exported);
    expect($session->exportedBettingSlip)->not->toBeNull();
    expect($session->exportedBettingSlip->id)->not->toBe($slip->id);

    $slip->refresh();
    expect($slip->status)->toBe(BettingSlipStatus::Ready);
    expect($slip->legs)->toHaveCount(2);

    $this->actingAs($user)
        ->get(route('planner.session', $session))
        ->assertOk()
        ->assertSee('Original slip — preserved')
        ->assertSee('New betting slip — created');
});

test('abandoning redirects to the Workspace and never touches the source slip', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->call('abandonSession')
        ->assertRedirect(route('analyze'));

    expect($session->fresh()->status)->toBe(PlannerSessionStatus::Abandoned);
    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready);
});

test('a customer cannot view another customer\'s planner session', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $slip = readySlipForPlannerUi($owner, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($owner, $slip);

    $this->actingAs($otherUser)
        ->get(route('planner.session', $session))
        ->assertForbidden();
});

test('returning a Planner-locked slip to draft shows the lock explanation and redirects into the open session', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    Livewire::actingAs($user)
        ->test('betting-slips.builder', ['bettingSlip' => $slip])
        ->call('returnToDraft')
        ->assertRedirect(route('planner.session', $session));

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready);
});

test('once exported, the session shows an ended-session notice rather than the working screen if abandoned instead', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    (new AbandonPlannerSession)->execute($session);

    $this->actingAs($user)
        ->get(route('planner.session', $session))
        ->assertOk()
        ->assertSee('This planning session has ended.');
});

test('the confirm action is still available and re-confirmable after Complete, matching the accepted lifecycle', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['1.50', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    (new CompletePlannerSession)->execute($session);

    $this->actingAs($user)
        ->get(route('planner.session', $session))
        ->assertOk()
        ->assertSee('You can keep changing your selections');
});

/**
 * U-07.8 validation finding: deleting a slip ever referenced by a
 * PlannerSession previously hit `source_betting_slip_id`'s restrictOnDelete
 * constraint directly — an uncaught QueryException (SQLSTATE[23000]). This
 * is permanent, not just "while a session is open": a PlannerSession row
 * is never deleted regardless of status, so the constraint still fires
 * even long after the session is Abandoned or Exported. Fixed by checking
 * hasPlannerHistory() (not isLockedByPlanner(), which only reflects the
 * *editing* lock and correctly excludes terminal sessions) before deleting,
 * both in the Delete action and by hiding the Delete control in the view.
 */
test('a slip used by an open Planner session cannot be deleted, and is refused without an exception', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    (new StartPlannerSession)->execute($user, $slip);

    Livewire::actingAs($user)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id)
        ->assertOk();

    expect(BettingSlip::find($slip->id))->not->toBeNull();
});

test('the Workspace hides the Delete action for a slip with any Planner history', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    (new StartPlannerSession)->execute($user, $slip);

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('Kept — used by Planner');
});

test('a slip remains permanently undeletable even after its Planner session ends, since the session history is retained', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    (new AbandonPlannerSession)->execute($session);

    Livewire::actingAs($user)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id)
        ->assertOk();

    expect(BettingSlip::find($slip->id))->not->toBeNull();

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('Kept — used by Planner');
});

test('Compare Revisions gracefully labels a selection that was later removed rather than fabricating its name', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['6.00', 'Correct Score']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    $firstRevision = $session->regenerationEvents->first();
    $weakest = $session->selections->firstWhere('decimal_odds', '6.00');

    Livewire::actingAs($user)
        ->test('planner.session', ['plannerSession' => $session])
        ->call('removeSelection', $weakest->id)
        ->call('compareWith', $firstRevision->id)
        ->assertSee('A selection that was later removed');
});

/**
 * U-07.8 §D Structural Risk Validation: confirms the Planner's persisted
 * MSC baseline score/band and the Risk Engine's independent analysis of
 * the exported slip agree exactly — the two systems (MSC ranking,
 * CalculateStructuralRisk) are expected to be consistent, never merely
 * assumed to be, since they run against the same underlying rule set.
 * Also confirms the exported slip's real status: a Draft, exactly like
 * any other newly created slip via SaveBettingSlip — not auto-Ready.
 */
test('an exported slip is a Draft, and once marked Ready analyses to the same score the Planner last showed', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result'], ['1.50', 'Match Result'], ['6.00', 'Correct Score']]);
    $session = (new StartPlannerSession)->execute($user, $slip);
    $plannerEvent = $session->regenerationEvents->last();

    (new CompletePlannerSession)->execute($session);
    $exportedSlip = (new ExportPlannerSession)->execute($session);

    expect($exportedSlip->status)->toBe(BettingSlipStatus::Draft);

    $exportedSlip->markReady();
    $analysis = (new AnalyzeBettingSlip)->execute($exportedSlip);

    expect($analysis->structural_score)->toBe($plannerEvent->structural_score);
    expect($analysis->risk_band)->toBe($plannerEvent->risk_band);
});

/**
 * U-07.8 §C Data Integrity Validation: user_id cascades on both
 * PlannerSession and BettingSlip, while source_betting_slip_id itself uses
 * restrictOnDelete() — checked that deleting a user with an open session
 * doesn't hit an FK-ordering conflict between those two cascade paths.
 * It doesn't: the database resolves both cascades within one delete.
 */
test('deleting a user with an open Planner session cascades cleanly, with no orphaned or conflicting records', function () {
    $user = User::factory()->create();
    $slip = readySlipForPlannerUi($user, [['1.30', 'Match Result']]);
    $session = (new StartPlannerSession)->execute($user, $slip);

    $user->delete();

    expect(BettingSlip::find($slip->id))->toBeNull();
    expect(PlannerSession::find($session->id))->toBeNull();
});
