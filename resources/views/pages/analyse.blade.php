<x-public-layout title="Analyse" description="How SlipGuard's deterministic structural risk engine works, and why it never predicts an outcome.">

    {{-- Page hero — differs from Home's, shares the same design language --}}
    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="analyse-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Analyse') }}</p>
            <h1 id="analyse-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('A fixed set of rules looks at how your slip is built — never at who wins.') }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __("The same slip always produces the same result under the same rule set. That's the whole point.") }}
            </p>
        </div>
    </section>

    {{--
        The problem, moved here from the old single-page homepage
        (HOMEPAGE_STORYBOARD.md §2). Founder direct instruction
        (2026-07-28): sitewide atmosphere-layer rhythm — alternate between
        the atmosphere-revealing shared section surface and an opaque,
        multi-stop neutral quiet surface. Hard `border-t` cuts removed between
        sections to match the homepage's existing gradient-continuity
        precedent.
    --}}
    <section class="public-section-quiet" aria-labelledby="problem-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20 text-center">
            <x-heroicon-o-document-text class="mx-auto size-10 text-neutral-400" aria-hidden="true" />
            <h2 id="problem-heading" class="mt-6 text-2xl font-semibold text-neutral-900">
                {{ __('"Just one more leg" changes more than it feels like it should.') }}
            </h2>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __("Every selection you add to an accumulator compounds its structural risk — often by more than it looks like leg-by-leg. It's hard to see that by eye, which is exactly why it's easy to miss.") }}
            </p>
        </div>
    </section>

    {{-- How it works, moved here from the old homepage. Atmosphere-rhythm beat (see "problem" section's comment above) --}}
    <section class="public-section-atmosphere" aria-labelledby="how-it-works-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="how-it-works-heading" class="text-2xl font-semibold text-neutral-900 text-center">
                {{ __('How SlipGuard works') }}
            </h2>
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center size-10 rounded-full bg-accent-strong text-white text-sm font-semibold">1</div>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('Enter your slip') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('Add the selections you\'re considering — no statistical knowledge required.') }}</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center size-10 rounded-full bg-accent-strong text-white text-sm font-semibold">2</div>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('SlipGuard analyzes structural risk') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('A fixed set of deterministic rules examines how your slip is built — never a prediction of who wins.') }}</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center size-10 rounded-full bg-accent-strong text-white text-sm font-semibold">3</div>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('You get a clear, explained report') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('A plain-language explanation of where the risk comes from, and why it matters.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- What the engine actually looks at — real factor names/explanations, reused verbatim from report.blade.php. Gradient beat --}}
    <section class="public-section-quiet" aria-labelledby="factors-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="factors-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('What the engine actually looks at') }}</h2>
            <p class="mt-3 text-sm text-neutral-600 text-center max-w-lg mx-auto">
                {{ __('Five structural factors, each one weighed and explained — never a black box.') }}
            </p>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Number of Selections') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Reflects the number of selections in your slip — more selections structurally add risk.') }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Combined Odds') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Reflects the combined odds across all your selections — higher combined odds structurally add risk.') }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Selection Odds') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Reflects how high the odds are on your individual selections — a small number of high-odds selections adds risk even in an otherwise short slip.') }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Risk Concentration') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __("Reflects how much of the slip's risk sits in a small number of selections, rather than being spread evenly.") }}</p>
                </x-card>
                <x-card class="sm:col-span-2">
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Market Complexity') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Reflects how complex the markets in your slip are — some markets carry more structural uncertainty than others.') }}</p>
                </x-card>
            </div>
        </div>
    </section>

    {{-- Weakest-leg explanation. Atmosphere-rhythm beat --}}
    <section class="public-section-atmosphere" aria-labelledby="weakest-leg-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20 text-center">
            <h2 id="weakest-leg-heading" class="text-2xl font-semibold text-neutral-900">{{ __('Which leg is doing the most damage?') }}</h2>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __("SlipGuard identifies which selection contributes the most structural risk to your slip — not by guessing, but by measuring how the score changes if that selection weren't there.") }}
            </p>
        </div>
    </section>

    {{-- Why deterministic matters / why not prediction. Gradient beat --}}
    <section class="public-section-quiet" aria-labelledby="deterministic-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="deterministic-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('Why deterministic, not predictive') }}</h2>
            <ul class="mt-10 max-w-lg mx-auto space-y-4">
                <li class="flex gap-3">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400 mt-0.5" aria-hidden="true" />
                    <span class="text-sm text-neutral-600">{{ __('Deterministic analysis — the same slip always produces the same result under the same rule set.') }}</span>
                </li>
                <li class="flex gap-3">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400 mt-0.5" aria-hidden="true" />
                    <span class="text-sm text-neutral-600">{{ __('No outcome prediction — SlipGuard evaluates structural risk, not who wins.') }}</span>
                </li>
                <li class="flex gap-3">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400 mt-0.5" aria-hidden="true" />
                    <span class="text-sm text-neutral-600">{{ __('Every result explains itself — what was found, and why it matters.') }}</span>
                </li>
            </ul>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-27): every CTA section site-wide uses the bold accent gradient background (DESIGN_TOKENS.md), button inverted to white for contrast. --}}
    <section class="light-sweep bg-gradient-cta" aria-labelledby="analyse-cta-heading">
        <div class="container-marketing mx-auto px-6 py-20 text-center">
            <h2 id="analyse-cta-heading" class="text-2xl font-semibold text-white">{{ __('See what a full report looks like') }}</h2>
            <a href="{{ route('reports') }}" wire:navigate
               class="mt-6 inline-flex items-center px-6 py-3 text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant">
                {{ __('View an example report') }}
            </a>
        </div>
    </section>
</x-public-layout>
