<?php

use App\Actions\Journal\CreateJournalEntry;
use App\Actions\Journal\UpdateJournalEntry;
use App\Domain\Journal\JournalEntryCategory;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * Sprint 12 dedicated reflection form. Analysis linkage is optional at
 * creation and immutable thereafter.
 */
new #[Layout('layouts.app')] class extends Component
{
    public ?JournalEntry $journalEntry = null;

    public ?int $slipAnalysisId = null;

    public string $title = '';

    public string $category = JournalEntryCategory::GeneralReflection->value;

    public string $reflection = '';

    public string $nextTimeNote = '';

    public function mount(?JournalEntry $journalEntry = null): void
    {
        if ($journalEntry) {
            $this->authorize('update', $journalEntry);

            $this->journalEntry = $journalEntry;
            $this->slipAnalysisId = $journalEntry->slip_analysis_id;
            $this->title = (string) $journalEntry->title;
            $this->category = $journalEntry->category->value;
            $this->reflection = $journalEntry->reflection;
            $this->nextTimeNote = (string) $journalEntry->next_time_note;

            return;
        }

        $this->authorize('create', JournalEntry::class);

        $requestedAnalysis = request()->integer('analysis');
        if ($requestedAnalysis > 0) {
            $this->slipAnalysisId = Auth::user()->slipAnalyses()->findOrFail($requestedAnalysis)->id;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'slipAnalysisId' => [
                'nullable',
                'integer',
                Rule::exists('slip_analyses', 'id')->where('user_id', Auth::id()),
            ],
            'title' => ['nullable', 'string', 'max:120'],
            'category' => ['required', Rule::enum(JournalEntryCategory::class)],
            'reflection' => ['required', 'string', 'max:5000'],
            'nextTimeNote' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = JournalEntryCategory::from($validated['category']);

        if ($this->journalEntry) {
            $this->authorize('update', $this->journalEntry);

            (new UpdateJournalEntry)->execute(
                $this->journalEntry,
                $validated['reflection'],
                $validated['title'] ?? null,
                $category,
                $validated['nextTimeNote'] ?? null,
            );
        } else {
            $slipAnalysis = filled($validated['slipAnalysisId'] ?? null)
                ? Auth::user()->slipAnalyses()->findOrFail($validated['slipAnalysisId'])
                : null;

            (new CreateJournalEntry)->execute(
                Auth::user(),
                $slipAnalysis,
                $validated['reflection'],
                $validated['title'] ?? null,
                $category,
                $validated['nextTimeNote'] ?? null,
            );
        }

        session()->flash('status', __('Journal entry saved.'));
        $this->dispatch('journal-entry-saved');
        $this->redirect(route('journal'), navigate: true);
    }

    public function with(): array
    {
        return [
            'availableAnalyses' => $this->journalEntry
                ? collect()
                : Auth::user()->slipAnalyses()->with('bettingSlip.legs')->latest()->get(),
            'categories' => JournalEntryCategory::cases(),
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="container-reading workspace-gutter mx-auto workspace-stack">
        <x-page-header :title="$journalEntry ? __('Edit Journal Entry') : __('Add a Journal Entry')"
                       :description="__('Record what influenced your decision and what you want to remember.')">
            <x-slot name="action">
                <a href="{{ route('journal') }}" wire:navigate class="text-sm font-medium text-neutral-600 hover:text-neutral-900">
                    {{ __('Back to Journal') }}
                </a>
            </x-slot>
        </x-page-header>

        <form wire:submit="save" class="space-y-6"
              x-data="{
                  dirty: false,
                  saving: false,
                  draftKey: 'slipguard-journal-draft-{{ Auth::id() }}-{{ $journalEntry?->id ?? 'new' }}',
                  persistDraft() {
                      this.dirty = true;
                      sessionStorage.setItem(this.draftKey, JSON.stringify({
                          title: $wire.title,
                          category: $wire.category,
                          slipAnalysisId: $wire.slipAnalysisId,
                          reflection: $wire.reflection,
                          nextTimeNote: $wire.nextTimeNote
                      }));
                  },
                  discardAndLeave(url) {
                      if (!this.dirty || confirm('{{ __('Discard your unsaved Journal changes?') }}')) {
                          sessionStorage.removeItem(this.draftKey);
                          this.dirty = false;
                          window.location.href = url;
                      }
                  }
              }"
              x-init="
                  const stored = sessionStorage.getItem(draftKey);
                  if (stored) {
                      const draft = JSON.parse(stored);
                      Object.entries(draft).forEach(([key, value]) => $wire.set(key, value));
                      dirty = true;
                  }
                  window.__slipguardJournalBeforeUnload && window.removeEventListener('beforeunload', window.__slipguardJournalBeforeUnload);
                  window.__slipguardJournalBeforeUnload = event => {
                      if (!dirty || saving) return;
                      event.preventDefault();
                      event.returnValue = '';
                  };
                  window.addEventListener('beforeunload', window.__slipguardJournalBeforeUnload);
                  window.__slipguardJournalNavigate && document.removeEventListener('livewire:navigate', window.__slipguardJournalNavigate);
                  window.__slipguardJournalNavigate = event => {
                      if (!dirty || saving || confirm('{{ __('Leave this page and discard your unsaved Journal changes?') }}')) return;
                      event.preventDefault();
                  };
                  document.addEventListener('livewire:navigate', window.__slipguardJournalNavigate);
              "
              @input.debounce.350ms="persistDraft()"
              @change="persistDraft()"
              @journal-entry-saved.window="
                  sessionStorage.removeItem(draftKey);
                  dirty = false;
                  saving = true;
              ">
            <section class="workspace-section-panel workspace-section" aria-labelledby="journal-context-heading">
                <div>
                    <h2 id="journal-context-heading" class="workspace-section-title">{{ __('Reflection context') }}</h2>
                    <p class="mt-1 workspace-helper">{{ __('Keep this light. Only the reflection itself is required.') }}</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="journal-title" :value="__('Title (optional)')" />
                        <x-text-input id="journal-title" wire:model="title" class="mt-1 block w-full"
                                      :placeholder="__('e.g. Late addition pattern')" maxlength="120" />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>
                    <div>
                        <x-input-label for="journal-category" :value="__('Category')" />
                        <select id="journal-category" wire:model="category"
                                class="mt-1 min-h-11 block w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900 focus:border-accent focus:ring-accent">
                            @foreach ($categories as $categoryOption)
                                <option value="{{ $categoryOption->value }}">{{ __($categoryOption->label()) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('category')" />
                    </div>
                </div>

                @if ($journalEntry)
                    <div>
                        <x-input-label :value="__('Linked analysis')" />
                        @if ($journalEntry->analysisLinkState() === 'linked')
                            <p class="mt-1 text-sm text-neutral-700">
                                {{ $journalEntry->slipAnalysis->bettingSlip->displayLabel() }}
                                <span class="text-neutral-500">&middot; {{ __('Analysed :time', ['time' => $journalEntry->slipAnalysis->created_at->diffForHumans()]) }}</span>
                            </p>
                        @elseif ($journalEntry->analysisLinkState() === 'unavailable')
                            <p class="mt-1 text-sm text-neutral-700">{{ __('The analysis originally linked to this reflection is no longer available.') }}</p>
                        @else
                            <p class="mt-1 text-sm text-neutral-700">{{ __('No analysis linked — this is an independent reflection.') }}</p>
                        @endif
                        <p class="mt-1 text-xs text-neutral-500">{{ __('The linked analysis cannot be changed once an entry is saved.') }}</p>
                    </div>
                @else
                    <div>
                        <x-input-label for="slipAnalysisId" :value="__('Linked analysis (optional)')" />
                        <select wire:model="slipAnalysisId" id="slipAnalysisId"
                                class="mt-1 min-h-11 block w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900 focus:border-accent focus:ring-accent">
                            <option value="">{{ __('No linked analysis — independent reflection') }}</option>
                            @foreach ($availableAnalyses as $analysis)
                                <option value="{{ $analysis->id }}">
                                    {{ $analysis->bettingSlip->displayLabel() }} — {{ $analysis->created_at->format('j M Y') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('slipAnalysisId')" />
                    </div>
                @endif
            </section>

            <section class="workspace-primary-card rounded-lg workspace-card-padding">
                <x-input-label for="reflection" :value="__('What influenced your decision, and what did you notice?')" />
                <textarea wire:model="reflection" id="reflection" rows="8"
                          class="mt-2 block w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900 focus:border-accent focus:ring-accent"
                          placeholder="{{ __('Write in your own words. SlipGuard will not interpret or score this reflection.') }}"></textarea>
                <x-input-error class="mt-2" :messages="$errors->get('reflection')" />

                <div class="mt-6 border-t workspace-internal-border pt-5">
                    <x-input-label for="nextTimeNote" :value="__('What would you like to remember next time? (optional)')" />
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ __('Keep a short reminder for your own decision process. It will appear with this reflection in your Journal.') }}
                    </p>
                    <textarea wire:model="nextTimeNote" id="nextTimeNote" rows="3"
                              class="mt-2 block w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900 focus:border-accent focus:ring-accent"
                              placeholder="{{ __('A short reminder for your future self.') }}"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('nextTimeNote')" />
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                <x-primary-button wire:loading.attr="disabled" wire:target="save">
                    {{ __('Save') }}
                </x-primary-button>
                <button type="button" @click="discardAndLeave(@js(route('journal')))"
                        class="min-h-11 px-3 text-sm font-medium text-neutral-600 hover:text-neutral-900">
                    {{ __('Cancel') }}
                </button>
                <p x-show="dirty" x-cloak class="text-xs text-neutral-500 sm:ml-auto" role="status">
                    {{ __('Draft kept for this browser session until you save or discard it.') }}
                </p>
            </div>
        </form>
    </div>
</div>
