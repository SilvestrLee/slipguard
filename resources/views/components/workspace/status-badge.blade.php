@props(['icon' => null])

<x-badge {{ $attributes }}>
    @if ($icon)
        <x-dynamic-component :component="$icon" class="size-3.5" aria-hidden="true" />
    @endif
    {{ $slot }}
</x-badge>
