@props(['title'])

<aside {{ $attributes->merge(['class' => 'rounded-lg border border-neutral-300 bg-surface-card workspace-card-padding lg:sticky lg:top-24']) }}>
    <h2 class="workspace-section-title">{{ $title }}</h2>
    <div class="mt-4">{{ $slot }}</div>
</aside>
