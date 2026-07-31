<?php

use App\Domain\Planner\PlannerSessionStatus;
use App\Domain\Risk\Results\RiskBand;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * U-08.1 §5: Planning History — a reverse-chronological list of the
 * customer's own Planner sessions, reading the already-existing
 * plannerSessions() relation. Opens into the existing Planner Workspace
 * screen unmodified, regardless of the session's status.
 */
new #[Layout('layouts.app')] class extends Component
{
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

    public function with(): array
    {
        return [
            'sessions' => Auth::user()->plannerSessions()
                ->with(['sourceBettingSlip.legs', 'exportedBettingSlip.legs', 'regenerationEvents'])
                ->latest('updated_at')
                ->get(),
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="container-analytics workspace-gutter mx-auto workspace-stack">

        <x-page-header :title="__('Planning History')" :description="__('Every accumulator you have planned with SlipGuard.')" />

        @if ($sessions->isEmpty())
            <x-empty-state :title="__('Nothing here yet.')"
                :description="__('Once you plan an accumulator, you\'ll be able to look back on it here.')">
                <x-slot name="action">
                    <a href="{{ route('analyze') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-accent-strong border border-transparent rounded-md font-semibold text-sm text-white hover:bg-accent">
                        {{ __('Go to your slips') }}
                    </a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="workspace-section-panel workspace-section" aria-label="{{ __('Planning sessions') }}">
                @foreach ($sessions as $session)
                    @php
                        $latestEvent = $session->regenerationEvents->last();
                        $token = $this->riskBandToken($latestEvent?->risk_band);
                    @endphp
                    <x-workspace.record-card :href="route('planner.session', $session)"
                        :title="$session->sourceBettingSlip->displayLabel()"
                        :metadata="__('Updated :time', ['time' => $session->updated_at->diffForHumans()])"
                        wire:navigate wire:key="session-{{ $session->id }}">
                        <x-slot name="status">
                            <div class="flex items-center gap-3 shrink-0">
                                @if ($latestEvent?->risk_band)
                                    <x-workspace.risk-badge :band="$latestEvent->risk_band->label()" :tone="$token" />
                                @endif
                                <x-workspace.status-badge>{{ $session->status->label() }}</x-workspace.status-badge>
                            </div>
                        </x-slot>
                        @if ($session->status === PlannerSessionStatus::Exported && $session->exportedBettingSlip)
                            <p class="mt-3 text-sm text-neutral-600">
                                {{ __('Exported to: :name', ['name' => $session->exportedBettingSlip->displayLabel()]) }}
                            </p>
                        @endif
                    </x-workspace.record-card>
                @endforeach
            </div>
        @endif
    </div>
</div>
