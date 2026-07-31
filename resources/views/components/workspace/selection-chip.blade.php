@props(['selected' => false, 'disabled' => false])

<button type="button" @disabled($disabled) @class([
    'inline-flex min-h-10 items-center gap-2 rounded-full border px-3 text-sm font-medium transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
    'border-accent/40 bg-accent/10 text-neutral-900' => $selected,
    'border-neutral-300 bg-surface-card text-neutral-700 hover:bg-surface-soft' => ! $selected && ! $disabled,
    'cursor-not-allowed border-neutral-200 bg-neutral-100 text-neutral-500 opacity-70' => $disabled,
]) {{ $attributes }}>
    @if ($selected)
        <x-heroicon-o-check class="size-4" aria-hidden="true" />
    @endif
    {{ $slot }}
</button>
