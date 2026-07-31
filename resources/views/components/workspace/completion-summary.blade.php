@props(['title'])

<section role="status" {{ $attributes->merge(['class' => 'rounded-lg border border-alert-success/30 bg-alert-success/10 workspace-card-padding']) }}>
    <div class="flex gap-3">
        <x-heroicon-o-check-circle class="size-6 shrink-0 text-alert-success-strong" aria-hidden="true" />
        <div>
            <h2 class="workspace-card-title">{{ $title }}</h2>
            <div class="mt-1 workspace-helper">{{ $slot }}</div>
            @isset($action)<div class="mt-4">{{ $action }}</div>@endisset
        </div>
    </div>
</section>
