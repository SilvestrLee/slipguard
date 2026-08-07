<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\SlipAnalysis;
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

    /**
     * A Planner regeneration event's risk band is nullable (no evaluation has run yet);
     * riskBandToken() is the single authoritative mapping otherwise — never duplicated here.
     */
    public function plannerRiskBandToken(?RiskBand $band): string
    {
        return $band === null ? 'moderate' : $this->riskBandToken($band);
    }

    /** Display label for a market family — FootballMarketTaxonomyV1's own vocabulary, never a second taxonomy. */
    public function marketFamilyLabel(MarketFamily $family): string
    {
        return match ($family) {
            MarketFamily::MatchResult => 'Match Result',
            MarketFamily::DoubleChance => 'Double Chance',
            MarketFamily::DrawNoBet => 'Draw No Bet',
            MarketFamily::TotalGoals => 'Total Goals',
            MarketFamily::BothTeamsToScore => 'Both Teams to Score',
            MarketFamily::TeamTotalGoals => 'Team Total Goals',
            MarketFamily::CorrectScore => 'Correct Score',
            MarketFamily::HalfTimeResult => 'Half-Time Result',
            MarketFamily::HalfTimeFullTime => 'Half-Time/Full-Time',
        };
    }

    /**
     * Dashboard Planning Exercise (`PO-PD011-001`/`PO-U02-DASH-002`): a real,
     * honest substitute for the fixture-name-based recognition the redesign
     * direction asked for — SlipGuard has no fixture/team data (Capability A
     * never captured it), so this composes only facts already computed by
     * the Risk Engine (leg count, recognized market families) rather than
     * inventing a competition or fixture name.
     */
    public function marketFamilyDescriptor(SlipAnalysis $analysis): string
    {
        $count = $analysis->leg_analyses_count;

        $selectionsText = trans_choice(':count selection|:count selections', $count, ['count' => $count]);

        $families = $analysis->legAnalyses
            ->pluck('market_family')
            ->filter()
            ->unique()
            ->map(fn (MarketFamily $family) => $this->marketFamilyLabel($family))
            ->values();

        return $families->isEmpty()
            ? $selectionsText
            : $selectionsText.' · '.$families->implode(', ');
    }

    public function with(): array
    {
        $user = Auth::user();

        $recentAnalyses = $user->slipAnalyses()
            ->with(['bettingSlip.legs', 'legAnalyses:id,slip_analysis_id,market_family'])
            ->withCount('legAnalyses')
            ->latest()
            ->limit(5)
            ->get();

        $totalAnalyses = $user->slipAnalyses()->count();

        // Dashboard Planning Exercise: never offers to "continue" a slip
        // that's actually locked by an open Planner session (mirrors
        // BettingSlip::isLockedByPlanner()'s own exclusion list) — Continue
        // Working must never link to a dead end.
        $continueSlip = $user->bettingSlips()
            ->whereIn('status', [BettingSlipStatus::Draft, BettingSlipStatus::Ready])
            ->whereDoesntHave('plannerSessions', fn ($query) => $query->whereNotIn('status', [
                PlannerSessionStatus::Exported->value,
                PlannerSessionStatus::Abandoned->value,
            ]))
            ->latest('updated_at')
            ->first();

        $continuingPlannerSession = $user->plannerSessions()
            ->whereIn('status', [PlannerSessionStatus::Draft->value, PlannerSessionStatus::Evaluated->value])
            ->with('sourceBettingSlip.legs')
            ->latest('updated_at')
            ->first();

        // Continue Working shows the single most recently touched piece of
        // unfinished work — whichever of the two is newer, never both.
        $continueWorkingSlip = null;
        $continueWorkingPlannerSession = null;

        if ($continueSlip && $continuingPlannerSession) {
            if ($continueSlip->updated_at->greaterThan($continuingPlannerSession->updated_at)) {
                $continueWorkingSlip = $continueSlip;
            } else {
                $continueWorkingPlannerSession = $continuingPlannerSession;
            }
        } elseif ($continueSlip) {
            $continueWorkingSlip = $continueSlip;
        } elseif ($continuingPlannerSession) {
            $continueWorkingPlannerSession = $continuingPlannerSession;
        }

        // Needs Your Attention: a real "decision made, not yet acted on"
        // signal — a Complete Planner session (candidates finalized) that
        // hasn't been exported into a slip yet. Nothing else on the
        // dashboard currently produces a genuine action-awaiting-you state.
        $attentionPlannerSession = $user->plannerSessions()
            ->where('status', PlannerSessionStatus::Complete->value)
            ->with(['sourceBettingSlip.legs', 'latestRegenerationEvent'])
            ->latest('updated_at')
            ->first();

        return [
            'hasAnySlips' => $user->bettingSlips()->exists(),
            'recentAnalyses' => $recentAnalyses,
            'totalAnalyses' => $totalAnalyses,
            'lastAnalysisAt' => $recentAnalyses->first()?->created_at,
            'openSlipsCount' => $user->bettingSlips()
                ->whereIn('status', [BettingSlipStatus::Draft, BettingSlipStatus::Ready])
                ->count(),
            'planningSessionsCount' => $user->plannerSessions()->count(),
            'journalEntriesCount' => $user->journalEntries()->count(),
            'continueWorkingSlip' => $continueWorkingSlip,
            'continueWorkingPlannerSession' => $continueWorkingPlannerSession,
            'attentionPlannerSession' => $attentionPlannerSession,
            // U-11.2 Phase 2: previously hardcoded to the empty state regardless
            // of real data (a stale assumption from before Journal existed, U-02).
            'latestJournalEntry' => $user->journalEntries()->with('slipAnalysis.bettingSlip.legs')->latest()->first(),
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="mx-auto max-w-[88rem] workspace-gutter workspace-stack">

        <section aria-labelledby="dashboard-heading" class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.75fr)]">
            <div class="workspace-primary-card relative overflow-hidden rounded-2xl p-6 sm:p-8">
                <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-hero opacity-80 sm:block" aria-hidden="true"></div>
                <div class="relative max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent-strong">{{ __('Your risk workspace') }}</p>
                    <h1 id="dashboard-heading" class="mt-3 text-2xl font-semibold tracking-tight text-neutral-900 sm:text-3xl">
                        {{ __('Welcome back, :name.', ['name' => explode(' ', Auth::user()->name)[0]]) }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-base leading-7 text-neutral-600">
                        {{ __("Understand how a slip is structured, where exposure concentrates, and what deserves another look. SlipGuard evaluates risk—it doesn't predict who wins.") }}
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('analyze.intake', ['method' => 'screenshot']) }}" wire:navigate
                           class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-accent-strong px-5 text-sm font-semibold text-white transition-colors hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                            <x-heroicon-o-photo class="size-5" aria-hidden="true" />
                            {{ __('Upload screenshot') }}
                        </a>
                        <a href="{{ route('analyze.intake', ['method' => 'pdf']) }}" wire:navigate
                           class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-surface-card px-5 text-sm font-semibold text-neutral-800 transition-colors hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                            <x-heroicon-o-document-arrow-up class="size-5" aria-hidden="true" />
                            {{ __('Upload PDF') }}
                        </a>
                    </div>
                    <a href="{{ route('analyze.intake', ['method' => 'betcode']) }}" wire:navigate
                       class="mt-3 inline-flex min-h-11 w-full items-center justify-between gap-3 rounded-lg border border-dashed border-neutral-300 bg-surface-soft px-4 text-left text-sm transition-colors hover:border-neutral-400 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent sm:w-auto">
                        <span class="inline-flex items-center gap-2 font-semibold text-neutral-700">
                            <x-heroicon-o-link class="size-4 text-neutral-500" aria-hidden="true" />
                            {{ __('Bet Code or Share Link') }}
                        </span>
                        <span class="rounded-full border border-neutral-300 bg-surface-card px-2 py-0.5 text-[0.62rem] font-semibold uppercase tracking-wide text-neutral-500">
                            {{ __('Coming soon') }}
                        </span>
                    </a>
                    <p class="mt-3 text-xs text-neutral-500">
                        <a href="{{ route('analyze.intake') }}" wire:navigate class="font-semibold text-accent-strong hover:underline">
                            {{ __('See all intake options') }}
                        </a>
                        <span aria-hidden="true"> · </span>
                        {{ __('Manual entry remains available as a fallback.') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('builder') }}" wire:navigate
               class="group flex min-h-full flex-col justify-between rounded-2xl border border-white/15 bg-[image:var(--gradient-dashboard-feature)] p-6 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-200">{{ __('Accumulator workspace') }}</span>
                        @unless (config('slipguard-market-intelligence.enabled'))
                            <span class="rounded-full border border-white/20 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-slate-200">{{ __('Preview') }}</span>
                        @endunless
                    </div>
                    <h2 class="mt-5 text-xl font-semibold">{{ __('Build around risk conditions.') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-300">
                        {{ __('Set competitions, markets and structural-risk constraints, then review whether the available evidence supports a suitable candidate.') }}
                    </p>
                </div>
                <span class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-white">
                    {{ config('slipguard-market-intelligence.enabled') ? __('Open Builder') : __('Explore the workflow') }}
                    <x-heroicon-o-arrow-right class="size-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </span>
            </a>
        </section>

        {{--
            `PO-U23-001` — the conversational entry layer for Capability B
            (§3.1/§4): a third accumulator-building method alongside the
            Guided and Manual Builders above, not a replacement for either.
            Own module, own component — kept out of the two-column hero
            grid above so it reads as a distinct capability, not a variant
            of the existing intake card.
        --}}
        <livewire:accumulator-conversation.composer />

        <section aria-label="{{ __('Account activity summary') }}">
            <dl class="dashboard-metric-strip workspace-primary-card grid grid-cols-2 overflow-hidden rounded-xl sm:grid-cols-4">
                @foreach ([
                    [__('Analyses'), $totalAnalyses],
                    [__('Open slips'), $openSlipsCount],
                    [__('Planning sessions'), $planningSessionsCount],
                    [__('Journal entries'), $journalEntriesCount],
                ] as [$label, $value])
                    <div class="workspace-internal-border p-4 even:border-l sm:border-l sm:first:border-l-0">
                        <dt class="text-xs font-medium text-neutral-500">{{ $label }}</dt>
                        <dd class="mt-1 text-2xl font-semibold tabular-nums text-neutral-900">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Continue Working: Dashboard Planning Exercise (PO-PD011-001/PO-U02-DASH-002) Level One — the single highest-priority section. Shown only when real unfinished work exists; never an empty state, since this section is about recognizing genuine in-progress work, not filling space. --}}
        @if ($continueWorkingSlip || $continueWorkingPlannerSession)
            <section aria-labelledby="continue-working-heading" class="workspace-section-panel">
                <h2 id="continue-working-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                    {{ __('Continue Working') }}
                </h2>
                @if ($continueWorkingSlip)
                    <x-card variant="interactive" :href="route('analyze.edit', $continueWorkingSlip)" wire:navigate class="mt-3 block">
                        <p class="text-sm font-medium text-neutral-900 truncate">
                            {{ $continueWorkingSlip->displayLabel() }}
                        </p>
                        <p class="mt-0.5 text-xs text-neutral-500">
                            {{ __('Last edited :time', ['time' => $continueWorkingSlip->updated_at->diffForHumans()]) }}
                        </p>
                    </x-card>
                @else
                    <x-card variant="interactive" :href="route('planner.session', $continueWorkingPlannerSession)" wire:navigate class="mt-3 block">
                        <p class="text-sm font-medium text-neutral-900 truncate">
                            {{ $continueWorkingPlannerSession->sourceBettingSlip->displayLabel() }}
                        </p>
                        <p class="mt-0.5 text-xs text-neutral-500">
                            {{ __('Planner · :status', ['status' => $continueWorkingPlannerSession->status->label()]) }}
                        </p>
                    </x-card>
                @endif
            </section>
        @endif

        {{-- Needs Your Attention: Level Two — the one real "decision made, not yet acted on" signal the current data model supports. Absent entirely otherwise; never invented to fill space. --}}
        @if ($attentionPlannerSession)
            @php
                $attentionToken = $this->plannerRiskBandToken($attentionPlannerSession->latestRegenerationEvent?->risk_band);
            @endphp
            <section aria-labelledby="attention-heading" class="workspace-section-panel">
                <h2 id="attention-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                    {{ __('Needs Your Attention') }}
                </h2>
                <x-card variant="interactive" :href="route('planner.session', $attentionPlannerSession)" wire:navigate class="mt-3 block">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-neutral-900 truncate">
                                {{ $attentionPlannerSession->sourceBettingSlip->displayLabel() }}
                            </p>
                            <p class="mt-0.5 text-xs text-neutral-500">
                                {{ __('Planner selections finalized — ready to review and export.') }}
                            </p>
                        </div>
                        @if ($attentionPlannerSession->latestRegenerationEvent?->risk_band)
                            <span class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-lg border text-risk-{{ $attentionToken }} bg-risk-{{ $attentionToken }}/10 border-risk-{{ $attentionToken }}/30">
                                <x-heroicon-o-exclamation-triangle class="size-4" aria-hidden="true" />
                                {{ $attentionPlannerSession->latestRegenerationEvent->risk_band->label() }}
                            </span>
                        @endif
                    </div>
                </x-card>
            </section>
        @endif

        {{-- Recent Activity --}}
        <section aria-labelledby="recent-analyses-heading" class="workspace-section-panel">
            <h2 id="recent-analyses-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                {{ __('Recent Activity') }}
            </h2>

            @if ($recentAnalyses->isNotEmpty())
                <ul class="mt-3 space-y-4">
                    @foreach ($recentAnalyses as $analysis)
                        <li wire:key="analysis-{{ $analysis->id }}">
                        <x-card variant="interactive" :href="route('analyze.report', $analysis->bettingSlip)" wire:navigate class="block">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 truncate">
                                        {{ $analysis->bettingSlip->displayLabel() }}
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
                            <p class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-accent-strong">
                                {{ __('Open risk report') }}
                                <x-heroicon-o-arrow-right class="size-3.5" aria-hidden="true" />
                            </p>
                        </x-card>
                        </li>
                    @endforeach
                </ul>
            @elseif (! $hasAnySlips)
                <x-empty-state icon="heroicon-o-document-text" :title="__('No slips yet.')"
                    :description="__('Add the legs from a slip you\'re considering, and SlipGuard will show you its structural risk before you place it.')"
                    class="mt-3">
                    <x-slot name="action">
                        <a href="{{ route('analyze.intake') }}" wire:navigate
                           class="inline-flex items-center h-10 px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent transition-colors duration-instant">
                            {{ __('Analyze a slip') }}
                        </a>
                    </x-slot>
                </x-empty-state>
            @else
                <x-empty-state icon="heroicon-o-document-text" :title="__('No analyses yet.')"
                    :description="__('Mark a slip as ready to see its structural risk report here.')"
                    class="mt-3">
                    <x-slot name="action">
                        <a href="{{ route('analyze') }}" wire:navigate
                           class="inline-flex items-center h-10 px-4 rounded-md border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-100 transition-colors duration-instant">
                            {{ __('View your slips') }}
                        </a>
                    </x-slot>
                </x-empty-state>
            @endif
        </section>

        {{-- Progress: kept deliberately minimal — literal counts only, no invented metrics. "Completed this week" removed (Dashboard Planning Exercise): it didn't answer any decision question on its own, per PO-U02-DASH-002's own Metrics Philosophy test. --}}
        @if ($totalAnalyses > 0)
            <section aria-labelledby="progress-heading" class="workspace-section-panel">
                <h2 id="progress-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                    {{ __('Progress') }}
                </h2>
                <x-card class="mt-3">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm text-neutral-500">{{ __('Total analyses') }}</dt>
                            <dd class="mt-1 text-2xl font-semibold text-neutral-900 font-tabular">{{ $totalAnalyses }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-neutral-500">{{ __('Last analysis') }}</dt>
                            <dd class="mt-1 text-2xl font-semibold text-neutral-900">{{ $lastAnalysisAt?->diffForHumans() ?? __('—') }}</dd>
                        </div>
                    </dl>
                </x-card>
            </section>
        @endif

        {{-- U-11.2 Phase 2: Journal preview now reads real data — previously hardcoded to the empty state regardless of what the customer had actually recorded. --}}
        <section aria-labelledby="journal-heading" class="workspace-section-panel">
            <h2 id="journal-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                {{ __('Journal') }}
            </h2>
            @if ($latestJournalEntry)
                <x-card variant="interactive" :href="route('journal')" wire:navigate class="mt-3 block">
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">
                        @if ($latestJournalEntry->analysisLinkState() === 'linked')
                            {{ $latestJournalEntry->slipAnalysis->bettingSlip->displayLabel() }}
                        @elseif ($latestJournalEntry->analysisLinkState() === 'unavailable')
                            {{ __('Analysis unavailable') }}
                        @else
                            {{ __('Independent reflection') }}
                        @endif
                    </p>
                    <p class="mt-2 text-sm font-semibold text-neutral-900">{{ $latestJournalEntry->displayTitle() }}</p>
                    <p class="mt-2 text-sm text-neutral-800">{{ Str::limit($latestJournalEntry->reflection, 140) }}</p>
                    <p class="mt-3 text-xs text-neutral-400">
                        {{ __('Written :time', ['time' => $latestJournalEntry->created_at->diffForHumans()]) }}
                    </p>
                </x-card>
            @else
                <x-empty-state :title="__('Your journal is empty.')"
                    :description="__('After you analyze a slip, record what you decided and what you learned — over time this helps you spot your own patterns.')"
                    class="mt-3" />
            @endif
        </section>

        {{-- Trust: approved language only (docs/05-ux/TRUST_SIGNALS.md) --}}
        <section aria-labelledby="trust-heading" class="workspace-section-panel">
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
