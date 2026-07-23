<?php

use App\Actions\BettingSlip\SaveBettingSlip;
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

    public function addLeg(): void
    {
        if (count($this->legs) >= config('slipguard.max_legs')) {
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
        unset($this->legs[$index]);

        $this->legs = array_values($this->legs);
    }

    public function moveLegUp(int $index): void
    {
        if ($index === 0) {
            return;
        }

        [$this->legs[$index - 1], $this->legs[$index]] = [$this->legs[$index], $this->legs[$index - 1]];
    }

    public function moveLegDown(int $index): void
    {
        if ($index >= count($this->legs) - 1) {
            return;
        }

        [$this->legs[$index + 1], $this->legs[$index]] = [$this->legs[$index], $this->legs[$index + 1]];
    }

    public function save(SaveBettingSlip $action): void
    {
        if ($this->bettingSlip) {
            $this->authorize('update', $this->bettingSlip);
        } else {
            $this->authorize('create', BettingSlip::class);
        }

        $maxLegs = config('slipguard.max_legs');
        $minLegs = config('slipguard.min_legs');

        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'legs' => ["required", "array", "min:{$minLegs}", "max:{$maxLegs}"],
            'legs.*.sport' => ['required', 'string', 'max:255'],
            'legs.*.competition' => ['nullable', 'string', 'max:255'],
            'legs.*.event_name' => ['required', 'string', 'max:255'],
            'legs.*.market_name' => ['required', 'string', 'max:255'],
            'legs.*.selection_name' => ['required', 'string', 'max:255'],
            'legs.*.decimal_odds' => ['required', 'numeric', 'min:1.01', 'max:1000'],
        ], [
            'legs.min' => __('Add at least :min leg to save this slip.', ['min' => $minLegs]),
            'legs.max' => __('A slip can have at most :max legs.', ['max' => $maxLegs]),
            'legs.*.sport.required' => __('Enter the sport for every leg.'),
            'legs.*.event_name.required' => __('Enter the event for every leg.'),
            'legs.*.market_name.required' => __('Enter the market for every leg.'),
            'legs.*.selection_name.required' => __('Enter the selection for every leg.'),
            'legs.*.decimal_odds.required' => __('Enter the decimal odds for every leg.'),
            'legs.*.decimal_odds.numeric' => __('Decimal odds must be a number.'),
            'legs.*.decimal_odds.min' => __('Decimal odds must be greater than 1.00.'),
        ]);

        $bettingSlip = $action->execute(
            Auth::user(),
            $this->bettingSlip,
            ['name' => $validated['name']],
            $validated['legs'],
        );

        session()->flash('status', __('Slip saved.'));

        $this->redirect(route('analyze.edit', $bettingSlip), navigate: true);
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

        <form wire:submit="save" class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <x-input-label for="name" :value="__('Slip name (optional)')" />
                <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Saturday accumulator') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            @error('legs')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="space-y-4">
                @foreach ($legs as $index => $leg)
                    <div wire:key="leg-{{ $index }}" class="bg-white border border-gray-200 rounded-lg p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-700">{{ __('Leg :number', ['number' => $index + 1]) }}</h3>
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="moveLegUp({{ $index }})" class="text-gray-400 hover:text-gray-700 disabled:opacity-30" @disabled($index === 0)>
                                    <x-heroicon-o-chevron-up class="h-4 w-4" />
                                </button>
                                <button type="button" wire:click="moveLegDown({{ $index }})" class="text-gray-400 hover:text-gray-700 disabled:opacity-30" @disabled($index === count($legs) - 1)>
                                    <x-heroicon-o-chevron-down class="h-4 w-4" />
                                </button>
                                <button type="button" wire:click="removeLeg({{ $index }})" class="text-sm font-medium text-red-600 hover:text-red-800">
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label :for="'sport-'.$index" :value="__('Sport')" />
                                <x-text-input wire:model="legs.{{ $index }}.sport" :id="'sport-'.$index" type="text" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.sport')" />
                            </div>

                            <div>
                                <x-input-label :for="'competition-'.$index" :value="__('Competition (optional)')" />
                                <x-text-input wire:model="legs.{{ $index }}.competition" :id="'competition-'.$index" type="text" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.competition')" />
                            </div>

                            <div>
                                <x-input-label :for="'event-'.$index" :value="__('Event')" />
                                <x-text-input wire:model="legs.{{ $index }}.event_name" :id="'event-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal vs Chelsea') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.event_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'market-'.$index" :value="__('Market')" />
                                <x-text-input wire:model="legs.{{ $index }}.market_name" :id="'market-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Match Result') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.market_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'selection-'.$index" :value="__('Selection')" />
                                <x-text-input wire:model="legs.{{ $index }}.selection_name" :id="'selection-'.$index" type="text" class="mt-1 block w-full" placeholder="{{ __('e.g. Arsenal to win') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.selection_name')" />
                            </div>

                            <div>
                                <x-input-label :for="'odds-'.$index" :value="__('Decimal odds')" />
                                <x-text-input wire:model="legs.{{ $index }}.decimal_odds" :id="'odds-'.$index" type="text" inputmode="decimal" class="mt-1 block w-full" placeholder="{{ __('e.g. 1.90') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('legs.'.$index.'.decimal_odds')" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between">
                <button type="button" wire:click="addLeg"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition">
                    {{ __('Add Leg') }}
                </button>

                <x-primary-button>
                    {{ __('Save Slip') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
