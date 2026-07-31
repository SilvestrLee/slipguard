@props(['title' => 'Limited data'])

<aside {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg border border-alert-caution/30 bg-alert-caution/10 p-4 text-sm text-neutral-800']) }}>
    <x-heroicon-o-information-circle class="mt-0.5 size-5 shrink-0 text-alert-caution-strong" aria-hidden="true" />
    <div><p class="font-semibold">{{ __($title) }}</p><div class="mt-1 text-neutral-700">{{ $slot }}</div></div>
</aside>
