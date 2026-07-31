<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Actions\Journal\CreateJournalEntry;
use App\Models\BettingSlip;
use App\Models\JournalEntry;
use App\Models\SlipAnalysis;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function analysedSlipForJournal(User $user): SlipAnalysis
{
    $slip = BettingSlip::factory()->for($user)->create();
    $slip->legs()->create([
        'sport' => 'Football', 'competition' => 'Premier League', 'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90', 'display_order' => 0,
    ]);
    $slip->markReady();

    return (new AnalyzeBettingSlip)->execute($slip);
}

test('Journal supports an independent first reflection when no analyses exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('journal'))
        ->assertOk()
        ->assertSee('Your Decision Journal is empty.')
        ->assertSee('Add your first reflection');
});

test('Journal shows an entry action when analyses exist but no entries do', function () {
    $user = User::factory()->create();
    analysedSlipForJournal($user);

    $this->actingAs($user)
        ->get(route('journal'))
        ->assertOk()
        ->assertSee('Your Decision Journal is empty.')
        ->assertSee('Add your first reflection');
});

test('creating a journal entry links it to the chosen analysis and cannot omit the reflection', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);

    Livewire::actingAs($user)
        ->test('journal.entry')
        ->set('slipAnalysisId', $analysis->id)
        ->set('reflection', 'Stuck to my staking plan and it paid off.')
        ->call('save')
        ->assertDispatched('journal-entry-saved')
        ->assertRedirect(route('journal'));

    $entry = JournalEntry::sole();
    expect($entry->user_id)->toBe($user->id);
    expect($entry->slip_analysis_id)->toBe($analysis->id);
    expect($entry->reflection)->toBe('Stuck to my staking plan and it paid off.');
});

test('an empty reflection is rejected', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);

    Livewire::actingAs($user)
        ->test('journal.entry')
        ->set('slipAnalysisId', $analysis->id)
        ->set('reflection', '')
        ->call('save')
        ->assertHasErrors(['reflection']);

    expect(JournalEntry::count())->toBe(0);
});

test('editing an entry updates only the reflection — the linked analysis is immutable', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);
    $otherAnalysis = analysedSlipForJournal($user);
    $entry = (new CreateJournalEntry)->execute($user, $analysis, 'Original note.');

    Livewire::actingAs($user)
        ->test('journal.entry', ['journalEntry' => $entry])
        ->set('reflection', 'Updated note.')
        ->call('save')
        ->assertRedirect(route('journal'));

    $entry->refresh();
    expect($entry->reflection)->toBe('Updated note.');
    expect($entry->slip_analysis_id)->toBe($analysis->id)
        ->not->toBe($otherAnalysis->id);
});

test('the Journal list shows entries newest first, each referencing its analysis', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);
    (new CreateJournalEntry)->execute($user, $analysis, 'A note worth remembering.');

    $this->actingAs($user)
        ->get(route('journal'))
        ->assertOk()
        ->assertSee('A note worth remembering.')
        ->assertSee($analysis->bettingSlip->displayLabel());
});

test('a customer cannot view or edit another customer\'s journal entry', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $analysis = analysedSlipForJournal($owner);
    $entry = (new CreateJournalEntry)->execute($owner, $analysis, 'Private note.');

    Livewire::actingAs($otherUser)
        ->test('journal.entry', ['journalEntry' => $entry])
        ->assertForbidden();
});

test('there is no delete ability for a journal entry', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);
    $entry = (new CreateJournalEntry)->execute($user, $analysis, 'Kept forever.');

    expect($user->can('delete', $entry))->toBeFalse();
});

test('U-09 §3.7: Journal issues a small, fixed number of queries regardless of entry count', function () {
    $user = User::factory()->create();
    foreach (range(1, 5) as $i) {
        $analysis = analysedSlipForJournal($user);
        (new CreateJournalEntry)->execute($user, $analysis, "Note {$i}");
    }

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('journal'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});
