<x-public-layout title="Planner" description="Deterministic help building an accumulator you already control — SlipGuard never generates or predicts selections.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="planner-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Planner') }}</p>
            <h1 id="planner-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('You choose every selection. SlipGuard helps you see it clearly.') }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __('The Planner never generates or suggests a selection. It works only with slips you already own, and only ever ranks and explains — never chooses for you.') }}
            </p>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-28): sitewide atmosphere-layer rhythm (see home.blade.php's Invisible Risk section for the full explanation) --}}
    <section class="public-section-quiet" aria-labelledby="planner-workflow-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="planner-workflow-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('How planning works') }}</h2>
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center size-10 rounded-full bg-accent-strong text-white text-sm font-semibold">1</div>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('Start from a slip you built') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('Planning starts from an existing, customer-supplied slip — never a blank generator.') }}</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center size-10 rounded-full bg-accent-strong text-white text-sm font-semibold">2</div>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('See the weakest leg') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('SlipGuard ranks your own selections by structural contribution — you decide what, if anything, to change.') }}</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center size-10 rounded-full bg-accent-strong text-white text-sm font-semibold">3</div>
                    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ __('Compare, then export') }}</h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ __('Every revision is kept for comparison. Exporting creates a new slip — your original is never overwritten.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Atmosphere-rhythm beat --}}
    <section class="public-section-atmosphere" aria-labelledby="planner-control-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-20">
            <h2 id="planner-control-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('You remain in control, always') }}</h2>
            <ul class="mt-10 max-w-lg mx-auto space-y-4">
                <li class="flex gap-3">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400 mt-0.5" aria-hidden="true" />
                    <span class="text-sm text-neutral-600">{{ __('No generated selections — SlipGuard never builds a slip from scratch.') }}</span>
                </li>
                <li class="flex gap-3">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400 mt-0.5" aria-hidden="true" />
                    <span class="text-sm text-neutral-600">{{ __("Your original slip is locked while planning, but never rewritten — exporting always creates a new one.") }}</span>
                </li>
                <li class="flex gap-3">
                    <x-heroicon-o-shield-check class="size-5 shrink-0 text-neutral-400 mt-0.5" aria-hidden="true" />
                    <span class="text-sm text-neutral-600">{{ __('Every revision stays available for comparison — nothing about your planning history disappears.') }}</span>
                </li>
            </ul>
        </div>
    </section>

    {{-- Founder direct instruction (2026-07-27): every CTA section site-wide uses the bold accent gradient background (DESIGN_TOKENS.md), button inverted to white for contrast. --}}
    <section class="light-sweep bg-gradient-cta" aria-labelledby="planner-cta-heading">
        <div class="container-marketing mx-auto px-6 py-20 text-center">
            <h2 id="planner-cta-heading" class="text-2xl font-semibold text-white">{{ __('Plan your next accumulator with SlipGuard') }}</h2>
            @auth
                <a href="{{ route('analyze') }}" wire:navigate
                   class="mt-6 inline-flex items-center px-6 py-3 text-sm font-semibold text-indigo-700 bg-white rounded-md hover:bg-indigo-50 transition-colors duration-instant">
                    {{ __('Go to your slips') }}
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
