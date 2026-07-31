@props(['name' => 'search', 'label' => 'Search', 'placeholder' => 'Search'])

<label class="relative block min-w-0 flex-1">
    <span class="sr-only">{{ __($label) }}</span>
    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-500" aria-hidden="true" />
    <input type="search" name="{{ $name }}" placeholder="{{ __($placeholder) }}"
           {{ $attributes->merge(['class' => 'min-h-11 w-full rounded-md border-neutral-300 bg-surface-card pl-9 text-sm text-neutral-900 placeholder:text-neutral-500 focus:border-accent focus:ring-accent']) }}>
</label>
