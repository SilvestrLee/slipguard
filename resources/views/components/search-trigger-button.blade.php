@props(['paletteId' => 'command-palette'])
{{--
    Founder direct instruction (2026-07-28): a search icon, sitewide,
    opening a command-palette-style quick-navigation modal (inspired by a
    reference screenshot of OddStorm's own search modal — reused for its
    interaction pattern only: overlay, input, "quick navigation" list,
    keyboard hints; none of OddStorm's dark-green sportsbook styling or
    betting-market content was copied, per VISUAL_INSPIRATION.md's
    existing "referenced for tone/characteristics only" rule for that
    reference point). Requires an ancestor with `x-data` to provide
    `$dispatch` — every header this is used in already has one.
--}}
<button type="button" @click="$dispatch('open-modal', '{{ $paletteId }}')"
        {{ $attributes->merge(['class' => 'p-2 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 focus:outline-none transition ease-in-out duration-150']) }}
        aria-label="{{ __('Search') }}">
    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
</button>
