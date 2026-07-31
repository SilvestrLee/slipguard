@props(['band', 'tone' => 'moderate'])

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-lg border border-risk-{$tone}/30 bg-risk-{$tone}/10 px-3 py-1.5 text-sm font-semibold text-risk-{$tone}"]) }}>
    <x-heroicon-o-exclamation-triangle class="size-4" aria-hidden="true" />
    <span>{{ $band }}</span>
</span>
