@props(['label' => 'Load more', 'disabled' => false])

<div class="flex justify-center border-t border-neutral-200 pt-5">
    <button type="button" @disabled($disabled)
            {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center rounded-md border border-neutral-300 bg-surface-card px-4 text-sm font-semibold text-neutral-800 hover:bg-surface-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-60']) }}>
        {{ __($label) }}
    </button>
</div>
