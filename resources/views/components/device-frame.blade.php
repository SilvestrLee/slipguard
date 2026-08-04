@props(['platform', 'src', 'alt'])
{{--
    `PO-U19.1-001` — the one, narrowly-scoped exception to
    `COMPONENT_PRINCIPLES.md`'s Hero Blocks "no device bezel" rule
    (documented there directly, not just here): permitted only because
    this section's entire purpose is communicating "this is a native
    app," where the frame itself carries real meaning rather than being
    decorative chrome. Never used anywhere else in the product.

    Deliberately generic hardware — no Apple/Google trademarked silhouette
    or logo, matching the same reasoning `ADR-012`/the existing "no
    official store badges" rule already applies: this isn't an official
    device render, it's a restrained visual convention for "phone-shaped."
    Graphite bezel, soft shadow, rounded corners — matches §13's "Rich
    Indigo, Graphite... rounded hardware... no exaggerated neon."
--}}
@php
    $isIphone = $platform === 'iphone';
@endphp
<div {{ $attributes->merge(['class' => 'relative w-full max-w-[280px] rounded-[2.75rem] bg-neutral-900 p-2.5 shadow-elevation-3 ring-1 ring-white/10']) }}>
    @if ($isIphone)
        <div class="absolute left-1/2 top-4 z-10 h-6 w-24 -translate-x-1/2 rounded-full bg-neutral-900" aria-hidden="true"></div>
    @else
        <div class="absolute left-1/2 top-3.5 z-10 h-2.5 w-2.5 -translate-x-1/2 rounded-full bg-neutral-900 ring-1 ring-white/20" aria-hidden="true"></div>
    @endif

    <div class="{{ $isIphone ? 'rounded-[2.25rem]' : 'rounded-[1.75rem]' }} overflow-hidden bg-surface-page">
        <img src="{{ $src }}" alt="{{ $alt }}" class="block w-full h-auto">
    </div>

    <span class="sr-only">{{ $isIphone ? __('iPhone mockup') : __('Android mockup') }}</span>
</div>
