<?php

use App\Actions\Journal\CreateJournalEntry;
use App\Domain\Journal\JournalEntryCategory;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('an independent reflection persists the Sprint 12 fields without implying an analysis', function () {
    $user = User::factory()->create();

    $entry = (new CreateJournalEntry)->execute(
        $user,
        null,
        'I paused before changing the slip.',
        'A deliberate pause',
        JournalEntryCategory::DecisionDiscipline,
        'Review the structure before making additions.',
    );

    expect($entry->fresh())
        ->title->toBe('A deliberate pause')
        ->category->toBe(JournalEntryCategory::DecisionDiscipline)
        ->next_time_note->toBe('Review the structure before making additions.')
        ->slip_analysis_id->toBeNull()
        ->analysis_was_linked->toBeFalse()
        ->analysisLinkState()->toBe('unlinked');
});

test('a selected analysis must belong to the journal owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $foreignAnalysis = analysedSlipForJournal($other);

    expect(fn () => (new CreateJournalEntry)->execute($owner, $foreignAnalysis, 'Not allowed.'))
        ->toThrow(AuthorizationException::class);

    Livewire::actingAs($owner)
        ->test('journal.entry')
        ->set('slipAnalysisId', $foreignAnalysis->id)
        ->set('reflection', 'Still not allowed.')
        ->call('save')
        ->assertHasErrors(['slipAnalysisId']);

    expect(JournalEntry::count())->toBe(0);
});

test('the analysis relationship survives deletion as an explicit unavailable state', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);
    $entry = (new CreateJournalEntry)->execute($user, $analysis, 'Keep this reflection.');

    $analysis->delete();
    $entry->refresh();

    expect($entry->slip_analysis_id)->toBeNull()
        ->and($entry->analysis_was_linked)->toBeTrue()
        ->and($entry->analysisLinkState())->toBe('unavailable');

    $this->actingAs($user)
        ->get(route('journal'))
        ->assertOk()
        ->assertSee('Analysis unavailable');
});

test('editing metadata does not replace the immutable analysis link', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);
    $otherAnalysis = analysedSlipForJournal($user);
    $entry = (new CreateJournalEntry)->execute($user, $analysis, 'Original.');

    Livewire::actingAs($user)
        ->test('journal.entry', ['journalEntry' => $entry])
        ->set('slipAnalysisId', $otherAnalysis->id)
        ->set('title', 'Updated title')
        ->set('category', JournalEntryCategory::ResearchGap->value)
        ->set('reflection', 'Updated reflection.')
        ->set('nextTimeNote', 'Check the source date.')
        ->call('save')
        ->assertRedirect(route('journal'));

    expect($entry->fresh())
        ->slip_analysis_id->toBe($analysis->id)
        ->title->toBe('Updated title')
        ->category->toBe(JournalEntryCategory::ResearchGap)
        ->next_time_note->toBe('Check the source date.');
});

test('History analysis preselection accepts only an owned analysis', function () {
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);

    $this->actingAs($user)
        ->get(route('journal.create', ['analysis' => $analysis->id]))
        ->assertOk()
        ->assertSee($analysis->bettingSlip->displayLabel());

    $foreignAnalysis = analysedSlipForJournal(User::factory()->create());

    $this->actingAs($user)
        ->get(route('journal.create', ['analysis' => $foreignAnalysis->id]))
        ->assertNotFound();
});

test('category date and analysis filters return traceable results', function () {
    Carbon::setTestNow('2026-07-30 12:00:00');
    $user = User::factory()->create();
    $analysis = analysedSlipForJournal($user);

    $recent = (new CreateJournalEntry)->execute(
        $user,
        $analysis,
        'Recent linked reflection.',
        category: JournalEntryCategory::SlipConstruction,
    );
    $older = (new CreateJournalEntry)->execute(
        $user,
        null,
        'Older independent reflection.',
        category: JournalEntryCategory::ResearchGap,
    );
    $older->forceFill(['created_at' => now()->subMonths(4)])->save();

    Livewire::actingAs($user)
        ->test('journal.index')
        ->set('categoryFilter', JournalEntryCategory::SlipConstruction->value)
        ->assertSee('Recent linked reflection.')
        ->assertDontSee('Older independent reflection.')
        ->set('categoryFilter', 'all')
        ->set('analysisFilter', 'unlinked')
        ->assertSee('Older independent reflection.')
        ->assertDontSee('Recent linked reflection.')
        ->set('analysisFilter', 'all')
        ->set('dateRange', 'month')
        ->assertSee('Recent linked reflection.')
        ->assertDontSee('Older independent reflection.');

    Carbon::setTestNow();
});

test('the archive groups by month, paginates, and expands long reflections intentionally', function () {
    $user = User::factory()->create();

    foreach (range(1, 10) as $index) {
        JournalEntry::factory()->for($user)->create([
            'slip_analysis_id' => null,
            'analysis_was_linked' => false,
            'title' => "Reflection {$index}",
            'reflection' => $index === 1 ? str_repeat('Long reflection content. ', 30) : "Short reflection {$index}.",
            'created_at' => now()->subDays($index),
        ]);
    }

    Livewire::actingAs($user)
        ->test('journal.index')
        ->assertSee(now()->format('F Y'))
        ->assertSee('Page 1 of 2')
        ->assertSee('Read reflection')
        ->call('toggleReflection', JournalEntry::where('title', 'Reflection 1')->value('id'))
        ->assertSee('Show less');
});

test('the form exposes temporary draft protection without persisting a record', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('journal.create'));

    $response->assertOk()
        ->assertSee('sessionStorage.setItem', false)
        ->assertSee("slipguard-journal-draft-{$user->id}-new", false)
        ->assertSee('journal-entry-saved', false)
        ->assertSee('beforeunload', false)
        ->assertSee('Discard your unsaved Journal changes?', false);

    expect(JournalEntry::count())->toBe(0);
});

test('validation failure does not announce a confirmed save', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('journal.entry')
        ->set('title', 'Unfinished reflection')
        ->set('reflection', '')
        ->call('save')
        ->assertHasErrors(['reflection'])
        ->assertNotDispatched('journal-entry-saved');

    expect(JournalEntry::count())->toBe(0);
});
