<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Exceptions\InvalidBettingSlipTransitionException;
use App\Models\BettingSlip;
use App\Models\SlipAnalysis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

/**
 * Sprint 11 Analysis History.
 *
 * This remains a read model over persisted SlipAnalysis records. Search,
 * metrics and presentation never invoke or recreate deterministic analysis.
 */
new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $risk = 'all';

    public string $dateRange = 'all';

    public string $availability = 'all';

    public string $sort = 'newest';

    public ?int $renamingAnalysisId = null;

    public string $renameValue = '';

    public ?int $removingAnalysisId = null;

    public string $operationError = '';

    public string $operationStatus = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'risk', 'dateRange', 'availability', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'risk', 'dateRange', 'availability', 'sort');
        $this->resetPage();
    }

    public function beginRename(int $analysisId): void
    {
        $analysis = $this->ownedAnalysis($analysisId);

        $this->renamingAnalysisId = $analysis->id;
        $this->renameValue = (string) $analysis->bettingSlip->name;
        $this->resetValidation();
        $this->dispatch('open-modal', 'rename-analysis');
    }

    public function renameAnalysis(): void
    {
        $validated = $this->validate([
            'renamingAnalysisId' => ['required', 'integer'],
            'renameValue' => ['required', 'string', 'max:120'],
        ], [
            'renameValue.required' => __('Enter a descriptive name for this analysis.'),
        ]);

        $analysis = $this->ownedAnalysis((int) $validated['renamingAnalysisId']);
        $this->authorize('update', $analysis->bettingSlip);

        $analysis->bettingSlip->update(['name' => trim($validated['renameValue'])]);

        $this->operationError = '';
        $this->operationStatus = __('Analysis renamed.');
        $this->dispatch('close-modal', 'rename-analysis');
        $this->reset('renamingAnalysisId', 'renameValue');
    }

    public function confirmRemoval(int $analysisId): void
    {
        $analysis = $this->ownedAnalysis($analysisId);

        $this->removingAnalysisId = $analysis->id;
        $this->dispatch('open-modal', 'remove-analysis');
    }

    /**
     * Analysed slips are immutable historical records and cannot be deleted.
     * "Remove from history" uses the existing Analysed -> Archived transition.
     */
    public function removeFromHistory(): void
    {
        if (! $this->removingAnalysisId) {
            return;
        }

        $analysis = $this->ownedAnalysis($this->removingAnalysisId);
        $this->authorize('update', $analysis->bettingSlip);

        try {
            $analysis->bettingSlip->archive();
        } catch (InvalidBettingSlipTransitionException) {
            $this->operationError = __('This analysis could not be removed because its slip is already archived.');
            $this->dispatch('close-modal', 'remove-analysis');

            return;
        }

        $this->operationError = '';
        $this->operationStatus = __('Analysis removed from history. Its deterministic record has been retained.');
        $this->dispatch('close-modal', 'remove-analysis');
        $this->reset('removingAnalysisId');
        $this->resetPage();
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

    public function availabilityLabel(AnalysisAvailability $availability): string
    {
        return match ($availability) {
            AnalysisAvailability::Full => __('Full report'),
            AnalysisAvailability::Limited => __('Limited report'),
            AnalysisAvailability::Unavailable => __('Report unavailable'),
        };
    }

    public function availabilityTone(AnalysisAvailability $availability): string
    {
        return match ($availability) {
            AnalysisAvailability::Full => 'quality-strong',
            AnalysisAvailability::Limited => 'quality-limited',
            AnalysisAvailability::Unavailable => 'quality-insufficient',
        };
    }

    public function mainContributingFactorName(SlipAnalysis $analysis): ?string
    {
        $order = ['RF-001', 'RF-002', 'RF-003', 'RF-004', 'RF-005', 'RF-006'];

        $top = collect($analysis->factor_results ?? [])
            ->reject(fn (array $factor) => ($factor['factor_code'] ?? null) === 'RF-006')
            ->sortBy(fn (array $factor) => array_search($factor['factor_code'] ?? '', $order, true))
            ->sortByDesc(fn (array $factor) => (float) ($factor['adjusted_contribution'] ?? 0))
            ->first();

        if (! $top || (float) ($top['adjusted_contribution'] ?? 0) <= 0.0) {
            return null;
        }

        return match ($top['factor_code']) {
            'RF-001' => __('Number of Selections'),
            'RF-002' => __('Combined Odds'),
            'RF-003' => __('Selection Odds'),
            'RF-004' => __('Risk Concentration'),
            'RF-005' => __('Market Complexity'),
            default => null,
        };
    }

    public function competitionSummary(BettingSlip $slip): string
    {
        $competitions = $slip->legs->pluck('competition')->filter()->unique()->values();

        if ($competitions->isNotEmpty()) {
            $visible = $competitions->take(2)->join(' · ');
            $remaining = $competitions->count() - 2;

            return $remaining > 0 ? $visible.__(' + :count more', ['count' => $remaining]) : $visible;
        }

        $events = $slip->legs->pluck('event_name')->filter()->unique()->values();

        return $events->first() ?: __('Competition not recorded');
    }

    public function combinedOdds(BettingSlip $slip): ?string
    {
        $odds = $slip->legs->pluck('decimal_odds')->filter();

        if ($odds->isEmpty() || $odds->count() !== $slip->legs->count()) {
            return null;
        }

        return number_format($odds->reduce(fn (float $total, mixed $odd) => $total * (float) $odd, 1.0), 2);
    }

    public function groupLabel(SlipAnalysis $analysis): string
    {
        $created = $analysis->created_at;

        return match (true) {
            $created->isToday() => __('Today'),
            $created->greaterThanOrEqualTo(now()->startOfWeek()) => __('This week'),
            $created->greaterThanOrEqualTo(now()->startOfMonth()) => __('Earlier this month'),
            default => __('Older'),
        };
    }

    private function ownedAnalysis(int $analysisId): SlipAnalysis
    {
        return Auth::user()->slipAnalyses()
            ->with('bettingSlip.legs')
            ->whereHas('bettingSlip', fn (Builder $query) => $query->where('status', '!=', BettingSlipStatus::Archived->value))
            ->findOrFail($analysisId);
    }

    private function baseQuery()
    {
        return Auth::user()->slipAnalyses()
            ->whereHas('bettingSlip', fn (Builder $query) => $query->where('status', '!=', BettingSlipStatus::Archived->value));
    }

    private function filteredQuery()
    {
        $query = $this->baseQuery()
            ->with('bettingSlip.legs');

        if ($search = trim($this->search)) {
            $query->whereHas('bettingSlip', function (Builder $slips) use ($search) {
                $slips->where(function (Builder $matchingSlips) use ($search) {
                    $matchingSlips
                        ->where('name', 'like', "%{$search}%")
                        ->orWhereHas('legs', function (Builder $legs) use ($search) {
                            $legs->where('competition', 'like', "%{$search}%")
                                ->orWhere('event_name', 'like', "%{$search}%")
                                ->orWhere('selection_name', 'like', "%{$search}%");
                        });
                });
            });
        }

        if ($this->risk !== 'all') {
            $query->where('risk_band', $this->risk);
        }

        if ($this->availability !== 'all') {
            $query->where('availability', $this->availability);
        }

        match ($this->dateRange) {
            'today' => $query->where('slip_analyses.created_at', '>=', now()->startOfDay()),
            'week' => $query->where('slip_analyses.created_at', '>=', now()->subDays(7)->startOfDay()),
            'month' => $query->where('slip_analyses.created_at', '>=', now()->subDays(30)->startOfDay()),
            default => null,
        };

        match ($this->sort) {
            'oldest' => $query->oldest('slip_analyses.created_at')->oldest('slip_analyses.id'),
            'highest-risk' => $query->orderByRaw("CASE risk_band WHEN 'very_high' THEN 4 WHEN 'high' THEN 3 WHEN 'moderate' THEN 2 WHEN 'low' THEN 1 ELSE 0 END DESC")
                ->latest('slip_analyses.created_at')
                ->latest('slip_analyses.id'),
            'lowest-risk' => $query->orderByRaw("CASE risk_band WHEN 'low' THEN 1 WHEN 'moderate' THEN 2 WHEN 'high' THEN 3 WHEN 'very_high' THEN 4 ELSE 5 END ASC")
                ->latest('slip_analyses.created_at')
                ->latest('slip_analyses.id'),
            default => $query->latest('slip_analyses.created_at')->latest('slip_analyses.id'),
        };

        return $query;
    }

    private function metrics(): array
    {
        $base = $this->baseQuery();
        $total = (clone $base)->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'commonRisk' => __('Not available'),
                'averageLegs' => __('Not available'),
                'commonFactor' => __('Not available'),
            ];
        }

        $commonRiskValue = (clone $base)
            ->whereNotNull('risk_band')
            ->select('risk_band', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('risk_band')
            ->orderByDesc('aggregate')
            ->value('risk_band');

        // Use only persisted slip ids for one compact leg-count query.
        $slipIds = (clone $base)->pluck('betting_slip_id');
        $averageLegs = $slipIds->isEmpty()
            ? null
            : DB::table('betting_slip_legs')
                ->whereIn('betting_slip_id', $slipIds)
                ->selectRaw('COUNT(*) * 1.0 / COUNT(DISTINCT betting_slip_id) as average_legs')
                ->value('average_legs');

        $factorCounts = [];
        foreach ((clone $base)->get(['factor_results']) as $analysis) {
            if ($factor = $this->mainContributingFactorName($analysis)) {
                $factorCounts[$factor] = ($factorCounts[$factor] ?? 0) + 1;
            }
        }
        arsort($factorCounts);

        $commonRisk = $commonRiskValue
            ? ($commonRiskValue instanceof RiskBand ? $commonRiskValue : RiskBand::from($commonRiskValue))->label()
            : __('Not available');

        return [
            'total' => $total,
            'commonRisk' => $commonRisk,
            'averageLegs' => $averageLegs !== null ? number_format((float) $averageLegs, 1) : __('Not available'),
            'commonFactor' => array_key_first($factorCounts) ?: __('Not available'),
        ];
    }

    public function with(): array
    {
        $analyses = $this->filteredQuery()->paginate(10);

        return [
            'analyses' => $analyses,
            'groups' => $analyses->getCollection()->groupBy(fn (SlipAnalysis $analysis) => $this->groupLabel($analysis)),
            'metrics' => $this->metrics(),
            'hasFilters' => trim($this->search) !== ''
                || $this->risk !== 'all'
                || $this->dateRange !== 'all'
                || $this->availability !== 'all'
                || $this->sort !== 'newest',
        ];
    }
}; ?>

