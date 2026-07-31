@props(['variant' => 'standard', 'href' => null])

{{--
    U-12.0 (SGDS) — consolidates the ad hoc `bg-neutral-50 border border-neutral-200
    rounded-lg p-6` pattern repeated across nearly every screen into one primitive,
    using the new surface-card/elevation tokens (DESIGN_TOKENS.md) instead of a flat
    page-coloured box. `interactive` renders as a single semantic <a> when `href` is
    given, per COMPONENT_PRINCIPLES.md's Cards rule (never a <div> with a click handler).
--}}
@php
    // Founder direct instruction (2026-07-27): no shadows anywhere — every
    // variant below is now distinguished by border weight/contrast alone.
    // `shadow-elevation-*` stays in the class list (resolves to `none`,
    // costs nothing) rather than being stripped from every call site.
    $variantClasses = match ($variant) {
        'elevated' => 'workspace-primary-card shadow-elevation-2',
        'interactive' => 'workspace-record-surface border shadow-elevation-1 hover:border-neutral-400 transition-colors',
        'soft' => 'bg-surface-section border workspace-structural-border',
        default => 'workspace-record-surface border shadow-elevation-1',
    };
    $classes = "rounded-lg workspace-card-padding $variantClasses";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </div>
@endif
