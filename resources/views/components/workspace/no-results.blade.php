@props(['title' => 'No matching results', 'description' => 'Try changing your search or filters.'])

<div role="status" {{ $attributes->merge(['class' => 'rounded-lg border border-neutral-200 bg-surface-soft px-6 py-8 text-center']) }}>
    <x-heroicon-o-magnifying-glass class="mx-auto size-7 text-neutral-500" aria-hidden="true" />
    <h3 class="mt-3 workspace-card-title">{{ __($title) }}</h3>
    <p class="mt-1 workspace-helper">{{ __($description) }}</p>
</div>
