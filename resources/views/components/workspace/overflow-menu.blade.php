@props(['label' => 'More actions'])

<details {{ $attributes->merge(['class' => 'relative']) }}>
    <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-md text-neutral-600 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
             aria-label="{{ __($label) }}">
        <x-heroicon-o-ellipsis-horizontal class="size-5" aria-hidden="true" />
    </summary>
    <div class="absolute right-0 z-20 mt-2 min-w-44 rounded-md border border-neutral-300 bg-surface-card p-1">
        {{ $slot }}
    </div>
</details>
