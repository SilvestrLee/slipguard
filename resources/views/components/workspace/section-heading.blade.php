@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        <h2 class="workspace-section-title">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 workspace-helper">{{ $description }}</p>
        @endif
    </div>
    @isset($action)
        <div class="shrink-0">{{ $action }}</div>
    @endisset
</div>
