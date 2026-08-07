<?php

use App\Actions\MarketIntelligence\BuildAccumulatorCandidate;
use App\Actions\MarketIntelligence\CreateCandidateBettingSlip;
use App\Domain\MarketIntelligence\Construction\CandidateRejectionReason;
use App\Domain\MarketIntelligence\Construction\CandidateSlot;
use App\Domain\MarketIntelligence\Construction\ChosenSlot;
use App\Domain\MarketIntelligence\Construction\ConstructedCandidate;
use App\Domain\MarketIntelligence\Construction\DetermineLegEligibility;
use App\Domain\MarketIntelligence\Construction\EvaluateCandidateSelections;
use App\Domain\MarketIntelligence\Construction\NoValidCandidate;
use App\Domain\MarketIntelligence\Construction\PlanningBrief;
use App\Domain\MarketIntelligence\Construction\ReplacementNeeded;
use App\Domain\MarketIntelligence\EvidenceProviderException;
use App\Domain\MarketIntelligence\EvidenceProviderFailure;
use App\Domain\Risk\Results\RiskBand;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Models\MarketIntelligenceFixture;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * U-17 Increment Two — the customer-facing Builder, wired to Increment
 * One's already-tested construction engine. One page, not a stepped wizard
 * (U-17.7 §2). Every domain object (CandidateSlot, ConstructedCandidate,
 * etc.) is ephemeral within a single request — never stored as a Livewire
 * property, since these are plain readonly classes, not Wireable, and
 * making them so would tie ADR-007's framework-agnostic domain layer to
 * Livewire. Only plain scalars/arrays persist between requests; any full
 * object is freshly, deterministically re-derived from them on demand via
 * Increment One's own existing, already-tested public methods.
 */
