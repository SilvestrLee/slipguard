<x-public-layout title="FAQ" description="Common questions about how SlipGuard's structural risk analysis works.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="faq-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('FAQ') }}</p>
            <h1 id="faq-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('Common questions') }}
            </h1>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-28): sitewide atmosphere-layer rhythm (see home.blade.php's Invisible Risk section for the full explanation) --}}
    <section class="public-section-quiet" aria-label="{{ __('Frequently asked questions') }}" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-8">
            <div>
                <h2 class="text-base font-semibold text-neutral-900">{{ __('Does SlipGuard predict who wins?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __('No. SlipGuard evaluates the structure of a betting slip — the number of selections, the odds involved, how concentrated the risk is — and never claims a team will win or a bet is safe.') }}</p>
            </div>
            <div>
                <h2 class="text-base font-semibold text-neutral-900">{{ __('What sports does SlipGuard support?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __("Structural risk analysis is currently available for football only. We're evaluating other sports for future coverage.") }}</p>
            </div>
            <div>
                <h2 class="text-base font-semibold text-neutral-900">{{ __('Does SlipGuard hold my money or place bets for me?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __('Never. SlipGuard never accepts deposits, holds funds, or places a wager on your behalf. Every financial transaction stays between you and your licensed operator.') }}</p>
            </div>
            <div>
                <h2 class="text-base font-semibold text-neutral-900">{{ __('What does the Planner actually do?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __('It works only with slips you already built. It ranks your own selections by structural contribution and lets you compare revisions — it never generates or suggests a selection for you.') }}</p>
            </div>
            <div>
                <h2 class="text-base font-semibold text-neutral-900">{{ __('Why does the same slip always get the same score?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __('Because the analysis is deterministic — a fixed rule set, not a model that changes its mind. The same input always produces the same output.') }}</p>
            </div>
            <div>
                <h2 class="text-base font-semibold text-neutral-900">{{ __('How much does SlipGuard cost?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('SlipGuard is free to use during controlled beta. See') }}
                    <a href="{{ route('pricing') }}" wire:navigate class="text-accent-strong hover:text-accent font-medium">{{ __('Pricing') }}</a>
                    {{ __('for what happens next.') }}
                </p>
            </div>
        </div>
    </section>
</x-public-layout>
