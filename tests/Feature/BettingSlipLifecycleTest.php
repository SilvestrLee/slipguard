<?php

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Domain\BettingSlip\AnalysisIneligibilityReason;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Exceptions\BettingSlipNotEditableException;
use App\Exceptions\InvalidBettingSlipTransitionException;
use App\Models\BettingSlip;
use App\Models\User;
use Livewire\Volt\Volt;

function completeLegAttributes(array $overrides = []): array
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

// --- Lifecycle transitions ---

test('a draft slip with a complete leg can be marked ready', function () {
    $slip = BettingSlip::factory()->create();
    $slip->legs()->create(completeLegAttributes());

    $slip->markReady();

    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Ready);
});

test('a ready slip can return to draft', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create(completeLegAttributes());

    $slip->returnToDraft();

    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Draft);
});

test('a ready slip can be marked analysed', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create(completeLegAttributes());

    $slip->markAnalysed();

    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Analysed);
});

test('draft, ready, and analysed slips can all be archived', function (BettingSlipStatus $status) {
    $slip = match ($status) {
        BettingSlipStatus::Draft => BettingSlip::factory()->create(),
        BettingSlipStatus::Ready => BettingSlip::factory()->ready()->create(),
        BettingSlipStatus::Analysed => BettingSlip::factory()->analysed()->create(),
        BettingSlipStatus::Archived => throw new LogicException('not used'),
    };

    $slip->archive();

    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Archived);
})->with([
    'draft' => [BettingSlipStatus::Draft],
    'ready' => [BettingSlipStatus::Ready],
    'analysed' => [BettingSlipStatus::Analysed],
]);

// --- Illegal transitions ---

test('a draft slip with no legs cannot be marked ready', function () {
    $slip = BettingSlip::factory()->create();

    expect(fn () => $slip->markReady())->toThrow(InvalidBettingSlipTransitionException::class);
    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Draft);
});

test('a draft slip with an incomplete leg cannot be marked ready', function () {
    $slip = BettingSlip::factory()->create();
    $slip->legs()->create(completeLegAttributes(['event_name' => '']));

    expect(fn () => $slip->markReady())->toThrow(InvalidBettingSlipTransitionException::class);
});

test('an analysed slip cannot return to draft or ready', function () {
    $slip = BettingSlip::factory()->analysed()->create();

    expect(fn () => $slip->returnToDraft())->toThrow(InvalidBettingSlipTransitionException::class);
    expect(fn () => $slip->markReady())->toThrow(InvalidBettingSlipTransitionException::class);
});

test('an archived slip cannot transition anywhere', function () {
    $slip = BettingSlip::factory()->archived()->create();

    expect(fn () => $slip->markReady())->toThrow(InvalidBettingSlipTransitionException::class);
    expect(fn () => $slip->returnToDraft())->toThrow(InvalidBettingSlipTransitionException::class);
    expect(fn () => $slip->markAnalysed())->toThrow(InvalidBettingSlipTransitionException::class);
    expect(fn () => $slip->archive())->toThrow(InvalidBettingSlipTransitionException::class);
});

test('a double archive from the builder fails gracefully instead of a hard error', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->archived()->create();

    // If archive() let the InvalidBettingSlipTransitionException escape
    // uncaught, this ->call() itself would throw and fail the test.
    Volt::actingAs($user)
        ->test('betting-slips.builder', ['bettingSlip' => $slip])
        ->call('archive');

    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Archived);
});

test('returning an already-draft slip to draft fails gracefully instead of a hard error', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder', ['bettingSlip' => $slip])
        ->call('returnToDraft');

    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Draft);
});

test('a draft slip cannot jump straight to analysed', function () {
    $slip = BettingSlip::factory()->create();
    $slip->legs()->create(completeLegAttributes());

    expect(fn () => $slip->markAnalysed())->toThrow(InvalidBettingSlipTransitionException::class);
});

// --- Analysis eligibility ---

