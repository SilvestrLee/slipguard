{{--
    U-15.2 (`PO-U15.2-001`) — complete homepage recomposition. HOMEPAGE_STORYBOARD.md
    v1.2 is the authority for the section order and reasoning; this is the
    implementation. The full Problem/How-it-works explanations still live on
    /analyse and the full example report on /reports (U-13.0's decision is
    not reversed) — every new section here is deliberately brief and uses
    different wording than those dedicated pages, never a verbatim copy.

    A raw `<?php ?>` block here gets silently stripped by Blade when the
    file's root element is a component tag (found during U-13.0) —
    @php/@endphp is the safe form in this position.
--}}
@php
    // `PO-U15.6-001` §29/§33: every value now reads from
    // `config/slipguard-product-facts.php` — the one maintained source of
    // truth — instead of being hardcoded here (the test count had already
    // drifted stale: 482 vs. the real, current 512). An explanation line
    // was added per §32 Option A ("always visible" — never hover-only,
    // since touch and keyboard users can't hover and this is trust
    // information, not a decoration).
    $facts = config('slipguard-product-facts');
    $slipguardMetrics = [
        [
            'value' => ($facts['deterministic'] ? '100%' : '0%'),
            'label' => __('Deterministic'),
            'explanation' => __('The same slip, evidence and rule-set version produce the same structural analysis.'),
            'animate' => false,
        ],
        [
            'value' => (string) $facts['outcome_predictions'],
            'label' => __('Outcome Predictions'),
            'explanation' => __('SlipGuard does not claim that a selection will win, lose or draw.'),
            'animate' => false,
        ],
        [
            'value' => __('Every'),
            'label' => __('Finding Explained'),
            'explanation' => __('A structural conclusion is always presented with the reason that produced it.'),
            'animate' => false,
        ],
        [
            'value' => (string) $facts['automated_tests'],
            'label' => __('Automated Tests'),
            'explanation' => __('The product is checked against repeatable behaviours and expected outputs.'),
            'animate' => true,
        ],
        [
            'value' => (string) $facts['structural_factors'],
            'label' => __('Structural Factors'),
            'explanation' => __('Each report evaluates a defined set of structural characteristics, not a general opinion.'),
            'animate' => true,
        ],
        [
            'value' => '1',
            'label' => __('Main Contributing Factor'),
            'explanation' => __('Each report identifies the single factor contributing most to the structural score.'),
            'animate' => true,
        ],
    ];
