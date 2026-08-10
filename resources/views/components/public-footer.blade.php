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
@php
    // `PO-U22-001` — confirmed handle (Product Office, direct instruction):
    // every SlipGuard social account uses the username "slipguardhq".
    // Supplying a verified URL here automatically promotes the
    // corresponding item from a labelled icon to a real external link —
    // the mechanism this comment originally described, now used.
    $socialProfiles = [
        ['brand' => 'x', 'label' => 'X', 'url' => 'https://x.com/slipguardhq'],
        ['brand' => 'youtube', 'label' => 'YouTube', 'url' => 'https://youtube.com/@slipguardhq'],
        ['brand' => 'linkedin', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/slipguardhq'],
        ['brand' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/slipguardhq'],
        ['brand' => 'facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/slipguardhq'],
        ['brand' => 'tiktok', 'label' => 'TikTok', 'url' => 'https://tiktok.com/@slipguardhq'],
        ['brand' => 'threads', 'label' => 'Threads', 'url' => 'https://threads.net/@slipguardhq'],
    ];
@endphp

<footer class="border-t border-neutral-200">
    <div class="container-marketing mx-auto px-6 sm:px-8 py-16">
        {{--
            `PO-U24-005` §3 — the brand column now clearly outweighs each
            nav column (~2fr vs 1fr, close to the requested ~40/20/20/20)
            instead of four equal columns. Applied at the same `sm:`
            breakpoint the four-column layout already used (no new
            breakpoint introduced) — mobile's existing `grid-cols-2` +
            `col-span-2` brand-first stacking is untouched, since PO-U24-005
            §5 explicitly says not to force the desktop ratio onto narrow
            screens and that model already puts the brand block first.
        --}}
        <div class="grid grid-cols-2 sm:grid-cols-[2fr_1fr_1fr_1fr] gap-10">
            <div class="col-span-2 sm:col-span-1">
                <a href="{{ route('home') }}" wire:navigate aria-label="{{ __('SlipGuard home') }}"
                   class="inline-flex items-center gap-2.5 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent">
                    <img src="{{ asset('brand/slipguard-icon-accent.svg') }}" alt="" aria-hidden="true" class="h-8 w-auto">
                    <span class="text-lg font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
                </a>
                <p class="mt-4 text-sm text-neutral-500 max-w-xs">
                    {{ __('An independent intelligence layer between the bettor and the bookmaker.') }}
                </p>
                {{--
                    `PO-U24-005` §6/§7/§8 — visually smaller, restrained
                    social row without shrinking the accessible target below
                    this project's 44×44px standard (`ACCESSIBILITY.md`): the
                    outer `<a>` (the actual interactive/focusable element)
                    grew from `size-10` (40px — already below the 44px
                    standard, a latent pre-existing gap this change also
                    happens to close) to `size-11` (44px, this codebase's own
                    existing accessible-icon-button convention), but now
                    carries no visible border itself — the visible "chip" is
                    a smaller inner span (`size-7`, 28px) so the icon reads
                    as secondary to the brand block above it. `gap-2` (8px)
                    between icons is kept, not tightened, per UI UX Pro Max's
                    own touch-target-spacing guideline (minimum 8px between
                    adjacent tappable elements) — genuinely queried for this
                    commission, not assumed.
                --}}
                <div class="mt-5 flex items-center gap-2" aria-label="{{ __('SlipGuard social media') }}">
                    @foreach ($socialProfiles as $profile)
                        @if ($profile['url'])
                            <a href="{{ $profile['url'] }}" target="_blank" rel="noopener noreferrer"
                               class="group inline-flex size-11 items-center justify-center rounded-lg text-neutral-500 transition-colors hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                               aria-label="{{ __($profile['label']) }}">
                                <span class="inline-flex size-7 items-center justify-center rounded-md border border-neutral-300 transition-colors group-hover:border-neutral-400 group-hover:bg-surface-soft">
                                    <x-social-icon :brand="$profile['brand']" class="!size-4" />
                                </span>
                            </a>
                        @else
                            <span class="inline-flex size-11 items-center justify-center rounded-lg text-neutral-400"
                                  role="img" aria-label="{{ __($profile['label'].' account link coming soon') }}"
                                  title="{{ __($profile['label'].' account link will be added when available') }}">
                                <span class="inline-flex size-7 items-center justify-center rounded-md border border-neutral-200">
                                    <x-social-icon :brand="$profile['brand']" class="!size-4" />
                                </span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold text-neutral-500 uppercase tracking-widest">{{ __('Product') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('analyse') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Analyse') }}</a></li>
                    <li><a href="{{ route('planner.public') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Planner') }}</a></li>
                    <li><a href="{{ route('reports') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Reports') }}</a></li>
                    {{-- `PO-RC1-009`: was an unconditional Dashboard link — a guest clicking it landed on /login with no explanation. Matches the header nav's own @auth/@else convention. --}}
                    @auth
                        <li><a href="{{ route('dashboard') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Dashboard') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}" wire:navigate class="text-neutral-600 hover:text-neutral-900">{{ __('Sign In') }}</a></li>
                    @endauth
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

        <div class="mt-4 pt-4 border-t border-neutral-100">
            <p class="text-xs text-neutral-400 max-w-2xl">
                {{ __('SlipGuard is intended for users aged 18 and over. If gambling stops being enjoyable or starts affecting your wellbeing, confidential support is available — search for a responsible gambling helpline in your region.') }}
            </p>
        </div>
    </div>
</footer>