test('a draft slip is ineligible — it must be marked ready first', function () {
    $slip = BettingSlip::factory()->create();
    $slip->legs()->create(completeLegAttributes());

    $eligibility = $slip->analysisEligibility();

    expect($eligibility->eligible)->toBeFalse();
    expect($eligibility->reasons)->toContain(AnalysisIneligibilityReason::NotReady);
});

test('an empty ready slip is ineligible for analysis', function () {
    // Only reachable via direct factory state — markReady() itself already
    // refuses to create an empty Ready slip. This is defense in depth.
    $slip = BettingSlip::factory()->ready()->create();

    $eligibility = $slip->analysisEligibility();

    expect($eligibility->eligible)->toBeFalse();
    expect($eligibility->reasons)->toContain(AnalysisIneligibilityReason::Empty);
});

test('a ready slip with an incomplete leg is ineligible for analysis', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create(completeLegAttributes(['selection_name' => '']));

    $eligibility = $slip->analysisEligibility();

    expect($eligibility->eligible)->toBeFalse();
    expect($eligibility->reasons)->toContain(AnalysisIneligibilityReason::Incomplete);
});

test('an analysed slip is ineligible for further analysis', function () {
    $slip = BettingSlip::factory()->analysed()->create();
    $slip->legs()->create(completeLegAttributes());

    expect($slip->analysisEligibility()->reasons)->toContain(AnalysisIneligibilityReason::AlreadyAnalysed);
});

test('an archived slip is ineligible for analysis', function () {
    $slip = BettingSlip::factory()->archived()->create();
    $slip->legs()->create(completeLegAttributes());

    expect($slip->analysisEligibility()->reasons)->toContain(AnalysisIneligibilityReason::Archived);
});

test('a ready slip with complete legs is eligible for analysis', function () {
    $slip = BettingSlip::factory()->ready()->create();
    $slip->legs()->create(completeLegAttributes());

    expect($slip->isAnalysisEligible())->toBeTrue();
});

// --- Immutable analysis input ---

test('the save action refuses to edit a slip that has left draft', function (string $factoryState) {
    $slip = BettingSlip::factory()->{$factoryState}()->create();
    $slip->legs()->create(completeLegAttributes());

    $action = app(SaveBettingSlip::class);

    expect(fn () => $action->execute($slip->user, $slip, ['name' => 'changed'], [completeLegAttributes()]))
        ->toThrow(BettingSlipNotEditableException::class);

    expect($slip->refresh()->name)->not->toBe('changed');
})->with([
    'ready' => ['ready'],
    'analysed' => ['analysed'],
    'archived' => ['archived'],
]);

test('the builder renders locked fields and hides mutation controls for a non-draft slip', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->ready()->create();
    $slip->legs()->create(completeLegAttributes());

    $this->actingAs($user)
        ->get(route('analyze.edit', $slip))
        ->assertOk()
        ->assertSee('Locked for analysis')
        ->assertDontSee('Add Leg')
        ->assertSee('Return to Draft');
});

test('the builder blocks add, remove, and save when the slip is not editable', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->ready()->create();
    $slip->legs()->create(completeLegAttributes());

    Volt::actingAs($user)
        ->test('betting-slips.builder', ['bettingSlip' => $slip])
        ->call('addLeg')
        ->call('save');

    expect($slip->refresh()->legs)->toHaveCount(1);
    expect($slip->status)->toBe(BettingSlipStatus::Ready);
});

// --- Duplicate legs ---

test('a slip cannot be saved with two legs describing the same event, market, and selection', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', completeLegAttributes())
        ->call('addLeg')
        ->set('legs.1', completeLegAttributes())
        ->call('save')
        ->assertHasErrors(['legs']);

    expect(BettingSlip::count())->toBe(0);
});

test('duplicate detection is not fooled by incidental internal whitespace', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.builder')
        ->set('legs.0', completeLegAttributes())
        ->call('addLeg')
        ->set('legs.1', completeLegAttributes(['event_name' => 'Arsenal vs Chelsea '])) // trailing space
        ->call('save')
        ->assertHasErrors(['legs']);

    expect(BettingSlip::count())->toBe(0);
});

