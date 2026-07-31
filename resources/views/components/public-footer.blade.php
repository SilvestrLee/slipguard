{{--
    U-14.2 — footer information architecture finalised. The commission's
    own "Community" column is explicitly named empty ("No fake forums.
    No feature requests. No social community.") — SD-002 defers exactly
    those capabilities, so the column is dropped entirely rather than
    rendered empty, which would look broken rather than intentional.
    "Methodology"/"How It Works"/"Rule Set" all point to the existing
    /analyse page rather than duplicating its content on a new page
    (HOMEPAGE_STORYBOARD.md's own cross-section rule, applied here too).
    "Research" points to SlipGuard Labs, which already covers it.

    U-15.2: spacing/typography/composition touch only, per that
    commission's explicit "retain existing architecture... do not
    redesign unnecessarily" — no column, link, or structural change.

    U-16.0 (Phase 3/13): root surface moved to `bg-surface-page`, matching
    the authenticated footer (`layouts/app.blade.php`/`layouts/labs.blade.php`)
    exactly — was the one footer still using the raw `bg-neutral-50` class.

    U-16.1 (Phase 12): that same explicit `bg-surface-page` removed again
    here — not a reversal, a further step. The authenticated footer
    (`partials/authenticated-footer.blade.php`) now also has no explicit
    background, letting the parent layout's `bg-gradient-page` continue
    visibly into both footers rather than being cut off by an opaque
    override. One rule for every footer, not two.
--}}
<footer class="border-t border-neutral-200">
    <div class="container-marketing mx-auto px-6 sm:px-8 py-16">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-10">
            <div class="col-span-2 sm:col-span-1">
                <a href="{{ route('home') }}" wire:navigate aria-label="{{ __('SlipGuard home') }}"
                   class="inline-flex items-center gap-2.5 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent">
                    <img src="{{ asset('brand/slipguard-icon-accent.svg') }}" alt="" aria-hidden="true" class="h-8 w-auto">
                    <span class="text-lg font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
                </a>
                <p class="mt-4 text-sm text-neutral-500 max-w-xs">
                    {{ __('An independent intelligence layer between the bettor and the bookmaker.') }}
                </p>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-neutral-500 uppercase tracking-widest">{{ __('Product') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('analyse') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Analyse') }}</a></li>
                    <li><a href="{{ route('planner.public') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Planner') }}</a></li>
                    <li><a href="{{ route('reports') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Reports') }}</a></li>
                    <li><a href="{{ route('dashboard') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Dashboard') }}</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-neutral-500 uppercase tracking-widest">{{ __('Company') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('about') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('About') }}</a></li>
                    <li><a href="{{ route('pricing') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Pricing') }}</a></li>
                    <li><a href="{{ route('labs') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('SlipGuard Labs') }}</a></li>
                    <li><a href="{{ route('release-notes') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Release Notes') }}</a></li>
                    <li><a href="{{ route('contact') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Contact') }}</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-neutral-500 uppercase tracking-widest">{{ __('Resources') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('analyse') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Methodology') }}</a></li>
                    <li><a href="{{ route('faq') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('FAQs') }}</a></li>
                    <li><a href="{{ route('labs') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Research') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-14 pt-8 border-t border-neutral-200 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-6">
                <p class="text-sm text-neutral-500">&copy; {{ now()->year }} SlipGuard</p>
                <a href="{{ route('privacy') }}" wire:navigate class="text-sm text-neutral-500 hover:text-neutral-900">{{ __('Privacy') }}</a>
                <a href="{{ route('terms') }}" wire:navigate class="text-sm text-neutral-500 hover:text-neutral-900">{{ __('Terms') }}</a>
            </div>
            <p class="text-xs text-neutral-400 max-w-sm">
                {{ __('SlipGuard evaluates decision risk. It does not predict outcomes. Sport remains uncertain, and the final decision is always yours.') }}
            </p>
        </div>
    </div>
</footer>
