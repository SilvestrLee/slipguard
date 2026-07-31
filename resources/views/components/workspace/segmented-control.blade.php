@props(['label'])

<div role="group" aria-label="{{ $label }}"
     {{ $attributes->merge(['class' => 'inline-flex flex-wrap gap-1 rounded-lg border border-neutral-200 bg-surface-soft p-1']) }}>
    {{ $slot }}
</div>
