@php
    $stages = [
        'receiving' => __('Receiving your slip'),
        'checking' => __('Checking your selections'),
        'evaluating' => __('Evaluating structural risk'),
        'preparing' => __('Preparing your report'),
        'complete' => __('Analysis complete'),
    ];
    $stageKeys = array_keys($stages);
    $currentIndex = array_search($currentStage, $stageKeys, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
@endphp

<div class="workspace-grid items-start">
    <section class="workspace-primary-card rounded-xl workspace-card-padding" aria-labelledby="analysis-progress-heading">
        <p class="sr-only">{{ __('Current analysis stage: :stage', ['stage' => $stages[$currentStage]]) }}</p>
        <div class="flex items-start gap-4">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-accent-strong text-white">
                @if ($currentStage === 'complete')
                    <x-heroicon-o-check class="size-6" aria-hidden="true" />
                @else
                    <x-heroicon-o-arrow-path class="size-6 motion-safe:animate-spin" aria-hidden="true" />
                @endif
            </span>
            <div>
                <p class="workspace-metadata">{{ __('Deterministic structural analysis') }}</p>
                <h2 id="analysis-progress-heading" class="mt-1 workspace-title">
                    {{ $currentStage === 'complete' ? __('Analysis complete') : __('Analysing your slip') }}
                </h2>
                <p class="mt-2 workspace-helper">
                    @if ($currentStage === 'complete')
                        {{ __('Your persisted risk report is ready.') }}
                    @else
                        @php $selectionCount = $facts['selections_received'] ?? $bettingSlip->legs->count(); @endphp
                        {{ trans_choice('SlipGuard is evaluating the recorded structure of :count selection.|SlipGuard is evaluating the recorded structure of :count selections.', $selectionCount, ['count' => $selectionCount]) }}
                    @endif
                </p>
            </div>
        </div>

        <ol class="mt-7 space-y-2" aria-label="{{ __('Analysis progress') }}">
            @foreach ($stages as $key => $label)
                @php
                    $stageIndex = array_search($key, $stageKeys, true);
                    $state = ($currentStage === 'complete' && $key === 'complete') || $stageIndex < $currentIndex
                        ? 'complete'
                        : ($stageIndex === $currentIndex ? 'current' : 'pending');
                @endphp
                <x-workspace.progress-stage :label="$label" :state="$state" />
            @endforeach
        </ol>

        <p class="mt-6 text-xs leading-5 text-neutral-500">
            {{ __('No outcome is predicted. The same slip and rule set always produce the same structural analysis.') }}
        </p>
    </section>

    <aside class="workspace-section-panel rounded-xl workspace-card-padding lg:sticky lg:top-24" aria-labelledby="submitted-slip-heading">
        <p class="workspace-metadata">{{ __('Submitted slip') }}</p>
        <h2 id="submitted-slip-heading" class="mt-1 workspace-card-title">{{ $bettingSlip->displayLabel() }}</h2>

        <dl class="mt-5 space-y-4 text-sm">
            <div>
                <dt class="workspace-metadata">{{ __('Selections received') }}</dt>
                <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['selections_received'] ?? $bettingSlip->legs->count() }}</dd>
            </div>

            @if (array_key_exists('selections_validated', $facts))
                <div class="border-t workspace-internal-border pt-4">
                    <dt class="workspace-metadata">{{ __('Selections validated') }}</dt>
                    <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['selections_validated'] }}</dd>
                </div>
            @endif

            <div class="border-t workspace-internal-border pt-4">
                <dt class="workspace-metadata">{{ __('Competitions represented') }}</dt>
                <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['competitions_recorded'] ?? $bettingSlip->legs->pluck('competition')->filter()->unique()->count() }}</dd>
            </div>

            @if (array_key_exists('market_types_recognized', $facts))
                <div class="border-t workspace-internal-border pt-4">
                    <dt class="workspace-metadata">{{ __('Market types recognised') }}</dt>
                    <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['market_types_recognized'] }}</dd>
                </div>
            @else
                <div class="border-t workspace-internal-border pt-4">
                    <dt class="workspace-metadata">{{ __('Market types recorded') }}</dt>
                    <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['markets_recorded'] ?? $bettingSlip->legs->pluck('market_name')->filter()->unique()->count() }}</dd>
                </div>
            @endif

            @if (array_key_exists('structural_factors_evaluated', $facts))
                <div class="border-t workspace-internal-border pt-4">
                    <dt class="workspace-metadata">{{ __('Structural factors evaluated') }}</dt>
                    <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['structural_factors_evaluated'] }}</dd>
                </div>
                <div class="border-t workspace-internal-border pt-4">
                    <dt class="workspace-metadata">{{ __('Rule set') }}</dt>
                    <dd class="mt-1 font-semibold text-neutral-900">{{ $facts['rule_set_version'] }}</dd>
                </div>
            @endif
        </dl>

        @if (($facts['normalization_limitations'] ?? 0) > 0 || ($facts['factors_not_evaluated'] ?? 0) > 0)
            <x-workspace.limited-data-notice class="mt-5" :title="__('Some information has limitations')">
                {{ __('The completed report will identify which recorded information could not be fully evaluated.') }}
            </x-workspace.limited-data-notice>
        @endif
    </aside>
</div>
