@props(['variant' => 'info'])

{{--
    U-12.0 (SGDS) — new component (documented in COMPONENT_PRINCIPLES.md):
    consolidates the ad hoc `bg-red-50 border-red-200 text-red-700`-style
    flash-message blocks repeated across Builder/Journal/Slip Index, none
    of which had a dark-mode value. Uses dedicated alert-* tokens
    (DESIGN_TOKENS.md) rather than the raw Tailwind palette or any
    risk-band colour.
--}}
@php
    $variantClasses = match ($variant) {
        'success' => 'bg-alert-success/10 border-alert-success/30 text-alert-success-strong',
        'error' => 'bg-alert-error/10 border-alert-error/30 text-alert-error-strong',
        'caution' => 'bg-alert-caution/10 border-alert-caution/30 text-alert-caution-strong',
        default => 'bg-alert-info/10 border-alert-info/30 text-alert-info-strong',
    };
@endphp

<div role="alert" {{ $attributes->merge(['class' => "rounded-md border px-4 py-3 text-sm $variantClasses"]) }}>
    {{ $slot }}
</div>
