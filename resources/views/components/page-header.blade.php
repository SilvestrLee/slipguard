@props(['title', 'description' => null])

{{--
    U-12.0 (SGDS) — consolidates the repeated "h2 title + supporting
    line + one action" header pattern at the top of Slip Index, History,
    Journal, Planning History, and the Builder.
--}}
<div {{ $attributes->merge(['class' => 'flex min-h-10 items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        {{-- The authenticated shell owns the visible sticky page title. Keep
             this heading available to assistive technology and to preserve
             the component's local section relationship without duplicating
             page chrome in the workspace content. --}}
        <h2 class="sr-only">{{ $title }}</h2>
        @if ($description)
            <p class="workspace-helper max-w-2xl">{{ $description }}</p>
        @endif
    </div>
    @isset($action)
        <div class="shrink-0">{{ $action }}</div>
    @endisset
</div>
