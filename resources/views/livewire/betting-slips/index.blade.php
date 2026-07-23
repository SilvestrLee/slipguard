<?php

use App\Models\BettingSlip;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * Delete one of the current user's own slips.
     */
    public function deleteSlip(int $bettingSlipId): void
    {
        $bettingSlip = BettingSlip::findOrFail($bettingSlipId);

        $this->authorize('delete', $bettingSlip);

        $bettingSlip->delete();
    }

    public function with(): array
    {
        return [
            'bettingSlips' => Auth::user()->bettingSlips()
                ->withCount('legs')
                ->latest('updated_at')
                ->get(),
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ __('Analyze Slip') }}</h2>
                <p class="mt-1 text-sm text-gray-600 max-w-xl">
                    {{ __('Enter a betting slip manually and SlipGuard will show you where its structural risk comes from.') }}
                </p>
            </div>
            <a href="{{ route('analyze.create') }}" wire:navigate
               class="shrink-0 inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition">
                {{ __('New Slip') }}
            </a>
        </div>

        @if ($bettingSlips->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-10 text-center">
                <p class="text-sm text-gray-600">{{ __('No slips yet.') }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ __('Create your first betting slip to get started.') }}</p>
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg divide-y divide-gray-200">
                @foreach ($bettingSlips as $bettingSlip)
                    <div wire:key="slip-{{ $bettingSlip->id }}" class="p-4 sm:p-5 flex items-center justify-between gap-4">
                        <a href="{{ route('analyze.edit', $bettingSlip) }}" wire:navigate class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $bettingSlip->name ?: __('Untitled slip') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ trans_choice(':count leg|:count legs', $bettingSlip->legs_count, ['count' => $bettingSlip->legs_count]) }}
                                &middot;
                                {{ __('Updated :time', ['time' => $bettingSlip->updated_at->diffForHumans()]) }}
                            </p>
                        </a>

                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('analyze.edit', $bettingSlip) }}" wire:navigate
                               class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                {{ __('Edit') }}
                            </a>
                            <button type="button"
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', 'confirm-slip-deletion-{{ $bettingSlip->id }}')"
                                    class="text-sm font-medium text-red-600 hover:text-red-800">
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>

                    <x-modal name="confirm-slip-deletion-{{ $bettingSlip->id }}" focusable>
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Delete this slip?') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('This permanently deletes ":name" and all of its legs.', ['name' => $bettingSlip->name ?: __('Untitled slip')]) }}
                            </p>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                                <x-danger-button wire:click="deleteSlip({{ $bettingSlip->id }})" x-on:click="$dispatch('close')">
                                    {{ __('Delete') }}
                                </x-danger-button>
                            </div>
                        </div>
                    </x-modal>
                @endforeach
            </div>
        @endif
    </div>
</div>
