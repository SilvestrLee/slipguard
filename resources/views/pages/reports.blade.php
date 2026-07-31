<x-public-layout title="Reports" description="A full example of a SlipGuard structural risk report — sample data, real methodology.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="reports-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Reports') }}</p>
            <h1 id="reports-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('This is what SlipGuard actually shows you.') }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __('Sample data below, styled exactly like the real report — every customer gets this same structure, populated with their own slip.') }}
            </p>
        </div>
    </section>

    {{--
        Full sample report, reusing report.blade.php's real section
        structure and copy, clearly labelled as sample data throughout.
        Founder direct instruction (2026-07-28): sitewide atmosphere-layer
        rhythm (see home.blade.php's Invisible Risk section for the full
        explanation) — this page's one content section is the gradient
        beat, between the Hero's atmosphere beat and the CTA's own
        `gradient-cta` bookend.
    --}}
    <section class="public-section-quiet" aria-label="{{ __('Sample structural risk report') }}" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <p class="text-xs font-semibold text-accent-strong uppercase tracking-widest text-center">{{ __('Sample — Saturday Accumulator') }}</p>
            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold text-neutral-900 text-center">{{ __('Analysis complete') }}</h2>

            <div class="mt-8 space-y-12">
                <section aria-labelledby="sample-risk-heading">
                    <h3 id="sample-risk-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Structural risk') }}</h3>
                    <div class="mt-3 bg-surface-card shadow-elevation-2 border border-neutral-200/60 rounded-lg p-6 sm:p-8">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 text-base font-semibold px-4 py-2 rounded-lg border text-risk-moderate bg-risk-moderate/10 border-risk-moderate/30">
                                <x-heroicon-o-exclamation-triangle class="size-5" aria-hidden="true" />
                                {{ __('Moderate') }}
                            </span>
                            <span class="text-2xl font-semibold text-neutral-900 font-tabular">
                                47<span class="text-sm font-normal text-neutral-500">/100</span>
                            </span>
                        </div>
                        <p class="mt-4 text-sm text-neutral-600">{{ __('This slip shows some structural characteristics that add risk — for example, a longer selection count, moderately elevated odds, or a noticeable concentration of risk in one selection.') }}</p>
                    </div>
                </section>

                <section aria-labelledby="sample-main-factor-heading">
                    <h3 id="sample-main-factor-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Main Contributing Factor') }}</h3>
                    <div class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                        <p class="text-sm font-semibold text-neutral-900">{{ __('Selection Odds') }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('Reflects how high the odds are on your individual selections — a small number of high-odds selections adds risk even in an otherwise short slip.') }}</p>
                    </div>
                </section>

                <section aria-labelledby="sample-quality-heading">
                    <h3 id="sample-quality-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Data quality') }}</h3>
                    <div class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6">
                        <p class="text-sm font-semibold text-neutral-900">{{ __('Strong') }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ __('SlipGuard was able to fully evaluate every selection in this slip.') }}</p>
                    </div>
                </section>

                <section aria-labelledby="sample-trust-heading">
                    <h3 id="sample-trust-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Trust') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm text-neutral-600">
                        <li>{{ __('Deterministic analysis — the same slip always produces the same result under the same rule set.') }}</li>
                        <li>{{ __('No outcome prediction — SlipGuard evaluates structural risk, not who wins.') }}</li>
                        <li>{{ __('Every result explains itself — what was found, and why it matters.') }}</li>
                    </ul>
                </section>

                <section aria-labelledby="sample-methodology-heading">
                    <h3 id="sample-methodology-heading" class="text-sm font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Methodology') }}</h3>
                    <div class="mt-3 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-lg p-6 text-sm text-neutral-600 space-y-4">
                        <p>{{ __('This score comes from a fixed set of rules that look at how a slip is built — the number of selections, the odds involved, how those odds are distributed, and how complex the markets are. The same slip always produces the same result under the current rule set. Data quality is assessed separately and never changes the score itself — it only tells you how completely SlipGuard could evaluate the information you provided.') }}</p>
                    </div>
                </section>
            </div>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-27): every CTA section site-wide uses the bold accent gradient background (DESIGN_TOKENS.md), button inverted to white for contrast. --}}
    <section class="light-sweep bg-gradient-cta" aria-labelledby="reports-cta-heading">
        <div class="container-marketing mx-auto px-6 py-20 text-center">
            <h2 id="reports-cta-heading" class="text-2xl font-semibold text-white">{{ __('Ready to see your own report?') }}</h2>
            @auth
                <x-marketing-cta-button :href="route('analyze.create')" wire:navigate class="mt-6">
                    {{ __('Analyze a slip') }}
                </x-marketing-cta-button>
            @else
                <x-marketing-cta-button :href="route('register')" wire:navigate class="mt-6">
                    {{ __('Get Started') }}
                </x-marketing-cta-button>
            @endauth
        </div>
    </section>
</x-public-layout>
