@props(['label' => 'Filter results'])

<div aria-label="{{ __($label) }}" {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-lg border workspace-internal-border bg-surface-section p-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    {{ $slot }}
</div>
