{{--
    U-14.2 §3 — real entries only, sourced from this repository's own
    verified history (docs/00-governance/DECISION_LOG.md, CHANGELOG.md),
    rewritten in plain customer language — no internal governance
    jargon (no "U-07.8", "ADR-012", handover IDs), no invented version
    numbers or dates beyond what's actually true. A raw `<?php ?>` block
    here gets silently stripped by Blade when the file's root element is
    a component tag (the same issue found and fixed on Home in U-13.0) —
    @php/@endphp is the safe form in this position.
--}}
@php
$releases = [
    [
        'version' => '2026.4',
        'date' => '27 Jul 2026',
        'area' => __('Public Website'),
        'summary' => __('SlipGuard now has a full public website — dedicated pages for how analysis works, the Planner, example reports, and pricing, instead of one long page.'),
        'why_it_matters' => __('Easier to explore exactly the part of SlipGuard you care about, without scrolling past everything else.'),
        'technical' => __('New public information architecture; a shared light/dark theme system across every public and authenticated screen.'),
    ],
    [
        'version' => '2026.3',
        'date' => '27 Jul 2026',
        'area' => __('Customer Workspace'),
        'summary' => __('Added History, Journal, and Planning History — a full record of every analysis, reflection, and accumulator you\'ve planned.'),
        'why_it_matters' => __('You can look back on past decisions, not just the most recent one.'),
        'technical' => __('New workspace screens reading directly from your existing analyses — no recalculation, ever.'),
    ],
    [
        'version' => '2026.2',
        'date' => '27 Jul 2026',
        'area' => __('Planner'),
        'summary' => __('Introduced the Planner — deterministic help ranking your own accumulator selections by structural risk, with revision comparison and export.'),
        'why_it_matters' => __("See which of your own selections is adding the most risk, without SlipGuard ever choosing a selection for you."),
        'technical' => __('Planner sessions never modify your original slip; exporting always creates a new one.'),
    ],
    [
        'version' => '2026.1',
        'date' => '25 Jul 2026',
        'area' => __('Risk Engine'),
        'summary' => __('Launched deterministic structural risk analysis — a fixed rule set that scores a slip and explains its main contributing factor.'),
        'why_it_matters' => __('The same slip always produces the same result. No prediction, ever — just a clear explanation of structural risk.'),
        'technical' => __('Five active structural risk factors: number of selections, combined odds, individual odds, risk concentration, and market complexity.'),
    ],
];
@endphp
<x-public-layout title="Release Notes" description="What's changed in SlipGuard, and why it matters — in plain language.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="release-notes-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Release Notes') }}</p>
            <h1 id="release-notes-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('What changed, and why it matters.') }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __('A real record of what shipped — not marketing announcements.') }}
            </p>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-28): sitewide atmosphere-layer rhythm (see home.blade.php's Invisible Risk section for the full explanation) --}}
    <section class="public-section-quiet" aria-label="{{ __('Release history') }}" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <ol class="space-y-8">
                @foreach ($releases as $release)
                    <li>
                        <x-card>
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-600 font-tabular">
                                    {{ __('v:version', ['version' => $release['version']]) }}
                                </span>
                                <time class="text-xs text-neutral-500">{{ $release['date'] }}</time>
                            </div>
                            <p class="mt-3 text-xs font-semibold text-accent-strong uppercase tracking-wide">{{ $release['area'] }}</p>
                            <h2 class="mt-1 text-base font-semibold text-neutral-900">{{ $release['summary'] }}</h2>
                            <p class="mt-2 text-sm text-neutral-600"><span class="font-medium text-neutral-700">{{ __('Why it matters:') }}</span> {{ $release['why_it_matters'] }}</p>
                            <p class="mt-2 text-sm text-neutral-500"><span class="font-medium text-neutral-600">{{ __('Technical notes:') }}</span> {{ $release['technical'] }}</p>
                        </x-card>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-public-layout>
