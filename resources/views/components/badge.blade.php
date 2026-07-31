@props(['tone' => 'neutral'])

{{--
    U-12.0 (SGDS) — COMPONENT_PRINCIPLES.md's Badges component: lifecycle
    status, data-quality bands, and Labs status only, never risk bands
    (Risk Indicators are a distinct, higher-stakes component that stays
    as its own markup). `labs-*` tones added U-14.2, consolidating the
    Labs page's own previously-bespoke status pill markup into this
    shared primitive — each Labs status keeps its own dedicated token
    (DESIGN_TOKENS.md), never repurposing a quality token for it.
--}}
@php
    $toneClasses = match ($tone) {
        'quality-strong' => 'bg-quality-strong/10 text-quality-strong border border-quality-strong/30',
        'quality-good' => 'bg-quality-good/10 text-quality-good border border-quality-good/30',
        'quality-limited' => 'bg-quality-limited/10 text-quality-limited border border-quality-limited/30',
        'quality-insufficient' => 'bg-quality-insufficient/10 text-quality-insufficient border border-quality-insufficient/30',
        'labs-research' => 'bg-labs-research/10 text-labs-research-strong border border-labs-research/30',
        'labs-planned' => 'bg-labs-planned/10 text-labs-planned-strong border border-labs-planned/30',
        'labs-designing' => 'bg-labs-designing/10 text-labs-designing-strong border border-labs-designing/30',
        'labs-development' => 'bg-labs-development/10 text-labs-development-strong border border-labs-development/30',
        'labs-beta' => 'bg-labs-beta/10 text-labs-beta-strong border border-labs-beta/30',
        'labs-released' => 'bg-labs-released/10 text-labs-released-strong border border-labs-released/30',
        default => 'bg-neutral-100 text-neutral-600',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full $toneClasses"]) }}>
    {{ $slot }}
</span>
