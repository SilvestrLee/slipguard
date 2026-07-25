<?php

use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\DataQualityBand;
use App\Domain\Risk\Results\ReasonCode;
use App\Domain\Risk\Results\RiskBand;
use App\Models\BettingSlip;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public BettingSlip $bettingSlip;

    /**
     * A report is read-only, persisted output — U-03.1 confirmed no route
     * displaying an analysis existed before this component. Authorization
     * is against the SlipAnalysis itself (App\Policies\SlipAnalysisPolicy),
     * not the slip, per the actual ownership boundary that model enforces.
     */
    public function mount(BettingSlip $bettingSlip): void
    {
        $analysis = $bettingSlip->analysis;

        abort_if($analysis === null, 404);

        $this->authorize('view', $analysis);

        $this->bettingSlip = $bettingSlip;
    }

    /** Maps a RiskBand to its DESIGN_TOKENS.md colour-token suffix (mirrors the Dashboard's own helper, U-02). */
    public function riskBandToken(RiskBand $band): string
    {
        return match ($band) {
            RiskBand::Low => 'low',
            RiskBand::Moderate => 'moderate',
            RiskBand::High => 'high',
            RiskBand::VeryHigh => 'very-high',
        };
    }

    /** U-03.2 §28 — one sentence per band, none of the forbidden outcome-based terms. */
    public function riskBandExplanation(RiskBand $band): string
    {
        return match ($band) {
            RiskBand::Low => __('This slip shows few of the structural characteristics that add risk — a small number of selections, moderate odds, and no strong concentration of risk in a single selection.'),
            RiskBand::Moderate => __('This slip shows some structural characteristics that add risk — for example, a longer selection count, moderately elevated odds, or a noticeable concentration of risk in one selection.'),
            RiskBand::High => __('This slip shows several structural characteristics that meaningfully add risk — for example, elevated odds, a longer selection count, or a strong concentration of risk in one selection.'),
            RiskBand::VeryHigh => __('This slip shows an unusually high combination of structural risk characteristics, such as an extended selection count, high odds, and a strong concentration of risk, together.'),
        };
    }

    /** U-03.2 §29 — band only, never the numeric score (PD-04). */
    public function dataQualityExplanation(DataQualityBand $band): string
    {
        return match ($band) {
            DataQualityBand::Strong => __('SlipGuard was able to fully evaluate every selection in this slip.'),
            DataQualityBand::Good => __('SlipGuard was able to evaluate this slip with only minor gaps in the available information.'),
            DataQualityBand::Limited => __('SlipGuard could only partially evaluate one or more selections in this slip — see the limitation above for what this affects.'),
            DataQualityBand::Insufficient => __('The available information for this slip was not enough to produce a reliable structural assessment.'),
        };
    }

    /** U-03.2 §22 — the customer-facing factor name register. */
    public function factorName(string $code): string
    {
        return match ($code) {
            'RF-001' => __('Number of Selections'),
            'RF-002' => __('Combined Odds'),
            'RF-003' => __('Selection Odds'),
            'RF-004' => __('Risk Concentration'),
            'RF-005' => __('Market Complexity'),
            'RF-006' => __('Selection Relationships'),
            default => $code,
        };
    }

    /**
     * Generic, structural, non-slip-specific descriptions of what each
     * factor measures — grounded in RISK_RULE_SET_2026_1.md's own factor
     * definitions (§6–§11). U-03.2 flagged the exact customer-facing
     * sentence as an open gap (OI-01); these are proposed, not yet
     * Product-Office-approved, same convention as the rest of that
     * document's PROPOSED UX COPY.
     */
    public function factorExplanation(string $code): string
    {
        return match ($code) {
            'RF-001' => __('Reflects the number of selections in your slip — more selections structurally add risk.'),
            'RF-002' => __('Reflects the combined odds across all your selections — higher combined odds structurally add risk.'),
            'RF-003' => __('Reflects how high the odds are on your individual selections — a small number of high-odds selections adds risk even in an otherwise short slip.'),
            'RF-004' => __("Reflects how much of the slip's risk sits in a small number of selections, rather than being spread evenly."),
            'RF-005' => __('Reflects how complex the markets in your slip are — some markets carry more structural uncertainty than others.'),
            default => '',
        };
    }

    /** U-03.2 §23 — canonical order, used for stable tie-breaking. */
    private function canonicalFactorOrder(): array
    {
        return ['RF-001', 'RF-002', 'RF-003', 'RF-004', 'RF-005', 'RF-006'];
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function orderedFactors(): \Illuminate\Support\Collection
    {
        $order = $this->canonicalFactorOrder();

        return collect($this->bettingSlip->analysis->factor_results)
            ->sortBy(fn (array $factor) => array_search($factor['factor_code'], $order, true))
            ->values();
    }

    /**
     * U-03.2 §20/§22 — highest adjustedContribution among the 5 active
     * factors (RF-006 never appears here, §23). Null means "no meaningful
     * contributor" (§23's calm summary state), not an error.
     */
    public function mainContributingFactor(): ?array
    {
        $top = $this->orderedFactors()
            ->reject(fn (array $factor) => $factor['factor_code'] === 'RF-006')
            ->sortByDesc(fn (array $factor) => (float) $factor['adjusted_contribution'])
            ->first();

        if (! $top || (float) $top['adjusted_contribution'] <= 0.0) {
            return null;
        }

        return $top;
    }

    /**
     * U-03.2 §20/§21.5/§23 — the remaining factors, ordered, excluding
     * RF-006 and the Main Contributing Factor. Zero-contribution factors
     * are omitted here (available in the expanded Methodology view
     * instead); a factor SlipGuard could not evaluate (§23 — only ever
     * RF-005) is always shown, distinctly labelled, never omitted.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function supportingFactors(): \Illuminate\Support\Collection
    {
        $mainCode = $this->mainContributingFactor()['factor_code'] ?? null;
        $notEvaluated = $this->bettingSlip->analysis->factors_not_evaluated ?? [];

        return $this->orderedFactors()
            ->reject(fn (array $factor) => $factor['factor_code'] === 'RF-006')
            ->reject(fn (array $factor) => $factor['factor_code'] === $mainCode)
            ->reject(fn (array $factor) => (float) $factor['adjusted_contribution'] <= 0.0
                && ! in_array($factor['factor_code'], $notEvaluated, true))
            ->sortByDesc(fn (array $factor) => (float) $factor['adjusted_contribution'])
            ->values();
    }

    public function isFactorNotEvaluated(string $code): bool
    {
        return in_array($code, $this->bettingSlip->analysis->factors_not_evaluated ?? [], true);
    }

    /**
     * U-03.2 §18/Frame U-02 — the three repository-supported Unavailable
     * reasons, detected from the persisted reason codes actually produced
     * by the gate (`DetermineAnalysisAvailability`), never invented.
     */
    public function unavailableReason(): string
    {
        $codes = $this->bettingSlip->analysis->reason_codes;

        if ($codes->contains(ReasonCode::SportUnsupported)) {
            return 'unsupported_sport';
        }

        if ($codes->contains(ReasonCode::MarketUnrecognized)) {
            return 'too_many_unrecognised';
        }

        return 'insufficient_information';
    }

    public function with(): array
    {
        return [
            'analysis' => $this->bettingSlip->analysis,
            'availability' => $this->bettingSlip->analysis->availability,
        ];
    }
}; ?>

