<x-public-layout title="About" description="Why SlipGuard exists, and the constitutional boundaries it will never cross.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="about-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('About') }}</p>
            <h1 id="about-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('The independent intelligence layer between the bettor and the bookmaker.') }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __('The bookmaker executes the wager. SlipGuard helps the customer understand it.') }}
            </p>
        </div>
    </section>

    {{--
        Founder direct instruction (2026-07-28): sitewide atmosphere-layer
        rhythm — alternate sections between `public-section-atmosphere`,
        which reveals the fixed layer, and `public-section-quiet`, whose
        opaque multi-stop neutral gradient is deliberately not flat.
        Hero already reads as an atmosphere beat via `gradient-hero`'s
        own translucency; this section is the gradient beat, "Boundaries"
        below is the atmosphere beat, CTA is a gradient beat (`gradient-cta`
        already fits). The hard `border-t` between sections was removed to
        match `U-16.2-CR-001`'s existing "gradient continuity replaces hard
        cuts" precedent, already applied on the homepage.
    --}}
    <section class="public-section-quiet" aria-labelledby="why-heading" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <h2 id="why-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('Why SlipGuard exists') }}</h2>
            <p class="mt-4 text-base text-neutral-600 text-center">
                {{ __("More research and more predictions don't improve betting decisions. What's missing is a disciplined, transparent decision process — not another forecast.") }}
            </p>
        </div>
    </section>

    {{-- Atmosphere-rhythm beat (see "Why SlipGuard exists" section's comment above) --}}
    <section class="public-section-atmosphere" aria-labelledby="boundaries-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="boundaries-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('What SlipGuard will never do') }}</h2>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Hold customer money') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('No deposits, wallets, withdrawals, or bet settlement. Every financial transaction stays between you and your licensed operator.') }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Decide for you') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('SlipGuard presents evidence. The final decision always remains yours.') }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Bet autonomously') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('No auto-placement, auto-stake-increases, or auto-loss-chasing. Every wager requires your own, conscious action.') }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Take a side') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('No affiliate agreement or commercial incentive ever biases a calculation, classification, or recommendation.') }}</p>
                </x-card>
            </div>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-27): every CTA section site-wide uses the bold accent gradient background (DESIGN_TOKENS.md), button inverted to white for contrast. --}}
    <section class="light-sweep bg-gradient-cta" aria-labelledby="about-cta-heading">
        <div class="container-marketing mx-auto px-6 py-20 text-center">
            <h2 id="about-cta-heading" class="text-2xl font-semibold text-white">{{ __('Structural risk, explained — never a prediction.') }}</h2>
            @auth
                <a href="{{ route('analyze.create') }}" wire:navigate
                   class="mt-6 inline-flex items-center px-6 py-3 text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant">
                    {{ __('Analyze a slip') }}
                </a>
            @else
                <a href="{{ route('register') }}" wire:navigate
                   class="mt-6 inline-flex items-center px-6 py-3 text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant">
                    {{ __('Get Started') }}
                </a>
            @endauth
        </div>
    </section>
</x-public-layout>
