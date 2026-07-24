<?php

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Domain\BettingSlip\BettingSlipValidationRules;
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

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">
                {{ $bettingSlip ? __('Edit Slip') : __('New Slip') }}
            </h2>
            <a href="{{ route('analyze') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900">
                {{ __('Back to Slips') }}
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($bettingSlip)
            <div class="rounded-md border px-4 py-3 text-sm flex items-center justify-between gap-4
                @if ($bettingSlip->status->value === 'draft') bg-gray-50 border-gray-200 text-gray-600
                @elseif ($bettingSlip->status->value === 'ready') bg-amber-50 border-amber-200 text-amber-800
                @elseif ($bettingSlip->status->value === 'analysed') bg-blue-50 border-blue-200 text-blue-800
                @else bg-gray-100 border-gray-200 text-gray-500 @endif">
                <div>
                    <span class="font-semibold">{{ $bettingSlip->status->label() }}.</span>
                    @if ($bettingSlip->status->value === 'draft')
                        {{ __('You can edit this slip freely.') }}
                    @elseif ($bettingSlip->status->value === 'ready')
                        {{ __('Locked for analysis. Return it to draft to make changes.') }}
                    @elseif ($bettingSlip->status->value === 'analysed')
                        {{ __('This slip has been analysed and is permanently locked.') }}
                    @else
                        {{ __('This slip is archived and read-only.') }}
                    @endif
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    @if ($bettingSlip->status->value === 'draft')
                        <button type="button" wire:click="markReady" wire:loading.attr="disabled"
                                class="text-sm font-medium text-gray-700 hover:text-gray-900">
                            {{ __('Mark as Ready') }}
                        </button>
                    @endif
                    @if ($bettingSlip->status->value === 'ready')
                        <button type="button" wire:click="returnToDraft" wire:loading.attr="disabled"
                                class="text-sm font-medium text-gray-700 hover:text-gray-900">
                            {{ __('Return to Draft') }}
                        </button>
                    @endif
                    @if (in_array($bettingSlip->status->value, ['draft', 'ready', 'analysed']))
                        <button type="button" wire:click="archive" wire:loading.attr="disabled"
                                class="text-sm font-medium text-red-600 hover:text-red-800">
                            {{ __('Archive') }}
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <x-input-label for="name" :value="__('Slip name (optional)')" />
                <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Saturday accumulator') }}" :disabled="$this->readOnly()" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ __('Please fix the errors below before saving.') }}
                </div>
            @endif

            @error('legs')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-4">
                @foreach ($legs as $index => $leg)
                    <div wire:key="leg-{{ $index }}" class="bg-white border border-gray-200 rounded-lg p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Leg :number', ['number' => $index + 1]) }}</h3>
                            @unless ($this->readOnly())
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="moveLegUp({{ $index }})" class="text-gray-400 hover:text-gray-700 disabled:opacity-30" @disabled($index === 0) aria-label="{{ __('Move leg up') }}">
                                        <x-heroicon-o-chevron-up class="h-4 w-4" />
                                    </button>
                                    <button type="button" wire:click="moveLegDown({{ $index }})" class="text-gray-400 hover:text-gray-700 disabled:opacity-30" @disabled($index === count($legs) - 1) aria-label="{{ __('Move leg down') }}">
                                        <x-heroicon-o-chevron-down class="h-4 w-4" />
                                    </button>
                                    <button type="button" wire:click="removeLeg({{ $index }})" class="text-sm font-medium text-red-600 hover:text-red-800">
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            @endunless
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label :for="'sport-'.$index" :value="__('Sport')" />
                                <x-text-input wire:model="legs.{{ $index }}.sport" :id="'sport-'.$index" type="text" class="mt-1 block w-full" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.sport')" />
                            </div>

                            <div>
                                <x-input-label :for="'competition-'.$index" :value="__('Competition (optional)')" />
                                <x-text-input wire:model="legs.{{ $index }}.competition" :id="'competition-'.$index" type="text" class="mt-1 block w-full" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.competition')" />
                            </div>

                            <div>
                                <x-input-label :for="'event-'.$index" :value="__('Event')" />
                                <x-text-input wire:model="legs.{{ $index }}.event_name" :id="'event-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal vs Chelsea') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.event_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'market-'.$index" :value="__('Market')" />
                                <x-text-input wire:model="legs.{{ $index }}.market_name" :id="'market-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Match Result') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.market_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'selection-'.$index" :value="__('Selection')" />
                                <x-text-input wire:model="legs.{{ $index }}.selection_name" :id="'selection-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal to win') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.selection_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'odds-'.$index" :value="__('Decimal odds')" />
                                <x-text-input wire:model="legs.{{ $index }}.decimal_odds" :id="'odds-'.$index" type="text" inputmode="decimal" class="mt-1 block w-full" placeholder="{{ __('e.g. 1.90') }}" :disabled="$this->readOnly()" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.decimal_odds')" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @unless ($this->readOnly())
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="addLeg" wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition disabled:opacity-50"
                                @disabled(count($legs) >= $this->maxLegs())>
                            {{ __('Add Leg') }}
                        </button>
                        <span class="text-sm text-gray-500">
                            {{ __(':count of :max legs', ['count' => count($legs), 'max' => $this->maxLegs()]) }}
                        </span>
                    </div>

                    <x-primary-button wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Save Slip') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </x-primary-button>
                </div>
            @endunless
        </form>
    </div>
</div>
