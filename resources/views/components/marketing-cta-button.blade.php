@props(['href', 'fixedHeight' => false])

{{--
    U-20.7 — consolidates the white-on-indigo primary CTA repeated
    identically across every public marketing page's closing CTA band
    (analyse, planner, reports, about, home — 9 call sites, verified via
    grep before extraction, not assumed) into one component. `fixedHeight`
    matches the one call site (home.blade.php's Final CTA) that sits in a
    flex row beside a secondary link and needs a fixed height instead of
    padding-driven auto height; every other call site keeps the original
    `px-6 py-3` auto-height treatment unchanged. Structure only — the
    rendered class list is byte-identical to what each call site had
    before this extraction.
--}}
@php
    $classes = 'inline-flex items-center justify-center text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant';
    $classes .= $fixedHeight ? ' h-12 px-6' : ' px-6 py-3';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
