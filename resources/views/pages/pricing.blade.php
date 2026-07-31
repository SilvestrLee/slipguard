{{--
    U-14.2 §1 — Pricing communicates current availability and future
    tiers honestly. No pricing model has ever been approved by Product
    Office (PROJECT.md's MVP Non-Goals excludes "advanced subscriptions";
    PO-U11.2-CL-002 rejects "pricing presentation" as an OddStorm-derived
    pattern) — so no number, discount, or subscription benefit is
    invented anywhere on this page. Structure (rhythm, comparison layout,
    FAQ placement, CTA cadence) studied conceptually per the commission;
    no colour, copy, or component copied.
--}}
<x-public-layout title="Pricing" description="SlipGuard's commercial plans are still being decided. Here's what's available today.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="pricing-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Pricing') }}</p>
            <h1 id="pricing-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __("We haven't finalised pricing yet — here's what's true today.") }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __('SlipGuard is in controlled beta. Full structural analysis is free to use while we build toward a commercial plan.') }}
            </p>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-28): sitewide atmosphere-layer rhythm (see home.blade.php's Invisible Risk section for the full explanation) --}}
    <section class="public-section-quiet" aria-labelledby="tiers-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="tiers-heading" class="sr-only">{{ __('Availability') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <x-card variant="elevated">
                    <p class="text-xs font-semibold text-accent-strong uppercase tracking-wide">{{ __('Available now') }}</p>
                    <h3 class="mt-2 text-xl font-semibold text-neutral-900">{{ __('Free') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('Manual slip entry, structural risk analysis, the Planner, Journal, and History — everything SlipGuard does today.') }}</p>
                    @auth
                        <a href="{{ route('analyze.create') }}" wire:navigate class="mt-5 inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-accent-strong rounded-md hover:bg-accent">
                            {{ __('Analyze a slip') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" wire:navigate class="mt-5 inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-accent-strong rounded-md hover:bg-accent">
                            {{ __('Get Started') }}
                        </a>
                    @endauth
                </x-card>

                <x-card>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Coming later') }}</p>
                    <h3 class="mt-2 text-xl font-semibold text-neutral-900">{{ __('Professional') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('For customers who want deeper history, faster planning workflows, and priority support. No price has been set.') }}</p>
                    <a href="{{ route('labs') }}" wire:navigate class="mt-5 inline-flex items-center px-4 py-2 text-sm font-semibold text-neutral-700 border border-neutral-300 rounded-md hover:bg-surface-soft">
                        {{ __('Notify Me') }}
                    </a>
                </x-card>

                <x-card>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide">{{ __('Future') }}</p>
                    <h3 class="mt-2 text-xl font-semibold text-neutral-900">{{ __('Enterprise') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('For operators and organisations who want SlipGuard-grade structural analysis at scale. Terms have not been defined.') }}</p>
                    <a href="{{ route('contact') }}" wire:navigate class="mt-5 inline-flex items-center px-4 py-2 text-sm font-semibold text-neutral-700 border border-neutral-300 rounded-md hover:bg-surface-soft">
                        {{ __('Enterprise Enquiry') }}
                    </a>
                </x-card>
            </div>
        </div>
    </section>

    {{-- Atmosphere-rhythm beat --}}
    <section class="public-section-atmosphere" aria-labelledby="pricing-faq-heading" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <h2 id="pricing-faq-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('Questions') }}</h2>
            <div class="mt-10 space-y-6">
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Why is there no price yet?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __("SlipGuard is in controlled beta. We'd rather tell you honestly that pricing isn't decided than invent a number.") }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Will the Free tier stay free?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __("We haven't decided the final shape of Free vs. Professional. Anyone using SlipGuard today will be told before anything changes.") }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('How do I find out when pricing is announced?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Register your interest on SlipGuard Labs, or check back here.') }}</p>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
