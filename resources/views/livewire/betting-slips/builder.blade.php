<?php

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Domain\BettingSlip\BettingSlipValidationRules;
use App\Domain\Planner\PlannerSessionStatus;
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

    public function mount(?BettingSlip $bettingSlip = null): void
    {
        if ($bettingSlip) {
            $this->authorize('update', $bettingSlip);

            $this->bettingSlip = $bettingSlip;
            $this->name = (string) $bettingSlip->name;
            $this->legs = $bettingSlip->legs->map(fn ($leg) => [
                'sport' => $leg->sport,
                'competition' => $leg->competition,
                'event_name' => $leg->event_name,
                'market_name' => $leg->market_name,
                'selection_name' => $leg->selection_name,
                'decimal_odds' => (string) $leg->decimal_odds,
            ])->all();
        } else {
            $this->authorize('create', BettingSlip::class);
        }

        if (empty($this->legs)) {
            $this->addLeg();
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

        $this->legs[] = [
            'sport' => '',
            'competition' => '',
            'event_name' => '',
            'market_name' => '',
            'selection_name' => '',
            'decimal_odds' => '',
        ];
    }

    public function removeLeg(int $index): void
    {
        if ($this->readOnly()) {
            return;
        }

        unset($this->legs[$index]);

        $this->legs = array_values($this->legs);
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
                            <p class="mt-1 text-sm leading-5 text-neutral-500">{{ __('Record the fixture, market, selection and decimal odds exactly as they appear on the slip.') }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-neutral-600">
                            {{ __(':count of :max legs', ['count' => count($legs), 'max' => $this->maxLegs()]) }}
                        </span>
                    </div>

                    <div class="space-y-4">
                    @foreach ($legs as $index => $leg)
                        @php $legComplete = BettingSlipValidationRules::legIsComplete($leg); @endphp
                        <article wire:key="leg-{{ $index }}" class="workspace-record-surface rounded-xl border p-4 sm:p-5">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-semibold text-neutral-900">{{ __('Selection :number', ['number' => $index + 1]) }}</h3>
                                        <x-badge :tone="$legComplete ? 'quality-strong' : 'neutral'">
                                            @if ($legComplete)
                                                <x-heroicon-o-check-circle class="size-3.5" aria-hidden="true" />
                                                {{ __('Complete') }}
                                            @else
                                                <x-heroicon-o-pencil-square class="size-3.5" aria-hidden="true" />
                                                {{ __('Needs details') }}
                                            @endif
                                        </x-badge>
                                    </div>
                                    <p class="mt-1 text-xs text-neutral-500">
                                        {{ filled($leg['event_name'] ?? null) ? $leg['event_name'] : __('Fixture not entered yet') }}
                                    </p>
                                </div>
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

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label :for="'sport-'.$index" :value="__('Sport')" />
                                <x-text-input wire:model.blur="legs.{{ $index }}.sport" :id="'sport-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Football') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.sport')" />
                            </div>

                            <div>
                                <x-input-label :for="'competition-'.$index" :value="__('Competition (optional)')" />
                                <x-text-input wire:model.blur="legs.{{ $index }}.competition" :id="'competition-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Premier League') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.competition')" />
                            </div>

                            <div>
                                <x-input-label :for="'event-'.$index" :value="__('Event')" />
                                <x-text-input wire:model.blur="legs.{{ $index }}.event_name" :id="'event-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal vs Chelsea') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.event_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'market-'.$index" :value="__('Market')" />
                                <x-text-input wire:model.blur="legs.{{ $index }}.market_name" :id="'market-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Match Result') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.market_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'selection-'.$index" :value="__('Selection')" />
                                <x-text-input wire:model.blur="legs.{{ $index }}.selection_name" :id="'selection-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal to win') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.selection_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'odds-'.$index" :value="__('Decimal odds')" />
                                <x-text-input wire:model.blur="legs.{{ $index }}.decimal_odds" :id="'odds-'.$index" type="text" inputmode="decimal" class="mt-1 block w-full font-tabular" placeholder="{{ __('e.g. 1.90') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.decimal_odds')" />
                            </div>
                            </div>
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
                        <button type="button" wire:click="addLeg" wire:loading.attr="disabled"
                                class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-700 hover:bg-surface-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:opacity-50"
                                @disabled(count($legs) >= $this->maxLegs())>
                            <x-heroicon-o-plus class="size-4" aria-hidden="true" />
                            {{ __('Add Selection') }}
                        </button>
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
