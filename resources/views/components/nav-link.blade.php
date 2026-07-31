@props(['active'])

{{--
    U-16.1 (Phase 9): two real gaps found and fixed. (1) `aria-current`
    was set on the mobile drawer's own active-item markup in the same
    file (`navigation.blade.php`) but never on this component — every
    one of its six call sites was missing it, silently. Added here once,
    at the component level, so a future seventh nav item can't repeat
    the omission. (2) Active state relied on border + text colour only —
    `font-semibold` added so weight also distinguishes it, matching the
    "never rely on colour alone" rule this component's own active state
    otherwise only partly satisfied.
--}}
@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-accent text-sm font-semibold leading-5 text-neutral-900 focus:outline-none focus:border-accent-strong transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 focus:outline-none focus:text-neutral-700 focus:border-neutral-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
