@props(['rows' => 3, 'label' => 'Loading content'])

<div role="status" aria-live="polite" aria-label="{{ __($label) }}" {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @foreach (range(1, $rows) as $row)
        <div class="h-16 animate-pulse rounded-lg border border-neutral-200 bg-neutral-100" aria-hidden="true"></div>
    @endforeach
    <span class="sr-only">{{ __($label) }}</span>
</div>
