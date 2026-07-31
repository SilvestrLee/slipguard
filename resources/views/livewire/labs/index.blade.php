<?php

use App\Domain\Labs\LabsFeatureStatus;
use App\Domain\Labs\LabsInterestType;
use App\Models\LabsFeature;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.labs')] class extends Component
{
    /**
     * Maps a LabsFeatureStatus to its DESIGN_TOKENS.md colour-token suffix.
     * Kept here (presentation layer), not on the domain enum itself —
     * matches the same convention already used for RiskBand on the
     * Dashboard and Risk Report screens.
     */
    public function statusToken(LabsFeatureStatus $status): string
    {
        return match ($status) {
            LabsFeatureStatus::Research => 'research',
            LabsFeatureStatus::Planned => 'planned',
            LabsFeatureStatus::Designing => 'designing',
            LabsFeatureStatus::InDevelopment => 'development',
            LabsFeatureStatus::Beta => 'beta',
            LabsFeatureStatus::Released => 'released',
        };
    }

    /**
     * U-14.1/U-14.2: SlipGuard Labs is now guest-visible (read-only) —
     * this action itself remains customer-only. SD-002 approves exactly
     * two customer actions for MVP — never accepts user_id from the
     * client, always scopes to the authenticated user. A guest never
     * reaches this action: the view renders a "Sign in" link instead of
     * the toggle buttons when unauthenticated (defence in depth here
     * too, since `labs_feature_interests.user_id` is not nullable).
     */
    public function toggleInterest(int $featureId, string $type): void
    {
        abort_unless(Auth::check(), 401);

        $interestType = LabsInterestType::from($type);

        $feature = LabsFeature::published()->find($featureId);

        abort_if($feature === null, 404);

        $enabled = match ($interestType) {
            LabsInterestType::Notify => $feature->notify_enabled,
            LabsInterestType::Beta => $feature->beta_enabled,
        };

        abort_unless($enabled, 403);

        $existing = $feature->interests()
            ->where('user_id', Auth::id())
            ->where('type', $interestType)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $feature->interests()->create([
                'user_id' => Auth::id(),
                'type' => $interestType,
            ]);
        }
    }

    public function with(): array
    {
        $features = LabsFeature::published()
            ->when(Auth::check(), fn ($query) => $query->with(['interests' => fn ($q) => $q->where('user_id', Auth::id())]))
            ->get();

        return [
            'features' => $features,
        ];
    }
}; ?>

<div class="py-10 sm:py-12">
    <div class="container-standard mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <section aria-labelledby="labs-heading">
            <h1 id="labs-heading" class="text-2xl sm:text-3xl font-semibold text-neutral-900">
                {{ __('SlipGuard Labs') }}
            </h1>
            <p class="mt-2 text-base text-neutral-600 max-w-2xl">
                {{ __("A look at what we're exploring next. These are informational, not commitments — register your interest and it will help shape what we build.") }}
            </p>
        </section>

        @if ($features->isEmpty())
            {{-- EMPTY_STATES.md: "No Labs Features" --}}
            <x-empty-state icon="heroicon-o-map" :title="__('Nothing published yet.')"
                :description="__('We\'re preparing what\'s next for SlipGuard. Check back soon.')">
                @auth
                    <x-slot name="action">
                        <a href="{{ route('dashboard') }}" wire:navigate
                           class="inline-flex items-center justify-center min-h-11 px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                            {{ __('Return to dashboard') }}
                        </a>
                    </x-slot>
                @endauth
            </x-empty-state>
        @else
            <div class="space-y-4">
                @foreach ($features as $feature)
                    @php
                        $token = $this->statusToken($feature->status);
                        $notified = Auth::check() && $feature->interests->firstWhere('type', \App\Domain\Labs\LabsInterestType::Notify);
                        $joinedBeta = Auth::check() && $feature->interests->firstWhere('type', \App\Domain\Labs\LabsInterestType::Beta);
                    @endphp
                    <article class="bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6" aria-labelledby="labs-feature-{{ $feature->id }}-title">
                        <div class="flex items-start justify-between gap-4">
                            <h2 id="labs-feature-{{ $feature->id }}-title" class="text-lg font-medium text-neutral-900">
                                {{ $feature->title }}
                            </h2>
                            <x-badge tone="labs-{{ $token }}" class="shrink-0">
                                {{ $feature->status->label() }}
                            </x-badge>
                        </div>

                        <p class="mt-3 text-sm text-neutral-700">{{ $feature->summary }}</p>

                        @if ($feature->why_it_matters)
                            <div class="mt-3 pt-1">
                                <h3 class="text-xs font-medium text-neutral-500 uppercase tracking-wide">{{ __('Why this matters') }}</h3>
                                <p class="mt-1 text-sm text-neutral-600">{{ $feature->why_it_matters }}</p>
                            </div>
                        @endif

                        @if ($feature->notify_enabled || $feature->beta_enabled)
                            <div class="mt-3 pt-2 flex flex-wrap gap-3">
                                @auth
                                    @if ($feature->notify_enabled)
                                        <button type="button" wire:click="toggleInterest({{ $feature->id }}, 'notify')"
                                                aria-pressed="{{ $notified ? 'true' : 'false' }}"
                                                class="inline-flex items-center gap-1.5 min-h-11 px-4 rounded-md border text-sm font-medium focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent
                                                    {{ $notified ? 'border-accent text-accent bg-accent/5' : 'border-neutral-300 text-neutral-700 hover:bg-neutral-50' }}">
                                            @if ($notified)
                                                <x-heroicon-o-check class="h-4 w-4" aria-hidden="true" />
                                            @endif
                                            {{ $notified ? __('Notified') : __('Notify Me') }}
                                        </button>
                                    @endif

                                    @if ($feature->beta_enabled)
                                        <button type="button" wire:click="toggleInterest({{ $feature->id }}, 'beta')"
                                                aria-pressed="{{ $joinedBeta ? 'true' : 'false' }}"
                                                class="inline-flex items-center gap-1.5 min-h-11 px-4 rounded-md border text-sm font-medium focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent
                                                    {{ $joinedBeta ? 'border-accent text-accent bg-accent/5' : 'border-neutral-300 text-neutral-700 hover:bg-neutral-50' }}">
                                            @if ($joinedBeta)
                                                <x-heroicon-o-check class="h-4 w-4" aria-hidden="true" />
                                            @endif
                                            {{ $joinedBeta ? __('Joined Beta') : __('Join Beta') }}
                                        </button>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" wire:navigate
                                       class="inline-flex items-center gap-1.5 min-h-11 px-4 rounded-md border border-neutral-300 text-sm font-medium text-neutral-700 hover:bg-neutral-50">
                                        {{ __('Sign in to register interest') }}
                                    </a>
                                @endauth
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
