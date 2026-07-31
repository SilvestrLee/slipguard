<?php

use App\Domain\Journal\JournalEntryCategory;
use App\Domain\Risk\Results\RiskBand;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

/**
 * Sprint 12 Decision Journal — a paginated, reflective archive. Filtering
 * uses only persisted structured fields and never interprets prose.
 */
new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $categoryFilter = 'all';

    public string $dateRange = 'all';

    public string $analysisFilter = 'all';

    /** @var array<int, int> */
    public array $expandedEntries = [];

    public string $operationError = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['categoryFilter', 'dateRange', 'analysisFilter'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('categoryFilter', 'dateRange', 'analysisFilter');
        $this->resetPage();
    }

    public function toggleReflection(int $entryId): void
    {
        $entry = Auth::user()->journalEntries()->findOrFail($entryId);

        if (in_array($entry->id, $this->expandedEntries, true)) {
            $this->expandedEntries = array_values(array_diff($this->expandedEntries, [$entry->id]));

            return;
        }

        $this->expandedEntries[] = $entry->id;
    }

    public function riskBandToken(?RiskBand $band): string
    {
        return match ($band) {
            RiskBand::Low => 'low',
            RiskBand::Moderate => 'moderate',
            RiskBand::High => 'high',
            RiskBand::VeryHigh => 'very-high',
            default => 'moderate',
        };
    }

    public function linkStateLabel(JournalEntry $entry): string
    {
        return match ($entry->analysisLinkState()) {
            'linked' => __('Analysis linked'),
            'unavailable' => __('Analysis unavailable'),
            default => __('Independent reflection'),
        };
    }

    private function filteredQuery()
    {
        $query = Auth::user()->journalEntries()
            ->with('slipAnalysis.bettingSlip.legs')
            ->latest('journal_entries.created_at')
            ->latest('journal_entries.id');

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        match ($this->dateRange) {
            'month' => $query->where('journal_entries.created_at', '>=', now()->subDays(30)->startOfDay()),
            'quarter' => $query->where('journal_entries.created_at', '>=', now()->subDays(90)->startOfDay()),
            'year' => $query->where('journal_entries.created_at', '>=', now()->subYear()->startOfDay()),
            default => null,
        };

        if ($this->analysisFilter === 'unlinked') {
            $query->whereNull('slip_analysis_id')->where('analysis_was_linked', false);
        } elseif ($this->analysisFilter === 'unavailable') {
            $query->whereNull('slip_analysis_id')->where('analysis_was_linked', true);
        } elseif (str_starts_with($this->analysisFilter, 'analysis:')) {
            $query->where('slip_analysis_id', (int) str($this->analysisFilter)->after('analysis:')->toString());
        }

        return $query;
    }

    public function with(): array
    {
        $entries = $this->filteredQuery()->paginate(8);
        $totalEntries = Auth::user()->journalEntries()->count();

        return [
            'entries' => $entries,
            'groups' => $entries->getCollection()->groupBy(fn (JournalEntry $entry) => $entry->created_at->format('F Y')),
            'hasAnyAnalyses' => Auth::user()->slipAnalyses()->exists(),
            'availableAnalyses' => Auth::user()->slipAnalyses()->with('bettingSlip.legs')->latest()->get(),
            'categories' => JournalEntryCategory::cases(),
            'totalEntries' => $totalEntries,
            'linkedEntries' => Auth::user()->journalEntries()->whereNotNull('slip_analysis_id')->count(),
            'hasFilters' => $this->categoryFilter !== 'all' || $this->dateRange !== 'all' || $this->analysisFilter !== 'all',
        ];
    }
}; ?>