<div class="workspace-page" x-data="{ filtersOpen: false }">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">
        <x-page-header :title="__('Analysis History')" :description="__('Search, compare and reopen your completed structural risk reports.')">
            <x-slot name="action">
                <a href="{{ route('analyze.intake') }}" wire:navigate
                   class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-md bg-accent-strong px-4 text-sm font-semibold text-white hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    <x-heroicon-o-plus class="mr-1.5 size-4" aria-hidden="true" />
                    {{ __('New analysis') }}
                </a>
            </x-slot>
        </x-page-header>

        @if ($operationStatus)
            <div role="status" class="rounded-md border border-alert-success/30 bg-alert-success/10 px-4 py-3 text-sm text-alert-success-strong">
                {{ $operationStatus }}
            </div>
        @endif

        @if ($operationError)
            <x-workspace.inline-error :title="__('History action unavailable')">
                {{ $operationError }}
            </x-workspace.inline-error>
        @endif

        @if ($metrics['total'] > 0)
            <section class="workspace-section-panel" aria-labelledby="history-summary-heading">
                <h2 id="history-summary-heading" class="sr-only">{{ __('Analysis summary') }}</h2>
                <dl class="grid grid-cols-2 gap-5 divide-neutral-200 sm:grid-cols-4 sm:divide-x">
                    <div class="sm:px-4 sm:first:pl-0">
                        <dt class="workspace-metadata">{{ __('Total analyses') }}</dt>
                        <dd class="mt-1 text-xl font-semibold font-tabular text-neutral-900">{{ $metrics['total'] }}</dd>
                    </div>
                    <div class="sm:px-4">
                        <dt class="workspace-metadata">{{ __('Most common risk') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $metrics['commonRisk'] }}</dd>
                    </div>
                    <div class="sm:px-4">
                        <dt class="workspace-metadata">{{ __('Average selections') }}</dt>
                        <dd class="mt-1 text-xl font-semibold font-tabular text-neutral-900">{{ $metrics['averageLegs'] }}</dd>
                    </div>
                    <div class="sm:px-4">
                        <dt class="workspace-metadata">{{ __('Frequent factor') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $metrics['commonFactor'] }}</dd>
                    </div>
                </dl>
            </section>
        @endif

        @if ($metrics['total'] > 0)
            <section class="workspace-section" aria-labelledby="archive-heading">
                <div class="flex items-center justify-between gap-4">
                    <x-workspace.section-heading id="archive-heading" :title="__('Report archive')" :description="__('Ten reports per page, grouped by when they were analysed.')" />
                    <button type="button" @click="filtersOpen = true"
                            class="inline-flex min-h-11 items-center gap-2 rounded-md border border-neutral-300 bg-surface-card px-3 text-sm font-semibold text-neutral-800 lg:hidden"
                            aria-controls="history-filter-sheet" :aria-expanded="filtersOpen">
                        <x-heroicon-o-adjustments-horizontal class="size-4" aria-hidden="true" />
                        {{ __('Filters') }}
                    </button>
                </div>

                <x-workspace.filter-bar class="hidden lg:flex">
                    <x-workspace.search-field wire:model.live.debounce.300ms="search" :placeholder="__('Search title, competition, fixture or team')" />
                    <div class="grid grid-cols-4 gap-2">
                        <label class="sr-only" for="history-risk">{{ __('Risk level') }}</label>
                        <select id="history-risk" wire:model.live="risk" class="min-h-11 rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                            <option value="all">{{ __('All risks') }}</option>
                            <option value="low">{{ __('Low risk') }}</option>
                            <option value="moderate">{{ __('Moderate risk') }}</option>
                            <option value="high">{{ __('High risk') }}</option>
                            <option value="very_high">{{ __('Very high risk') }}</option>
                        </select>
                        <label class="sr-only" for="history-date">{{ __('Date range') }}</label>
                        <select id="history-date" wire:model.live="dateRange" class="min-h-11 rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                            <option value="all">{{ __('Any date') }}</option>
                            <option value="today">{{ __('Today') }}</option>
                            <option value="week">{{ __('Past 7 days') }}</option>
                            <option value="month">{{ __('Past 30 days') }}</option>
                        </select>
                        <label class="sr-only" for="history-availability">{{ __('Report availability') }}</label>
                        <select id="history-availability" wire:model.live="availability" class="min-h-11 rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                            <option value="all">{{ __('All reports') }}</option>
                            <option value="full">{{ __('Full') }}</option>
                            <option value="limited">{{ __('Limited') }}</option>
                            <option value="unavailable">{{ __('Unavailable') }}</option>
                        </select>
                        <label class="sr-only" for="history-sort">{{ __('Sort reports') }}</label>
                        <select id="history-sort" wire:model.live="sort" class="min-h-11 rounded-md border-neutral-300 bg-surface-card text-sm text-neutral-900 focus:border-accent focus:ring-accent">
                            <option value="newest">{{ __('Newest') }}</option>
                            <option value="oldest">{{ __('Oldest') }}</option>
                            <option value="highest-risk">{{ __('Highest risk') }}</option>
                            <option value="lowest-risk">{{ __('Lowest risk') }}</option>
                        </select>
                    </div>
                </x-workspace.filter-bar>

                <div wire:loading.delay wire:target="search,risk,dateRange,availability,sort,gotoPage,nextPage,previousPage" class="workspace-section-panel">
                    <x-workspace.loading-skeleton :rows="4" :label="__('Updating analysis history')" />
                </div>

                <div wire:loading.remove wire:target="search,risk,dateRange,availability,sort,gotoPage,nextPage,previousPage">
                    @if ($analyses->isEmpty())
                        <x-workspace.no-results :title="__('No matching analyses')" :description="__('Try a broader search or clear one of the filters.')" class="workspace-section-panel">
                        </x-workspace.no-results>
                        <div class="mt-4 text-center">
                            <button type="button" wire:click="resetFilters" class="min-h-11 text-sm font-semibold text-accent-strong hover:text-accent">
                                {{ __('Clear search and filters') }}
                            </button>
                        </div>
                    @else
                        <div class="space-y-8">
                            @foreach ($groups as $group => $groupAnalyses)
                                <section class="workspace-section-panel workspace-section" aria-labelledby="group-{{ str($group)->slug() }}">
                                    <div class="flex items-center justify-between gap-4">
                                        <h2 id="group-{{ str($group)->slug() }}" class="workspace-section-title">{{ $group }}</h2>
                                        <span class="workspace-metadata">{{ trans_choice(':count report|:count reports', $groupAnalyses->count(), ['count' => $groupAnalyses->count()]) }}</span>
                                    </div>

                                    <div class="space-y-3">
                                        @foreach ($groupAnalyses as $analysis)
                                            @php
                                                $slip = $analysis->bettingSlip;
                                                $token = $this->riskBandToken($analysis->risk_band);
                                                $factor = $this->mainContributingFactorName($analysis);
                                                $combinedOdds = $this->combinedOdds($slip);
                                            @endphp
                                            <article class="group relative rounded-lg border workspace-record-surface workspace-card-padding transition-colors hover:border-neutral-400 focus-within:border-neutral-400"
                                                     wire:key="analysis-{{ $analysis->id }}">
                                                <a href="{{ route('analyze.report', $slip) }}" wire:navigate
                                                   class="absolute inset-0 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                                                   aria-label="{{ __('Open risk report: :title', ['title' => $slip->displayLabel()]) }}"></a>

                                                <div class="relative pointer-events-none flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                                    <div class="min-w-0 flex-1">
                                                        <h3 class="workspace-card-title text-base">{{ $slip->displayLabel() }}</h3>
                                                        <p class="mt-1 workspace-metadata">
                                                            {{ $analysis->created_at->format('j M Y, H:i') }}
                                                            <span aria-hidden="true"> · </span>
                                                            {{ trans_choice(':count selection|:count selections', $slip->legs->count(), ['count' => $slip->legs->count()]) }}
                                                        </p>
                                                        <p class="mt-3 text-sm font-medium text-neutral-800">{{ $this->competitionSummary($slip) }}</p>
                                                        <dl class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                                            <div>
                                                                <dt class="inline text-neutral-500">{{ __('Combined odds:') }}</dt>
                                                                <dd class="inline font-tabular text-neutral-800">{{ $combinedOdds ?: __('Not recorded') }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="inline text-neutral-500">{{ __('Primary factor:') }}</dt>
                                                                <dd class="inline text-neutral-800">{{ $factor ?: __('No dominant factor') }}</dd>
                                                            </div>
                                                        </dl>
                                                    </div>

                                                    <div class="flex shrink-0 flex-wrap items-center gap-2 sm:max-w-56 sm:justify-end">
                                                        @if ($analysis->risk_band)
                                                            <x-workspace.risk-badge :band="$analysis->risk_band->label()" :tone="$token" />
                                                        @else
                                                            <x-workspace.status-badge tone="quality-insufficient" icon="heroicon-o-minus-circle">
                                                                {{ __('Risk unavailable') }}
                                                            </x-workspace.status-badge>
                                                        @endif
                                                        <x-workspace.status-badge :tone="$this->availabilityTone($analysis->availability)"
                                                                                  :icon="$analysis->availability === AnalysisAvailability::Full ? 'heroicon-o-document-check' : 'heroicon-o-information-circle'">
                                                            {{ $this->availabilityLabel($analysis->availability) }}
                                                        </x-workspace.status-badge>
                                                    </div>
                                                </div>

                                                @if ($analysis->availability === AnalysisAvailability::Limited)
                                                    <x-workspace.limited-data-notice class="relative pointer-events-none mt-4 py-3" :title="__('Limited report')">
                                                        {{ __('This report is based only on the evidence available when the slip was analysed.') }}
                                                    </x-workspace.limited-data-notice>
                                                @elseif ($analysis->availability === AnalysisAvailability::Unavailable)
                                                    <div class="relative pointer-events-none mt-4 rounded-md border border-neutral-300 bg-surface-soft px-4 py-3 text-sm text-neutral-700">
                                                        <span class="font-semibold">{{ __('Report unavailable.') }}</span>
                                                        {{ __('Open the record to review why analysis could not be completed.') }}
                                                    </div>
                                                @endif

                                                <div class="relative mt-4 flex items-center justify-between gap-3">
                                                    <span class="pointer-events-none inline-flex items-center gap-1.5 text-xs font-semibold text-accent-strong">
                                                        {{ __('Open risk report') }}
                                                        <x-heroicon-o-arrow-right class="size-3.5" aria-hidden="true" />
                                                    </span>

                                                    <x-workspace.overflow-menu :label="__('Actions for :title', ['title' => $slip->displayLabel()])" class="z-10">
                                                        <button type="button" wire:click="beginRename({{ $analysis->id }})"
                                                                class="flex min-h-10 w-full items-center rounded px-3 text-left text-sm text-neutral-700 hover:bg-surface-soft">
                                                            {{ __('Rename') }}
                                                        </button>
                                                        <a href="{{ route('journal.create', ['analysis' => $analysis->id]) }}" wire:navigate
                                                           class="flex min-h-10 items-center rounded px-3 text-sm text-neutral-700 hover:bg-surface-soft">
                                                            {{ __('Add journal note') }}
                                                        </a>
                                                        <button type="button" wire:click="confirmRemoval({{ $analysis->id }})"
                                                                class="flex min-h-10 w-full items-center rounded px-3 text-left text-sm text-alert-error-strong hover:bg-alert-error/10">
                                                            {{ __('Remove from history') }}
                                                        </button>
                                                    </x-workspace.overflow-menu>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        @if ($analyses->hasPages())
                            <nav class="mt-6 flex items-center justify-between gap-4 border-t workspace-internal-border pt-5"
                                 aria-label="{{ __('Analysis history pages') }}">
                                <button type="button" wire:click="previousPage" @disabled($analyses->onFirstPage())
                                        class="inline-flex min-h-11 items-center rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-800 hover:bg-surface-soft disabled:cursor-not-allowed disabled:opacity-50">
                                    {{ __('Previous') }}
                                </button>
                                <p class="workspace-metadata font-tabular">
                                    {{ __('Page :current of :last', ['current' => $analyses->currentPage(), 'last' => $analyses->lastPage()]) }}
                                </p>
                                <button type="button" wire:click="nextPage" @disabled(! $analyses->hasMorePages())
                                        class="inline-flex min-h-11 items-center rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-800 hover:bg-surface-soft disabled:cursor-not-allowed disabled:opacity-50">
                                    {{ __('Next') }}
                                </button>
                            </nav>
                        @endif
                    @endif
                </div>
            </section>
        @else
            <x-empty-state :title="__('Nothing here yet.')"
                :description="__('Once you\'ve completed a few analyses, you\'ll be able to search and reopen them here.')">
                <x-slot name="action">
                    <a href="{{ route('analyze.intake') }}" wire:navigate
                       class="inline-flex min-h-11 items-center rounded-md bg-accent-strong px-4 text-sm font-semibold text-white hover:bg-accent">
                        {{ __('Analyze a slip') }}
                    </a>
                </x-slot>
            </x-empty-state>
        @endif
    </div>

    <div id="history-filter-sheet" x-cloak x-show="filtersOpen" class="fixed inset-0 z-50 lg:hidden"
         role="dialog" aria-modal="true" aria-labelledby="history-filter-title"
         @keydown.escape.window="filtersOpen = false">
        <button type="button" class="absolute inset-0 bg-neutral-950/60" @click="filtersOpen = false" aria-label="{{ __('Close filters') }}"></button>
        <div class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-xl border border-neutral-300 bg-surface-card p-5">
            <div class="flex items-center justify-between gap-4">
                <h2 id="history-filter-title" class="workspace-title">{{ __('Search and filters') }}</h2>
                <button type="button" @click="filtersOpen = false" class="flex size-11 items-center justify-center rounded-md text-neutral-700" aria-label="{{ __('Close filters') }}">
                    <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                </button>
            </div>
            <div class="mt-5 space-y-4">
                <x-workspace.search-field wire:model.live.debounce.300ms="search" :placeholder="__('Search title, competition, fixture or team')" />
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Risk level') }}
                    <select wire:model.live="risk" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="all">{{ __('All risks') }}</option>
                        <option value="low">{{ __('Low risk') }}</option>
                        <option value="moderate">{{ __('Moderate risk') }}</option>
                        <option value="high">{{ __('High risk') }}</option>
                        <option value="very_high">{{ __('Very high risk') }}</option>
                    </select>
                </label>
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Date range') }}
                    <select wire:model.live="dateRange" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="all">{{ __('Any date') }}</option>
                        <option value="today">{{ __('Today') }}</option>
                        <option value="week">{{ __('Past 7 days') }}</option>
                        <option value="month">{{ __('Past 30 days') }}</option>
                    </select>
                </label>
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Report availability') }}
                    <select wire:model.live="availability" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="all">{{ __('All reports') }}</option>
                        <option value="full">{{ __('Full') }}</option>
                        <option value="limited">{{ __('Limited') }}</option>
                        <option value="unavailable">{{ __('Unavailable') }}</option>
                    </select>
                </label>
                <label class="block text-sm font-medium text-neutral-700">
                    {{ __('Sort reports') }}
                    <select wire:model.live="sort" class="mt-1 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900">
                        <option value="newest">{{ __('Newest') }}</option>
                        <option value="oldest">{{ __('Oldest') }}</option>
                        <option value="highest-risk">{{ __('Highest risk') }}</option>
                        <option value="lowest-risk">{{ __('Lowest risk') }}</option>
                    </select>
                </label>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" wire:click="resetFilters" class="min-h-11 flex-1 rounded-md border border-neutral-300 text-sm font-semibold text-neutral-800">
                    {{ __('Clear') }}
                </button>
                <button type="button" @click="filtersOpen = false" class="min-h-11 flex-1 rounded-md bg-accent-strong text-sm font-semibold text-white">
                    {{ __('Show results') }}
                </button>
            </div>
        </div>
    </div>

    <x-workspace.confirmation-dialog name="rename-analysis" :title="__('Rename analysis')">
        <label for="history-rename" class="block text-sm font-medium text-neutral-700">{{ __('Analysis name') }}</label>
        <input id="history-rename" type="text" wire:model="renameValue" maxlength="120"
               class="mt-2 min-h-11 w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900 focus:border-accent focus:ring-accent">
        <x-input-error class="mt-2" :messages="$errors->get('renameValue')" />
        <x-slot name="actions">
            <button type="button" x-on:click="$dispatch('close')" class="min-h-11 rounded-md border border-neutral-300 px-4 text-sm font-semibold text-neutral-700">{{ __('Cancel') }}</button>
            <button type="button" wire:click="renameAnalysis" wire:loading.attr="disabled" wire:target="renameAnalysis"
                    class="min-h-11 rounded-md bg-accent-strong px-4 text-sm font-semibold text-white disabled:opacity-60">
                {{ __('Save name') }}
            </button>
        </x-slot>
    </x-workspace.confirmation-dialog>

    <x-workspace.confirmation-dialog name="remove-analysis" :title="__('Remove this analysis from history?')">
        <p>{{ __('The report will leave your active archive, but its deterministic record will be retained rather than destroyed.') }}</p>
        <x-slot name="actions">
            <button type="button" x-on:click="$dispatch('close')" class="min-h-11 rounded-md border border-neutral-300 px-4 text-sm font-semibold text-neutral-700">{{ __('Cancel') }}</button>
            <button type="button" wire:click="removeFromHistory" wire:loading.attr="disabled" wire:target="removeFromHistory"
                    class="min-h-11 rounded-md bg-alert-error px-4 text-sm font-semibold text-white disabled:opacity-60">
                {{ __('Remove from history') }}
            </button>
        </x-slot>
    </x-workspace.confirmation-dialog>
</div>
