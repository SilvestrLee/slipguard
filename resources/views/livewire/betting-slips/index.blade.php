<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Models\BettingSlip;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $statusFilter = 'all';

    /**
     * Delete one of the current user's own Draft or Ready slips.
     * Analysed slips must be archived instead — see BettingSlipPolicy.
     */
    public function deleteSlip(int $bettingSlipId): void
    {
        $bettingSlip = BettingSlip::findOrFail($bettingSlipId);

        $this->authorize('delete', $bettingSlip);

        $bettingSlip->delete();
    }

    public function setFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function emptyStateMessage(): array
    {
        return match ($this->statusFilter) {
            'draft' => [__('No draft slips.'), __('Slips you are still building appear here.')],
            'ready' => [__('No slips ready for analysis.'), __('Mark a draft slip as ready when it is complete.')],
            'analysed' => [__('No completed slips.'), __('Slips appear here once the risk engine has analysed them.')],
            'archived' => [__('No archived slips.'), __('Slips you archive for reference appear here.')],
            default => [__('No slips yet.'), __('Create your first betting slip to get started.')],
        };
    }

    public function with(): array
    {
        $query = Auth::user()->bettingSlips()->withCount('legs');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return [
            'bettingSlips' => $query->latest('updated_at')->get(),
            'hasAnySlips' => Auth::user()->bettingSlips()->exists(),
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

        @if ($hasAnySlips)
            <div class="flex items-center gap-1 border-b border-gray-200 text-sm">
                @foreach (['all' => __('All'), 'draft' => __('Draft'), 'ready' => __('Ready'), 'analysed' => __('Analysed'), 'archived' => __('Archived')] as $value => $label)
                    <button type="button" wire:click="setFilter('{{ $value }}')"
                            class="px-3 py-2 -mb-px border-b-2 font-medium
                                {{ $statusFilter === $value ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        @endif

        @if ($bettingSlips->isEmpty())
            @php [$emptyTitle, $emptyBody] = $this->emptyStateMessage(); @endphp
            <div class="bg-white border border-dashed border-gray-300 rounded-lg p-10 text-center">
                <p class="text-sm text-gray-600">{{ $emptyTitle }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $emptyBody }}</p>
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg divide-y divide-gray-200">
                @foreach ($bettingSlips as $bettingSlip)
                    <div wire:key="slip-{{ $bettingSlip->id }}" class="p-4 sm:p-5 flex items-center justify-between gap-4">
                        <a href="{{ route('analyze.edit', $bettingSlip) }}" wire:navigate class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $bettingSlip->name ?: __('Untitled slip') }}
                                </p>
                                <span class="shrink-0 text-xs font-medium px-2 py-0.5 rounded-full
                                    @if ($bettingSlip->status === BettingSlipStatus::Draft) bg-gray-100 text-gray-600
                                    @elseif ($bettingSlip->status === BettingSlipStatus::Ready) bg-amber-100 text-amber-800
                                    @elseif ($bettingSlip->status === BettingSlipStatus::Analysed) bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ $bettingSlip->status->label() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ trans_choice(':count leg|:count legs', $bettingSlip->legs_count, ['count' => $bettingSlip->legs_count]) }}
                                &middot;
                                {{ __('Updated :time', ['time' => $bettingSlip->updated_at->diffForHumans()]) }}
                            </p>
                        </a>

                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('analyze.edit', $bettingSlip) }}" wire:navigate
                               class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                {{ $bettingSlip->isEditable() ? __('Edit') : __('View') }}
                            </a>
                            @if (in_array($bettingSlip->status, [BettingSlipStatus::Draft, BettingSlipStatus::Ready], true))
                                <button type="button"
                                        x-data=""
                                        x-on:click="$dispatch('open-modal', 'confirm-slip-deletion-{{ $bettingSlip->id }}')"
                                        class="text-sm font-medium text-red-600 hover:text-red-800">
                                    {{ __('Delete') }}
                                </button>
                            @elseif ($bettingSlip->status === BettingSlipStatus::Analysed)
                                <span class="text-sm text-gray-400" title="{{ __('Analysed slips are kept as a record — archive it instead.') }}">
                                    {{ __('Archive to remove') }}
                                </span>
                            @endif
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