// --- Ownership hardening on lifecycle actions ---

test('a user cannot transition another users slip', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $slip = BettingSlip::factory()->for($owner)->create();
    $slip->legs()->create(completeLegAttributes());

    // Mounting the builder itself already authorizes 'update' — a non-owner
    // is rejected before any transition method could ever be reached.
    $this->actingAs($intruder)
        ->get(route('analyze.edit', $slip))
        ->assertForbidden();

    expect($intruder->can('update', $slip))->toBeFalse();
    expect($slip->refresh()->status)->toBe(BettingSlipStatus::Draft);
});

test('guests cannot reach lifecycle actions', function () {
    $slip = BettingSlip::factory()->create();

    $this->get(route('analyze.edit', $slip))->assertRedirect(route('login'));
});

// --- Deletion policy: Draft and Ready only ---

test('draft and ready slips can be deleted', function (BettingSlipStatus $status) {
    $user = User::factory()->create();
    $slip = match ($status) {
        BettingSlipStatus::Draft => BettingSlip::factory()->for($user)->create(),
        BettingSlipStatus::Ready => BettingSlip::factory()->for($user)->ready()->create(),
        default => throw new LogicException('not used'),
    };

    Volt::actingAs($user)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id);

    expect(BettingSlip::find($slip->id))->toBeNull();
})->with([
    'draft' => [BettingSlipStatus::Draft],
    'ready' => [BettingSlipStatus::Ready],
]);

test('analysed and archived slips cannot be deleted', function (BettingSlipStatus $status) {
    $user = User::factory()->create();
    $slip = match ($status) {
        BettingSlipStatus::Analysed => BettingSlip::factory()->for($user)->analysed()->create(),
        BettingSlipStatus::Archived => BettingSlip::factory()->for($user)->archived()->create(),
        default => throw new LogicException('not used'),
    };

    Volt::actingAs($user)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id)
        ->assertForbidden();

    expect(BettingSlip::find($slip->id))->not->toBeNull();
})->with([
    'analysed' => [BettingSlipStatus::Analysed],
    'archived' => [BettingSlipStatus::Archived],
]);

test('a non-owner cannot delete a draft or ready slip either', function (BettingSlipStatus $status) {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $slip = match ($status) {
        BettingSlipStatus::Draft => BettingSlip::factory()->for($owner)->create(),
        BettingSlipStatus::Ready => BettingSlip::factory()->for($owner)->ready()->create(),
        default => throw new LogicException('not used'),
    };

    Volt::actingAs($intruder)
        ->test('betting-slips.index')
        ->call('deleteSlip', $slip->id)
        ->assertForbidden();

    expect(BettingSlip::find($slip->id))->not->toBeNull();
})->with([
    'draft' => [BettingSlipStatus::Draft],
    'ready' => [BettingSlipStatus::Ready],
]);

test('the index hides the delete action for analysed and archived slips', function () {
    $user = User::factory()->create();
    $analysed = BettingSlip::factory()->for($user)->analysed()->create(['name' => 'Analysed Slip']);
    $archived = BettingSlip::factory()->for($user)->archived()->create(['name' => 'Archived Slip']);

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('Archive to remove');
});

// --- Index filtering ---

test('the slip index can be filtered by status', function () {
    $user = User::factory()->create();
    BettingSlip::factory()->for($user)->create(['name' => 'Draft One']);
    BettingSlip::factory()->for($user)->ready()->create(['name' => 'Ready One']);
    BettingSlip::factory()->for($user)->archived()->create(['name' => 'Archived One']);

    Volt::actingAs($user)
        ->test('betting-slips.index')
        ->call('setFilter', 'draft')
        ->assertSee('Draft One')
        ->assertDontSee('Ready One')
        ->assertDontSee('Archived One');
});
