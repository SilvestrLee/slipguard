<?php

use App\Actions\Planner\AbandonPlannerSession;
use App\Actions\Planner\AddPlannerSelection;
use App\Actions\Planner\CompletePlannerSession;
use App\Actions\Planner\ExportPlannerSession;
use App\Actions\Planner\RemovePlannerSelection;
use App\Actions\Planner\ToggleSelectionLock;
use App\Domain\BettingSlip\BettingSlipValidationRules;
use App\Domain\Planner\Presentation\DerivePlannerTimeline;
use App\Domain\Planner\Presentation\PlannerCapabilities;
use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Domain\Risk\Results\RiskBand;
use App\Models\PlannerRegenerationEvent;
use App\Models\PlannerSession;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * U-06.4 — the Planner Workspace: a single persistent screen (§8 Navigation
 * Flow) implementing Frames E-02, L-01/L-02, P-01..P-05, R-01..R-04,
 * C-01..C-03, A-01/A-02, X-01/X-02, B-01. Every figure shown is read
 * straight from the latest persisted PlannerRegenerationEvent — nothing
 * here recalculates the engine (ADR-008's forbidden call flow).
 *
 * Known, honestly-scoped gaps against the U-06.4 storyboard (not silently
 * papered over — see the U-07.7 implementation report):
 *  - Data Quality / Rule Set Information / Analysis Metadata (storyboard
 *    §11 Frame P-05) are not persisted anywhere on PlannerRegenerationEvent
 *    (confirmed against the migration and RankLegsByStructuralWeakness's
 *    own output) — showing them would mean either fabricating values or
 *    re-invoking the engine live, both forbidden. Omitted from this screen
 *    rather than faked.
 *  - Reason-code deltas (reasonCodesGainedWithoutLeg/Lost) have no
 *    customer-facing copy register yet (unlike Risk Factor codes, which
 *    report.blade.php already translates) — omitted rather than shown as
 *    raw enum values, which would violate the no-academic-wording rule.
 *  - Compare Revisions (§13 C-02) gracefully degrades for a selection that
 *    was later removed: RemovePlannerSelection hard-deletes the row, so a
 *    historical revision's attribution can reference an id that no longer
 *    resolves. Handled honestly (a plain "later removed" label), not
 *    fabricated.
 *  - "Return to an earlier revision" (§13 C-03 / PD-008's OI-1) is not
 *    implemented — no restore action exists in the domain layer, and
 *    adding one is backend behaviour outside this commission's UI-only
 *    scope. Compare is read-only for this delivery.
 *
 * `PO-U07.X.1-001` (2026-07-29) — premium workspace presentation pass, on
 * top of everything above, without touching any of it: a workspace
 * summary header, a session timeline (`DerivePlannerTimeline` — read-only,
 * derived entirely from persisted `PlannerRegenerationEvent`/session data,
 * never a new audit subsystem), an "Analysis Inputs & Platform Readiness"
 * panel driven by `config/slipguard-planner-capabilities.php` (strictly
 * separates what genuinely runs today from named future product
 * direction — no capability is ever shown as active unless it really is),
 * and a "what SlipGuard just did" milestone disclosure that only lists
 * steps this codebase's own evaluation pipeline genuinely performs. No
 * fake delays anywhere — every milestone list renders already-complete
 * once the real Livewire round-trip has finished, never staggered with
 * invented per-step timing.
 */
new #[Layout('layouts.app')] class extends Component
{
    public PlannerSession $plannerSession;

    public bool $showAddForm = false;

    public ?int $compareRevisionId = null;

    /** @var array<string, string> */
    public array $newLeg = [
        'sport' => '',
        'competition' => '',
        'event_name' => '',
        'market_name' => '',
        'selection_name' => '',
        'decimal_odds' => '',
    ];

    public function mount(PlannerSession $plannerSession): void
    {
        $this->authorize('view', $plannerSession);

        $this->plannerSession = $plannerSession->load([
            'selections', 'regenerationEvents', 'sourceBettingSlip', 'exportedBettingSlip',
        ]);
    }

    public function isEditable(): bool
    {
        return $this->plannerSession->status->canTransitionTo(PlannerSessionStatus::Evaluated);
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

    public function toggleAddForm(): void
    {
        $this->showAddForm = ! $this->showAddForm;
    }

    public function toggleLock(int $selectionId): void
    {
        $this->authorize('update', $this->plannerSession);

        $selection = $this->plannerSession->selections()->findOrFail($selectionId);

        (new ToggleSelectionLock)->execute($selection);

        $this->plannerSession->refresh();
    }

    /**
     * A UI-level safeguard, not a domain rule: RemovePlannerSelection has
     * no minimum-leg guard, so this prevents reaching an unspecified
     * zero-selection state rather than relying on backend behaviour no
     * one designed for.
     */
    public function removeSelection(int $selectionId): void
    {
        $this->authorize('update', $this->plannerSession);

        if (! $this->isEditable() || $this->plannerSession->selections()->count() <= 1) {
            return;
        }

        $selection = $this->plannerSession->selections()->findOrFail($selectionId);

        (new RemovePlannerSelection)->execute($selection);

        $this->plannerSession->refresh();

        if ($this->compareRevisionId) {
            $this->compareRevisionId = null;
        }
    }

    public function addSelection(): void
    {
        $this->authorize('update', $this->plannerSession);

        if (! $this->isEditable()) {
            return;
        }

        $rules = collect(BettingSlipValidationRules::legRules())
            ->mapWithKeys(fn ($rule, $field) => ["newLeg.{$field}" => $rule])
            ->all();

        $validated = $this->validate($rules);

        (new AddPlannerSelection)->execute($this->plannerSession, $validated['newLeg']);

        $this->plannerSession->refresh();
        $this->reset('newLeg');
        $this->showAddForm = false;
    }

    public function confirmSelection(): void
    {
        $this->authorize('update', $this->plannerSession);

        (new CompletePlannerSession)->execute($this->plannerSession);

        $this->plannerSession->refresh();
    }

    public function exportSelection(): void
    {
        $this->authorize('update', $this->plannerSession);

        (new ExportPlannerSession)->execute($this->plannerSession);

        $this->plannerSession->refresh();
    }

    public function abandonSession(): void
    {
        $this->authorize('update', $this->plannerSession);

        (new AbandonPlannerSession)->execute($this->plannerSession);

        session()->flash('status', __('Planning session abandoned. Your original slip is unaffected.'));

        $this->redirect(route('analyze'), navigate: true);
    }

    public function compareWith(?int $revisionId): void
    {
        $this->compareRevisionId = $revisionId ?: null;
    }

    private function latestEvent(): ?PlannerRegenerationEvent
    {
        return $this->plannerSession->regenerationEvents->last();
    }

    public function comparisonEvent(): ?PlannerRegenerationEvent
    {
        if (! $this->compareRevisionId) {
            return null;
        }

        return $this->plannerSession->regenerationEvents->firstWhere('id', $this->compareRevisionId);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function rankedAttributions(?PlannerRegenerationEvent $event = null): Collection
    {
        $event ??= $this->latestEvent();

        if (! $event) {
            return collect();
        }

        return collect($event->attributions)->sortBy('rank')->values();
    }

    public function primaryFinding(?PlannerRegenerationEvent $event = null): ?array
    {
        return $this->rankedAttributions($event)->firstWhere('rank', 1);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function otherAttributions(?PlannerRegenerationEvent $event = null): Collection
    {
        return $this->rankedAttributions($event)->reject(fn (array $a) => $a['rank'] === 1)->values();
    }

    /**
     * @param  array<string, mixed>  $attribution
     * @return array{label: string, odds: string, available: bool}
     */
    public function describeAttribution(array $attribution): array
    {
        $selection = $this->plannerSession->selections->firstWhere('id', $attribution['planner_selection_id']);

        if ($selection) {
            return [
                'label' => "{$selection->event_name} — {$selection->selection_name}",
                'odds' => (string) $selection->decimal_odds,
                'available' => true,
            ];
        }

        return [
            'label' => __('A selection that was later removed'),
            'odds' => $attribution['decimal_odds'],
            'available' => false,
        ];
    }

    /**
     * `PO-U07.X.1-001` §10: null when removing the leg wouldn't change the
     * band at all — the prior copy ("...move from Low to Low") read as a
     * bug even though it was mathematically correct.
     */
    public function bandChangeSentence(?RiskBand $currentBand, ?string $bandWithoutLegValue): ?string
    {
        if (! $bandWithoutLegValue) {
            return null;
        }

        $without = RiskBand::from($bandWithoutLegValue);

        if ($currentBand === $without) {
            return null;
        }

        return __('Without this selection, the slip\'s band would move from :current to :without.', [
            'current' => $currentBand?->label(),
            'without' => $without->label(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function timeline(): array
    {
        return (new DerivePlannerTimeline)->derive($this->plannerSession);
    }

    /** @return array{active: array<int, array<string, mixed>>, planned: array<int, array<string, mixed>>} */
    public function capabilities(): array
    {
        return (new PlannerCapabilities)->grouped();
    }

    public function ruleSetVersion(): ?string
    {
        return (new PlannerCapabilities)->ruleSetVersion();
    }

    /** @return array<string, mixed> */
    public function workspaceSummary(): array
    {
        $event = $this->latestEvent();
        $primary = $event ? $this->primaryFinding($event) : null;
        $primaryLabel = $primary ? $this->describeAttribution($primary)['label'] : null;

        return [
            'revision' => $event?->sequence_number,
            'structuralScore' => $event?->structural_score,
            'riskBand' => $event?->risk_band,
            'primaryLabel' => $primaryLabel,
            'selectionsCount' => $this->plannerSession->selections->count(),
            'keptCount' => $this->plannerSession->selections->filter(fn ($s) => $s->isLocked())->count(),
            'revisionsCount' => $this->plannerSession->regenerationEvents->count(),
            'ruleSetVersion' => $this->ruleSetVersion(),
            'lastRecalculated' => $event?->created_at,
        ];
    }

    public function with(): array
    {
        return [
            'latestEvent' => $this->latestEvent(),
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="container-analytics workspace-gutter mx-auto">

        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-semibold text-neutral-900">{{ __('Planner Workspace') }}</h1>
                <p class="mt-1 text-sm text-neutral-500">
                    {{ __('Status: :status', ['status' => $plannerSession->status->label()]) }}
                </p>
            </div>
            <a href="{{ route('analyze') }}" wire:navigate class="text-sm font-medium text-neutral-600 hover:text-neutral-900 shrink-0">
                {{ __('Back to Workspace') }}
            </a>
        </div>

        @if (session('status'))
            <x-alert variant="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        @if (! in_array($plannerSession->status, [PlannerSessionStatus::Abandoned, PlannerSessionStatus::Exported], true))
            {{--
                PO-U07.X.1-001 §6/§7 — a premium workspace summary, real
                values only. "Evidence platform: planned" (not "verified")
                since no provider evidence is connected to this workspace.
            --}}
            @php $summary = $this->workspaceSummary(); @endphp
            <div class="workspace-primary-card mt-6 rounded-lg p-5 sm:p-6" data-reveal>
                <dl class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-4 gap-y-5">
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Revision') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">{{ $summary['revision'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Structural score') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">{{ $summary['structuralScore'] !== null ? $summary['structuralScore'].'/100' : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Risk band') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $summary['riskBand']?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Selections') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">{{ $summary['selectionsCount'] }} <span class="font-normal text-neutral-500">{{ __(':kept kept', ['kept' => $summary['keptCount']]) }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Rule set') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $summary['ruleSetVersion'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Original slip') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ __('Preserved') }}</dd>
                    </div>
                </dl>
                @if ($summary['lastRecalculated'])
                    <p class="mt-4 text-xs text-neutral-500">{{ __('Last recalculated :time', ['time' => $summary['lastRecalculated']->diffForHumans()]) }}</p>
                @endif
            </div>
        @endif

        @if ($plannerSession->status === \App\Domain\Planner\PlannerSessionStatus::Abandoned)
            {{-- §25 state 5: reached only via stale navigation back to an ended session — a plain notice, not an error. --}}
            <div class="mt-8 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6 text-center">
                <p class="text-sm text-neutral-700">{{ __('This planning session has ended.') }}</p>
                <a href="{{ route('analyze') }}" wire:navigate class="mt-3 inline-block text-sm font-semibold text-accent-strong hover:text-accent">
                    {{ __('Back to Workspace') }}
                </a>
            </div>
        @elseif ($plannerSession->status === \App\Domain\Planner\PlannerSessionStatus::Exported)
            {{--
                Frame X-02: two equal-weight artifacts, chronological order
                only, no "old vs new" hierarchy. PO-U07.X.1-001 §14/finding
                3.1.4 — a modest, non-celebratory acknowledgment (a
                checkmark accent, clearer completion language) rather than
                the previous plain, visually flat text-only cards. Never a
                celebratory/gamified treatment — no confetti, no
                "Congratulations!", per DESIGN_LANGUAGE.md's Brand Tone.
            --}}
            <div class="mt-8 space-y-6" data-reveal>
                <div class="bg-surface-card shadow-elevation-2 border border-neutral-200/60 rounded-lg p-6 text-center">
                    <x-heroicon-o-check-circle class="size-8 mx-auto text-accent-strong" aria-hidden="true" />
                    <p class="mt-2 text-sm font-semibold text-neutral-900">{{ __('Planning complete') }}</p>
                    <p class="mt-1 text-sm text-neutral-500">{{ __('Planner history retained — this session stays in your Planning History.') }}</p>
                </div>
                <div class="bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-lock-closed class="size-4 text-neutral-400" aria-hidden="true" />
                        <p class="text-sm font-semibold text-neutral-900">{{ __('Original slip — preserved') }}</p>
                    </div>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Unchanged, exactly as you entered it.') }}</p>
                    <a href="{{ route('analyze.edit', $plannerSession->sourceBettingSlip) }}" wire:navigate
                       class="mt-3 inline-block text-sm font-semibold text-accent-strong hover:text-accent">
                        {{ __('View original slip') }}
                    </a>
                </div>
                <div class="bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-check class="size-4 text-accent-strong" aria-hidden="true" />
                        <p class="text-sm font-semibold text-neutral-900">{{ __('New betting slip — created') }}</p>
                    </div>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('The selection you confirmed, exported as its own independent slip — ready for its next supported action.') }}</p>
                    <a href="{{ route('analyze.edit', $plannerSession->exportedBettingSlip) }}" wire:navigate
                       class="mt-3 inline-block text-sm font-semibold text-accent-strong hover:text-accent">
                        {{ __('View new slip') }}
                    </a>
                </div>
                <a href="{{ route('analyze') }}" wire:navigate
                   class="inline-flex items-center justify-center h-12 px-6 rounded-md bg-gradient-button text-white font-semibold text-sm hover:brightness-110">
                    {{ __('Back to Workspace') }}
                </a>
            </div>
        @else
            <div class="mt-8 space-y-10">

                {{-- Frame L-01: Original Slip Reference Panel, collapsed by default. --}}
                <section x-data="{ open: false }" aria-labelledby="original-slip-heading">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="original-slip-panel"
                            class="w-full flex items-center justify-between text-start bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-5 text-sm font-medium text-neutral-700 hover:bg-neutral-100">
                        <span id="original-slip-heading" class="inline-flex items-center gap-2">
                            <x-heroicon-o-lock-closed class="size-4" aria-hidden="true" />
                            {{ __('Your original slip') }}
                        </span>
                        <x-heroicon-o-chevron-down class="size-4 shrink-0 transition-transform" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true" />
                    </button>
                    <div id="original-slip-panel" x-show="open" x-cloak class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                        <p class="text-sm text-neutral-600">
                            {{ __("This slip stays exactly as you entered it. SlipGuard never changes it — you're working on a separate copy below.") }}
                        </p>
                        <ul class="mt-4 space-y-2 divide-y divide-neutral-200">
                            @foreach ($plannerSession->sourceBettingSlip->legs as $leg)
                                <li class="pt-2 first:pt-0 text-sm text-neutral-700">
                                    {{ $leg->event_name }} — {{ $leg->selection_name }}
                                    <span class="text-neutral-500">({{ $leg->decimal_odds }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>

                {{--
                    PO-U07.X.1-001 §6/§12 — deliberately not titled "Evidence
                    Used": most listed capabilities are planned, not active,
                    so a title implying they were all used would be untrue.
                    Collapsed by default, matching the original-slip panel's
                    own progressive-disclosure pattern.
                --}}
                @php $capabilities = $this->capabilities(); @endphp
                <section x-data="{ open: false }" aria-labelledby="capabilities-heading">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="capabilities-panel"
                            class="w-full flex items-center justify-between text-start bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-5 text-sm font-medium text-neutral-700 hover:bg-neutral-100">
                        <span id="capabilities-heading" class="inline-flex items-center gap-2">
                            <x-heroicon-o-information-circle class="size-4" aria-hidden="true" />
                            {{ __('Analysis Inputs & Platform Readiness') }}
                        </span>
                        <x-heroicon-o-chevron-down class="size-4 shrink-0 transition-transform" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true" />
                    </button>
                    <div id="capabilities-panel" x-show="open" x-cloak class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6 space-y-6">
                        <p class="text-sm text-neutral-600">
                            {{ __('Current analysis uses the selections you entered, the normalised slip structure, the accepted deterministic rules and the persisted revision results.') }}
                        </p>
                        <div>
                            <h3 class="text-xs font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Active now') }}</h3>
                            <ul class="mt-2 space-y-2">
                                @foreach ($capabilities['active'] as $capability)
                                    <li class="flex items-start gap-2 text-sm text-neutral-700">
                                        <x-heroicon-o-check-circle class="size-4 shrink-0 mt-0.5 text-accent-strong" aria-hidden="true" />
                                        <span><span class="font-medium text-neutral-900">{{ $capability['label'] }}</span> — {{ $capability['description'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Planned — not yet connected') }}</h3>
                            <p class="mt-1 text-xs text-neutral-500">
                                {{ __('SlipGuard is being prepared to combine relevant provider evidence with its own deterministic intelligence. These are not active in this workspace yet.') }}
                            </p>
                            <ul class="mt-2 space-y-2">
                                @foreach ($capabilities['planned'] as $capability)
                                    <li class="flex items-start gap-2 text-sm text-neutral-500">
                                        <x-heroicon-o-clock class="size-4 shrink-0 mt-0.5 text-neutral-400" aria-hidden="true" />
                                        <span><span class="font-medium text-neutral-600">{{ $capability['label'] }}</span> — {{ $capability['description'] }} <span class="italic">{{ __('(Planned)') }}</span></span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>

                @if (! $latestEvent)
                    <div class="bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6 text-center" role="status">
                        <p class="text-sm text-neutral-600">{{ __('Preparing your first evaluation…') }}</p>
                    </div>
                @elseif ($latestEvent->availability === AnalysisAvailability::Unavailable)
                    <section class="bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                        <p class="text-sm text-neutral-700">
                            {{ __("SlipGuard can't evaluate the structural risk of your current selection yet — check that every selection has a recognised sport and market.") }}
                        </p>
                    </section>
                @else
                    {{-- Frame P-01: Overall Structural Risk, this Revision. --}}
                    <section aria-labelledby="revision-risk-heading">
                        <h2 id="revision-risk-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">
                            {{ __('Revision :n', ['n' => $latestEvent->sequence_number]) }}
                        </h2>
                        <div class="mt-3 bg-surface-card shadow-elevation-2 border border-neutral-200/60 rounded-lg p-6 sm:p-8">
                            <div wire:loading.flex wire:target="toggleLock,removeSelection,addSelection" class="items-center gap-2 text-sm text-neutral-500" role="status">
                                <x-heroicon-o-arrow-path class="size-4 animate-spin" aria-hidden="true" />
                                {{ __('Recalculating your revision…') }}
                            </div>
                            <div wire:loading.remove wire:target="toggleLock,removeSelection,addSelection">
                                @php $token = $this->riskBandToken($latestEvent->risk_band); @endphp
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1.5 text-base font-semibold px-4 py-2 rounded-lg border text-risk-{{ $token }} bg-risk-{{ $token }}/10 border-risk-{{ $token }}/30">
                                        <x-heroicon-o-exclamation-triangle class="size-5" aria-hidden="true" />
                                        {{ $latestEvent->risk_band?->label() }}
                                    </span>
                                    <span class="text-2xl font-semibold text-neutral-900 [font-variant-numeric:tabular-nums]">
                                        {{ $latestEvent->structural_score }}<span class="text-sm font-normal text-neutral-500">/100</span>
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-neutral-500">
                                    {{ __('Updated :time', ['time' => $latestEvent->created_at->diffForHumans()]) }}
                                </p>

                                {{--
                                    PO-U07.X.1-001 §9: every milestone maps to
                                    a real step EvaluatePlannerSession
                                    genuinely performs — no fake per-step
                                    delay, rendered already-complete since the
                                    real round-trip has already finished by
                                    the time this is visible.
                                --}}
                                <div x-data="{ open: false }" class="mt-4">
                                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="revision-milestones"
                                            class="text-xs font-medium text-accent-strong hover:text-accent">
                                        <span x-show="!open">{{ __('What SlipGuard just did') }}</span>
                                        <span x-show="open" x-cloak>{{ __('Hide') }}</span>
                                    </button>
                                    <ul id="revision-milestones" x-show="open" x-cloak class="mt-2 space-y-1 text-xs text-neutral-500">
                                        @foreach ([
                                            __('Current selection set received'),
                                            __('Selection changes validated'),
                                            __('Slip structure prepared'),
                                            __('Deterministic rule set loaded'),
                                            __('Structural relationships calculated'),
                                            __('Weakest-leg contribution ranked'),
                                            __('Explainability prepared'),
                                            __('Revision persisted'),
                                        ] as $milestone)
                                            <li class="flex items-center gap-1.5">
                                                <x-heroicon-o-check class="size-3.5 shrink-0 text-accent-strong" aria-hidden="true" />
                                                {{ $milestone }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Frame P-02: Primary Finding. --}}
                    @if ($primary = $this->primaryFinding())
                        @php
                            $described = $this->describeAttribution($primary);
                            $primarySelection = $plannerSession->selections->firstWhere('id', $primary['planner_selection_id']);
                            $primaryLocked = $primarySelection?->isLocked() ?? false;
                        @endphp
                        <section aria-labelledby="primary-finding-heading">
                            <h2 id="primary-finding-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Primary finding') }}</h2>
                            <div class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                                <p class="text-sm font-semibold text-neutral-900">{{ $described['label'] }} <span class="text-neutral-500 font-normal">({{ $described['odds'] }})</span></p>
                                <p class="mt-1 text-sm text-neutral-600">
                                    {{ __("This selection currently contributes the most to your slip's structural risk.") }}
                                </p>
                                @if ($bandChange = $this->bandChangeSentence($latestEvent->risk_band, $primary['band_without_leg']))
                                    <p class="mt-2 text-sm text-neutral-600">{{ $bandChange }}</p>
                                @endif
                                <p class="mt-3 text-xs text-neutral-500">
                                    {{ __('SlipGuard explains the structural effect. You decide what to keep, review or remove.') }}
                                </p>

                                @if ($primarySelection && $this->isEditable())
                                    @if ($primaryLocked)
                                        <p class="mt-4 text-sm font-medium text-accent-strong">{{ __('Selection kept') }}</p>
                                        <button type="button" wire:click="toggleLock({{ $primarySelection->id }})" class="mt-1 text-sm font-medium text-neutral-600 hover:text-neutral-900">
                                            {{ __('Stop keeping this selection') }}
                                        </button>
                                    @else
                                        <div class="mt-4 flex items-center gap-4">
                                            <button type="button" wire:click="toggleLock({{ $primarySelection->id }})" class="text-sm font-medium text-neutral-700 hover:text-neutral-900">
                                                {{ __('Keep this selection') }}
                                            </button>
                                            @if ($plannerSession->selections->count() > 1)
                                                <button type="button" wire:click="removeSelection({{ $primarySelection->id }})" class="text-sm font-medium text-red-600 hover:text-red-800">
                                                    {{ __('Remove') }}
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </section>
                    @endif

                    {{-- Frame P-03/P-04: remaining ranked legs, expandable detail. --}}
                    @if ($this->otherAttributions()->isNotEmpty())
                        <section aria-labelledby="other-legs-heading">
                            <h2 id="other-legs-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Your other selections') }}</h2>
                            <div class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg divide-y divide-neutral-200">
                                @foreach ($this->otherAttributions() as $attribution)
                                    @php
                                        $selection = $plannerSession->selections->firstWhere('id', $attribution['planner_selection_id']);
                                        $described = $this->describeAttribution($attribution);
                                        $locked = $selection?->isLocked() ?? false;
                                    @endphp
                                    <div wire:key="attribution-{{ $attribution['planner_selection_id'] }}" x-data="{ open: false }" class="p-5">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-neutral-900 break-words sm:truncate">{{ $described['label'] }}</p>
                                                <p class="text-xs text-neutral-500">{{ __('Contributes less structural risk than your primary finding.') }}</p>
                                            </div>
                                            @if ($selection && $this->isEditable())
                                                <div class="flex items-center gap-3 shrink-0">
                                                    <button type="button" wire:click="toggleLock({{ $selection->id }})" class="text-xs font-medium {{ $locked ? 'text-accent-strong' : 'text-neutral-600 hover:text-neutral-900' }}">
                                                        {{ $locked ? __('Kept') : __('Keep this selection') }}
                                                    </button>
                                                    @if (! $locked && $plannerSession->selections->count() > 1)
                                                        <button type="button" wire:click="removeSelection({{ $selection->id }})" class="text-xs font-medium text-red-600 hover:text-red-800">
                                                            {{ __('Remove') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" @click="open = !open" class="mt-2 text-xs font-medium text-accent-strong hover:text-accent">
                                            <span x-show="!open">{{ __('Show detail') }}</span>
                                            <span x-show="open" x-cloak>{{ __('Hide detail') }}</span>
                                        </button>
                                        <dl x-show="open" x-cloak class="mt-2 text-xs text-neutral-600 space-y-1">
                                            <div><dt class="inline text-neutral-500">{{ __('Odds') }}:</dt> <dd class="inline">{{ $described['odds'] }}</dd></div>
                                            <div><dt class="inline text-neutral-500">{{ __('Market complexity') }}:</dt> <dd class="inline">{{ ucfirst($attribution['market_complexity']) }}</dd></div>
                                        </dl>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Frame R-03: Add a replacement selection — customer types it, never suggested. --}}
                    @if ($this->isEditable())
                        <section aria-labelledby="add-selection-heading">
                            <h2 id="add-selection-heading" class="sr-only">{{ __('Add a replacement selection') }}</h2>
                            <button type="button" wire:click="toggleAddForm"
                                    class="text-sm font-semibold text-accent-strong hover:text-accent">
                                {{ __('+ Add a replacement selection') }}
                            </button>

                            @if ($showAddForm)
                                <form wire:submit="addSelection" class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-5 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="new-sport" :value="__('Sport')" />
                                            <x-text-input wire:model="newLeg.sport" id="new-sport" type="text" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('newLeg.sport')" />
                                        </div>
                                        <div>
                                            <x-input-label for="new-competition" :value="__('Competition (optional)')" />
                                            <x-text-input wire:model="newLeg.competition" id="new-competition" type="text" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('newLeg.competition')" />
                                        </div>
                                        <div>
                                            <x-input-label for="new-event" :value="__('Event')" />
                                            <x-text-input wire:model="newLeg.event_name" id="new-event" type="text" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('newLeg.event_name')" />
                                        </div>
                                        <div>
                                            <x-input-label for="new-market" :value="__('Market')" />
                                            <x-text-input wire:model="newLeg.market_name" id="new-market" type="text" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('newLeg.market_name')" />
                                        </div>
                                        <div>
                                            <x-input-label for="new-selection" :value="__('Selection')" />
                                            <x-text-input wire:model="newLeg.selection_name" id="new-selection" type="text" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('newLeg.selection_name')" />
                                        </div>
                                        <div>
                                            <x-input-label for="new-odds" :value="__('Decimal odds')" />
                                            <x-text-input wire:model="newLeg.decimal_odds" id="new-odds" type="text" inputmode="decimal" class="mt-1 block w-full" />
                                            <x-input-error class="mt-2" :messages="$errors->get('newLeg.decimal_odds')" />
                                        </div>
                                    </div>
                                    <x-primary-button wire:loading.attr="disabled" wire:target="addSelection" class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                                        {{ __('Add selection') }}
                                    </x-primary-button>
                                </form>
                            @endif
                        </section>
                    @endif

                    {{-- Frame C-01/C-02: Compare Revisions — strictly customer-pulled, never auto-surfaced (U-06.4 §31). --}}
                    @if ($plannerSession->regenerationEvents->count() > 1)
                        <section aria-labelledby="compare-heading">
                            <h2 id="compare-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Compare revisions') }}</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($plannerSession->regenerationEvents as $event)
                                    @if ($event->id !== $latestEvent->id)
                                        <button type="button" wire:click="compareWith({{ $compareRevisionId === $event->id ? 'null' : $event->id }})"
                                                class="text-xs font-medium px-3 py-1.5 rounded-full border {{ $compareRevisionId === $event->id ? 'border-accent-strong text-accent-strong bg-accent-strong/10' : 'border-neutral-300 text-neutral-600 hover:bg-neutral-100' }}">
                                            {{ __('Compare with Revision :n', ['n' => $event->sequence_number]) }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>

                            @if ($comparison = $this->comparisonEvent())
                                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @foreach ([$comparison, $latestEvent] as $side)
                                        @php $sideToken = $this->riskBandToken($side->risk_band); @endphp
                                        <div class="bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-5">
                                            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide">
                                                {{ __('Revision :n', ['n' => $side->sequence_number]) }}
                                                @if ($side->id === $latestEvent->id) &middot; {{ __('current') }} @endif
                                            </p>
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1 text-sm font-semibold px-2.5 py-1 rounded-md border text-risk-{{ $sideToken }} bg-risk-{{ $sideToken }}/10 border-risk-{{ $sideToken }}/30">
                                                    {{ $side->risk_band?->label() }}
                                                </span>
                                                <span class="text-sm font-semibold text-neutral-900">{{ $side->structural_score }}/100</span>
                                            </div>
                                            <ul class="mt-3 space-y-1.5 divide-y divide-neutral-200">
                                                @foreach ($this->rankedAttributions($side) as $attribution)
                                                    @php $d = $this->describeAttribution($attribution); @endphp
                                                    <li class="pt-1.5 first:pt-0 text-xs {{ $d['available'] ? 'text-neutral-700' : 'text-neutral-400 italic' }}">
                                                        {{ $d['label'] }}
                                                        @if ($attribution['rank'] === 1) <span class="text-neutral-500">({{ __('primary finding') }})</span> @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-3 text-xs text-neutral-500">
                                    {{ __('Revision :a\'s overall band is :bandA; Revision :b\'s was :bandB.', ['a' => $latestEvent->sequence_number, 'bandA' => $latestEvent->risk_band?->label(), 'b' => $comparison->sequence_number, 'bandB' => $comparison->risk_band?->label()]) }}
                                </p>
                            @endif
                        </section>
                    @endif
                @endif

                {{--
                    PO-U07.X.1-001 §8/§9/§12 — unifies "Revision Timeline"
                    and "Session Activity" into one truthful list, since
                    both would otherwise read from the exact same persisted
                    data (there is no separate audit-log model). Every
                    entry comes from DerivePlannerTimeline — nothing here
                    is invented.
                --}}
                <section aria-labelledby="timeline-heading">
                    <h2 id="timeline-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Session timeline') }}</h2>
                    {{--
                        A real, found-and-fixed contrast bug: this was
                        initially the only section without an opaque
                        `bg-surface-card` background — in dark theme, the
                        fixed atmospheric layer (MOTION_SYSTEM.md) sits
                        directly behind it with nothing blocking it, washing
                        out the text's contrast even though the colour
                        token itself was correct (confirmed via
                        getComputedStyle during browser verification).
                        Every other section on this screen already uses
                        `bg-surface-card`; this one now matches.
                    --}}
                    <div class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-5 sm:p-6">
                        <ol class="space-y-0">
                            @foreach ($this->timeline() as $entry)
                                <li class="relative pl-6 pb-5 last:pb-0 border-l border-neutral-200 last:border-transparent">
                                    <span class="absolute -left-[5px] top-1 size-2.5 rounded-full bg-accent-strong"></span>
                                    <p class="text-sm font-medium text-neutral-900">{{ $entry['label'] }}</p>
                                    @if (($entry['type'] ?? null) === 'revision')
                                        <p class="text-xs text-neutral-500">
                                            {{ $entry['risk_band']?->label() }} · {{ $entry['structural_score'] }}/100
                                        </p>
                                        @if (! empty($entry['detail']['added']))
                                            <p class="mt-1 text-xs text-neutral-600">{{ __('Added:') }} {{ implode(', ', $entry['detail']['added']) }}</p>
                                        @endif
                                        @if (! empty($entry['detail']['removed']))
                                            <p class="mt-1 text-xs text-neutral-600">{{ __('Removed:') }} {{ implode(', ', $entry['detail']['removed']) }}</p>
                                        @endif
                                    @endif
                                    @if ($entry['occurred_at'])
                                        <p class="mt-1 text-xs text-neutral-400">{{ $entry['occurred_at']->diffForHumans() }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>

                {{--
                    Frame A-01/A-02, X-01: Confirm and Export.
                    PO-U07.X.1-001 §1/finding 3.1.1 — these previously
                    hand-rolled a flat `bg-accent-strong` fill, predating
                    the founder's 2026-07-27 gradient-button standard
                    (primary-button.blade.php). Now reuse the same button
                    components the rest of the workspace already does.
                --}}
                <section aria-label="{{ __('Session actions') }}" class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-neutral-200">
                    @if ($plannerSession->status === PlannerSessionStatus::Evaluated || $plannerSession->status === PlannerSessionStatus::Complete)
                        <x-primary-button type="button" wire:click="confirmSelection" wire:loading.attr="disabled" wire:target="confirmSelection"
                                class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                            {{ $plannerSession->status === PlannerSessionStatus::Complete ? __('Confirmed') : __('Confirm this selection') }}
                        </x-primary-button>
                    @endif
                    @if ($plannerSession->status === PlannerSessionStatus::Complete)
                        <x-secondary-button wire:click="exportSelection" wire:loading.attr="disabled" wire:target="exportSelection"
                                class="!h-12 !px-6 !text-sm !normal-case !tracking-normal">
                            {{ __('Create a betting slip from this selection') }}
                        </x-secondary-button>
                    @endif
                    <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'abandon-planner-session')"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium text-red-600 hover:text-red-800">
                        {{ __('Abandon this session') }}
                    </button>
                </section>

                @if ($plannerSession->status === PlannerSessionStatus::Complete)
                    <p class="text-xs text-neutral-500 -mt-6">{{ __('You can keep changing your selections — confirming again will update this.') }}</p>
                @endif
            </div>

            <x-modal name="abandon-planner-session" focusable>
                <div class="p-6">
                    <h2 class="text-lg font-medium text-neutral-900">{{ __('Abandon this planning session?') }}</h2>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Your original slip is completely unaffected either way.') }}</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                        <x-danger-button wire:click="abandonSession" x-on:click="$dispatch('close')">{{ __('Abandon session') }}</x-danger-button>
                    </div>
                </div>
            </x-modal>
        @endif
    </div>
</div>
