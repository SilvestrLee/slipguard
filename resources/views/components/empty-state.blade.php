@props(['icon' => null, 'title', 'description' => null])

{{--
    U-12.0 (SGDS) — consolidates the near-identical empty-state blocks
    repeated across History/Journal/Planning History/Slip Index
    (EMPTY_STATES.md's copy is unchanged; this only unifies the markup).
--}}
<div {{ $attributes->merge(['class' => 'bg-surface-section border border-dashed workspace-structural-border rounded-lg p-10 text-center']) }}>
    @if ($icon)
        <x-dynamic-component :component="$icon" class="mx-auto size-8 text-neutral-400" aria-hidden="true" />
    @endif
    <h3 class="mt-3 text-base font-semibold text-neutral-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 text-sm text-neutral-600 max-w-md mx-auto">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
