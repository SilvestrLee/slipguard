<?php

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Domain\BettingSlip\BettingSlipValidationRules;
use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Normalization\NormalizeFootballMarket;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Exceptions\BettingSlipLockedByPlannerException;
use App\Exceptions\InvalidBettingSlipTransitionException;
use App\Models\BettingSlip;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?BettingSlip $bettingSlip = null;

    public string $name = '';

    /** @var array<int, array<string, mixed>> */
    public array $legs = [];

    /**
     * A stable per-leg identifier, independent of array position — reorder
     * and remove operations move a leg's whole array (including this key)
     * together, so collapse state below never desyncs from the wrong leg
     * the way an index-keyed lookup could after a reorder.
     */
    public int $nextLegUid = 0;

    /**
     * `_uid` values of legs currently shown collapsed. U-19.1 Interaction
     * Cost Reduction: complete legs start collapsed (both on load and
     * whenever a new leg is added) so the form stays short — never on the
     * leg actively being filled in.
     *
     * @var array<int, int>
     */
    public array $collapsedLegUids = [];

    public function mount(?BettingSlip $bettingSlip = null): void
    {
        if ($bettingSlip) {
            $this->authorize('update', $bettingSlip);

            $this->bettingSlip = $bettingSlip;
            $this->name = (string) $bettingSlip->name;
            $this->legs = $bettingSlip->legs->map(function ($leg) {
                // U-19 Minimal Manual Input: re-derive the Total Goals /
                // Correct Score guided sub-fields from previously saved free
                // text via the real domain normalizer (not a re-implementation
                // of it), so editing an older slip doesn't lose data the
                // guided controls would otherwise show blank.
                $normalized = (new NormalizeFootballMarket)->normalize((string) $leg->market_name, (string) $leg->selection_name);
                $facts = $normalized->selectionFacts;
                $uid = $this->nextLegUid++;

                $legArray = [
                    'sport' => $leg->sport ?: 'Football',
                    'competition' => $leg->competition,
                    'event_name' => $leg->event_name,
                    'market_name' => $leg->market_name,
                    'selection_name' => $leg->selection_name,
                    'decimal_odds' => (string) $leg->decimal_odds,
                    'total_goals_direction' => $facts['direction'] ?? '',
                    'total_goals_line' => $facts['line'] ?? '',
                    'correct_score_home' => $facts['score_home'] ?? '',
                    'correct_score_away' => $facts['score_away'] ?? '',
                    '_uid' => $uid,
                ];

                if (BettingSlipValidationRules::legIsComplete($legArray)) {
                    $this->collapsedLegUids[] = $uid;
                }

                return $legArray;
            })->all();
        } else {
            $this->authorize('create', BettingSlip::class);
        }

        if (empty($this->legs)) {
            $this->addLeg();
        }
    }

    /**
     * U-19 Minimal Manual Input (`PO-UX-001`, scoped implementation): the
     * canonical football market list, straight from the already-approved
     * taxonomy — not a new enum. Option values are each market's own
     * normalized alias (guaranteed to round-trip through
     * `NormalizeFootballMarket` back to the same family), with one cosmetic
     * fix: "Half-Time / Full-Time" has spaces around its slash that its own
     * alias list doesn't, so they're stripped for the stored value only —
     * the visible label is untouched.
     *
     * @return array<string, string> option value => display label
     */
    public function marketOptions(): array
    {
        $options = [];

        foreach ((new FootballMarketTaxonomyV1)->definitions() as $definition) {
            $options[str_replace(' / ', '/', $definition->displayName)] = $definition->displayName;
        }

        return $options;
    }

    /**
     * Resolves the currently chosen market to its family using the real
     * normalizer, so the guided Selection control shown below always agrees
     * with what analysis will actually do with this leg — never a
     * second, drifting copy of that logic.
     */
    public function marketFamilyFor(int $index): ?MarketFamily
    {
        $marketName = $this->legs[$index]['market_name'] ?? '';

        if (blank($marketName)) {
            return null;
        }

        return (new NormalizeFootballMarket)->normalize($marketName, '')->marketFamily;
    }

    /**
     * Which Selection control to render. Only the three market families
     * `NormalizeFootballMarket` actually parses structured facts for get a
     * guided control — every other family has no deterministic
     * selection-outcome taxonomy anywhere in this codebase yet, so it stays
     * free text rather than this component inventing one.
     */
    public function selectionInputMode(int $index): string
    {
        return match ($this->marketFamilyFor($index)) {
            MarketFamily::BothTeamsToScore => 'yes_no',
            MarketFamily::TotalGoals => 'over_under_line',
            MarketFamily::CorrectScore => 'correct_score',
            default => 'freetext',
        };
    }

    /**
     * Livewire's generic nested-property hook — the only way to react to an
     * arbitrary `legs.{index}.{field}` path, since Volt's `updated{Prop}()`
     * convention only matches top-level property names.
     */
    public function updated(string $name): void
    {
        if (! preg_match('/^legs\.(\d+)\.(.+)$/', $name, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        $field = $matches[2];

        if ($field === 'market_name') {
            // Changing the market invalidates whatever selection was chosen
            // for the previous one — always start the selection fresh rather
            // than risk silently keeping a mismatched value.
            $this->legs[$index]['selection_name'] = '';
            $this->legs[$index]['total_goals_direction'] = '';
            $this->legs[$index]['total_goals_line'] = '';
            $this->legs[$index]['correct_score_home'] = '';
            $this->legs[$index]['correct_score_away'] = '';

            return;
        }

        if (in_array($field, ['total_goals_direction', 'total_goals_line'], true)) {
            $this->syncTotalGoalsSelection($index);
        }

        if (in_array($field, ['correct_score_home', 'correct_score_away'], true)) {
            $this->syncCorrectScoreSelection($index);
        }
    }

    private function syncTotalGoalsSelection(int $index): void
    {
        $direction = $this->legs[$index]['total_goals_direction'] ?? '';
        $line = $this->legs[$index]['total_goals_line'] ?? '';

        $this->legs[$index]['selection_name'] = ($direction !== '' && $line !== '')
            ? ucfirst($direction).' '.$line.' Goals'
            : '';
    }

    private function syncCorrectScoreSelection(int $index): void
    {
        $home = $this->legs[$index]['correct_score_home'] ?? '';
        $away = $this->legs[$index]['correct_score_away'] ?? '';

        $this->legs[$index]['selection_name'] = ($home !== '' && $away !== '')
            ? $home.'-'.$away
            : '';
    }

    /**
     * U-19.1: a stepper-friendly adjustment for the Total Goals line —
     * always moves between the half-integer values ("2.5", "3.5", …) every
     * real over/under goals line actually uses, the same convention every
     * bookmaker's own line already follows, so a tap never lands on a
     * value `NormalizeFootballMarket` would treat as unusual. The line
     * remains a normal, directly-editable text input for any other value.
     */
    public function adjustTotalGoalsLine(int $index, int $direction): void
    {
        if ($this->readOnly()) {
            return;
        }

        $current = $this->legs[$index]['total_goals_line'] ?? '';
        $currentValue = is_numeric($current) ? (float) $current : null;

        $next = $currentValue === null
            ? 0.5
            : max(0.5, $currentValue + ($direction >= 0 ? 1.0 : -1.0));

        $formatted = rtrim(rtrim(number_format($next, 1, '.', ''), '0'), '.');
        $this->legs[$index]['total_goals_line'] = $formatted === '' ? '0.5' : $formatted;

        $this->syncTotalGoalsSelection($index);
    }

    public function adjustCorrectScoreHome(int $index, int $direction): void
    {
        $this->adjustCorrectScoreField($index, 'correct_score_home', $direction);
    }

    public function adjustCorrectScoreAway(int $index, int $direction): void
    {
        $this->adjustCorrectScoreField($index, 'correct_score_away', $direction);
    }

    private function adjustCorrectScoreField(int $index, string $field, int $direction): void
    {
        if ($this->readOnly()) {
            return;
        }

        $current = is_numeric($this->legs[$index][$field] ?? null) ? (int) $this->legs[$index][$field] : 0;
        $this->legs[$index][$field] = (string) max(0, min(9, $current + ($direction >= 0 ? 1 : -1)));

        $this->syncCorrectScoreSelection($index);
    }

    /**
     * U-19.1 leg summary: a single recognisable line for a complete leg,
     * shown when it's collapsed — the fixture, market, selection and odds
     * together, so scanning the whole slip never requires reopening a leg
     * that's already filled in correctly.
     */
    public function legSummaryLine(int $index): ?string
    {
        $leg = $this->legs[$index] ?? null;

        if (! $leg || ! BettingSlipValidationRules::legIsComplete($leg)) {
            return null;
        }

        return sprintf(
            '%s — %s: %s @ %s',
            $leg['event_name'],
            $leg['market_name'],
            $leg['selection_name'],
            $leg['decimal_odds'],
        );
    }

    public function legIsCollapsed(int $index): bool
    {
        $uid = $this->legs[$index]['_uid'] ?? $index;

        return in_array($uid, $this->collapsedLegUids, true);
    }

    public function toggleLegCollapse(int $index): void
    {
        $uid = $this->legs[$index]['_uid'] ?? $index;

        if (in_array($uid, $this->collapsedLegUids, true)) {
            $this->collapsedLegUids = array_values(array_diff($this->collapsedLegUids, [$uid]));
        } else {
            $this->collapsedLegUids[] = $uid;
        }
    }

    /**
     * U-19.1 "Duplicate Previous Leg": carries forward the fields that
     * usually repeat across legs on the same slip (sport, competition,
     * market, and — for the three guided families — the selection type)
     * while leaving the fixture, the specific selection, and the odds
     * blank, since those are almost always genuinely different per leg.
     * Never produces an exact duplicate leg outright, which
     * `NoDuplicateBettingSlipLegs` would reject unedited anyway.
     */
    public function duplicatePreviousLeg(): void
    {
        if ($this->readOnly() || count($this->legs) >= $this->maxLegs() || empty($this->legs)) {
            return;
        }

        $previous = $this->legs[array_key_last($this->legs)];

        // Nothing worth carrying forward from a still-blank leg — duplicating
        // it would be indistinguishable from plain "Add Selection".
        if (blank($previous['market_name'] ?? null)) {
            return;
        }

        $this->collapseCompleteLegs();

        $this->legs[] = [
            'sport' => $previous['sport'] ?: 'Football',
            'competition' => $previous['competition'] ?? '',
            'event_name' => '',
            'market_name' => $previous['market_name'] ?? '',
            'selection_name' => '',
            'decimal_odds' => '',
            'total_goals_direction' => $previous['total_goals_direction'] ?? '',
            'total_goals_line' => '',
            'correct_score_home' => '',
            'correct_score_away' => '',
            '_uid' => $this->nextLegUid++,
        ];
    }

    private function collapseCompleteLegs(): void
    {
        foreach ($this->legs as $i => $leg) {
            $uid = $leg['_uid'] ?? $i;

            if (BettingSlipValidationRules::legIsComplete($leg) && ! in_array($uid, $this->collapsedLegUids, true)) {
                $this->collapsedLegUids[] = $uid;
            }
        }
    }

    public function readOnly(): bool
    {
        return $this->bettingSlip !== null && ! $this->bettingSlip->isEditable();
    }

    public function maxLegs(): int
    {
        return config('slipguard.max_legs');
    }

    public function completedLegsCount(): int
    {
        return collect($this->legs)
            ->filter(fn (array $leg): bool => BettingSlipValidationRules::legIsComplete($leg))
            ->count();
    }

    public function combinedOddsPreview(): ?string
    {
        if (empty($this->legs)) {
            return null;
        }

        $combined = 1.0;

        foreach ($this->legs as $leg) {
            $odds = $leg['decimal_odds'] ?? null;

            if (! is_numeric($odds) || (float) $odds < 1.01) {
                return null;
            }

            $combined *= (float) $odds;
        }

        return number_format($combined, 2, '.', '');
    }

    public function addLeg(): void
    {
        if ($this->readOnly() || count($this->legs) >= $this->maxLegs()) {
            return;
        }

        // U-19.1: carry the previous leg's Competition forward — accumulators
        // frequently stack several selections from the same league, and this
        // is the one field genuinely likely to repeat verbatim. Sport isn't
        // "carried forward" so much as fixed (see the Sport field's own
        // note) — Football is the only value `NormalizeSport` supports.
        $previousCompetition = empty($this->legs) ? '' : (string) ($this->legs[array_key_last($this->legs)]['competition'] ?? '');

        $this->collapseCompleteLegs();

        $this->legs[] = [
            'sport' => 'Football',
            'competition' => $previousCompetition,
            'event_name' => '',
            'market_name' => '',
            'selection_name' => '',
            'decimal_odds' => '',
            'total_goals_direction' => '',
            'total_goals_line' => '',
            'correct_score_home' => '',
            'correct_score_away' => '',
            '_uid' => $this->nextLegUid++,
        ];
    }

    public function removeLeg(int $index): void
    {
        if ($this->readOnly()) {
            return;
        }

        unset($this->legs[$index]);

        $this->legs = array_values($this->legs);

        // Tidy up rather than let a removed leg's uid sit unused forever —
        // harmless either way since collapse state is looked up by uid, not
        // position, but there's no reason to keep it.
        $this->collapsedLegUids = array_values(array_intersect($this->collapsedLegUids, array_column($this->legs, '_uid')));
    }

    public function moveLegUp(int $index): void
    {
        if ($this->readOnly() || $index === 0) {
            return;
        }

        [$this->legs[$index - 1], $this->legs[$index]] = [$this->legs[$index], $this->legs[$index - 1]];
    }

    public function moveLegDown(int $index): void
    {
        if ($this->readOnly() || $index >= count($this->legs) - 1) {
            return;
        }

        [$this->legs[$index + 1], $this->legs[$index]] = [$this->legs[$index], $this->legs[$index + 1]];
    }

    public function save(SaveBettingSlip $action): void
    {
        if ($this->readOnly()) {
            session()->flash('error', __('This slip is locked and can no longer be edited.'));

            $this->redirect(route('analyze.edit', $this->bettingSlip), navigate: true);

            return;
        }

        if ($this->bettingSlip) {
            $this->authorize('update', $this->bettingSlip);
        } else {
            $this->authorize('create', BettingSlip::class);
        }

        $minLegs = config('slipguard.min_legs');
        $maxLegs = $this->maxLegs();

        $validated = $this->validate(
            BettingSlipValidationRules::slipRules($minLegs, $maxLegs),
            BettingSlipValidationRules::messages($minLegs, $maxLegs),
        );

        $bettingSlip = $action->execute(
            Auth::user(),
            $this->bettingSlip,
            ['name' => $validated['name']],
            $validated['legs'],
        );

        session()->flash('status', __('Slip saved.'));

        $this->redirect(route('analyze.edit', $bettingSlip), navigate: true);
    }

    public function markReady(): void
    {
        $this->authorize('update', $this->bettingSlip);

        try {
            $this->bettingSlip->markReady();
            session()->flash('status', __('Slip marked ready for analysis.'));
        } catch (InvalidBettingSlipTransitionException) {
            session()->flash('error', __('Add at least one complete leg before marking this slip ready.'));
        }

        $this->redirect(route('analyze.edit', $this->bettingSlip), navigate: true);
    }

    public function returnToDraft(): void
    {
        $this->authorize('update', $this->bettingSlip);

        try {
            $this->bettingSlip->returnToDraft();
            session()->flash('status', __('Slip returned to draft — you can edit it again.'));
        } catch (InvalidBettingSlipTransitionException) {
            session()->flash('error', __('This slip can no longer be returned to draft.'));
        } catch (BettingSlipLockedByPlannerException) {
            // U-06.4 Frame L-02: PD-008's hard lock made customer-visible, with a way forward rather than a dead end.
            $openSession = $this->bettingSlip->plannerSessions()
                ->whereNotIn('status', [PlannerSessionStatus::Exported->value, PlannerSessionStatus::Abandoned->value])
                ->first();

            session()->flash('error', __('This slip is currently part of an open planning session. Finish or abandon that session to edit it directly.'));

            if ($openSession) {
                $this->redirect(route('planner.session', $openSession), navigate: true);

                return;
            }
        }

        $this->redirect(route('analyze.edit', $this->bettingSlip), navigate: true);
    }

    public function archive(): void
    {
        $this->authorize('update', $this->bettingSlip);

        try {
            $this->bettingSlip->archive();
            session()->flash('status', __('Slip archived.'));
        } catch (InvalidBettingSlipTransitionException) {
            session()->flash('error', __('This slip is already archived.'));
        }

        $this->redirect(route('analyze'), navigate: true);
    }
}; ?>

<div class="workspace-page">
    <div class="container-analytics workspace-gutter mx-auto workspace-stack">
        <x-page-header
            :title="$bettingSlip ? __('Edit Slip') : __('Manual Slip Entry')"
            :description="__('Enter the selections from an existing betting slip. Manual entry is a fallback when upload or structured intake is not available.')">
            <x-slot name="action">
                <a href="{{ route('analyze') }}" wire:navigate
                   class="inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-neutral-600 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />
                    {{ __('Back to Slips') }}
                </a>
            </x-slot>
        </x-page-header>

        @if (session('status'))
            <x-alert variant="success">{{ session('status') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert variant="error">{{ session('error') }}</x-alert>
        @endif

        @if ($bettingSlip)
            <section class="workspace-section-panel flex flex-col gap-4 rounded-xl border sm:flex-row sm:items-center sm:justify-between
                @if ($bettingSlip->status->value === 'draft') bg-neutral-50 border-neutral-200 text-neutral-600
                @elseif ($bettingSlip->status->value === 'ready') bg-alert-caution/10 border-alert-caution/30 text-alert-caution-strong
                @elseif ($bettingSlip->status->value === 'analysed') bg-alert-info/10 border-alert-info/30 text-alert-info-strong
                @else bg-neutral-100 border-neutral-200 text-neutral-500 @endif"
                aria-label="{{ __('Slip status') }}">
                <div>
                    <p class="text-sm font-semibold">{{ $bettingSlip->status->label() }}</p>
                    <p class="mt-1 text-sm leading-5">
                    @if ($bettingSlip->status->value === 'draft')
                        {{ __('You can edit this slip freely.') }}
                    @elseif ($bettingSlip->status->value === 'ready')
                        {{ __('Locked for analysis. Return it to draft to make changes.') }}
                    @elseif ($bettingSlip->status->value === 'analysed')
                        {{ __('This slip has been analysed and is permanently locked.') }}
                    @else
                        {{ __('This slip is archived and read-only.') }}
                    @endif
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-3">
                    @if ($bettingSlip->status->value === 'draft')
                        <button type="button" wire:click="markReady" wire:loading.attr="disabled"
                                class="min-h-10 rounded-md border border-neutral-300 bg-surface-card px-3 text-sm font-semibold text-neutral-700 hover:bg-surface-soft hover:text-neutral-900">
                            {{ __('Mark as Ready') }}
                        </button>
                    @endif
                    @if ($bettingSlip->status->value === 'ready')
                        <button type="button" wire:click="returnToDraft" wire:loading.attr="disabled"
                                class="min-h-10 rounded-md border border-neutral-300 bg-surface-card px-3 text-sm font-semibold text-neutral-700 hover:bg-surface-soft hover:text-neutral-900">
                            {{ __('Return to Draft') }}
                        </button>
                    @endif
                    @if (in_array($bettingSlip->status->value, ['draft', 'ready', 'analysed']))
                        <button type="button" wire:click="archive" wire:loading.attr="disabled"
                                class="min-h-10 px-2 text-sm font-semibold text-alert-error-strong hover:text-alert-error">
                            {{ __('Archive') }}
                        </button>
                    @endif
                </div>
            </section>
        @endif

        <form wire:submit="save" class="workspace-grid items-start">
            <div class="space-y-6">
                @if ($bettingSlip?->source_screenshot_path)
                    <section class="workspace-section-panel rounded-xl border workspace-structural-border bg-surface-section" aria-labelledby="screenshot-reference-heading">
                        <div class="flex flex-col gap-5 md:flex-row md:items-start">
                            <div class="min-w-0 flex-1">
                                <p class="workspace-metadata">{{ __('Screenshot reference') }}</p>
                                <h2 id="screenshot-reference-heading" class="mt-2 workspace-section-title">{{ __('Transcribe what you can verify') }}</h2>
                                <p class="mt-2 text-sm leading-6 text-neutral-600">
                                    {{ __("SlipGuard can't read this automatically yet. Use the image as your source and enter only the selections you can confirm.") }}
                                </p>
                            </div>
                            <img src="{{ route('analyze.screenshot', $bettingSlip) }}"
                                 alt="{{ __('Uploaded betting slip screenshot') }}"
                                 class="max-h-64 w-auto max-w-full shrink-0 rounded-lg border border-neutral-300 md:max-w-[45%]">
                        </div>
                    </section>
                @endif

                <section class="workspace-section-panel rounded-xl border workspace-structural-border bg-surface-section" aria-labelledby="slip-details-heading">
                    <p class="workspace-metadata">{{ __('Step 1') }}</p>
                    <h2 id="slip-details-heading" class="mt-2 workspace-section-title">{{ __('Name this slip') }}</h2>
                    <p class="mt-1 text-sm leading-5 text-neutral-500">{{ __('A short name makes this slip easier to recognise in History and your Decision Journal.') }}</p>
                    <div class="mt-4">
                        <x-input-label for="name" :value="__('Slip name (optional)')" />
                        <x-text-input wire:model.blur="name" id="name" name="name" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Saturday accumulator') }}" :disabled="$this->readOnly()" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                </section>

                @if ($errors->any())
                    <x-workspace.inline-error :title="__('Review the highlighted slip details')">
                        {{ __('Some information is missing or invalid. Your entries remain in place so you can correct them.') }}
                    </x-workspace.inline-error>
                @endif

                @error('legs')
                    <p class="text-sm text-alert-error-strong" role="alert">{{ $message }}</p>
                @enderror

                <section aria-labelledby="selections-heading">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="workspace-metadata">{{ __('Step 2') }}</p>
                            <h2 id="selections-heading" class="mt-2 workspace-section-title">{{ __('Enter each selection') }}</h2>
                            <p class="mt-1 text-sm leading-5 text-neutral-500">{{ __('Choose the market, then record the fixture, selection and decimal odds exactly as they appear on the slip.') }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-neutral-600">
                            {{ __(':count of :max legs', ['count' => count($legs), 'max' => $this->maxLegs()]) }}
                        </span>
                    </div>

                    <div class="space-y-3">
                    @foreach ($legs as $index => $leg)
                        @php
                            $legComplete = BettingSlipValidationRules::legIsComplete($leg);
                            $collapsed = $this->legIsCollapsed($index);
                            $summary = $this->legSummaryLine($index);
                        @endphp
                        <article wire:key="leg-{{ $leg['_uid'] ?? $index }}" class="workspace-record-surface rounded-xl border p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-4">
                                <button type="button" wire:click="toggleLegCollapse({{ $index }})"
                                        class="flex min-h-10 min-w-0 flex-1 items-start gap-2 rounded-md text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                                        aria-expanded="{{ $collapsed ? 'false' : 'true' }}" aria-controls="leg-fields-{{ $index }}">
                                    <x-heroicon-o-chevron-down class="mt-0.5 size-4 shrink-0 text-neutral-400 transition-transform duration-instant {{ $collapsed ? '-rotate-90' : '' }}" aria-hidden="true" />
                                    <span class="min-w-0">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-semibold text-neutral-900">{{ __('Selection :number', ['number' => $index + 1]) }}</span>
                                            <x-badge :tone="$legComplete ? 'quality-strong' : 'neutral'">
                                                @if ($legComplete)
                                                    <x-heroicon-o-check-circle class="size-3.5" aria-hidden="true" />
                                                    {{ __('Complete') }}
                                                @else
                                                    <x-heroicon-o-pencil-square class="size-3.5" aria-hidden="true" />
                                                    {{ __('Needs details') }}
                                                @endif
                                            </x-badge>
                                        </span>
                                        <span class="mt-1 block truncate text-xs text-neutral-500">
                                            {{ $summary ?? (filled($leg['event_name'] ?? null) ? $leg['event_name'] : __('Fixture not entered yet')) }}
                                        </span>
                                    </span>
                                </button>
                            @unless ($this->readOnly())
                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" wire:click="moveLegUp({{ $index }})" class="inline-flex size-10 items-center justify-center rounded-md text-neutral-400 hover:bg-surface-soft hover:text-neutral-700 disabled:opacity-30" @disabled($index === 0) aria-label="{{ __('Move selection up') }}">
                                        <x-heroicon-o-chevron-up class="size-4" aria-hidden="true" />
                                    </button>
                                    <button type="button" wire:click="moveLegDown({{ $index }})" class="inline-flex size-10 items-center justify-center rounded-md text-neutral-400 hover:bg-surface-soft hover:text-neutral-700 disabled:opacity-30" @disabled($index === count($legs) - 1) aria-label="{{ __('Move selection down') }}">
                                        <x-heroicon-o-chevron-down class="size-4" aria-hidden="true" />
                                    </button>
                                    <button type="button" wire:click="removeLeg({{ $index }})" class="inline-flex min-h-10 items-center px-2 text-sm font-semibold text-alert-error-strong hover:text-alert-error">
                                        {{ __('Remove') }}<span class="sr-only"> {{ __('selection :number', ['number' => $index + 1]) }}</span>
                                    </button>
                                </div>
                            @endunless
                            </div>

                            @unless ($collapsed)
                            <div id="leg-fields-{{ $index }}" class="mt-5">
                                <p class="text-xs text-neutral-500">{{ __('Sport') }}: <span class="font-medium text-neutral-700">{{ __('Football') }}</span> <span class="text-neutral-400">— {{ __('the only sport currently supported') }}</span></p>

                                <div class="mt-4">
                                    <x-input-label :for="'market-'.$index" :value="__('Market')" />
                                    <select wire:model.live="legs.{{ $index }}.market_name" id="{{ 'market-'.$index }}"
                                            class="mt-1 block w-full bg-neutral-50 border-neutral-300 focus:border-accent focus:ring-accent rounded-md shadow-sm text-sm"
                                            @disabled($this->readOnly())>
                                        <option value="">{{ __('Choose a market…') }}</option>
                                        @foreach ($this->marketOptions() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.market_name')" />
                                </div>

                                {{-- U-19.1 progressive disclosure: nothing below is relevant until a market is chosen. --}}
                                @if (filled($leg['market_name'] ?? null))
                                    @php $selectionMode = $this->selectionInputMode($index); @endphp
                                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <x-input-label :for="'event-'.$index" :value="__('Event')" />
                                            <x-text-input wire:model.blur="legs.{{ $index }}.event_name" :id="'event-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal vs Chelsea') }}" :disabled="$this->readOnly()" />
                                            <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.event_name')" />
                                        </div>

                                        <div>
                                            <x-input-label :for="'competition-'.$index" :value="__('Competition (optional)')" />
                                            <x-text-input wire:model.blur="legs.{{ $index }}.competition" :id="'competition-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Premier League') }}" :disabled="$this->readOnly()" />
                                            <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.competition')" />
                                        </div>

                                        <div>
                                            <x-input-label :for="'selection-'.$index" :value="__('Selection')" />

                                            @if ($selectionMode === 'yes_no')
                                                <x-segmented-radio wireModel="legs.{{ $index }}.selection_name" name="selection-{{ $index }}" class="mt-1"
                                                                   :options="['Yes' => __('Yes'), 'No' => __('No')]" :disabled="$this->readOnly()" />
                                            @elseif ($selectionMode === 'over_under_line')
                                                <div class="mt-1 space-y-2">
                                                    <x-segmented-radio wireModel="legs.{{ $index }}.total_goals_direction" name="direction-{{ $index }}"
                                                                       :options="['over' => __('Over'), 'under' => __('Under')]" :disabled="$this->readOnly()" />
                                                    <x-number-stepper wireModel="legs.{{ $index }}.total_goals_line"
                                                                      decrementAction="adjustTotalGoalsLine({{ $index }}, -1)"
                                                                      incrementAction="adjustTotalGoalsLine({{ $index }}, 1)"
                                                                      :ariaLabel="__('Goals line')" :disabled="$this->readOnly()" />
                                                </div>
                                                <p class="mt-1 text-xs text-neutral-500">{{ __('Total goals across the match, e.g. Over 2.5.') }}</p>
                                            @elseif ($selectionMode === 'correct_score')
                                                <div class="mt-1 flex items-center gap-3">
                                                    <x-number-stepper wireModel="legs.{{ $index }}.correct_score_home"
                                                                      decrementAction="adjustCorrectScoreHome({{ $index }}, -1)"
                                                                      incrementAction="adjustCorrectScoreHome({{ $index }}, 1)"
                                                                      :ariaLabel="__('Home team score')" :disabled="$this->readOnly()" />
                                                    <span class="shrink-0 text-neutral-400" aria-hidden="true">–</span>
                                                    <x-number-stepper wireModel="legs.{{ $index }}.correct_score_away"
                                                                      decrementAction="adjustCorrectScoreAway({{ $index }}, -1)"
                                                                      incrementAction="adjustCorrectScoreAway({{ $index }}, 1)"
                                                                      :ariaLabel="__('Away team score')" :disabled="$this->readOnly()" />
                                                </div>
                                            @else
                                                <x-text-input wire:model.blur="legs.{{ $index }}.selection_name" :id="'selection-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal to win') }}" :disabled="$this->readOnly()" />
                                            @endif

                                            <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.selection_name')" />
                                        </div>

                                        <div>
                                            <x-input-label :for="'odds-'.$index" :value="__('Decimal odds')" />
                                            <x-text-input wire:model.blur="legs.{{ $index }}.decimal_odds" :id="'odds-'.$index" type="text" inputmode="decimal" class="mt-1 block w-full font-tabular" placeholder="{{ __('e.g. 1.90') }}" :disabled="$this->readOnly()" />
                                            <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.decimal_odds')" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endunless
                        </article>
                    @endforeach
                    </div>
                </section>

                @unless ($this->readOnly())
                    <div class="flex flex-col gap-3 rounded-xl border border-dashed border-neutral-300 bg-surface-section p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-neutral-900">{{ __('Another selection on this slip?') }}</p>
                            <p class="mt-1 text-xs leading-5 text-neutral-500">{{ __('Add only selections that appear on the original slip. You can reorder them at any time.') }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if (count($legs) > 0 && filled($legs[array_key_last($legs)]['market_name'] ?? null))
                                <button type="button" wire:click="duplicatePreviousLeg" wire:loading.attr="disabled"
                                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-700 hover:bg-surface-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:opacity-50"
                                        @disabled(count($legs) >= $this->maxLegs())>
                                    <x-heroicon-o-document-duplicate class="size-4" aria-hidden="true" />
                                    {{ __('Duplicate Previous Leg') }}
                                </button>
                            @endif
                            <button type="button" wire:click="addLeg" wire:loading.attr="disabled"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-700 hover:bg-surface-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:opacity-50"
                                    @disabled(count($legs) >= $this->maxLegs())>
                                <x-heroicon-o-plus class="size-4" aria-hidden="true" />
                                {{ __('Add Selection') }}
                            </button>
                        </div>
                    </div>
                @endunless
            </div>

            <x-workspace.sticky-summary :title="__('Slip summary')" :aria-label="__('Slip summary')" aria-live="polite">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="workspace-metadata">{{ __('Slip name') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">{{ filled(trim($name)) ? $name : __('Not named yet') }}</dd>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Entry source') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">
                            {{ $bettingSlip?->source_screenshot_path ? __('Screenshot reference') : __('Manual fallback') }}
                        </dd>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Selections') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">
                            {{ trans_choice(':count selection|:count selections', count($legs), ['count' => count($legs)]) }}
                        </dd>
                        <p class="mt-1 text-xs leading-5 text-neutral-500">
                            {{ __(':complete complete', ['complete' => $this->completedLegsCount()]) }}
                        </p>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Entered combined odds') }}</dt>
                        <dd class="mt-1 font-tabular font-medium text-neutral-900">
                            {{ $this->combinedOddsPreview() ?? __('Complete every decimal-odds field') }}
                        </dd>
                        <p class="mt-1 text-xs leading-5 text-neutral-500">{{ __('A direct multiplication of entered decimal odds, not a prediction.') }}</p>
                    </div>
                    <div class="border-t workspace-internal-border pt-4">
                        <dt class="workspace-metadata">{{ __('Current state') }}</dt>
                        <dd class="mt-1 font-medium text-neutral-900">{{ $bettingSlip?->status->label() ?? __('Unsaved') }}</dd>
                    </div>
                </dl>

                @unless ($this->readOnly())
                    <div class="mt-5 border-t workspace-internal-border pt-5">
                        <x-primary-button wire:loading.attr="disabled" wire:target="save" class="w-full justify-center !h-12 !text-sm !normal-case !tracking-normal">
                            <span wire:loading.remove wire:target="save">{{ __('Save Slip') }}</span>
                            <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
                        </x-primary-button>
                        <p class="mt-3 text-xs leading-5 text-neutral-500">
                            {{ __('Saving records this draft. You remain responsible for confirming every selection before analysis.') }}
                        </p>
                    </div>
                @else
                    <x-workspace.limited-data-notice class="mt-5" :title="__('This slip is read-only')">
                        {{ __('Its recorded selections remain available for review, but they can no longer be changed here.') }}
                    </x-workspace.limited-data-notice>
                @endunless
            </x-workspace.sticky-summary>
        </form>
    </div>
</div>
