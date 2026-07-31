@props(['href' => null, 'title', 'metadata' => null])

@php
    $classes = 'group block rounded-lg border workspace-record-surface workspace-card-padding transition-colors hover:border-neutral-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <article {{ $attributes->merge(['class' => $classes]) }}>
@endif
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h3 class="workspace-card-title truncate">{{ $title }}</h3>
            @if ($metadata)
                <p class="mt-1 workspace-metadata">{{ $metadata }}</p>
            @endif
        </div>
        @isset($status)
            <div class="shrink-0">{{ $status }}</div>
        @endisset
    </div>
    {{ $slot }}
@if ($href)
    </a>
@else
    </article>
@endif