@endphp
<x-public-layout description="SlipGuard is an independent betting decision intelligence platform — deterministic structural risk analysis, explained, never a prediction.">

    {{--
        1. Hero — `PO-U15.4-001` reconstruction. The prior split (copy left,
        illustrated sample-report card right) is replaced with a Linear-
        inspired two-row structure: Row One is copy (left) + actions (right),
        Row Two is a full-width real Demo Workspace dashboard screenshot.
        The illustrated report card previously here is fully removed — its
        markup was unique to this Hero (verified: `grep`'d for its own
        copy strings against every other view first; no shared component
        depended on it) — its full example still lives on /reports,
        unaffected.

        Specialist consultation performed before finalising (`PO-GOV-UX-002`):
        UI UX Pro Max's `gsap` domain corroborated a subtle one-time
        opacity+translate-y reveal (300-400ms) for the showcase, and
        explicitly cautioned that scroll-scrubbed parallax belongs on
        "background/decorative layers only, never... interactive controls"
        — the dashboard screenshot is real product content, not decoration,
        so continuous scroll-linked parallax was evaluated and rejected for
        that reason; only the one-time reveal was adopted. 21st.dev/Magic
        was queried but returned a network error at the time of this
        implementation (`getaddrinfo ENOTFOUND 21st.dev`) — disclosed
        explicitly rather than silently skipped or fabricated a result for.
    --}}
    <section class="public-hero-atmosphere relative bg-gradient-hero" aria-labelledby="hero-heading">
        <div class="container-marketing mx-auto px-6 pt-14 pb-6 sm:pt-20 sm:pb-8">
            {{--
                `PO-U15.4R-001` §5.1/§13.1: the top row uses a narrower inner
                width than the dashboard row below, so the dashboard reads
                as wider/more immersive than the copy — extra horizontal
                padding on large screens narrows the copy row's effective
                width without a second, raw `max-w-Nxl` container token
                (`tests/Feature/DesignSystemTest.php`'s own existing
                "container-marketing throughout, never a raw max-w-Nxl
                wrapper" rule — an arbitrary `max-w-6xl` was tried first and
                correctly caught by that test).
            --}}
            <div class="lg:px-8 xl:px-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-start">
                    <div class="lg:col-span-7">
                        <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">
                            {{ __('Deterministic Betting Decision Intelligence') }}
                        </p>
                        <h1 id="hero-heading" class="mt-4 text-4xl sm:text-5xl font-semibold tracking-tight text-neutral-900 leading-[1.1]">
                            <span class="bg-gradient-button bg-clip-text text-transparent">{{ __('Know') }}</span>
                            {{ __('where') }}
                            <span class="bg-gradient-button bg-clip-text text-transparent">{{ __('the risk') }}</span>
                            {{ __('is in your slip') }}
                            <span class="bg-gradient-button bg-clip-text text-transparent">{{ __('before you place it.') }}</span>
                        </h1>
                        <p class="mt-5 text-base sm:text-lg text-neutral-600 max-w-lg">
                            {{ __("SlipGuard analyzes the structure of a betting slip and explains where its unnecessary risk comes from — which leg contributes most and why. It does not predict who wins.") }}
                        </p>
                    </div>

                    {{--
                        Right column: actions, moved from the left per §3.1/§4.
                        `PO-U15.4R-001` §5.2: no longer bottom-of-row aligned —
                        a top offset instead positions it roughly level with
                        the headline/description transition ("editorial
                        asymmetry, not arbitrary centring"), and the CTA group
                        itself now carries the column's visual weight (no
                        supporting statement — removed per founder direct
                        instruction, 2026-07-28, superseding this same
                        commission's own §8 recommendation to re-add it).
                    --}}
                    <div class="lg:col-span-5 lg:pt-14 text-end">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            @auth
                                <a href="{{ route('analyze.create') }}" wire:navigate
                                   class="inline-flex items-center justify-center h-14 px-8 text-base font-semibold text-white bg-gradient-button rounded-md hover:brightness-110 transition-[filter] duration-instant">
                                    {{ __('Analyse Your Slip') }}
                                </a>
                            @else
                                <a href="{{ route('register') }}" wire:navigate
                                   class="inline-flex items-center justify-center h-14 px-8 text-base font-semibold text-white bg-gradient-button rounded-md hover:brightness-110 transition-[filter] duration-instant">
                                    {{ __('Analyse Your Slip') }}
                                </a>
                            @endauth
                            <a href="{{ route('reports') }}" wire:navigate
                               class="inline-flex items-center justify-center h-14 px-6 text-base font-semibold text-neutral-700 border border-neutral-300 rounded-md hover:bg-surface-soft hover:border-neutral-400 transition-colors duration-instant focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                                {{ __('See Example Report') }}
                            </a>
                        </div>

                        <p class="mt-5 text-sm text-neutral-600">
                            {{ __('Deterministic analysis · No outcome prediction · Every result explained') }}
                        </p>
                    </div>
                </div>
            </div>

            {{--
                Row Two — Dashboard Showcase (§3.2/§9-§17). A real screenshot
                of DW-01 (the canonical Demo Workspace, `PO-U17.0-001`) at
                `/dashboard`, not a hand-designed mock-up — captured directly
                via Playwright against the actual running application.
                Light/dark variants swap via pure CSS keyed off the same
                `[data-theme]` attribute/media-query pair every other themed
                token in this app already uses (`.hero-dashboard-image*`
                rules, `resources/css/app.css`) — no JS-driven `src` swap, so
                there is no pre-hydration window for the wrong-theme
                screenshot to flash during (§35).

                `PO-U15.4R-001` §13.2: this row is deliberately NOT wrapped in
                the same `max-w-6xl` as the top row, and gets a small negative
                horizontal bleed (`-mx-3 sm:-mx-4`) beyond the section's own
                padding, so it reads as wider/more immersive than the copy
                above it without introducing a new, wider global container.
            --}}
            <div
                class="mt-8 sm:mt-10 -mx-3 sm:-mx-4"
                x-data="{
                    shown: false,
                    reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                }"
                x-init="
                    const observer = new IntersectionObserver(([entry]) => {
                        if (entry.isIntersecting && ! shown) {
                            shown = true;
                            observer.disconnect();
                        }
                    }, { threshold: 0.2 });
                    observer.observe($el);
                "
            >
                <div class="flex items-center justify-center gap-2">
                    <span class="size-1.5 rounded-full bg-accent-strong" aria-hidden="true"></span>
                    <p class="text-sm font-semibold tracking-widest text-accent-strong uppercase">{{ __('Product Preview') }}</p>
                </div>
                <p class="mt-2 text-center text-sm text-neutral-700 max-w-md mx-auto">
                    {{ __('The SlipGuard workspace is actively evolving. Built from the real SlipGuard Demo Workspace — some interface details may change as the product develops.') }}
                </p>

                {{--
                    `PO-U15.4R-001` §21: a real negative-margin overlap into
                    the following "Invisible Risk" section — `relative z-10`
                    so the frame paints above that section's own background
                    rather than being covered by it (verified visually, not
                    assumed — a naive negative margin without the explicit
                    z-index was tried first and did get covered).
                --}}
                <div class="hero-dashboard-glow relative z-10 mt-4 -mb-12 sm:-mb-16">
                    {{-- U-08.1 §11: this is now the Hero's one named "premium light sweep" placement, moved from the removed report illustration. --}}
                    <div
                        class="hero-dashboard-frame light-sweep relative rounded-xl border bg-surface-card overflow-hidden shadow-elevation-2 transition-[opacity,transform] duration-700 ease-out"
                        :class="reduceMotion ? '' : (shown ? 'opacity-100 scale-100 translate-y-0' : 'opacity-0 scale-[0.985] translate-y-4')"
                    >
                        <img src="{{ asset('images/homepage/slipguard-dashboard-demo-light.webp') }}"
                             width="1400" height="820" loading="eager"
                             alt="{{ __('SlipGuard demonstration workspace showing structural risk summaries, recent analyses, planning activity and customer decision history.') }}"
                             class="hero-dashboard-image hero-dashboard-image--light w-full h-auto block">
                        <img src="{{ asset('images/homepage/slipguard-dashboard-demo-dark.webp') }}"
                             width="1400" height="820" loading="eager" alt="" aria-hidden="true"
                             class="hero-dashboard-image hero-dashboard-image--dark w-full h-auto block">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{--
        `PO-U16.2-CR-001` §8/§9: every section background below changed from
        fully opaque to a translucent, still-token-derived surface
        (`DESIGN_TOKENS.md`'s Gradients section, amended) — so the shell's
        `bg-gradient-page` (layouts/public.blade.php) is now visible through
        every section rather than being covered by an opaque fill, without
        changing any copy, order, or removing a section. Hard `border-t`/
        `border-b` cuts between sections removed in favour of the visible
        gradient continuity itself doing the separating (§8.5's "hard
        section cuts into subtle tonal transitions," applied consistently,
        not only at the footer boundary it was originally written for).

        2. Invisible Risk (replaces the former "Problem" section, `U-15.3`,
        `PO-U15.3-001`; interactive demonstration added `PO-U15.5-001`) —
        an illustrated sample accumulator rather than a paragraph, with one
        leg marked as the weakest structurally, not the one with the
        longest odds. Brief; the full explanation still lives on /analyse.

        `PO-U15.5-001` — every number in the demonstration (structural
        score 53, weakest-leg contribution 28 points / ~53%, comparison
        score 25, High → Moderate band change) is real deterministic engine
        output, not invented: computed once via `App\Domain\Risk\Engine\RankLegsByStructuralWeakness`
        (the accepted Marginal Structural Contribution model,
        `PO-U06.2A-AC-001`) against a slip built from these exact five
        legs, run through `php artisan tinker` against the real engine
        (verified: `rule_set_version 2026.1`), then curated here as static
        content — satisfying §21's "acceptable implementation: generate
        one real demonstration analysis, approve its values, store the
        curated public representation" without executing the engine per
        visitor. The odds used to produce these values are not displayed
        (this illustration only ever showed selection names, not odds), so
        no invented-looking number appears without a real source; the exact
        computation is reproducible from this comment alone.
    --}}
    {{--
        Founder direct instruction (2026-07-28): "maintain an atmosphere
        layer rhythm sitewide — after a section that has an atmosphere
        rhythm, the next section should be subtle gradient, not flat, then
        the next section will have the atmosphere layer, and so on." The
        fixed `.atmosphere` layer is one shared, `position: fixed` layer
        per layout (not per-section). PW-03 adds restrained scroll-linked
        depth to those fixed forms.

        The clarified rhythm is explicit rather than opacity-by-degree:
        `public-section-atmosphere` reveals the shared layer, while
        `public-section-quiet` masks it with an opaque multi-stop neutral
        gradient and restrained edge definition. Quiet sections therefore
        remain dimensional without carrying the atmospheric forms.
        Alternation across
        this page: Hero (atmosphere, via `gradient-hero`'s own
        translucency) → Invisible Risk (quiet) → How SlipGuard Thinks
        (atmosphere) → Proof (quiet — `gradient-inverse`'s pre-existing
        opaque dark band predates this rhythm and is left as the one
        deliberate exception, not retrofitted) → How It Works (atmosphere)
        → Capabilities (quiet) → Trust (atmosphere) → CTA (`gradient-cta`,
        same pre-existing-opaque-exception reasoning as Proof).
    --}}
    <section class="public-section-quiet py-16 sm:py-20" aria-labelledby="invisible-risk-heading" data-reveal>
        <div class="container-marketing mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-10 items-center">
                <div class="lg:col-span-5">
                    <p class="text-xs font-semibold tracking-widest text-neutral-400 uppercase">{{ __('Invisible Risk') }}</p>
                    <h2 id="invisible-risk-heading" class="mt-3 text-2xl sm:text-3xl font-semibold text-neutral-900">
                        {{ __("The most dangerous selection isn't always the one with the longest odds.") }}
                    </h2>
                    <p class="mt-4 text-base text-neutral-600 max-w-lg">
                        {{ __('SlipGuard analyses how selections interact with one another and identifies where structural uncertainty accumulates — not just which one looks riskiest at a glance.') }}
                    </p>
                    {{-- `PO-U15.5-001` §18: customer-control statement — visible, not a tooltip. --}}
                    <p class="mt-4 text-sm font-medium text-neutral-700">
                        {{ __('SlipGuard shows the structural effect. You decide what to do.') }}
                    </p>
                    <a href="{{ route('analyse') }}" wire:navigate class="mt-6 inline-flex items-center text-sm font-semibold text-accent-strong hover:text-accent">
                        {{ __('See how weakest-leg analysis works') }} →
                    </a>
                </div>

                {{--
                    Illustrated sample accumulator — deliberately labelled
                    "Sample," matching the Hero preview's own "Sample
                    Structural Report" convention, so it never reads as a
                    real slip or a tip.

                    `PO-U15.5-001`: every value below (the contribution bar,
                    the 53/25 structural-score comparison, the High →
                    Moderate band change) is always present in the markup —
                    never injected only after JavaScript runs — satisfying
                    §28's "if JavaScript fails... the section remains
                    understandable" requirement. The per-row stagger and the
                    comparison's own reveal reuse the exact sitewide
                    `data-reveal` mechanism (an inline `transition-delay`
                    per element, exactly like the "How SlipGuard Thinks"
                    pipeline stages above), not a new animation approach —
                    satisfying `MOTION_SYSTEM.md`'s "one reveal-in per
                    section" budget by treating the whole sequence as one
                    coordinated reveal event, not a second animation layered
                    on top of a separate fade. No optional "show impact"
                    button was added: §10.3 explicitly prefers the simplest
                    implementation where the scroll-triggered reveal alone
                    already communicates the concept.
                --}}
                <div class="lg:col-span-7">
                    <div class="rounded-lg border border-neutral-300 bg-surface-card overflow-hidden">
                        <div class="flex items-center gap-2 px-6 py-3 border-b border-neutral-200/60 bg-surface-soft">
                            <x-heroicon-o-shield-check class="size-4 text-neutral-400" aria-hidden="true" />
                            <p class="text-xs font-semibold text-neutral-500">{{ __('Sample Accumulator') }}</p>
                        </div>
                        <div class="p-6 sm:p-7 space-y-2">
                            @foreach ([
                                ['label' => __('Chelsea — Win'), 'weakest' => false],
                                ['label' => __('Arsenal — Win'), 'weakest' => false],
                                ['label' => __('Over 2.5 Goals'), 'weakest' => true],
                                ['label' => __('Both Teams to Score'), 'weakest' => false],
                                ['label' => __('Draw No Bet'), 'weakest' => false],
                            ] as $i => $leg)
                                <div data-reveal style="transition-delay: {{ $i * 60 }}ms"
                                     class="rounded-md border {{ $leg['weakest'] ? 'border-risk-high/30 bg-risk-high/5' : 'border-neutral-200/60' }}">
                                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                                        <span class="text-sm font-medium text-neutral-900">{{ $leg['label'] }}</span>
                                        @if ($leg['weakest'])
                                            <span class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded border text-risk-high bg-risk-high/10 border-risk-high/30">
                                                {{ __('Weakest Leg') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($leg['weakest'])
                                        {{-- Contribution indicator — real MSC output (28 of 53 structural-score points), not an invented percentage. --}}
                                        <div class="px-4 pb-3">
                                            <div class="flex items-center justify-between text-xs text-neutral-600">
                                                <span>{{ __('Contribution to structural risk') }}</span>
                                                <span class="font-semibold text-risk-high">{{ __('53%') }}</span>
                                            </div>
                                            <div class="mt-1.5 h-1.5 rounded-full bg-neutral-200/70 overflow-hidden">
                                                <div class="h-full rounded-full bg-risk-high" style="width: 53%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Structural comparison — the section's aha moment. --}}
                        <div data-reveal style="transition-delay: 320ms" class="px-6 sm:px-7 py-5 border-t border-neutral-200/60 bg-surface-soft">
                            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Structural Score') }}</p>
                            <div class="mt-2 flex items-center gap-4">
                                <div>
                                    <p class="text-2xl font-semibold text-neutral-900 font-tabular">53</p>
                                    <p class="text-xs text-neutral-500">{{ __('Current slip · High') }}</p>
                                </div>
                                <x-heroicon-o-arrow-right class="size-4 text-neutral-400 shrink-0" aria-hidden="true" />
                                <div>
                                    <p class="text-2xl font-semibold text-neutral-900 font-tabular">25</p>
                                    <p class="text-xs text-neutral-500">{{ __('Without weakest leg · Moderate') }}</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-neutral-500">
                                {{ __('Lower structural concentration does not predict the result. It only shows how the risk in this slip is distributed.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{--
        3. How SlipGuard Thinks (replaces the former "Solution" section,
        `U-15.3`, `PO-U15.3-001`; per-stage explanations and an
        authenticity statement added `PO-U15.6-001`) — a simplified
        five-stage pipeline illustration. `PO-U15.3-001`'s own §6 claimed
        this "deliberately aligns with the recently approved U-07 Evidence
        Acquisition Strategy" and named a "Parser"/"Evidence Warehouse"
        architecture — verified directly against this repository's actual
        `docs/adr/ADR-INDEX.md` and `TASKS.md` and found to be fabricated;
        `PO-U15.6-001` repeated the *identical* fabrication under a new
        citation ("AO-U07.1-001 — Evidence Acquisition & Intelligence Data
        Strategy") and an even more elaborate claimed pipeline (Parser →
        Normaliser → Fixture Identification → Evidence Acquisition →
        Evidence Warehouse → Deterministic Rule Engine → Risk Scoring →
        Explainability → Customer Report) — re-verified the same way, with
        the same result: `U-07` is Planner Orchestration (`ADR-008`/
        `ADR-009`), not an Evidence Acquisition Strategy; no "AO-U07.1-001"
        document, no "Evidence Warehouse," no "Fixture Identification"
        stage exists anywhere in this repository. `PO-U15.6-001`'s own §8
        "Approved Customer-Facing Intelligence Flow" independently
        specifies the identical five stages already implemented here
        (Your Slip → Normalisation → Deterministic Rules → Explainability
        → Risk Report) — so no stage relabelling was needed this time,
        only the fabricated §9 "architectural mapping table" was rejected,
        not reproduced.
    --}}
    <section class="public-section-atmosphere py-16 sm:py-20" aria-labelledby="how-slipguard-thinks-heading" data-reveal>
        <div class="container-marketing mx-auto px-6">
            <div class="text-center max-w-xl mx-auto">
                <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('How SlipGuard Thinks') }}</p>
                <h2 id="how-slipguard-thinks-heading" class="mt-3 text-2xl sm:text-3xl font-semibold text-neutral-900">
                    {{ __('Every report begins with evidence, not opinion.') }}
                </h2>
                <p class="mt-4 text-base text-neutral-600">
                    {{ __('SlipGuard reads your slip, applies a fixed set of deterministic rules, and explains every conclusion in plain language — it never guesses, and it never predicts.') }}
                </p>
            </div>

            {{--
                Progressive, staggered reveal — each stage marked with its
                own `data-reveal` (the exact same sitewide mechanism as
                every other section, not a new one) plus an inline
                `transition-delay`, so the five stages visibly reveal in
                sequence rather than all at once. This is read as one
                coordinated reveal event for the section, not a second
                animation layered on top of a separate fade — satisfying
                `MOTION_SYSTEM.md`'s "one reveal-in per section maximum."
                Content (the stage label and icon) is never hidden by
                default in markup, only by the same JS-added classes every
                other `data-reveal` element already uses — so without
                JavaScript, every stage remains fully visible immediately,
                simply without the staggered entrance.
            --}}
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-5 gap-4 sm:gap-3 items-start">
                @foreach ([
                    ['label' => __('Your Slip'), 'icon' => 'document-text', 'explanation' => __('SlipGuard reads the selections you provide.')],
                    ['label' => __('Normalisation'), 'icon' => 'squares-2x2', 'explanation' => __('Different market and competition names become one consistent structure.')],
                    ['label' => __('Deterministic Rules'), 'icon' => 'cog-6-tooth', 'explanation' => __('The same input and rule set always produce the same analysis.')],
                    ['label' => __('Explainability'), 'icon' => 'chat-bubble-left-right', 'explanation' => __('Every finding is translated into plain language.')],
                    ['label' => __('Risk Report'), 'icon' => 'shield-check', 'explanation' => __('You receive the structural score, weakest leg and why it matters.')],
                ] as $i => $stage)
                    <div class="flex sm:flex-col items-center gap-4 sm:gap-0">
                        <div data-reveal style="transition-delay: {{ $i * 90 }}ms"
                             class="flex-1 sm:flex-none sm:w-full rounded-lg border border-neutral-300 bg-surface-card px-4 py-5 text-center">
                            <x-dynamic-component :component="'heroicon-o-'.$stage['icon']" class="size-5 text-accent-strong mx-auto" aria-hidden="true" />
                            <p class="mt-2 text-xs font-semibold text-neutral-900">{{ $stage['label'] }}</p>
                            <p class="mt-1 text-xs leading-snug text-neutral-500">{{ $stage['explanation'] }}</p>
                        </div>
                        @unless ($loop->last)
                            <div class="hidden sm:block h-px w-3 bg-neutral-300 shrink-0" aria-hidden="true"></div>
                        @endunless
                    </div>
                @endforeach
            </div>

            {{-- `PO-U15.6-001` §23: real, not fabricated — this is a plain-language description of the actual implemented domain flow, not a claimed live execution per visitor. --}}
            <p class="mt-8 text-center text-xs text-neutral-500">
                {{ __('This is the same deterministic process used by SlipGuard’s analysis engine.') }}
            </p>
        </div>
    </section>

    {{-- 4. Proof — the Intelligence Credibility Section (U-11.4), content and count-up logic unchanged --}}
    <section class="bg-surface-inverse bg-gradient-inverse" aria-labelledby="intelligence-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-widest">
                <span class="size-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span>
                {{ __('Deterministic Engine') }}
            </p>
            <p class="mt-4 text-xs font-semibold text-blue-400 uppercase tracking-widest">{{ __('Proof, Not a Promise') }}</p>
            <h2 id="intelligence-heading" class="mt-3 text-2xl sm:text-3xl font-semibold text-white max-w-2xl mx-auto">
                {{ __('Every analysis is deterministic, explainable and repeatable.') }}
            </h2>
            <p class="mt-4 text-sm text-slate-300 max-w-xl mx-auto">
                {{ __('SlipGuard applies a fixed rule set to every analysis, identifies the factors contributing most to structural risk, and explains why they matter.') }}
            </p>

            <div
                x-data="{
                    shown: false,
                    reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                    animateValue(el, target) {
                        if (this.reduceMotion) { el.textContent = target; return; }
                        const duration = 600;
                        const start = performance.now();
                        const step = (now) => {
                            const progress = Math.min((now - start) / duration, 1);
                            el.textContent = Math.round(progress * target);
                            if (progress < 1) requestAnimationFrame(step);
                        };
                        requestAnimationFrame(step);
                    },
                }"
                x-init="
                    const observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting && ! shown) {
                            shown = true;
                            $el.querySelectorAll('[data-animate-to]').forEach((el) => animateValue(el, parseInt(el.dataset.animateTo, 10)));
                            observer.disconnect();
                        }
                    }, { threshold: 0.3 });
                    observer.observe($el);
                "
                class="mt-10 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-10 sm:gap-y-12 text-left sm:text-center">
                @foreach ($slipguardMetrics as $metric)
                    <x-metric-card :label="$metric['label']" :explanation="$metric['explanation']" inverse>
                        @if ($metric['animate'] && is_numeric($metric['value']))
                            <span data-animate-to="{{ $metric['value'] }}">0</span>
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-metric-card>
                @endforeach
            </div>
        </div>
    </section>

    {{--
        5. How It Works — `PO-U15.6-001` §35-§41: the three steps now
        reference one continuous slip (the same Sample Accumulator already
        shown in Invisible Risk — Chelsea Win, Arsenal Win, Over 2.5 Goals,
        Both Teams to Score, Draw No Bet, structural score 53/High, weakest
        leg Over 2.5 Goals) rather than three unrelated text columns, for
        narrative continuity (§37's own recommendation). A stateful
        JS-driven transition between three replacing states was considered
        and deliberately not built — the three numbered panels are instead
        always simultaneously visible in one connected card, all real
        content present without JavaScript, consistent with the same
        progressive-enhancement reasoning already applied to Invisible Risk
        (§10.3/§19's own "if the simpler approach communicates it clearly,
        do not add unnecessary complexity").
    --}}
    <section class="public-section-atmosphere py-16 sm:py-20" aria-labelledby="how-it-works-heading" data-reveal>
        <div class="container-marketing mx-auto px-6">
            <div class="text-center max-w-xl mx-auto">
                <p class="text-xs font-semibold tracking-widest text-neutral-400 uppercase">{{ __('How It Works') }}</p>
                <h2 id="how-it-works-heading" class="mt-3 text-2xl sm:text-3xl font-semibold text-neutral-900">
                    {{ __('Three steps. No statistics degree required.') }}
                </h2>
            </div>

            <div class="mt-12 max-w-2xl mx-auto rounded-lg border border-neutral-300 bg-surface-card overflow-hidden">
                <div class="flex items-center gap-2 px-6 py-3 border-b border-neutral-200/60 bg-surface-soft">
                    <x-heroicon-o-shield-check class="size-4 text-neutral-400" aria-hidden="true" />
                    <p class="text-xs font-semibold text-neutral-500">{{ __('Sample Accumulator') }}</p>
                </div>

                <div class="p-6 sm:p-7 space-y-6">
                    <div>
                        <p class="text-xs font-semibold text-accent-strong">{{ __('01 · Enter your slip') }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('Add the selections you\'re considering — manually, for now.') }}</p>
                        <p class="mt-2 text-sm text-neutral-900">{{ __('Chelsea — Win · Arsenal — Win · Over 2.5 Goals · Both Teams to Score · Draw No Bet') }}</p>
                    </div>

                    <div class="pt-6 border-t border-neutral-200/60">
                        <p class="text-xs font-semibold text-accent-strong">{{ __('02 · SlipGuard analyses the structure') }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('A fixed rule set — never a prediction — evaluates the number of selections, the odds, and how the risk is distributed.') }}</p>
                        <ul class="mt-2 text-sm text-neutral-500 space-y-0.5">
                            <li>{{ __('Reading selections') }}</li>
                            <li>{{ __('Normalising markets') }}</li>
                            <li>{{ __('Applying structural rules') }}</li>
                        </ul>
                    </div>

                    <div class="pt-6 border-t border-neutral-200/60">
                        <p class="text-xs font-semibold text-accent-strong">{{ __('03 · You get a clear, explained report') }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('The main contributing factor, in plain language — and why it matters.') }}</p>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg border text-risk-high bg-risk-high/10 border-risk-high/30">
                                <x-heroicon-o-exclamation-triangle class="size-4" aria-hidden="true" />
                                {{ __('High') }}
                            </span>
                            <span class="text-2xl font-semibold text-neutral-900 font-tabular">53<span class="text-sm font-normal text-neutral-500">/100</span></span>
                        </div>
                        <p class="mt-2 text-sm text-neutral-600">{{ __('Weakest leg: Over 2.5 Goals — contributes 53% of the structural score.') }}</p>
                        <p class="mt-3 text-xs text-neutral-500">{{ __('The report explains the structure. You make the decision.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. Product Capabilities — the three public destinations as deep-link cards, plus the remaining capabilities named without implying a guest can open an authenticated screen unannounced --}}
    {{-- Atmosphere-rhythm gradient beat (see Invisible Risk section's comment above) --}}
    <section class="public-section-quiet py-16 sm:py-20" aria-labelledby="capabilities-heading" data-reveal>
        <div class="container-marketing mx-auto px-6">
            <p class="text-xs font-semibold tracking-widest text-neutral-400 uppercase text-center">{{ __('Product Capabilities') }}</p>
            <h2 id="capabilities-heading" class="mt-3 text-2xl sm:text-3xl font-semibold text-neutral-900 text-center">{{ __('Explore SlipGuard') }}</h2>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6">
                <x-card :href="route('analyse')" wire:navigate variant="interactive" class="group">
                    <span class="inline-flex items-center justify-center size-10 rounded-full bg-accent-strong/10 text-accent-strong">
                        <x-heroicon-o-magnifying-glass class="size-5" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('Analyse') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('How the structural risk engine works, and why it never predicts.') }}</p>
                    <span class="mt-4 inline-flex items-center text-sm font-semibold text-accent-strong">
                        {{ __('Learn more') }}
                        <x-heroicon-o-arrow-right class="ms-1.5 size-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                    </span>
                </x-card>
                <x-card :href="route('reports')" wire:navigate variant="interactive" class="group">
                    <span class="inline-flex items-center justify-center size-10 rounded-full bg-accent-strong/10 text-accent-strong">
                        <x-heroicon-o-document-text class="size-5" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('Reports') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('A full example of what SlipGuard actually shows you.') }}</p>
                    <span class="mt-4 inline-flex items-center text-sm font-semibold text-accent-strong">
                        {{ __('Learn more') }}
                        <x-heroicon-o-arrow-right class="ms-1.5 size-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                    </span>
                </x-card>
                <x-card :href="route('planner.public')" wire:navigate variant="interactive" class="group">
                    <span class="inline-flex items-center justify-center size-10 rounded-full bg-accent-strong/10 text-accent-strong">
                        <x-heroicon-o-clock class="size-5" aria-hidden="true" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('Planner') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Deterministic help building an accumulator you already control.') }}</p>
                    <span class="mt-4 inline-flex items-center text-sm font-semibold text-accent-strong">
                        {{ __('Learn more') }}
                        <x-heroicon-o-arrow-right class="ms-1.5 size-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                    </span>
                </x-card>
            </div>

            {{-- Remaining capabilities named, not linked — every one of these lives behind sign-in, and a homepage card that silently redirects a guest to login is a worse experience than an honest, unlinked mention (unchanged reasoning from the original capability strip, U-11.3 §6). --}}
            <ul class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
                @foreach ([
                    __('Decision Journal'),
                    __('Planning History'),
                    __('Weakest-Leg Explanation'),
                    __('Customer-Controlled Decisions'),
                    __('No Outcome Prediction'),
                ] as $capability)
                    <li class="text-xs font-semibold text-neutral-500 uppercase tracking-wide whitespace-nowrap">{{ $capability }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- 7. Trust — links out to real, verifiable evidence; no badges, seals, or manufactured signals (VISUAL_INSPIRATION.md) --}}
    <section class="public-section-atmosphere py-16 sm:py-20" aria-labelledby="trust-heading" data-reveal>
        <div class="container-marketing mx-auto px-6">
            <div class="max-w-2xl mx-auto text-center">
                <p class="text-xs font-semibold tracking-widest text-neutral-400 uppercase">{{ __('Trust') }}</p>
                <h2 id="trust-heading" class="mt-3 text-2xl sm:text-3xl font-semibold text-neutral-900">
                    {{ __('Trust that comes from what you can check, not what we claim.') }}
                </h2>
            </div>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-3xl mx-auto text-center">
                <div>
                    <x-heroicon-o-book-open class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-semibold text-neutral-900">{{ __('Methodology') }}</p>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('The exact rule set behind every score.') }}</p>
                    <a href="{{ route('analyse') }}" wire:navigate class="mt-2 inline-block text-sm font-semibold text-accent-strong hover:text-accent">{{ __('Read it') }} →</a>
                </div>
                <div>
                    <x-heroicon-o-document-chart-bar class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-semibold text-neutral-900">{{ __('Real Reports') }}</p>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('A genuine sample, not an illustration.') }}</p>
                    <a href="{{ route('reports') }}" wire:navigate class="mt-2 inline-block text-sm font-semibold text-accent-strong hover:text-accent">{{ __('See one') }} →</a>
                </div>
                <div>
                    <x-heroicon-o-sparkles class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-semibold text-neutral-900">{{ __('Release Notes') }}</p>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('What actually shipped, in plain language.') }}</p>
                    <a href="{{ route('release-notes') }}" wire:navigate class="mt-2 inline-block text-sm font-semibold text-accent-strong hover:text-accent">{{ __('Read notes') }} →</a>
                </div>
            </div>
        </div>
    </section>

    {{--
        8. CTA — founder direct instruction (2026-07-27): every CTA section
        site-wide uses a bold accent-colour gradient background, constant
        in both light and dark theme (DESIGN_TOKENS.md's Gradients section).
        Text/buttons inverted for contrast against the now-solid blue field:
        heading and secondary link go white/light-blue, the primary button
        flips to a white fill with accent-coloured text (a blue button on a
        blue section would disappear) — the same button hierarchy, inverted
        palette only.
    --}}
    <section class="light-sweep bg-gradient-cta py-20" aria-labelledby="cta-heading">
        <div class="container-marketing mx-auto px-6 text-center">
            <h2 id="cta-heading" class="text-2xl sm:text-3xl font-semibold text-white">
                {{ __('Structural risk, explained — never a prediction.') }}
            </h2>
            <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('analyze.create') }}" wire:navigate
                       class="inline-flex items-center justify-center h-12 px-6 text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant">
                        {{ __('Analyse a slip') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" wire:navigate
                       class="inline-flex items-center justify-center h-12 px-6 text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant">
                        {{ __('Get Started') }}
                    </a>
                @endauth
                <a href="{{ route('pricing') }}" wire:navigate class="text-sm font-semibold text-blue-100 hover:text-white">
                    {{ __('View pricing') }}
                </a>
            </div>
        </div>
    </section>

    {{--
        U-15.2's own scroll-reveal script previously lived here, inline,
        duplicated per page. Founder direct instruction (2026-07-28) —
        "sitewide" — moved it to `resources/js/app.js` (`initScrollReveal`,
        bound to Livewire's `livewire:navigated` event so it covers every
        page and every `wire:navigate` transition from one place) so it
        isn't re-declared per page as more pages adopt `data-reveal`.
    --}}
</x-public-layout>
