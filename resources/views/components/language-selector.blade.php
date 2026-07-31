@props(['modalId' => 'language-modal'])
{{--
    Founder direct instruction (2026-07-28), refined 2026-07-31 to open a
    modal "inspired by the search modal" rather than a small dropdown —
    matches `<x-search-trigger-button>`'s own structure exactly (no
    wrapping element, `$attributes` merged directly onto the button, same
    icon-button classes, same aria-label convention) so the two remain
    interchangeable everywhere they're already placed side by side,
    including the mobile drawer's `x-on:click="closeMenu(false)"` usage.
    Requires an ancestor with `x-data` to provide `$dispatch`, identical
    requirement to the search trigger.
--}}
<button type="button" @click="$dispatch('open-modal', '{{ $modalId }}')"
        {{ $attributes->merge(['class' => 'p-2 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 focus:outline-none transition ease-in-out duration-150']) }}
        aria-label="{{ __('Language') }}">
    <x-heroicon-o-language class="h-5 w-5" />
</button>