new #[Layout('layouts.app')] class extends Component
{
    // Planning brief fields (U-17.7 §2)
    public array $competitions = ['soccer_epl', 'soccer_spain_la_liga', 'soccer_italy_serie_a'];

    public int $windowDays = 7;

    public int $legCount = 5;

    public array $markets = ['h2h', 'totals', 'double_chance', 'draw_no_bet', 'btts'];

    public bool $includeHalfTimeResult = false;

    public bool $targetOddsEnabled = false;

    public ?string $targetOddsMin = null;

    public ?string $targetOddsMax = null;

    public string $riskCeiling = 'high';

    public ?string $briefError = null;

    public ?string $evidenceFailureType = null;

    // Discovery state
    public bool $discovered = false;

    /** @var array<int, string> */
    public array $rankedFixtureKeys = [];

    /** @var array<string, string> fixtureKey => MarketFamily value */
    public array $marketFamilyForFixture = [];

    /** @var array<int, string> */
    public array $activeFixtureKeys = [];

    public int $nextSpareIndex = 0;

    /** @var array<string, string> fixtureKey => chosen selectionDescription */
    public array $chosenOutcomeName = [];

    public ?string $noOpportunitiesReason = null;

    // Evaluation state
    public ?string $evaluationOutcome = null;

    /** @var array<int, array{description: string, odds: string, structural_contribution: ?int, gate_dependent: bool}> */
    public array $constructedLegsSummary = [];

    public ?string $combinedOdds = null;

    public ?string $riskBandLabel = null;

    public ?string $riskBandToken = null;

    public ?int $structuralScore = null;

    public ?string $rejectionReason = null;

    public ?string $replacementRemovedFixtureKey = null;

    public ?string $replacementNewFixtureKey = null;

    public ?string $replacementReasonLabel = null;

    public int $riskAttemptsUsed = 0;

    public int $oddsAttemptsUsed = 0;

    public bool $hasRegenerated = false;

    public ?string $conversationalSummary = null;

    /**
     * `PO-U23-001` §15/§16 — the one piece of orchestration glue for the
     * conversational-builder hand-off: applies a session-flashed brief
     * seed (set by `accumulator-conversation.composer`, never trusted
     * beyond what this component's own existing `planningRules()`
     * validates a moment later) onto the same public properties the
     * manual planning-brief form already binds to, then runs the
     * completely unmodified `findCandidate()` so the customer lands
     * directly on discovered candidates instead of re-seeing the form.
     * A normal page visit with nothing flashed behaves exactly as before
     * this method existed.
     */
    public function mount(): void
    {
        $seed = session('conversational_planning_brief');

        if (! is_array($seed)) {
            return;
        }

        $this->conversationalSummary = session('conversational_summary');
        $this->competitions = $seed['competitions'] ?? $this->competitions;
        $this->windowDays = $seed['windowDays'] ?? $this->windowDays;
        $this->legCount = $seed['legCount'] ?? $this->legCount;
        $this->markets = $seed['markets'] ?? $this->markets;
        $this->riskCeiling = $seed['riskCeiling'] ?? $this->riskCeiling;

        $this->findCandidate();
    }

    public function capabilityEnabled(): bool
    {
        return (bool) config('slipguard-market-intelligence.enabled');
    }

    public function marketFamilyLabel(MarketFamily $family): string
    {
        return (new FootballMarketTaxonomyV1)->findByFamily($family)->displayName;
    }

    public function riskBandOptions(): array
    {
        return [
            RiskBand::Low->value => RiskBand::Low->label(),
            RiskBand::Moderate->value => RiskBand::Moderate->label(),
            RiskBand::High->value => RiskBand::High->label(),
            RiskBand::VeryHigh->value => RiskBand::VeryHigh->label(),
        ];
    }

    public function riskBandDescriptions(): array
    {
        return [
            RiskBand::Low->value => __('Keeps the candidate within the lowest structural-risk band.'),
            RiskBand::Moderate->value => __('Allows some concentration while limiting stronger structural vulnerabilities.'),
            RiskBand::High->value => __('Permits broader structural exposure while retaining a defined ceiling.'),
            RiskBand::VeryHigh->value => __('Allows every structural-risk band and may expose significant vulnerabilities.'),
        ];
    }

    private function planningRules(): array
    {
        return [
            'competitions' => ['required', 'array', 'min:1'],
            'competitions.*' => ['string', Rule::in(array_keys($this->allowedCompetitions()))],
            'windowDays' => ['required', 'integer', Rule::in([1, 3, 7])],
            'legCount' => ['required', 'integer', 'between:2,8'],
            'markets' => ['array'],
            'markets.*' => ['string', Rule::in(array_keys($this->marketLabels()))],
            'includeHalfTimeResult' => ['boolean'],
            'targetOddsEnabled' => ['boolean'],
            'targetOddsMin' => ['nullable', 'required_if:targetOddsEnabled,true', 'numeric', 'gt:1'],
            'targetOddsMax' => ['nullable', 'required_if:targetOddsEnabled,true', 'numeric', 'gt:targetOddsMin'],
            'riskCeiling' => ['required', Rule::enum(RiskBand::class)],
        ];
    }

    private function planningMessages(): array
    {
        return [
            'competitions.required' => __('Choose at least one supported competition.'),
            'competitions.min' => __('Choose at least one supported competition.'),
            'targetOddsMin.required_if' => __('Enter a minimum decimal-odds value.'),
            'targetOddsMin.numeric' => __('Enter a valid minimum decimal-odds value.'),
            'targetOddsMin.gt' => __('Minimum decimal odds must be greater than 1.00.'),
            'targetOddsMax.required_if' => __('Enter a maximum decimal-odds value.'),
            'targetOddsMax.numeric' => __('Enter a valid maximum decimal-odds value.'),
            'targetOddsMax.gt' => __('Maximum decimal odds must be greater than the minimum.'),
        ];
    }

    public function updatedTargetOddsEnabled(bool $enabled): void
    {
        $this->resetValidation(['targetOddsMin', 'targetOddsMax']);

        if (! $enabled) {
            $this->targetOddsMin = null;
            $this->targetOddsMax = null;
        }
    }

    public function updatedTargetOddsMin(): void
    {
        if (! $this->targetOddsEnabled) {
            return;
        }

        $this->resetValidation(['targetOddsMin', 'targetOddsMax']);

        if ($this->targetOddsMin !== null && $this->targetOddsMin !== '') {
            $this->validateOnly('targetOddsMin', $this->planningRules(), $this->planningMessages());
        }

        if ($this->targetOddsMax !== null && $this->targetOddsMax !== '') {
            $this->validateOnly('targetOddsMax', $this->planningRules(), $this->planningMessages());
        }
    }

    public function updatedTargetOddsMax(): void
    {
        if (! $this->targetOddsEnabled) {
            return;
        }

        $this->resetValidation('targetOddsMax');

        if ($this->targetOddsMax !== null && $this->targetOddsMax !== '') {
            $this->validateOnly('targetOddsMax', $this->planningRules(), $this->planningMessages());
        }
    }

    public function marketOptions(): array
    {
        return [
            'Core markets' => [
                'h2h' => __('Match Result'),
                'double_chance' => __('Double Chance'),
                'draw_no_bet' => __('Draw No Bet'),
            ],
            'Goals markets' => [
                'totals' => __('Total Goals'),
                'btts' => __('Both Teams to Score'),
            ],
        ];
    }

    public function marketLabels(): array
    {
        return collect($this->marketOptions())
            ->collapse()
            ->put('h2h_h1', __('Half-Time Result'))
            ->all();
    }

    public function windowLabel(): string
    {
        return match ($this->windowDays) {
            1 => __('Next 24 hours'),
            3 => __('Next 3 days'),
            7 => __('Next 7 days'),
            default => __('Unsupported window'),
        };
    }

    public function fixturePoolCount(): int
    {
        if ($this->competitions === [] || ! in_array($this->windowDays, [1, 3, 7], true)) {
            return 0;
        }

        return MarketIntelligenceFixture::query()
            ->whereIn('competition_key', $this->competitions)
            ->whereBetween('commence_time', [now(), now()->addDays($this->windowDays)])
            ->where('cache_expires_at', '>', now())
            ->count();
    }

    public function allowedCompetitions(): array
    {
        return config('slipguard-market-intelligence.allowed_competitions');
    }

    public function toggleCompetition(string $competition): void
    {
        if (! array_key_exists($competition, $this->allowedCompetitions())) {
            return;
        }

        $this->competitions = in_array($competition, $this->competitions, true)
            ? array_values(array_diff($this->competitions, [$competition]))
            : [...$this->competitions, $competition];
    }

    public function toggleMarket(string $market): void
    {
        if (! array_key_exists($market, $this->marketLabels()) || $market === 'h2h_h1') {
            return;
        }

        $this->markets = in_array($market, $this->markets, true)
            ? array_values(array_diff($this->markets, [$market]))
            : [...$this->markets, $market];
    }

    /**
     * @return array<int, string>
     */
    private function selectedMarkets(): array
    {
        return $this->includeHalfTimeResult
            ? [...$this->markets, 'h2h_h1']
            : $this->markets;
    }

    /**
     * @param  array<int, string>  $excludedFixtureIds
     */
    private function buildBrief(array $excludedFixtureIds = []): PlanningBrief
    {
        return new PlanningBrief(
            competitions: $this->competitions,
            windowDays: $this->windowDays,
            legCountTarget: $this->legCount,
            requestedMarkets: $this->selectedMarkets(),
            targetOddsMin: $this->targetOddsEnabled ? $this->targetOddsMin : null,
            targetOddsMax: $this->targetOddsEnabled ? $this->targetOddsMax : null,
            riskCeiling: RiskBand::from($this->riskCeiling),
            excludedFixtureIds: $excludedFixtureIds,
        );
    }

    /**
     * Re-derives one fixture's real, current CandidateSlot — the one piece
     * of new orchestration glue this component needs, composed entirely
     * from Increment One's existing DetermineLegEligibility. Returns null
     * if the fixture's evidence no longer supports the market it was
     * discovered against (rare — the 2-hour cache TTL makes this unlikely
     * within one Builder session, but real and handled, not assumed away).
     */
    private function rebuildSlot(string $fixtureKey, PlanningBrief $brief): ?CandidateSlot
    {
        $fixture = MarketIntelligenceFixture::where('provider_event_id', $fixtureKey)->first();

        if (! $fixture) {
            return null;
        }

        $family = MarketFamily::from($this->marketFamilyForFixture[$fixtureKey]);
        $evaluation = (new DetermineLegEligibility)->evaluate($brief, $fixture, $family);

        if ($evaluation->options === []) {
            return null;
        }

        $rank = array_search($fixtureKey, $this->rankedFixtureKeys, true);

        return new CandidateSlot($evaluation->options, $rank === false ? null : $rank + 1, 'stored');
    }

    public function findCandidate(): void
    {
        $this->briefError = null;
        $this->evidenceFailureType = null;
        $this->resetErrorBag();

        $this->validate($this->planningRules(), $this->planningMessages());

        if (! $this->capabilityEnabled()) {
            $this->briefError = __('Live candidate evaluation is not available in this workspace yet.');

            return;
        }

        if ($this->selectedMarkets() === []) {
            $this->addError('markets', __('Choose at least one supported market.'));

            return;
        }

        $this->findCandidateExcluding([]);
    }

    /**
     * @param  array<int, string>  $excludedFixtureIds
     */
    private function findCandidateExcluding(array $excludedFixtureIds): void
    {
        $brief = $this->buildBrief($excludedFixtureIds);
        $this->clearCandidateState();

        try {
            $result = (new BuildAccumulatorCandidate)->execute($brief);
        } catch (EvidenceProviderException $exception) {
            $this->evidenceFailureType = $exception->failure === EvidenceProviderFailure::ConfigurationFailure
                ? 'configuration'
                : 'provider_unavailable';

            return;
        }

        $this->evidenceFailureType = null;
        $this->resetEvaluationState();

        if ($result instanceof NoValidCandidate) {
            $this->discovered = false;
            $this->noOpportunitiesReason = $result->reason->label();

            return;
        }

        $this->noOpportunitiesReason = null;
        $this->discovered = true;

        $allSlots = [...$result->proposedSlots, ...$result->spareSlots];
        $this->rankedFixtureKeys = array_map(fn (CandidateSlot $slot) => $slot->fixtureKey(), $allSlots);
        $this->marketFamilyForFixture = [];

        foreach ($allSlots as $slot) {
            $this->marketFamilyForFixture[$slot->fixtureKey()] = $slot->options[0]->marketFamily->value;
        }

        $this->activeFixtureKeys = array_map(fn (CandidateSlot $slot) => $slot->fixtureKey(), $result->proposedSlots);
        $this->nextSpareIndex = count($result->proposedSlots);
        $this->chosenOutcomeName = [];
    }

    public function selectOutcome(string $fixtureKey, string $selectionDescription): void
    {
        $brief = $this->buildBrief();
        $slot = $this->rebuildSlot($fixtureKey, $brief);

        if ($slot === null || ! collect($slot->options)->contains(fn ($leg) => $leg->selectionDescription === $selectionDescription)) {
            $this->evaluationOutcome = 'changed';

            return;
        }

        $this->chosenOutcomeName[$fixtureKey] = $selectionDescription;
    }

    public function evaluateCandidate(): void
    {
        if (count($this->chosenOutcomeName) < count($this->activeFixtureKeys)) {
            return;
        }

        $brief = $this->buildBrief();
        $chosenSlots = [];

        foreach ($this->activeFixtureKeys as $fixtureKey) {
            $slot = $this->rebuildSlot($fixtureKey, $brief);
            $description = $this->chosenOutcomeName[$fixtureKey] ?? null;
            $leg = $slot ? collect($slot->options)->first(fn ($option) => $option->selectionDescription === $description) : null;

            if (! $slot || ! $leg) {
                $this->evaluationOutcome = 'changed';

                return;
            }

            $chosenSlots[] = new ChosenSlot($slot, $leg);
        }

        $spareSlots = [];

        foreach (array_slice($this->rankedFixtureKeys, $this->nextSpareIndex) as $fixtureKey) {
            $slot = $this->rebuildSlot($fixtureKey, $brief);

            if ($slot !== null) {
                $spareSlots[] = $slot;
            }
        }

        $result = (new EvaluateCandidateSelections)->execute($chosenSlots, $spareSlots, $brief, $this->riskAttemptsUsed, $this->oddsAttemptsUsed);

        if ($result instanceof ConstructedCandidate) {
            $this->applyConstructed($result);

            return;
        }

        if ($result instanceof ReplacementNeeded) {
            $this->applyReplacementNeeded($result);

            return;
        }

        $this->rejectionReason = $result->reason->label();
        $this->evaluationOutcome = 'no_valid_candidate';
    }

    private function applyConstructed(ConstructedCandidate $candidate): void
    {
        $attributions = collect($candidate->legAttributionRanking?->attributions ?? [])
            ->keyBy('bettingSlipLegId');

        $this->constructedLegsSummary = array_map(function ($leg, $index) use ($attributions) {
            $attribution = $attributions->get($index);

            return [
                'description' => $leg->selectionDescription,
                'odds' => $leg->decimalOdds,
                'structural_contribution' => $attribution?->msc,
                'gate_dependent' => $attribution?->gateDependent ?? false,
            ];
        }, $candidate->legs, array_keys($candidate->legs));

        $this->combinedOdds = (string) $candidate->combinedOdds->toScale(2, RoundingMode::HalfUp);
        $this->riskBandLabel = $candidate->riskAnalysis->riskBand?->label();
        $this->riskBandToken = $candidate->riskAnalysis->riskBand?->value;
        $this->structuralScore = $candidate->riskAnalysis->structuralScore;
        $this->evaluationOutcome = 'constructed';
    }

    private function applyReplacementNeeded(ReplacementNeeded $result): void
    {
        $position = array_search($result->removedFixtureKey, $this->activeFixtureKeys, true);
        $newFixtureKey = $result->replacementSlot->fixtureKey();

        if ($position !== false) {
            $this->activeFixtureKeys[$position] = $newFixtureKey;
        }

        unset($this->chosenOutcomeName[$result->removedFixtureKey]);

        $this->replacementRemovedFixtureKey = $result->removedFixtureKey;
        $this->replacementNewFixtureKey = $newFixtureKey;
        $this->replacementReasonLabel = $result->reason->label();

        if ($result->reason === CandidateRejectionReason::RiskCeilingUnreachable) {
            $this->riskAttemptsUsed = $result->attemptsUsedSoFar;
        } else {
            $this->oddsAttemptsUsed = $result->attemptsUsedSoFar;
        }

        $this->nextSpareIndex++;
        $this->evaluationOutcome = 'replacement_needed';
    }

    public function tryDifferentCandidate(): void
    {
        if ($this->evaluationOutcome !== 'constructed' || $this->hasRegenerated) {
            return;
        }

        $excluded = $this->activeFixtureKeys;
        $this->hasRegenerated = true;
        $this->findCandidateExcluding($excluded);
    }

    public function acceptCandidate(): void
    {
        $brief = $this->buildBrief();
        $chosenSlots = [];

        foreach ($this->activeFixtureKeys as $fixtureKey) {
            $slot = $this->rebuildSlot($fixtureKey, $brief);
            $description = $this->chosenOutcomeName[$fixtureKey] ?? null;
            $leg = $slot ? collect($slot->options)->first(fn ($option) => $option->selectionDescription === $description) : null;

            if (! $slot || ! $leg) {
                $this->evaluationOutcome = 'changed';

                return;
            }

            $chosenSlots[] = new ChosenSlot($slot, $leg);
        }

        $spareSlots = array_values(array_filter(array_map(
            fn (string $key) => $this->rebuildSlot($key, $brief),
            array_slice($this->rankedFixtureKeys, $this->nextSpareIndex),
        )));

        $result = (new EvaluateCandidateSelections)->execute($chosenSlots, $spareSlots, $brief, $this->riskAttemptsUsed, $this->oddsAttemptsUsed);

        if (! $result instanceof ConstructedCandidate) {
            $this->evaluationOutcome = 'changed';

            return;
        }

        $session = (new CreateCandidateBettingSlip)->execute(Auth::user(), $result);

        $this->redirect(route('planner.session', $session), navigate: true);
    }

    public function editBrief(): void
    {
        $this->evidenceFailureType = null;
        $this->discovered = false;
        $this->noOpportunitiesReason = null;
        $this->resetEvaluationState();
    }

    private function clearCandidateState(): void
    {
        $this->discovered = false;
        $this->rankedFixtureKeys = [];
        $this->marketFamilyForFixture = [];
        $this->activeFixtureKeys = [];
        $this->nextSpareIndex = 0;
        $this->chosenOutcomeName = [];
        $this->noOpportunitiesReason = null;
        $this->resetEvaluationState();
    }

    private function resetEvaluationState(): void
    {
        $this->evaluationOutcome = null;
        $this->constructedLegsSummary = [];
        $this->combinedOdds = null;
        $this->riskBandLabel = null;
        $this->riskBandToken = null;
        $this->structuralScore = null;
        $this->rejectionReason = null;
        $this->replacementRemovedFixtureKey = null;
        $this->replacementNewFixtureKey = null;
        $this->replacementReasonLabel = null;
    }

    public function with(): array
    {
        $activeSlots = [];

        if ($this->discovered && $this->evaluationOutcome === null) {
            $brief = $this->buildBrief();

            foreach ($this->activeFixtureKeys as $fixtureKey) {
                $activeSlots[$fixtureKey] = $this->rebuildSlot($fixtureKey, $brief);
            }
        }

        $replacementSlot = null;

        if ($this->evaluationOutcome === 'replacement_needed' && $this->replacementNewFixtureKey) {
            $replacementSlot = $this->rebuildSlot($this->replacementNewFixtureKey, $this->buildBrief());
        }

        return [
            'activeSlots' => $activeSlots,
            'replacementSlot' => $replacementSlot,
            'selectionsMade' => count($this->chosenOutcomeName),
            'selectionsTotal' => count($this->activeFixtureKeys),
            'recentSessions' => Auth::user()->plannerSessions()
                ->with(['sourceBettingSlip.legs', 'latestRegenerationEvent'])
                ->latest('updated_at')
                ->limit(3)
                ->get(),
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">

        <x-page-header :title="__('Build an Accumulator')"
            :description="__('Build accumulator ideas from supported evidence for your review — never prediction.')" />

        @if (! $this->capabilityEnabled())
            <section class="overflow-hidden rounded-xl border border-neutral-200 bg-surface-card" aria-labelledby="builder-preview-heading">
                <div class="border-b border-neutral-200 bg-accent/5 px-5 py-5 sm:px-7">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="max-w-2xl">
                            <span class="inline-flex rounded-full border border-accent/30 bg-accent/10 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-accent-strong">
                                {{ __('Experience preview') }}
                            </span>
                            <h2 id="builder-preview-heading" class="mt-3 text-xl font-semibold text-neutral-900">
                                {{ __('Plan around structural exposure, not predicted winners.') }}
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-neutral-600">
                                {{ __('The Accumulator Builder evaluates available evidence against your competition, market, leg-count and risk constraints. It can return no candidate when those conditions cannot be met responsibly.') }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-lg border border-neutral-300 bg-surface-card px-3 py-2 text-xs font-semibold text-neutral-600">
                            {{ __('Live evaluation unavailable') }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="p-5 sm:p-7">
                        <h3 class="text-sm font-semibold text-neutral-900">{{ __('How the workflow is designed') }}</h3>
                        <ol class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                [__('Set planning conditions'), __('Choose a time window, competitions, supported markets and a maximum structural risk band.')],
                                [__('Review available evidence'), __('SlipGuard checks eligible fixtures and market evidence without forecasting an outcome.')],
                                [__('Assess compatibility'), __('Selections are tested together against leg, odds and structural-risk constraints.')],
                                [__('Decide what to do next'), __('Review a candidate, revise constraints, or accept that no suitable candidate is currently available.')],
                            ] as [$title, $description])
                                <li class="flex gap-3">
                                    <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-accent/10 text-xs font-bold text-accent-strong">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold text-neutral-900">{{ $title }}</span>
                                        <span class="mt-1 block text-sm leading-5 text-neutral-500">{{ $description }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>

                        <section class="mt-7 rounded-lg border workspace-internal-border bg-surface-section p-5" aria-labelledby="illustrative-candidate-heading">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <span class="inline-flex rounded-full border border-accent/30 bg-accent/10 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-accent-strong">
                                        {{ __('Illustrative preview · not a live candidate') }}
                                    </span>
                                    <h3 id="illustrative-candidate-heading" class="mt-3 text-base font-semibold text-neutral-900">
                                        {{ __('Candidate review destination') }}
                                    </h3>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-xs text-neutral-500">{{ __('Example combined decimal odds') }}</p>
                                    <p class="mt-1 text-lg font-semibold font-tabular text-neutral-900">5.42</p>
                                </div>
                            </div>

                            <ul class="mt-5 divide-y workspace-internal-border">
                                @foreach ([
                                    [__('Northbridge FC vs Riverside Athletic'), __('Double Chance · Home or Draw'), '1.42', __('Lower example contribution')],
                                    [__('Harbour United vs City Rovers'), __('Total Goals · Over 1.5'), '1.68', __('Moderate example contribution')],
                                    [__('Meadow Park vs Borough FC'), __('Both Teams to Score · Yes'), '2.27', __('Moderate example contribution')],
                                ] as [$fixture, $market, $odds, $contribution])
                                    <li class="grid gap-2 py-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                                        <div>
                                            <p class="text-sm font-semibold text-neutral-900">{{ $fixture }}</p>
                                            <p class="mt-1 text-xs text-neutral-500">{{ $market }} · {{ $contribution }}</p>
                                        </div>
                                        <span class="font-tabular text-sm font-semibold text-neutral-700">{{ $odds }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <x-workspace.risk-badge band="Moderate example" tone="moderate" />
                                <x-badge tone="quality-strong">
                                    <x-heroicon-o-check-circle class="size-3.5" aria-hidden="true" />
                                    {{ __('Example constraints satisfied') }}
                                </x-badge>
                            </div>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <button type="button" disabled class="min-h-11 rounded-md border border-neutral-300 px-4 text-sm font-semibold text-neutral-500 opacity-70">
                                    {{ __('Replace selection · preview') }}
                                </button>
                                <button type="button" disabled class="min-h-11 rounded-md bg-accent-strong px-4 text-sm font-semibold text-white opacity-70">
                                    {{ __('Analyse candidate · preview') }}
                                </button>
                            </div>
                            <p class="mt-4 text-xs leading-5 text-neutral-500">
                                {{ __('The fictional fixtures and values above demonstrate the review layout only. They are not repository evidence, a recommendation, or an analysis result.') }}
                            </p>
                        </section>
                    </div>

                    <aside class="border-t border-neutral-200 bg-neutral-100/60 p-5 sm:p-7 lg:border-l lg:border-t-0" aria-label="{{ __('Current builder coverage') }}">
                        <h3 class="text-sm font-semibold text-neutral-900">{{ __('Designed coverage') }}</h3>
                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-400">{{ __('Competitions') }}</dt>
                                <dd class="mt-1 text-neutral-700">{{ collect($this->allowedCompetitions())->implode(', ') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-400">{{ __('Markets') }}</dt>
                                <dd class="mt-1 text-neutral-700">{{ __('Match Result, Total Goals, Double Chance, Draw No Bet, Both Teams to Score') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-400">{{ __('Method') }}</dt>
                                <dd class="mt-1 text-neutral-700">{{ __('Deterministic rules and bounded evidence checks') }}</dd>
                            </div>
                        </dl>
                        <p class="mt-5 border-t border-neutral-200 pt-4 text-xs leading-5 text-neutral-500">
                            {{ __('This preview contains no generated candidate and makes no claim about likely match outcomes.') }}
                        </p>
                    </aside>
                </div>
            </section>
        @else
        @if ($briefError)
            <x-alert variant="error">{{ $briefError }}</x-alert>
        @endif

        @if ($evidenceFailureType)
            <x-workspace.inline-error :title="__('Live fixture information is temporarily unavailable')">
                <p>
                    @if ($evidenceFailureType === 'configuration')
                        {{ __('The live evidence connection is not available right now. Your planning choices have been preserved. Please try again later.') }}
                    @else
                        {{ __('We could not retrieve the supported market information needed to build candidates. Your planning choices have been preserved. Please try again later.') }}
                    @endif
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-primary-button wire:click="findCandidate" wire:loading.attr="disabled" wire:target="findCandidate">
                        <span wire:loading.remove wire:target="findCandidate">{{ __('Try Again') }}</span>
                        <span wire:loading wire:target="findCandidate">{{ __('Trying again…') }}</span>
                    </x-primary-button>
                    <x-secondary-button wire:click="editBrief">{{ __('Review Planning Choices') }}</x-secondary-button>
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="inline-flex min-h-10 items-center text-sm font-semibold text-neutral-700 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        {{ __('Return to workspace') }}
                    </a>
                </div>
            </x-workspace.inline-error>
        @endif

        {{-- Planning brief form --}}
        @if (! $discovered && $evaluationOutcome === null && ! $noOpportunitiesReason)
            <div class="workspace-grid items-start">
                <x-card>
                    <div class="space-y-8">
                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900">{{ __('Competitions') }}</h2>
                        <p class="mt-1 text-xs leading-5 text-neutral-500">
                            {{ __('Only competitions with confirmed provider and market coverage are selectable.') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($this->allowedCompetitions() as $key => $label)
                                <x-workspace.selection-chip :selected="in_array($key, $competitions, true)"
                                    wire:click="toggleCompetition('{{ $key }}')"
                                    :aria-pressed="in_array($key, $competitions, true) ? 'true' : 'false'">
                                    {{ $label }}
                                </x-workspace.selection-chip>
                            @endforeach
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('competitions')" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900">{{ __('Planning window') }}</h2>
                        <x-workspace.segmented-control class="mt-3 w-full sm:w-auto" :label="__('Planning window')">
                            @foreach (['1' => __('Next 24 hours'), '3' => __('Next 3 days'), '7' => __('Next 7 days')] as $value => $label)
                                <button type="button" wire:click="$set('windowDays', {{ $value }})"
                                    @class([
                                        'min-h-10 flex-1 rounded-md px-3 text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent sm:flex-none',
                                        'bg-accent-strong text-white' => $windowDays === (int) $value,
                                        'text-neutral-600 hover:bg-surface-card' => $windowDays !== (int) $value,
                                    ])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </x-workspace.segmented-control>
                        <x-input-error class="mt-2" :messages="$errors->get('windowDays')" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900">{{ __('Number of legs') }}</h2>
                        <p class="mt-1 text-xs leading-5 text-neutral-500">
                            {{ __('Higher leg counts require more compatible fixtures and may reduce candidate availability.') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @for ($n = 2; $n <= 8; $n++)
                                <x-workspace.selection-chip :selected="$legCount === $n"
                                    wire:click="$set('legCount', {{ $n }})"
                                    :aria-label="trans_choice(':count leg|:count legs', $n, ['count' => $n])"
                                    :aria-pressed="$legCount === $n ? 'true' : 'false'">
                                    {{ $n }}
                                </x-workspace.selection-chip>
                            @endfor
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('legCount')" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900">{{ __('Allowed markets') }}</h2>
                        <div class="mt-3 space-y-4">
                            @foreach ($this->marketOptions() as $group => $marketOptions)
                                <fieldset>
                                    <legend class="workspace-metadata font-semibold">{{ $group }}</legend>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($marketOptions as $key => $label)
                                            <x-workspace.selection-chip :selected="in_array($key, $markets, true)"
                                                wire:click="toggleMarket('{{ $key }}')"
                                                :aria-pressed="in_array($key, $markets, true) ? 'true' : 'false'">
                                                {{ $label }}
                                            </x-workspace.selection-chip>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="mt-3" x-data="{ open: false }">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                                class="min-h-10 text-sm text-accent-strong font-semibold">
                                {{ __('More markets') }}
                            </button>
                            <div x-show="open" x-cloak class="mt-2">
                                <label class="flex items-center gap-2 text-sm text-neutral-700">
                                    <input type="checkbox" wire:model.live="includeHalfTimeResult" class="size-4 shrink-0">
                                    {{ __('Half-Time Result') }}
                                </label>
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('markets')" />
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900">{{ __('Target odds (optional)') }}</h2>
                        <label class="mt-2 flex cursor-pointer items-center gap-3 text-sm text-neutral-700">
                            <input type="checkbox" wire:model.live="targetOddsEnabled" role="switch" class="peer sr-only">
                            <span aria-hidden="true" class="relative h-6 w-11 shrink-0 rounded-full border border-neutral-300 bg-neutral-200 transition-colors peer-checked:border-accent/60 peer-checked:bg-accent peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5 motion-reduce:after:transition-none"></span>
                            <span>{{ __('Set a target combined odds range') }}</span>
                        </label>
                        @if ($targetOddsEnabled)
                            <div class="mt-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-end">
                                <label class="block text-sm text-neutral-700">
                                    {{ __('Minimum decimal odds') }}
                                    <x-text-input type="text" inputmode="decimal" wire:model.live.debounce.350ms="targetOddsMin" placeholder="2.00" class="mt-1 w-full" />
                                </label>
                                <span class="text-neutral-400">{{ __('to') }}</span>
                                <label class="block text-sm text-neutral-700">
                                    {{ __('Maximum decimal odds') }}
                                    <x-text-input type="text" inputmode="decimal" wire:model.live.debounce.350ms="targetOddsMax" placeholder="6.00" class="mt-1 w-full" />
                                </label>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('targetOddsMin')" />
                            <x-input-error class="mt-2" :messages="$errors->get('targetOddsMax')" />
                            <p class="mt-2 text-xs leading-5 text-neutral-500">{{ __('Tighter ranges may reduce the number of candidates that satisfy every condition.') }}</p>
                        @endif
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900">{{ __('Maximum structural risk') }}</h2>
                        <p class="mt-1 text-xs text-neutral-500">{{ __('SlipGuard will not exceed this even if it means fewer legs match your other choices.') }}</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($this->riskBandOptions() as $value => $label)
                                <label @class([
                                    'flex cursor-pointer items-start gap-3 rounded-lg border p-3',
                                    'border-accent/40 bg-accent/10' => $riskCeiling === $value,
                                    'border-neutral-300 bg-surface-card' => $riskCeiling !== $value,
                                ])>
                                    <input type="radio" wire:model.live="riskCeiling" value="{{ $value }}" class="size-4 shrink-0">
                                    <span>
                                        <span class="block text-sm font-semibold text-neutral-900">{{ $label }}</span>
                                        <span class="mt-1 block text-xs leading-5 text-neutral-500">{{ $this->riskBandDescriptions()[$value] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('riskCeiling')" />
                    </div>

                    <div class="pt-2">
                        <x-primary-button wire:click="findCandidate" wire:loading.attr="disabled" wire:target="findCandidate" class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                            <span wire:loading.remove wire:target="findCandidate">{{ __('Find a candidate') }}</span>
                            <span wire:loading wire:target="findCandidate">{{ __('Evaluating conditions…') }}</span>
                        </x-primary-button>
                        <p class="mt-3 max-w-xl text-xs leading-5 text-neutral-500">
                            {{ __('SlipGuard will search supported fixtures and return a candidate for your review. It will not place a bet or decide for you.') }}
                        </p>
                    </div>

                    <div wire:loading wire:target="findCandidate"
                         class="rounded-xl border border-accent/25 bg-accent/5 p-4"
                         role="status" aria-live="polite" aria-atomic="true">
                        <div class="flex items-start gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-accent-strong text-white motion-safe:animate-pulse">
                                <x-heroicon-o-magnifying-glass class="size-5" aria-hidden="true" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-neutral-900">{{ __('Candidate evaluation underway') }}</p>
                                <p class="mt-1 text-sm text-neutral-600">
                                    {{ __('SlipGuard is applying your planning brief to the available supported evidence.') }}
                                </p>
                                <ul class="mt-3 space-y-2 text-xs leading-5 text-neutral-600" aria-label="{{ __('Work included in this request') }}">
                                    @foreach ([
                                        __('Retrieve or reuse fresh fixture evidence'),
                                        __('Check competition, market and planning-window eligibility'),
                                        __('Rank compatible fixture and market opportunities'),
                                        __('Prepare eligible selections for your review'),
                                    ] as $activity)
                                        <li class="flex items-start gap-2">
                                            <x-heroicon-o-arrow-right class="mt-0.5 size-4 shrink-0 text-accent-strong" aria-hidden="true" />
                                            <span>{{ $activity }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-3 text-xs leading-5 text-neutral-500">
                                    {{ __('These checks run as one request. SlipGuard does not display a percentage because the remaining work cannot be measured truthfully. A candidate appears only when the available evidence and your selected conditions can be satisfied.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    </div>
                </x-card>

                <x-workspace.sticky-summary :title="__('Your planning request')" :aria-label="__('Your planning request')" aria-live="polite">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="workspace-metadata">{{ __('Competitions') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">
                                {{ collect($competitions)->map(fn ($key) => $this->allowedCompetitions()[$key] ?? $key)->implode(', ') ?: __('None selected') }}
                            </dd>
                        </div>
                        <div class="border-t workspace-internal-border pt-4">
                            <dt class="workspace-metadata">{{ __('Planning window') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">{{ $this->windowLabel() }}</dd>
                        </div>
                        <div class="border-t workspace-internal-border pt-4">
                            <dt class="workspace-metadata">{{ __('Selections') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">{{ trans_choice(':count leg|:count legs', $legCount, ['count' => $legCount]) }}</dd>
                        </div>
                        <div class="border-t workspace-internal-border pt-4">
                            <dt class="workspace-metadata">{{ __('Markets') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">
                                {{ collect($this->selectedMarkets())->map(fn ($key) => $this->marketLabels()[$key] ?? $key)->implode(', ') ?: __('None selected') }}
                            </dd>
                        </div>
                        <div class="border-t workspace-internal-border pt-4">
                            <dt class="workspace-metadata">{{ __('Target combined odds') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">
                                {{ $targetOddsEnabled ? (($targetOddsMin ?: '—').' – '.($targetOddsMax ?: '—').' '.__('decimal')) : __('No target range') }}
                            </dd>
                        </div>
                        <div class="border-t workspace-internal-border pt-4">
                            <dt class="workspace-metadata">{{ __('Maximum structural risk') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">{{ $this->riskBandOptions()[$riskCeiling] ?? __('Unsupported') }}</dd>
                        </div>
                        <div class="border-t workspace-internal-border pt-4">
                            @php
                                $fixturePoolCount = $this->fixturePoolCount();
                            @endphp
                            <dt class="workspace-metadata">{{ __('Fresh fixtures in selected window') }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">{{ $fixturePoolCount }}</dd>
                            <p class="mt-1 text-xs leading-5 text-neutral-500">
                                {{ __('A repository-backed fixture count, not an estimate of suitable candidates.') }}
                            </p>
                            @if ($fixturePoolCount < $legCount)
                                <p class="mt-2 flex items-start gap-2 text-xs leading-5 text-alert-caution-strong">
                                    <x-heroicon-o-information-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                                    <span>{{ __('This window currently contains fewer fixtures than your requested leg count. Widen the window or reduce the number of legs before searching.') }}</span>
                                </p>
                            @endif
                        </div>
                    </dl>

                    @if ($errors->any())
                        <div class="mt-5 rounded-md border border-alert-error/30 bg-alert-error/10 p-3" role="alert">
                            <p class="text-sm font-semibold text-alert-error-strong">{{ __('Resolve these planning issues') }}</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-neutral-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="mt-5 border-t workspace-internal-border pt-4 text-xs leading-5 text-neutral-500">
                        {{ __('SlipGuard will search supported fixtures and return a candidate for your review. It will not place a bet or decide for you.') }}
                    </p>
                </x-workspace.sticky-summary>
            </div>
        @endif

        {{-- No opportunities at discovery time --}}
        @if ($noOpportunitiesReason)
            <div class="workspace-grid items-start">
                <x-empty-state :title="__('No accumulator candidate satisfies your current planning constraints.')" :description="$noOpportunitiesReason">
                    <x-slot name="action">
                        <x-secondary-button wire:click="editBrief">{{ __('Change planning constraints') }}</x-secondary-button>
                    </x-slot>
                </x-empty-state>
                <x-workspace.sticky-summary :title="__('Request reviewed')" :aria-label="__('Request reviewed')">
                    <x-workspace.limited-data-notice :title="__('No suitable candidate found')">
                        {{ __('The available evidence did not satisfy every selected competition, market, leg-count and structural-risk condition.') }}
                    </x-workspace.limited-data-notice>
                    <p class="mt-4 text-xs leading-5 text-neutral-500">
                        {{ __('Try a wider planning window, fewer legs, more markets, or a different risk ceiling. SlipGuard will not weaken your constraints silently.') }}
                    </p>
                </x-workspace.sticky-summary>
            </div>
        @endif

        {{-- Opportunity review --}}
        @if ($discovered && $evaluationOutcome === null)
            <div class="workspace-grid items-start">
            <div class="space-y-4">
                @if ($conversationalSummary)
                    {{-- `PO-U23-001` §15 — "Clearly indicate that SlipGuard has created a first draft." --}}
                    <x-alert variant="success" role="status">
                        {{ __('Your first draft is ready.') }} {{ $conversationalSummary }}
                    </x-alert>
                @endif
                <p class="text-sm text-neutral-600">
                    {{ trans_choice(':made of :total selection made|:made of :total selections made', $selectionsTotal, ['made' => $selectionsMade, 'total' => $selectionsTotal]) }}
                </p>

                @foreach ($activeSlots as $fixtureKey => $slot)
                    @continue (! $slot)
                    <x-card>
                        <fieldset>
                            <legend class="text-sm font-medium text-neutral-900">
                                {{ $slot->options[0]->fixture->home_team }} vs {{ $slot->options[0]->fixture->away_team }}
                                <span class="text-neutral-500 font-normal">· {{ $this->marketFamilyLabel($slot->options[0]->marketFamily) }}</span>
                            </legend>
                            <p class="mt-1 text-xs text-neutral-500">{{ __('Choose the outcome you want SlipGuard to evaluate.') }}</p>

                            <div class="mt-3 space-y-2">
                                @foreach ($slot->options as $option)
                                    <label class="flex items-center justify-between gap-4 px-3 py-2.5 rounded-md border border-neutral-300 text-sm cursor-pointer">
                                        <span class="flex items-center gap-2">
                                            <input type="radio" name="outcome-{{ $fixtureKey }}" class="size-4 shrink-0"
                                                @checked(($chosenOutcomeName[$fixtureKey] ?? null) === $option->selectionDescription)
                                                wire:click="selectOutcome('{{ $fixtureKey }}', '{{ $option->selectionDescription }}')">
                                            {{ $option->selectionDescription }}
                                        </span>
                                        <span class="font-tabular text-neutral-600">{{ $option->decimalOdds }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @if (($chosenOutcomeName[$fixtureKey] ?? null))
                                <p class="mt-2 text-xs text-neutral-500">{{ __('You selected :outcome.', ['outcome' => $chosenOutcomeName[$fixtureKey]]) }}</p>
                            @endif
                        </fieldset>
                    </x-card>
                @endforeach

                <x-primary-button wire:click="evaluateCandidate" wire:loading.attr="disabled" wire:target="evaluateCandidate"
                    :disabled="$selectionsMade < $selectionsTotal" class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                    <span wire:loading.remove wire:target="evaluateCandidate">{{ __('Evaluate Candidate') }}</span>
                    <span wire:loading wire:target="evaluateCandidate">{{ __('Evaluating…') }}</span>
                </x-primary-button>
                @if ($selectionsMade < $selectionsTotal)
                    <p class="text-xs text-neutral-500">{{ __('Choose an outcome for every opportunity to continue.') }}</p>
                @endif
            </div>
            <x-workspace.sticky-summary :title="__('Candidate under review')" :aria-label="__('Candidate under review')" aria-live="polite">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="workspace-metadata">{{ __('Selection compatibility') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">
                            {{ trans_choice(':made of :total choice recorded|:made of :total choices recorded', $selectionsTotal, ['made' => $selectionsMade, 'total' => $selectionsTotal]) }}
                        </dd>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Maximum structural risk') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">{{ $this->riskBandOptions()[$riskCeiling] ?? __('Unsupported') }}</dd>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Target odds') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">
                            {{ $targetOddsEnabled ? (($targetOddsMin ?: '—').' – '.($targetOddsMax ?: '—').' '.__('decimal')) : __('No target range') }}
                        </dd>
                    </div>
                </dl>
                <p class="mt-5 border-t workspace-internal-border pt-4 text-xs leading-5 text-neutral-500">
                    {{ __('Choose one outcome per fixture. SlipGuard evaluates the assembled structure only after every choice is explicit.') }}
                </p>
            </x-workspace.sticky-summary>
            </div>
        @endif

        {{-- Replacement needed --}}
        @if ($evaluationOutcome === 'replacement_needed')
            <x-alert variant="caution">
                {{ __(':removed no longer fits within your plan.', ['removed' => $replacementRemovedFixtureKey]) }}
                {{ $replacementReasonLabel }}
            </x-alert>

            <div class="space-y-3">
                @foreach ($activeFixtureKeys as $fixtureKey)
                    @continue ($fixtureKey === $replacementNewFixtureKey)
                    <x-card class="opacity-60">
                        <p class="text-sm text-neutral-700">{{ $chosenOutcomeName[$fixtureKey] ?? '—' }}</p>
                    </x-card>
                @endforeach

                @if ($replacementSlot)
                    <x-card>
                        <fieldset>
                            <legend class="text-sm font-medium text-neutral-900">
                                {{ $replacementSlot->options[0]->fixture->home_team }} vs {{ $replacementSlot->options[0]->fixture->away_team }}
                                <span class="text-neutral-500 font-normal">· {{ $this->marketFamilyLabel($replacementSlot->options[0]->marketFamily) }}</span>
                            </legend>
                            <p class="mt-1 text-xs text-neutral-500">{{ __('Choose the outcome you want SlipGuard to evaluate.') }}</p>

                            <div class="mt-3 space-y-2">
                                @foreach ($replacementSlot->options as $option)
                                    <label class="flex items-center justify-between gap-4 px-3 py-2.5 rounded-md border border-neutral-300 text-sm cursor-pointer">
                                        <span class="flex items-center gap-2">
                                            <input type="radio" name="outcome-{{ $replacementNewFixtureKey }}" class="size-4 shrink-0"
                                                wire:click="selectOutcome('{{ $replacementNewFixtureKey }}', '{{ $option->selectionDescription }}')">
                                            {{ $option->selectionDescription }}
                                        </span>
                                        <span class="font-tabular text-neutral-600">{{ $option->decimalOdds }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </x-card>

                    <x-primary-button wire:click="evaluateCandidate" wire:loading.attr="disabled" wire:target="evaluateCandidate"
                        :disabled="! ($chosenOutcomeName[$replacementNewFixtureKey] ?? null)" class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                        {{ __('Evaluate Candidate') }}
                    </x-primary-button>
                @endif
            </div>
        @endif

        {{-- Changed --}}
        @if ($evaluationOutcome === 'changed')
            <x-alert variant="info">
                {{ __('Odds changed since this candidate was built.') }}
            </x-alert>
            <x-secondary-button wire:click="editBrief">{{ __('Edit planning brief') }}</x-secondary-button>
        @endif

        {{-- No valid candidate after evaluation --}}
        @if ($evaluationOutcome === 'no_valid_candidate')
            <x-empty-state :title="__('No accumulator candidate satisfies your current planning constraints.')" :description="$rejectionReason">
                <x-slot name="action">
                    <div class="flex gap-3">
                        <x-primary-button wire:click="editBrief">{{ __('Edit planning brief') }}</x-primary-button>
                        <x-secondary-button href="{{ route('analyze') }}" wire:navigate>{{ __('Back to workspace') }}</x-secondary-button>
                    </div>
                </x-slot>
            </x-empty-state>
        @endif

        {{-- Constructed --}}
        @if ($evaluationOutcome === 'constructed')
            <div class="workspace-grid items-start">
            <x-card>
                <div class="flex items-center gap-3">
                    @if ($riskBandToken)
                        @php
                            $token = str_replace('_', '-', $riskBandToken);
                        @endphp
                        <span class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium px-3 py-1.5 rounded-lg border text-risk-{{ $token }} bg-risk-{{ $token }}/10 border-risk-{{ $token }}/30">
                            <x-heroicon-o-exclamation-triangle class="size-4" aria-hidden="true" />
                            {{ $riskBandLabel }}
                        </span>
                    @endif
                    <p class="text-sm font-medium text-neutral-900">{{ __('Candidate ready for your review') }}</p>
                </div>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ trans_choice(':count leg · Combined indicative odds :odds|:count legs · Combined indicative odds :odds', count($constructedLegsSummary), ['count' => count($constructedLegsSummary), 'odds' => $combinedOdds]) }}
                </p>

                <ul class="mt-4 space-y-2">
                    @foreach ($constructedLegsSummary as $leg)
                        <li class="flex items-start justify-between gap-4 border-b workspace-internal-border pb-2 text-sm last:border-b-0 last:pb-0">
                            <span>
                                <span class="block text-neutral-800">{{ $leg['description'] }}</span>
                                <span class="mt-0.5 block text-xs text-neutral-500">
                                    @if ($leg['gate_dependent'])
                                        {{ __('Structural contribution unavailable because this leg is required for report availability') }}
                                    @elseif ($leg['structural_contribution'] > 0)
                                        {{ trans_choice("Adds :score point to this candidate's structural-risk assessment.|Adds :score points to this candidate's structural-risk assessment.", $leg['structural_contribution'], ['score' => $leg['structural_contribution']]) }}
                                    @elseif ($leg['structural_contribution'] < 0)
                                        {{ trans_choice("Offsets :score point in this candidate's structural-risk assessment.|Offsets :score points in this candidate's structural-risk assessment.", abs($leg['structural_contribution']), ['score' => abs($leg['structural_contribution'])]) }}
                                    @elseif ($leg['structural_contribution'] === 0)
                                        {{ __('No measurable contribution to this candidate’s structural-risk assessment.') }}
                                    @else
                                        {{ __('Structural contribution unavailable') }}
                                    @endif
                                </span>
                            </span>
                            <span class="shrink-0 font-tabular text-neutral-600">{{ $leg['odds'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-3 text-xs leading-5 text-neutral-500">
                    {{ __('Structural contribution describes each leg’s effect on slip structure, not its likelihood of winning.') }}
                </p>

                <p class="mt-4 text-xs text-neutral-500">
                    {{ __('Odds are time-stamped provider data for planning purposes and may change. SlipGuard does not place the bet or guarantee bookmaker availability.') }}
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <x-primary-button wire:click="acceptCandidate" wire:loading.attr="disabled" class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                        {{ __('Use this candidate in Planner') }}
                    </x-primary-button>
                    @if (! $hasRegenerated)
                        <x-secondary-button wire:click="tryDifferentCandidate" wire:loading.attr="disabled" class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                            {{ __('Try a different candidate') }}
                        </x-secondary-button>
                    @endif
                </div>
            </x-card>
            <x-workspace.sticky-summary :title="__('Constraint review')" :aria-label="__('Constraint review')">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="workspace-metadata">{{ __('Combined indicative odds') }}</dt>
                        <dd class="mt-1 text-lg font-semibold font-tabular text-neutral-900">{{ $combinedOdds }}</dd>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Structural risk') }}</dt>
                        <dd class="mt-1 font-semibold text-neutral-900">{{ $riskBandLabel ?? __('Unavailable') }}</dd>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Constraint compliance') }}</dt>
                        <dd class="mt-1 flex items-center gap-2 font-medium text-neutral-900">
                            <x-heroicon-o-check-circle class="size-4 text-quality-strong" aria-hidden="true" />
                            {{ __('All requested conditions satisfied') }}
                        </dd>
                    </div>
                </dl>
                <p class="mt-5 border-t workspace-internal-border pt-4 text-xs leading-5 text-neutral-500">
                    {{ __('This is a candidate for review, not a predicted outcome or recommendation.') }}
                </p>
            </x-workspace.sticky-summary>
            </div>
        @endif
        @endif

        <section class="workspace-section-panel workspace-section" aria-labelledby="recent-planning-heading">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="workspace-metadata">{{ $this->capabilityEnabled() ? __('Planner available') : __('Experience preview') }}</p>
                    <h2 id="recent-planning-heading" class="mt-1 text-lg font-semibold text-neutral-900">
                        {{ __('Recent planning sessions') }}
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-neutral-600">
                        {{ __('Continue a saved plan or review how an earlier accumulator changed through deterministic structural analysis.') }}
                    </p>
                </div>
                <a href="{{ route('planner.history') }}" wire:navigate
                    class="inline-flex min-h-10 items-center text-sm font-semibold text-accent-strong hover:text-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    {{ __('View Planning History') }}
                </a>
            </div>

            @if ($recentSessions->isEmpty())
                <div class="mt-5 border-t workspace-internal-border pt-5">
                    <p class="text-sm font-medium text-neutral-900">{{ __('No planning sessions yet') }}</p>
                    <p class="mt-1 text-sm leading-6 text-neutral-600">
                        {{ $this->capabilityEnabled()
                            ? __('Your first accepted candidate will appear here without replacing the planning choices you started with.')
                            : __('Planning History will retain genuine sessions when live candidate evaluation is authorised and available.') }}
                    </p>
                </div>
            @else
                <div class="mt-5 grid gap-3">
                    @foreach ($recentSessions as $session)
                        <x-workspace.record-card :href="route('planner.session', $session)"
                            :title="$session->sourceBettingSlip->displayLabel()"
                            :metadata="__('Updated :time', ['time' => $session->updated_at->diffForHumans()])"
                            wire:navigate wire:key="recent-planner-session-{{ $session->id }}">
                            <x-slot name="status">
                                <x-workspace.status-badge>{{ $session->status->label() }}</x-workspace.status-badge>
                            </x-slot>
                            <p class="mt-3 text-sm text-neutral-600">
                                {{ trans_choice(':count selection in this plan|:count selections in this plan', $session->sourceBettingSlip->legs->count(), ['count' => $session->sourceBettingSlip->legs->count()]) }}
                            </p>
                        </x-workspace.record-card>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
