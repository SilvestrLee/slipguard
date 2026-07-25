<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * Maps a RiskBand to its DESIGN_TOKENS.md colour-token suffix. Kept here
     * (presentation layer), not on the domain enum itself — App\Domain\Risk\*
     * stays framework/UI-free per ADR-007.
     */
    public function riskBandToken(RiskBand $band): string
    {
        return match ($band) {
            RiskBand::Low => 'low',
            RiskBand::Moderate => 'moderate',
            RiskBand::High => 'high',
            RiskBand::VeryHigh => 'very-high',
        };
    }

    public function with(): array
    {
        $user = Auth::user();

        $recentAnalyses = $user->slipAnalyses()
            ->with('bettingSlip:id,name')
            ->withCount('legAnalyses')
            ->latest()
            ->limit(5)
            ->get();

        $totalAnalyses = $user->slipAnalyses()->count();

        return [
            'hasAnySlips' => $user->bettingSlips()->exists(),
            'recentAnalyses' => $recentAnalyses,
            'totalAnalyses' => $totalAnalyses,
            'lastAnalysisAt' => $recentAnalyses->first()?->created_at,
            'completedThisWeek' => $user->slipAnalyses()->where('created_at', '>=', now()->startOfWeek())->count(),
            'continueSlip' => $user->bettingSlips()
                ->whereIn('status', [BettingSlipStatus::Draft, BettingSlipStatus::Ready])
                ->latest('updated_at')
                ->first(),
        ];
    }
}; ?>

