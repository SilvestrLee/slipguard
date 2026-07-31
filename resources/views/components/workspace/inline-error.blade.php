@props(['title' => 'Something needs attention'])

<div role="alert" {{ $attributes->merge(['class' => 'flex gap-3 rounded-md border border-alert-error/30 bg-alert-error/10 px-4 py-3 text-sm text-alert-error-strong']) }}>
    <x-heroicon-o-exclamation-circle class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
    <div><p class="font-semibold">{{ __($title) }}</p><div class="mt-0.5">{{ $slot }}</div></div>
</div>