<div class="workspace-page" x-data="{ filtersOpen: false }">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">
        <x-page-header :title="__('Decision Journal')"
                       :description="__('Record what influenced your decisions, what you noticed and what you want to do differently next time.')">
            <x-slot name="action">
                <a href="{{ route('journal.create') }}" wire:navigate
                   class="inline-flex min-h-11 shrink-0 items-center rounded-md bg-accent-strong px-4 text-sm font-semibold text-white hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    <x-heroicon-o-plus class="mr-1.5 size-4" aria-hidden="true" />
                    {{ __('Add entry') }}
                </a>
            </x-slot>
        </x-page-header>

        @if (session('status'))
            <x-alert variant="success">{{ session('status') }}</x-alert>
        @endif

        @if ($operationError)
            <x-workspace.inline-error :title="__('Journal action unavailable')">
                {{ $operationError }}
            </x-workspace.inline-error>
        @endif

        @if ($totalEntries > 0)
            <section class="workspace-section-panel" aria-labelledby="journal-context-summary">
                <h2 id="journal-context-summary" class="sr-only">{{ __('Journal summary') }}</h2>
                <dl class="grid grid-cols-2 gap-5">
                    <div>
                        <dt class="workspace-metadata">{{ __('Total reflections') }}</dt>
                        <dd class="mt-1 text-xl font-semibold font-tabular text-neutral-900">{{ $totalEntries }}</dd>
                    </div>
                    <div class="border-l workspace-internal-border pl-5">
                        <dt class="workspace-metadata">{{ __('Linked to analyses') }}</dt>
                        <dd class="mt-1 text-xl font-semibold font-tabular text-neutral-900">{{ $linkedEntries }}</dd>
                    </div>
                </dl>
            </section>

            <section class="workspace-section" aria-labelledby="journal-timeline-heading">
                <div class="flex items-center justify-between gap-4">
                    <x-workspace.section-heading id="journal-timeline-heading" :title="__('Reflection timeline')"
                                                 :description="__('Eight entries per page, grouped by the month they were written.')" />
                    <div class="flex items-center gap-3">
                        @if ($hasFilters)
                            <button type="button" wire:click="resetFilters"
                                    class="hidden min-h-10 text-sm font-semibold text-neutral-600 hover:text-neutral-900 lg:inline-flex lg:items-center">
                                {{ __('Clear filters') }}
                            </button>
                        @endif
                        <button type="button" @click="filtersOpen = true"
                                class="inline-flex min-h-11 items-center gap-2 rounded-md border border-neutral-300 bg-surface-card px-3 text-sm font-semibold text-neutral-800 lg:hidden"
                                aria-controls="journal-filter-sheet" :aria-expanded="filtersOpen">
                            <x-heroicon-o-adjustments-horizontal class="size-4" aria-hidden="true" />
                            {{ __('Filters') }}
                        </button>
                    </div>
                </div>

                <x-workspace.filter-bar class="hidden lg:flex">
                    <div class="grid w-full grid-cols-3 gap-3">
                        <label class="block">
                            <span class="sr-only">{{ __('Category') }}</span>
                            <select wire:model.live="categoryFilter" class="min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                                <option value="all">{{ __('All categories') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->value }}">{{ __($category->label()) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="sr-only">{{ __('Date written') }}</span>
                            <select wire:model.live="dateRange" class="min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                                <option value="all">{{ __('Any date') }}</option>
                                <option value="month">{{ __('Past 30 days') }}</option>
                                <option value="quarter">{{ __('Past 90 days') }}</option>
                                <option value="year">{{ __('Past year') }}</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="sr-only">{{ __('Linked analysis') }}</span>
                            <select wire:model.live="analysisFilter" class="min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                                <option value="all">{{ __('Any analysis link') }}</option>
                                <option value="unlinked">{{ __('Independent reflections') }}</option>
                                <option value="unavailable">{{ __('Unavailable analyses') }}</option>
                                @foreach ($availableAnalyses as $analysis)
                                    <option value="analysis:{{ $analysis->id }}">{{ $analysis->bettingSlip->displayLabel() }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </x-workspace.filter-bar>

                <div wire:loading.delay wire:target="categoryFilter,dateRange,analysisFilter,gotoPage,nextPage,previousPage" class="workspace-section-panel">
                    <x-workspace.loading-skeleton :rows="4" :label="__('Updating Decision Journal')" />
                </div>

                <div wire:loading.remove wire:target="categoryFilter,dateRange,analysisFilter,gotoPage,nextPage,previousPage">
                    @if ($entries->isEmpty())
                        <x-workspace.no-results :title="__('No matching reflections')" :description="__('Try changing the category, date or analysis filter.')" />
                        <div class="mt-4 text-center">
                            <button type="button" wire:click="resetFilters" class="min-h-11 text-sm font-semibold text-accent-strong hover:text-accent">
                                {{ __('Clear filters') }}
                            </button>
                        </div>
                    @else
                        <div class="space-y-8">
                            @foreach ($groups as $month => $monthEntries)
                                <section class="workspace-section-panel workspace-section" aria-labelledby="journal-month-{{ str($month)->slug() }}">
                                    <div class="flex items-center justify-between gap-4">
                                        <h2 id="journal-month-{{ str($month)->slug() }}" class="workspace-section-title">{{ $month }}</h2>
                                        <span class="workspace-metadata">{{ trans_choice(':count entry|:count entries', $monthEntries->count(), ['count' => $monthEntries->count()]) }}</span>
                                    </div>

                                    <div class="relative space-y-3 before:absolute before:bottom-4 before:left-2 before:top-4 before:w-px before:bg-neutral-300">
                                        @foreach ($monthEntries as $entry)
                                            @php
                                                $linkedAnalysis = $entry->slipAnalysis;
                                                $linkState = $entry->analysisLinkState();
                                                $isLong = mb_strlen($entry->reflection) > 360;
                                                $expanded = in_array($entry->id, $expandedEntries, true);
                                            @endphp
                                            <article class="relative ml-6 rounded-lg border workspace-record-surface workspace-card-padding"
                                                     wire:key="journal-entry-{{ $entry->id }}">
                                                <span class="absolute -left-[1.93rem] top-6 size-3 rounded-full border-2 border-accent bg-surface-page" aria-hidden="true"></span>

                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                    <div class="min-w-0">
                                                        <h3 class="workspace-card-title text-base">{{ $entry->displayTitle() }}</h3>
                                                        <p class="mt-1 workspace-metadata">{{ __('Written :time', ['time' => $entry->created_at->diffForHumans()]) }}</p>
                                                    </div>
                                                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                                                        <span class="inline-flex items-center rounded-full border border-neutral-300 bg-surface-soft px-2.5 py-1 text-xs font-semibold text-neutral-700">
                                                            {{ __($entry->category->label()) }}
                                                        </span>
                                                        @if ($linkedAnalysis?->risk_band)
                                                            <x-workspace.risk-badge :band="$linkedAnalysis->risk_band->label()" :tone="$this->riskBandToken($linkedAnalysis->risk_band)" class="px-2.5 py-1 text-xs" />
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="mt-4 rounded-md border workspace-internal-border bg-surface-soft p-4">
                                                    @if ($linkState === 'linked')
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ __('Linked analysis') }}</p>
                                                        <p class="mt-1 text-sm font-semibold text-neutral-900">{{ $linkedAnalysis->bettingSlip->displayLabel() }}</p>
                                                    @elseif ($linkState === 'unavailable')
                                                        <p class="flex items-center gap-2 text-sm font-semibold text-neutral-700">
                                                            <x-heroicon-o-link-slash class="size-4" aria-hidden="true" />
                                                            {{ __('Analysis unavailable') }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-neutral-500">{{ __('This reflection originally had a linked analysis, but that report is no longer accessible.') }}</p>
                                                    @else
                                                        <p class="flex items-center gap-2 text-sm font-semibold text-neutral-700">
                                                            <x-heroicon-o-pencil-square class="size-4" aria-hidden="true" />
                                                            {{ __('Independent reflection') }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-neutral-500">{{ __('No analysis was linked when this entry was created.') }}</p>
                                                    @endif
                                                </div>

                                                <div class="mt-4 text-sm leading-6 text-neutral-800">
                                                    @if ($isLong && ! $expanded)
                                                        <p>{{ \Illuminate\Support\Str::limit($entry->reflection, 360) }}</p>
                                                    @else
                                                        <p class="whitespace-pre-line">{{ $entry->reflection }}</p>
                                                    @endif
                                                </div>

                                                @if ($isLong)
                                                    <button type="button" wire:click="toggleReflection({{ $entry->id }})"
                                                            class="mt-2 min-h-10 text-sm font-semibold text-accent-strong hover:text-accent"
                                                            aria-expanded="{{ $expanded ? 'true' : 'false' }}">
                                                        {{ $expanded ? __('Show less') : __('Read reflection') }}
                                                    </button>
                                                @endif

                                                @if ($entry->next_time_note)
                                                    <aside class="mt-4 rounded-md border workspace-internal-border bg-accent/5 px-4 py-3">
                                                        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-accent-strong">
                                                            <x-heroicon-o-bookmark class="size-4" aria-hidden="true" />
                                                            {{ __('Remember next time') }}
                                                        </p>
                                                        <p class="mt-2 text-sm leading-6 text-neutral-800">{{ $entry->next_time_note }}</p>
                                                    </aside>
                                                @endif

                                                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t workspace-internal-border pt-4">
                                                    @if ($linkState === 'linked')
                                                        <a href="{{ route('analyze.report', $linkedAnalysis->bettingSlip) }}" wire:navigate
                                                           class="inline-flex min-h-10 items-center text-sm font-semibold text-accent-strong hover:text-accent">
                                                            {{ __('Open linked analysis') }}
                                                            <x-heroicon-o-arrow-right class="ml-1.5 size-4" aria-hidden="true" />
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('journal.edit', $entry) }}" wire:navigate
                                                       class="inline-flex min-h-10 items-center text-sm font-semibold text-neutral-700 hover:text-neutral-900">
                                                        {{ __('Edit reflection') }}
                                                    </a>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        @if ($entries->hasPages())
                            <nav class="mt-6 flex items-center justify-between gap-4 border-t workspace-internal-border pt-5"
                                 aria-label="{{ __('Decision Journal pages') }}">
                                <button type="button" wire:click="previousPage" @disabled($entries->onFirstPage())
                                        class="inline-flex min-h-11 items-center rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-800 hover:bg-surface-soft disabled:opacity-50">
                                    {{ __('Previous') }}
                                </button>
                                <p class="workspace-metadata font-tabular">{{ __('Page :current of :last', ['current' => $entries->currentPage(), 'last' => $entries->lastPage()]) }}</p>
                                <button type="button" wire:click="nextPage" @disabled(! $entries->hasMorePages())
                                        class="inline-flex min-h-11 items-center rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-800 hover:bg-surface-soft disabled:opacity-50">
                                    {{ __('Next') }}
                                </button>
                            </nav>
                        @endif
                    @endif
                </div>
            </section>
        @else
            <x-empty-state :title="__('Your Decision Journal is empty.')"
                :description="__('Record what influenced your decisions, what you noticed and what you want to remember next time.')">
                <x-slot name="action">
                    <a href="{{ route('journal.create') }}" wire:navigate
                       class="inline-flex min-h-11 items-center rounded-md bg-accent-strong px-4 text-sm font-semibold text-white hover:bg-accent">
                        {{ __('Add your first reflection') }}
                    </a>
                </x-slot>
            </x-empty-state>
        @endif
    </div>

    <div id="journal-filter-sheet" x-cloak x-show="filtersOpen" class="fixed inset-0 z-50 lg:hidden"
         role="dialog" aria-modal="true" aria-labelledby="journal-filter-title"
         @keydown.escape.window="filtersOpen = false">
        <button type="button" class="absolute inset-0 bg-neutral-950/60" @click="filtersOpen = false" aria-label="{{ __('Close filters') }}"></button>
        <div class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-xl border border-neutral-300 bg-surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <h2 id="journal-filter-title" class="workspace-title">{{ __('Journal filters') }}</h2>
                <button type="button" @click="filtersOpen = false" class="flex size-11 items-center justify-center rounded-md text-neutral-700" aria-label="{{ __('Close filters') }}">
                    <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                </button>
            </div>
            <div class="mt-5 space-y-4">
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Category') }}
                    <select wire:model.live="categoryFilter" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="all">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->value }}">{{ __($category->label()) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Date written') }}
                    <select wire:model.live="dateRange" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="all">{{ __('Any date') }}</option>
                        <option value="month">{{ __('Past 30 days') }}</option>
                        <option value="quarter">{{ __('Past 90 days') }}</option>
                        <option value="year">{{ __('Past year') }}</option>
                    </select>
                </label>
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Linked analysis') }}
                    <select wire:model.live="analysisFilter" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="all">{{ __('Any analysis link') }}</option>
                        <option value="unlinked">{{ __('Independent reflections') }}</option>
                        <option value="unavailable">{{ __('Unavailable analyses') }}</option>
                        @foreach ($availableAnalyses as $analysis)
                            <option value="analysis:{{ $analysis->id }}">{{ $analysis->bettingSlip->displayLabel() }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" wire:click="resetFilters" class="min-h-11 flex-1 rounded-md border border-neutral-300 text-sm font-semibold text-neutral-800">{{ __('Clear') }}</button>
                <button type="button" @click="filtersOpen = false" class="min-h-11 flex-1 rounded-md bg-accent-strong text-sm font-semibold text-white">{{ __('Show entries') }}</button>
            </div>
        </div>
    </div>
</div>
