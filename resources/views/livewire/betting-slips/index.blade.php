<?php

use App\Actions\Planner\StartPlannerSession;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Planner\PlannerSessionStatus;
use App\Models\BettingSlip;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $statusFilter = 'all';

    public string $deleteError = '';

    /**
     * Delete one of the current user's own Draft or Ready slips.
     * Analysed slips must be archived instead — see BettingSlipPolicy.
     */
    /**
     * U-07.8 validation finding: deleting a slip ever referenced by a
     * PlannerSession — even one long since Abandoned or Exported — hit the
     * database's restrictOnDelete constraint directly (an uncaught
     * QueryException/500), because a PlannerSession row is never deleted
     * regardless of status. hasPlannerHistory() (not isLockedByPlanner(),
     * which only reflects the *editing* lock and correctly excludes
     * terminal sessions) is the right, permanent check here. The Delete
     * action is now hidden accordingly (see the view), but this check
     * stays here too as defence in depth against a stale page state.
     */
    public function deleteSlip(int $bettingSlipId): void
    {
        $bettingSlip = BettingSlip::findOrFail($bettingSlipId);

        $this->authorize('delete', $bettingSlip);

        if ($bettingSlip->hasPlannerHistory()) {
            $this->deleteError = __('This slip has been used in a planning session and is kept as part of that history — it can no longer be deleted.');

            return;
        }

        $this->deleteError = '';

        $bettingSlip->delete();
    }

    /**
     * U-06.4 §9 Frame E-01: "Plan this accumulator" — an alternative path to
     * "Analyze" on any Ready slip, per PD-007/DR-03's existing-slip-only
     * entry point. Resumes an already-open session for this slip if one
     * exists (a customer can only ever have one non-terminal session per
     * slip, since PD-008's lock already prevents a second one being
     * started against the same Ready slip) rather than starting a new one.
     */
    public function planThisAccumulator(int $bettingSlipId): void
    {
        $bettingSlip = BettingSlip::findOrFail($bettingSlipId);

        $this->authorize('update', $bettingSlip);

        $openSession = $bettingSlip->plannerSessions()
            ->whereNotIn('status', [PlannerSessionStatus::Exported->value, PlannerSessionStatus::Abandoned->value])
            ->first();

        $session = $openSession ?? (new StartPlannerSession)->execute(Auth::user(), $bettingSlip);

        $this->redirect(route('planner.session', $session), navigate: true);
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
        $query = Auth::user()->bettingSlips()->with('legs')->withCount('legs');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return [
            'bettingSlips' => $query->latest('updated_at')->get(),
            'hasAnySlips' => Auth::user()->bettingSlips()->exists(),
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">

        <x-page-header :title="__('Analyze Slip')"
            :description="__('Enter a betting slip manually and SlipGuard will show you where its structural risk comes from.')">
            <x-slot name="action">
                <a href="{{ route('analyze.intake') }}" wire:navigate
                   class="shrink-0 inline-flex items-center px-4 py-2 bg-accent-strong border border-transparent rounded-md font-semibold text-sm text-white hover:bg-accent focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition">
                    {{ __('New Slip') }}
                </a>
            </x-slot>
        </x-page-header>

        {{-- U-17 Increment Two — internal-only entry point, beside (never replacing) New Slip above, per U-17.7 §1. Hidden entirely while the feature flag is off. --}}
        @if (config('slipguard-market-intelligence.enabled'))
            <x-card variant="interactive" :href="route('builder')" wire:navigate class="block">
                <p class="text-sm font-medium text-neutral-900">{{ __('Build an Accumulator') }}</p>
                <p class="mt-1 text-sm text-neutral-500">{{ __('Tell SlipGuard your constraints — it builds a candidate from supported fixtures for you to review.') }}</p>
            </x-card>
        @endif

        @if ($deleteError)
            <x-workspace.inline-error>{{ $deleteError }}</x-workspace.inline-error>
        @endif

        @if ($hasAnySlips)
            <x-workspace.segmented-control :label="__('Filter slips by status')">
                @foreach (['all' => __('All'), 'draft' => __('Draft'), 'ready' => __('Ready'), 'analysed' => __('Analysed'), 'archived' => __('Archived')] as $value => $label)
                    <button type="button" wire:click="setFilter('{{ $value }}')"
                            @class([
                                'min-h-10 rounded-md px-3 text-sm font-medium focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                                'bg-surface-card text-neutral-900 border border-neutral-200' => $statusFilter === $value,
                                'text-neutral-600 hover:bg-neutral-100' => $statusFilter !== $value,
                            ])
                            aria-pressed="{{ $statusFilter === $value ? 'true' : 'false' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </x-workspace.segmented-control>
        @endif

        @if ($bettingSlips->isEmpty())
            @php [$emptyTitle, $emptyBody] = $this->emptyStateMessage(); @endphp
            <x-empty-state :title="$emptyTitle" :description="$emptyBody" />
        @else
            <div class="workspace-section-panel divide-y workspace-internal-border">
                @foreach ($bettingSlips as $bettingSlip)
                    @php
                        $primaryRoute = $bettingSlip->status === BettingSlipStatus::Analysed
                            ? route('analyze.report', $bettingSlip)
                            : route('analyze.edit', $bettingSlip);
                    @endphp
                    <div wire:key="slip-{{ $bettingSlip->id }}" class="workspace-record-surface p-4 first:rounded-t-lg last:rounded-b-lg sm:p-5 flex items-center justify-between gap-4">
                        <a href="{{ $primaryRoute }}" wire:navigate class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-neutral-900 truncate">
                                    {{ $bettingSlip->displayLabel() }}
                                </p>
                                {{-- U-11.2 Phase 3: migrated from ad hoc amber/blue to the neutral, undifferentiated
                                     treatment already established for lifecycle-status badges elsewhere (Planning
                                     History's PlannerSessionStatus badge) — DESIGN_TOKENS.md reserves risk/quality
                                     colour tokens for risk/quality meaning only; a slip's lifecycle status is neither. --}}
                                <x-badge class="shrink-0">{{ $bettingSlip->status->label() }}</x-badge>
                            </div>
                            <p class="text-sm text-neutral-500">
                                {{ trans_choice(':count leg|:count legs', $bettingSlip->legs_count, ['count' => $bettingSlip->legs_count]) }}
                                &middot;
                                {{ __('Updated :time', ['time' => $bettingSlip->updated_at->diffForHumans()]) }}
                            </p>
                        </a>

                        <div class="flex items-center gap-3 shrink-0">
                            @if ($bettingSlip->status === BettingSlipStatus::Ready)
                                {{-- U-06.4 Frame E-01: equal visual weight to Analyze, neither more prominent — the customer chooses which path fits their intent. --}}
                                <button type="button" wire:click="planThisAccumulator({{ $bettingSlip->id }})"
                                        wire:loading.attr="disabled" wire:target="planThisAccumulator({{ $bettingSlip->id }})"
                                        class="text-sm font-semibold text-neutral-900 hover:text-neutral-700 disabled:opacity-50">
                                    {{-- PO-U17-NAMING-001: retired "Plan this accumulator" — too similar to Capability B's "Build an Accumulator" on this same page. "Continue Planning" when a session is already open (isLockedByPlanner — non-terminal only, unlike hasPlannerHistory which also covers terminal history), otherwise "Improve This Slip". --}}
                                    <span wire:loading.remove wire:target="planThisAccumulator({{ $bettingSlip->id }})">{{ $bettingSlip->isLockedByPlanner() ? __('Continue Planning') : __('Improve This Slip') }}</span>
                                    <span wire:loading wire:target="planThisAccumulator({{ $bettingSlip->id }})" role="status">{{ __('Opening planner…') }}</span>
                                </button>
                                <a href="{{ route('analyze.processing', $bettingSlip) }}" wire:navigate
                                   class="text-sm font-semibold text-neutral-900 hover:text-neutral-700">
                                    {{ __('Analyze') }}
                                </a>
                            @else
                                <a href="{{ $primaryRoute }}" wire:navigate
                                   class="text-sm font-medium text-neutral-600 hover:text-neutral-900">
                                    {{ $bettingSlip->isEditable() ? __('Edit') : __('View report') }}
                                </a>
                            @endif
                            @if (in_array($bettingSlip->status, [BettingSlipStatus::Draft, BettingSlipStatus::Ready], true) && ! $bettingSlip->hasPlannerHistory())
                                <button type="button"
                                        x-data=""
                                        x-on:click="$dispatch('open-modal', 'confirm-slip-deletion-{{ $bettingSlip->id }}')"
                                        class="text-sm font-medium text-red-600 hover:text-red-800">
                                    {{ __('Delete') }}
                                </button>
                            @elseif ($bettingSlip->status === BettingSlipStatus::Analysed)
                                <span class="text-sm text-neutral-400" title="{{ __('Analysed slips are kept as a record — archive it instead.') }}">
                                    {{ __('Archive to remove') }}
                                </span>
                            @elseif ($bettingSlip->hasPlannerHistory())
                                {{-- U-07.8 validation finding: source_betting_slip_id's restrictOnDelete constraint blocks deletion permanently, not only while a session is open (the PlannerSession row is never deleted, even Abandoned/Exported) — per PD-008/PD-009's "permanently preserved" principle. Hidden here, not just refused after the click. --}}
                                <span class="text-sm text-neutral-400" title="{{ __('This slip has been used in a planning session and is kept as part of that history — it can no longer be deleted.') }}">
                                    {{ __('Kept — used by Planner') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <x-workspace.confirmation-dialog name="confirm-slip-deletion-{{ $bettingSlip->id }}" :title="__('Delete this slip?')">
                        <p>{{ __('This permanently deletes ":name" and all of its legs.', ['name' => $bettingSlip->displayLabel()]) }}</p>
                        <x-slot name="actions">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    {{ __('Cancel') }}
                                </x-secondary-button>
                                <x-danger-button wire:click="deleteSlip({{ $bettingSlip->id }})" x-on:click="$dispatch('close')">
                                    {{ __('Delete') }}
                                </x-danger-button>
                        </x-slot>
                    </x-workspace.confirmation-dialog>
                @endforeach
            </div>
        @endif
    </div>
</div>