<div class="py-10 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        {{-- Hero: the page's single h1, one primary action, one secondary action --}}
        <section aria-labelledby="dashboard-heading">
            <h1 id="dashboard-heading" class="text-2xl sm:text-3xl font-semibold text-neutral-900">
                {{ __('Welcome back, :name.', ['name' => explode(' ', Auth::user()->name)[0]]) }}
            </h1>
            <p class="mt-2 text-base text-neutral-600 max-w-2xl">
                {{ __("SlipGuard looks at the structure of a betting slip and explains where its unnecessary risk comes from — it doesn't predict who wins.") }}
            </p>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('analyze.create') }}" wire:navigate
                   class="inline-flex items-center justify-center h-12 px-6 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-colors duration-instant">
                    {{ __('Analyze a slip') }}
                </a>

                @if ($continueSlip)
                    <a href="{{ route('analyze.edit', $continueSlip) }}" wire:navigate
                       class="inline-flex items-center justify-center h-12 px-6 rounded-md border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-colors duration-instant">
                        {{ __('Continue previous slip') }}
                    </a>
                @endif
            </div>
        </section>

        {{-- Recent Analyses --}}
        <section aria-labelledby="recent-analyses-heading">
            <h2 id="recent-analyses-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                {{ __('Recent Analyses') }}
            </h2>

            @if ($recentAnalyses->isNotEmpty())
                <ul class="mt-3 space-y-4">
                    @foreach ($recentAnalyses as $analysis)
                        <li wire:key="analysis-{{ $analysis->id }}"
                            class="bg-neutral-50 border border-neutral-200 rounded-lg p-6 sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 truncate">
                                        {{ $analysis->bettingSlip->name ?: __('Untitled slip') }}
                                    </p>
                                    <p class="mt-1 text-sm text-neutral-500">
                                        {{ trans_choice(':count selection|:count selections', $analysis->leg_analyses_count, ['count' => $analysis->leg_analyses_count]) }}
                                        &middot;
                                        <time datetime="{{ $analysis->created_at->toIso8601String() }}">
                                            {{ $analysis->created_at->diffForHumans() }}
                                        </time>
                                    </p>
                                </div>

                                @if ($analysis->availability === AnalysisAvailability::Full || $analysis->availability === AnalysisAvailability::Limited)
                                    @php
                                        $token = $this->riskBandToken($analysis->risk_band);
                                        $textToken = in_array($token, ['low', 'moderate', 'high'], true) ? "{$token}-strong" : $token;
                                    @endphp
                                    <span class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-lg border text-risk-{{ $textToken }} bg-risk-{{ $token }}/10 border-risk-{{ $token }}/30">
                                        <x-heroicon-o-exclamation-triangle class="size-4" aria-hidden="true" />
                                        {{ $analysis->risk_band->label() }}
                                        @if ($analysis->availability === AnalysisAvailability::Limited)
                                            <span class="sr-only">({{ __('Limited analysis') }})</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="shrink-0 text-sm font-medium px-3 py-1.5 rounded-lg border border-neutral-300 text-neutral-500">
                                        {{ __('Unavailable') }}
                                    </span>
                                @endif
                            </div>

                            @if ($analysis->availability === AnalysisAvailability::Limited)
                                <p class="mt-3 text-xs text-neutral-500">{{ __('Limited analysis — some selections could not be fully evaluated.') }}</p>
                            @elseif ($analysis->availability === AnalysisAvailability::Unavailable)
                                <p class="mt-3 text-xs text-neutral-500">{{ __("This slip couldn't be analyzed — one or more selections fell outside what SlipGuard currently supports.") }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @elseif (! $hasAnySlips)
                <div class="mt-3 bg-neutral-50 border border-dashed border-neutral-300 rounded-lg p-10 text-center">
                    <x-heroicon-o-document-text class="mx-auto size-8 text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-medium text-neutral-700">{{ __('No slips yet.') }}</p>
                    <p class="mt-1 text-sm text-neutral-500 max-w-sm mx-auto">
                        {{ __("Add the legs from a slip you're considering, and SlipGuard will show you its structural risk before you place it.") }}
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('analyze.create') }}" wire:navigate
                           class="inline-flex items-center h-10 px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent transition-colors duration-instant">
                            {{ __('Analyze a slip') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="mt-3 bg-neutral-50 border border-dashed border-neutral-300 rounded-lg p-10 text-center">
                    <x-heroicon-o-document-text class="mx-auto size-8 text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-medium text-neutral-700">{{ __('No analyses yet.') }}</p>
                    <p class="mt-1 text-sm text-neutral-500 max-w-sm mx-auto">
                        {{ __('Mark a slip as ready to see its structural risk report here.') }}
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('analyze') }}" wire:navigate
                           class="inline-flex items-center h-10 px-4 rounded-md border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-100 transition-colors duration-instant">
                            {{ __('View your slips') }}
                        </a>
                    </div>
                </div>
            @endif
        </section>

        {{-- Progress: kept deliberately minimal — literal counts only, no invented metrics --}}
        @if ($totalAnalyses > 0)
            <section aria-labelledby="progress-heading">
                <h2 id="progress-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                    {{ __('Progress') }}
                </h2>
                <div class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm text-neutral-500">{{ __('Total analyses') }}</dt>
                            <dd class="mt-1 text-2xl font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">{{ $totalAnalyses }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-neutral-500">{{ __('Completed this week') }}</dt>
                            <dd class="mt-1 text-2xl font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">{{ $completedThisWeek }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-neutral-500">{{ __('Last analysis') }}</dt>
                            <dd class="mt-1 text-2xl font-semibold text-neutral-900">{{ $lastAnalysisAt?->diffForHumans() ?? __('—') }}</dd>
                        </div>
                    </dl>
                </div>
            </section>
        @endif

        {{-- Journal preview: the Journal feature itself isn't built yet (E-06), so this is
             always the "No Journal Entries" empty state, not a live preview. --}}
        <section aria-labelledby="journal-heading">
            <h2 id="journal-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                {{ __('Journal') }}
            </h2>
            <div class="mt-3 bg-neutral-50 border border-dashed border-neutral-300 rounded-lg p-8 text-center">
                <p class="text-sm font-medium text-neutral-700">{{ __('Your journal is empty.') }}</p>
                <p class="mt-1 text-sm text-neutral-500 max-w-sm mx-auto">
                    {{ __('After you analyze a slip, record what you decided and what you learned — over time this helps you spot your own patterns.') }}
                </p>
            </div>
        </section>

        {{-- Trust: approved language only (docs/05-ux/TRUST_SIGNALS.md) --}}
        <section aria-labelledby="trust-heading" class="border-t border-neutral-200 pt-8">
            <h2 id="trust-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                {{ __('How SlipGuard decides') }}
            </h2>
            <ul class="mt-3 space-y-2 text-sm text-neutral-600">
                <li class="flex gap-2">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400" aria-hidden="true" />
                    <span>{{ __('Deterministic analysis — the same slip always produces the same result under the same rule set.') }}</span>
                </li>
                <li class="flex gap-2">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400" aria-hidden="true" />
                    <span>{{ __('No outcome prediction — SlipGuard evaluates structural risk, not who wins.') }}</span>
                </li>
                <li class="flex gap-2">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400" aria-hidden="true" />
                    <span>{{ __('Every result explains itself — what was found, and why it matters.') }}</span>
                </li>
            </ul>
        </section>

    </div>
</div>