<div class="py-10 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- §20.1 Analysis state --}}
        <h1 class="text-2xl sm:text-3xl font-semibold text-neutral-900">
            @if ($availability === AnalysisAvailability::Full)
                {{ __('Analysis complete') }}
            @elseif ($availability === AnalysisAvailability::Limited)
                {{ __('Limited analysis available') }}
            @else
                {{ __("We couldn't analyze this slip") }}
            @endif
        </h1>
        <p class="mt-1 text-sm text-neutral-500">
            {{ __('Analysed :time', ['time' => $analysis->created_at->diffForHumans()]) }}
        </p>

        <div class="mt-8 space-y-12">

            @if ($availability === AnalysisAvailability::Unavailable)
                {{-- Unavailable: Frames U-01/U-02/U-03/U-04 — no score, no band, no factors, no data quality --}}
                <section aria-labelledby="unavailable-reason-heading">
                    <h2 id="unavailable-reason-heading" class="sr-only">{{ __('Reason') }}</h2>
                    <div class="bg-neutral-50 border border-neutral-200 rounded-lg p-6">
                        <p class="text-sm text-neutral-700">
                            @switch($this->unavailableReason())
                                @case('unsupported_sport')
                                    {{ __("SlipGuard doesn't support this sport yet. Structural risk analysis is currently available for football only. We're evaluating other sports for future coverage.") }}
                                    @break
                                @case('too_many_unrecognised')
                                    {{ __("Too many selections on this slip couldn't be recognized.") }}
                                    @break
                                @default
                                    {{ __("The available information for this slip wasn't enough for SlipGuard to produce a reliable structural assessment.") }}
                            @endswitch
                        </p>
                    </div>
                </section>

                <section aria-labelledby="unavailable-trust-heading">
                    <h2 id="unavailable-trust-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Trust') }}</h2>
                    <p class="mt-3 text-sm text-neutral-600">
                        {{ __("SlipGuard doesn't produce a structural risk score when it can't evaluate a slip responsibly. Refusing to guess is part of how this analysis stays trustworthy.") }}
                    </p>
                </section>
            @else
                {{-- Full and Limited share the same structure — §20.2 through §20.9 --}}

                @if ($availability === AnalysisAvailability::Limited)
                    {{-- §20.6 Limitation information — shown in the same initial view, never below the fold --}}
                    <section aria-labelledby="limitation-heading">
                        <h2 id="limitation-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Limitation') }}</h2>
                        <div class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6">
                            <p class="text-sm text-neutral-700">
                                {{ __('SlipGuard completed the analysis, but some slip information could not be fully evaluated.') }}
                            </p>
                            <p class="mt-2 text-sm text-neutral-600">
                                {{ __("Some selections in this slip use markets SlipGuard could only partially recognise. The result below reflects everything SlipGuard was able to evaluate — it doesn't affect how the score itself is calculated for the selections that were fully understood.") }}
                            </p>
                        </div>
                    </section>
                @endif

                {{-- §20.2 Overall Structural Risk --}}
                <section aria-labelledby="structural-risk-heading">
                    <h2 id="structural-risk-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __("Structural risk") }}</h2>
                    <div class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6 sm:p-8">
                        @php $token = $this->riskBandToken($analysis->risk_band); @endphp
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 text-base font-semibold px-4 py-2 rounded-lg border text-risk-{{ $token }} bg-risk-{{ $token }}/10 border-risk-{{ $token }}/30">
                                <x-heroicon-o-exclamation-triangle class="size-5" aria-hidden="true" />
                                {{ $analysis->risk_band->label() }}
                            </span>
                            <span class="text-2xl font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">
                                {{ $analysis->structural_score }}<span class="text-sm font-normal text-neutral-500">/100</span>
                            </span>
                        </div>
                        <p class="mt-4 text-sm text-neutral-600">{{ $this->riskBandExplanation($analysis->risk_band) }}</p>
                    </div>
                </section>

                {{-- §20.3 Main Contributing Factor --}}
                <section aria-labelledby="main-factor-heading">
                    <h2 id="main-factor-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Main Contributing Factor') }}</h2>
                    <div class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6">
                        @if ($main = $this->mainContributingFactor())
                            <p class="text-sm font-semibold text-neutral-900">{{ $this->factorName($main['factor_code']) }}</p>
                            <p class="mt-1 text-sm text-neutral-600">{{ $this->factorExplanation($main['factor_code']) }}</p>
                        @else
                            <p class="text-sm text-neutral-600">
                                {{ __("No single factor stood out as the main driver of this slip's structural risk — the score reflects a combination of smaller contributions.") }}
                            </p>
                        @endif
                    </div>
                </section>

                {{-- §20.4 Supporting Contributing Factors --}}
                @if ($this->supportingFactors()->isNotEmpty())
                    <section aria-labelledby="supporting-factors-heading" x-data="{ open: false }">
                        <h2 id="supporting-factors-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Other contributing factors') }}</h2>
                        <div class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg divide-y divide-neutral-200">
                            @foreach ($this->supportingFactors()->take(2) as $factor)
                                <div class="p-5">
                                    <p class="text-sm font-medium text-neutral-900">
                                        {{ $this->factorName($factor['factor_code']) }}
                                        @if ($this->isFactorNotEvaluated($factor['factor_code']))
                                            <span class="ms-2 text-xs font-medium text-neutral-500">{{ __('Not evaluated') }}</span>
                                        @endif
                                    </p>
                                    @if (! $this->isFactorNotEvaluated($factor['factor_code']))
                                        <p class="mt-1 text-sm text-neutral-600">{{ $this->factorExplanation($factor['factor_code']) }}</p>
                                    @endif
                                </div>
                            @endforeach
                            @if ($this->supportingFactors()->count() > 2)
                                <div x-show="open" x-cloak class="divide-y divide-neutral-200">
                                    @foreach ($this->supportingFactors()->skip(2) as $factor)
                                        <div class="p-5">
                                            <p class="text-sm font-medium text-neutral-900">
                                                {{ $this->factorName($factor['factor_code']) }}
                                                @if ($this->isFactorNotEvaluated($factor['factor_code']))
                                                    <span class="ms-2 text-xs font-medium text-neutral-500">{{ __('Not evaluated') }}</span>
                                                @endif
                                            </p>
                                            @if (! $this->isFactorNotEvaluated($factor['factor_code']))
                                                <p class="mt-1 text-sm text-neutral-600">{{ $this->factorExplanation($factor['factor_code']) }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                                        class="w-full text-start p-5 text-sm font-medium text-accent-strong hover:text-accent">
                                    <span x-show="!open">{{ __('Show more') }}</span>
                                    <span x-show="open" x-cloak>{{ __('Show less') }}</span>
                                </button>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- §20.5 Data Quality --}}
                <section aria-labelledby="data-quality-heading">
                    <h2 id="data-quality-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Data quality') }}</h2>
                    <div class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6">
                        <p class="text-sm font-semibold text-neutral-900">{{ ucfirst($analysis->data_quality_band->value) }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ $this->dataQualityExplanation($analysis->data_quality_band) }}</p>
                    </div>
                </section>

                {{-- §20.7 Trust statement --}}
                <section aria-labelledby="trust-heading">
                    <h2 id="trust-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Trust') }}</h2>
                    <ul class="mt-3 space-y-1 text-sm text-neutral-600">
                        <li>{{ __('Deterministic analysis — the same slip always produces the same result under the same rule set.') }}</li>
                        <li>{{ __('No outcome prediction — SlipGuard evaluates structural risk, not who wins.') }}</li>
                        <li>{{ __('Every result explains itself — what was found, and why it matters.') }}</li>
                    </ul>
                </section>

                {{-- §20.8 Methodology --}}
                <section aria-labelledby="methodology-heading" x-data="{ open: false }">
                    <h2 id="methodology-heading" class="sr-only">{{ __('Methodology') }}</h2>
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="methodology-panel"
                            class="w-full flex items-center justify-between text-start bg-neutral-50 border border-neutral-200 rounded-lg p-5 text-sm font-medium text-neutral-700 hover:bg-neutral-100">
                        {{ __('How was this analysis produced?') }}
                        <x-heroicon-o-chevron-down class="size-4 shrink-0 transition-transform" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true" />
                    </button>
                    <div id="methodology-panel" x-show="open" x-cloak class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6 text-sm text-neutral-600 space-y-4">
                        <p>{{ __('This score comes from a fixed set of rules that look at how a slip is built — the number of selections, the odds involved, how those odds are distributed, and how complex the markets are. The same slip always produces the same result under the current rule set. Data quality is assessed separately and never changes the score itself — it only tells you how completely SlipGuard could evaluate the information you provided.') }}</p>
                        <p>{{ __('Relationship analysis between selections is not active in the current Rule Set.') }}</p>
                    </div>
                </section>

                {{-- §20.9 Report details --}}
                <section aria-labelledby="report-details-heading" x-data="{ open: false }">
                    <h2 id="report-details-heading" class="sr-only">{{ __('Report details') }}</h2>
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="report-details-panel"
                            class="w-full flex items-center justify-between text-start bg-neutral-50 border border-neutral-200 rounded-lg p-5 text-sm font-medium text-neutral-700 hover:bg-neutral-100">
                        {{ __('View report details') }}
                        <x-heroicon-o-chevron-down class="size-4 shrink-0" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true" />
                    </button>
                    {{-- Single-column at every breakpoint — COMPONENT_PRINCIPLES.md's Reports rule: "never becomes multi-column even on desktop." --}}
                    <dl id="report-details-panel" x-show="open" x-cloak class="mt-3 bg-neutral-50 border border-neutral-200 rounded-lg p-6 space-y-4 text-sm">
                        <div>
                            <dt class="text-neutral-500">{{ __('Analysed') }}</dt>
                            <dd class="text-neutral-900">{{ $analysis->created_at->format('j M Y, H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">{{ __('Rule Set version') }}</dt>
                            <dd class="text-neutral-900">{{ $analysis->rule_set_version }}</dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">{{ __('Engine version') }}</dt>
                            <dd class="text-neutral-900">{{ $analysis->engine_version }}</dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">{{ __('Input schema version') }}</dt>
                            <dd class="text-neutral-900">{{ $analysis->input_schema_version }}</dd>
                        </div>
                        <div>
                            <dt class="text-neutral-500">{{ __('Market taxonomy version') }}</dt>
                            <dd class="text-neutral-900">{{ $analysis->market_taxonomy_version }}</dd>
                        </div>
                    </dl>
                </section>
            @endif

            {{-- §20.10 Exit actions — PD-08/PD-09/PD-10: identical across all three outcomes, no "Edit this slip" anywhere --}}
            <section aria-label="{{ __('Exit actions') }}" class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-neutral-200">
                <a href="{{ route('analyze.create') }}" wire:navigate
                   class="inline-flex items-center justify-center h-12 px-6 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-colors duration-instant">
                    {{ __('Analyse another slip') }}
                </a>
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="inline-flex items-center justify-center h-12 px-6 rounded-md border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-colors duration-instant">
                    {{ __('Return to dashboard') }}
                </a>
            </section>
        </div>
    </div>
</div>
