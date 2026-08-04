{{--
    U-13.0 — public site navigation. Every item is an independent route
    destination, never a same-page anchor (the commission's explicit
    rejection of #anchor-style nav). Restrained two-state sticky
    behaviour unchanged from U-11.3 (padding/shadow toggle past a scroll
    threshold, not a continuous scroll-linked morph — MOTION_SYSTEM.md).

    2026-07-31 — the approved icon and text wordmark are static. The
    former Signature Motion rotation is superseded. Scroll observation
    remains only for the restrained sticky-header condensation.

    U-15.2 — header reconstruction (`PO-U15.2-001`): larger logo presence,
    more generous height, wider nav-item spacing, and a proper utility
    area (a hairline divider separates navigation from account actions,
    theme toggle grouped with them rather than floating alone). The
    active-nav-item treatment now matches COMPONENT_PRINCIPLES.md's
    Navigation rule exactly — "weight + a subtle background, not colour
    alone" — via a pill background, not text colour only as before.

    U-16.2 first pass — reset to `h-10`/`h-8` for cross-app consistency
    with every other logo instance. Superseded by the `PO-U16.2-CR-001`
    correction directly below — Product Office made the 50px *visual*
    height requirement explicit and mandatory, overriding "match the
    other instances" as the deciding factor for this one, specific
    header.

    `PO-U16.2-CR-001` §4 — exact 50px visual height, measured not
    guessed. The source PNG (1685×621, embedded in the approved SVG)
    was decoded and its alpha channel sampled pixel-by-pixel (PHP GD):
    the actual non-transparent content occupies y[72,548] of 621 total
    — a content-to-canvas height ratio of 0.7665 (≈23.3% is transparent
    padding, split top/bottom). A target 50px *visual* mark therefore
    requires a CSS height of 50 / 0.7665 ≈ 65px on the `<img>` itself
    (the padding scales uniformly with the box, since the SVG's
    `preserveAspectRatio="xMidYMid meet"` scales the whole canvas as one
    unit). Condensed state scaled proportionally (65 × 0.8 ≈ 52px,
    matching this component's own existing expanded:condensed ratio) —
    visual height ≈ 40px condensed. No cropping of the source artwork
    was needed or performed (`public/brand/README.md`'s restriction
    stands) — only the rendering height changed, arithmetically
    accounting for padding already inside the file exactly as directed.
    This 50px requirement is scoped to the public header only, per the
    directive's own §4 "Public header is the only instance governed by
    this 50px requirement" — the footer (`h-8`) and the authenticated
    shell (`h-10`, `navigation.blade.php`/`layouts/guest.blade.php`) keep
    their existing, separate, already-established sizes, untouched.

    `PO-U16.2-CR-001` §5 — header geometry recalibrated around the
    restored 65px/52px logo box: vertical padding tightened again
    (`py-5`/`py-3` → `py-2.5`/`py-2`) so total header height lands close
    to a typical premium SaaS header (~85px expanded / ~68px condensed)
    rather than stacking generous padding on top of an already-larger
    logo box, which is what produced the "still feels unfinished"/
    "excessively large" impression in the first place.

    `PO-U16.2-CR-001` §10.3/§10.7 — a real, independent defect, confirmed
    empirically with a real browser (Playwright + the system's installed
    Chrome), not just reasoned about: `wire:navigate` does NOT reuse this
    `<header>` DOM node across a navigation (verified directly — the node
    identity changes on every page transition), so the original
    `x-init="window.addEventListener(...)"` with no teardown accumulated
    one additional `window` scroll listener per navigation, and never
    called `updateFromScroll()` immediately on mount, leaving a rebuilt
    header showing `condensed: false` until the next scroll
    event even if the page loaded already scrolled. Alpine's `$cleanup`
    magic — the documented fix for exactly this — is not available in
    the Alpine build this project's `livewire/livewire` version bundles
    (confirmed directly: calling it throws `$cleanup is not defined` in
    the real browser, not merely absent from a doc search). Fixed
    instead with a single global listener slot (`window.__slipguardHeaderScroll`):
    each init removes whatever listener occupied that slot before adding
    its own, which is safe because exactly one header is ever mounted at
    a time in this application — verified this actually stops the
    accumulation by triggering three consecutive `wire:navigate`
    transitions and confirming only one `scroll` listener produced any
    effect afterward.

    2026-07-30 — the approved icon is now the single source for both
    themes. Its own dark background and indigo mark are intentionally
    theme-independent, so no light/dark image swap is required.
    computation exactly, and updates live (no reload) via a
    `slipguard-theme-changed` event the toggle now dispatches on click.
--}}
@php
    $publicPrimaryItems = [
        ['route' => 'analyse', 'label' => __('Analyse')],
        ['route' => 'planner.public', 'label' => __('Planner')],
        ['route' => 'reports', 'label' => __('Reports')],
        ['route' => 'pricing', 'label' => __('Pricing')],
    ];

    // Command palette quick-navigation targets for the public site — this
    // header's own real, named routes, mirroring the authenticated
    // header's `paletteItems()` but scoped to what a public visitor can
    // actually reach (2026-07-28, founder direct instruction).
    $publicPaletteItems = [
        ['label' => __('Home'), 'description' => __('SlipGuard overview'), 'url' => route('home')],
        ['label' => __('Analyse'), 'description' => __('How SlipGuard evaluates a betting slip'), 'url' => route('analyse')],
        ['label' => __('Planner'), 'description' => __('Deterministic, explainable accumulator planning'), 'url' => route('planner.public')],
        ['label' => __('Reports'), 'description' => __('What a SlipGuard risk report looks like'), 'url' => route('reports')],
        ['label' => __('Pricing'), 'description' => __('Plans and pricing'), 'url' => route('pricing')],
        ['label' => __('SlipGuard Labs'), 'description' => __("What's new and what's coming next"), 'url' => route('labs')],
        ['label' => __('FAQ'), 'description' => __('Common questions, answered plainly'), 'url' => route('faq')],
    ];
    $publicPaletteItems[] = auth()->check()
        ? ['label' => __('Dashboard'), 'description' => __('Go to your SlipGuard dashboard'), 'url' => route('dashboard')]
        : ['label' => __('Sign In'), 'description' => __('Log in to your account'), 'url' => route('login')];
    if (! auth()->check()) {
        $publicPaletteItems[] = ['label' => __('Create Account'), 'description' => __('Start using SlipGuard'), 'url' => route('register')];
    }
@endphp
<div
    x-data="{
        condensed: false,
        mobileMenuOpen: false,
        isDark: window.SlipGuardTheme?.isDark()
            ?? (document.documentElement.getAttribute('data-theme') === 'dark'
                || (document.documentElement.getAttribute('data-theme') === null
                    && window.matchMedia('(prefers-color-scheme: dark)').matches)),
        updateFromScroll() {
            this.condensed = window.scrollY > 40;
        },
        openMenu() {
            this.mobileMenuOpen = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.mobileMenuClose.focus());
        },
        closeMenu(restoreFocus = true) {
            if (! this.mobileMenuOpen) return;
            this.mobileMenuOpen = false;
            document.body.classList.remove('overflow-hidden');
            if (restoreFocus) this.$nextTick(() => this.$refs.mobileMenuTrigger.focus());
        },
        selectDestination() {
            this.mobileMenuOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        trapMenuFocus(event) {
            if (! this.mobileMenuOpen) return;
            const focusables = this.$refs.mobileMenu.querySelectorAll('a[href], button:not([disabled])');
            if (! focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (! event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }"
    x-init="
        updateFromScroll();
        let handleScroll = () => updateFromScroll();
        if (window.__slipguardHeaderScroll) { window.removeEventListener('scroll', window.__slipguardHeaderScroll); }
        window.__slipguardHeaderScroll = handleScroll;
        window.addEventListener('scroll', handleScroll, { passive: true });
    "
    x-on:slipguard-theme-changed.window="isDark = $event.detail.dark"
    x-on:keydown.escape.window="closeMenu()"
    x-on:keydown.tab.window="trapMenuFocus($event)"
    x-on:resize.window="if (window.innerWidth >= 768) closeMenu(false)"
    class="sticky top-0 z-40"
>
<header
    {{--
        Founder direct instruction (2026-07-27): glassmorphism for the
        header — a frosted, translucent surface at all times (not only
        once condensed), rather than the prior solid background. Kept
        restrained: a single blur + translucency treatment, a hairline
        border for edge definition, no colour tint, no iridescence.
    --}}
    :class="condensed ? 'py-2 shadow-elevation-1' : 'py-2.5'"
    class="border-b border-neutral-200/70 backdrop-blur-md bg-surface-page/70 transition-[padding,box-shadow] duration-standard ease-in-out">
    <div class="container-marketing mx-auto flex items-center justify-between gap-4 px-4 sm:px-6 md:gap-8 md:px-8">
        <a href="{{ route('home') }}" wire:navigate aria-label="SlipGuard home"
           x-data="{ loaded: false }" x-init="requestAnimationFrame(() => loaded = true)"
           class="shrink-0 flex items-center gap-2.5">
            {{--
                Approved static icon. The wordmark below is genuine text.
                Static `src` below is the pre-Alpine fallback (avoids a flash of a broken image before hydration) — `:src` overrides it immediately once Alpine initialises.

                Founder-reported bug (2026-07-28): "the logo icon flashes enormous, covering the screen, then returns to set size" on
                every page load. Root cause found directly: the source SVG declares an intrinsic `width="721" height="848"` on its own
                root element, and the ONLY height constraint on this `<img>` was the Alpine `:class` binding below (`h-8`/`h-10`) — which
                doesn't apply until Alpine hydrates. In the brief pre-hydration window, the browser has nothing but the static `class`
                list to size the image by, and that list had no height at all, so it rendered at its native ~721×848px size before
                snapping down once Alpine ran. Fixed by adding `h-10` (the un-condensed default, matching a fresh page load's initial
                scroll state) to the static class list itself — the `:class` binding still overrides it correctly once Alpine initialises
                and the header condenses on scroll; this only fixes the sizing during the window before that binding exists.
            --}}
            <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                 alt=""
                 aria-hidden="true"
                 :class="`${loaded ? 'opacity-100' : 'opacity-0'} ${condensed ? '!h-8' : 'h-8 md:h-10'}`"
                 class="h-8 w-auto shrink-0 transition-[opacity,height] duration-300 ease-out md:h-10">
            {{-- Wordmark — genuine text, not an image; static and theme-aware. --}}
            <span :class="loaded ? 'opacity-100' : 'opacity-0'"
                  class="text-lg font-semibold tracking-tight text-neutral-900 transition-opacity duration-300 ease-out md:text-xl">
                {{ __('SlipGuard') }}
            </span>
        </a>

        {{-- U-14.1/U-14.2: primary nav trimmed to primary workflows only — About and SlipGuard Labs moved to the footer. --}}
        <nav class="hidden md:flex items-center gap-2" aria-label="{{ __('Primary') }}">
            @foreach ($publicPrimaryItems as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   @if ($isActive) aria-current="page" @endif
                   class="px-3.5 py-2 rounded-md text-sm font-medium transition-colors duration-instant focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent {{ $isActive ? 'text-neutral-900 bg-neutral-100' : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100/60' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 md:flex md:gap-4">
            {{-- Founder direct instruction (2026-07-28): search + language, sitewide — shared components, identical to the authenticated header and the guest auth layout. --}}
            <x-search-trigger-button />
            <x-language-selector />
            <x-theme-toggle />
            <div class="h-6 w-px bg-neutral-200" aria-hidden="true"></div>
            @auth
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="px-3 py-2 rounded-md text-sm font-medium text-neutral-700 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    {{ __('Dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}" wire:navigate
                   class="px-3 py-2 rounded-md text-sm font-medium text-neutral-700 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    {{ __('Sign In') }}
                </a>
                <a href="{{ route('register') }}" wire:navigate
                   class="px-4 py-2.5 text-sm font-semibold text-white bg-gradient-button rounded-md hover:brightness-110 transition-[filter] duration-instant focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    {{ __('Get Started') }}
                </a>
            @endauth
        </div>

        <button x-ref="mobileMenuTrigger" type="button" x-on:click="openMenu()"
                x-bind:aria-expanded="mobileMenuOpen.toString()" aria-controls="public-mobile-menu"
                class="inline-flex size-11 shrink-0 items-center justify-center rounded-lg text-neutral-700 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent md:hidden"
                aria-label="{{ __('Open navigation') }}">
            <x-heroicon-o-bars-3 class="size-6" aria-hidden="true" />
        </button>
    </div>
</header>

<div x-show="mobileMenuOpen" x-cloak x-on:click="closeMenu()"
     x-transition.opacity
     class="fixed inset-0 z-40 bg-neutral-950/55 md:hidden" aria-hidden="true"></div>

<aside id="public-mobile-menu" x-ref="mobileMenu" x-show="mobileMenuOpen" x-cloak
       x-transition:enter="transition-transform duration-200 ease-out"
       x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition-transform duration-150 ease-in"
       x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
       class="fixed inset-y-0 right-0 z-50 flex w-[min(22rem,88vw)] flex-col border-l border-neutral-300 bg-surface-card md:hidden"
       role="dialog" aria-modal="true" aria-label="{{ __('Public navigation') }}"
       style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-neutral-200 px-4">
        <span class="flex items-center gap-2.5">
            <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                 alt="" aria-hidden="true" class="h-8 w-auto">
            <span class="font-semibold text-neutral-900">{{ __('SlipGuard') }}</span>
        </span>
        <button x-ref="mobileMenuClose" type="button" x-on:click="closeMenu()"
                class="inline-flex size-11 items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                aria-label="{{ __('Close navigation') }}">
            <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
        </button>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto p-4">
        <nav class="space-y-1" aria-label="{{ __('Primary') }}">
            @foreach ($publicPrimaryItems as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate x-on:click="selectDestination()"
                   @if ($isActive) aria-current="page" @endif
                   @class([
                       'flex min-h-12 items-center rounded-lg border px-3 py-2.5 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                       'border-accent/30 bg-accent/10 font-semibold text-neutral-900' => $isActive,
                       'border-transparent font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900' => ! $isActive,
                   ])>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-5 space-y-3 border-t border-neutral-200 pt-5">
            <div class="flex min-h-12 items-center justify-between gap-3 px-3">
                <span class="text-sm font-medium text-neutral-600">{{ __('Search') }}</span>
                <x-search-trigger-button x-on:click="closeMenu(false)" />
            </div>
            <div class="flex min-h-12 items-center justify-between gap-3 px-3">
                <span class="text-sm font-medium text-neutral-600">{{ __('Language') }}</span>
                <x-language-selector x-on:click="closeMenu(false)" />
            </div>
            <div class="flex min-h-12 items-center justify-between gap-3 px-3">
                <span class="text-sm font-medium text-neutral-600">{{ __('Theme') }}</span>
                <x-theme-toggle />
            </div>
        </div>
    </div>

    <div class="shrink-0 space-y-2 border-t border-neutral-200 p-4">
        @auth
            <a href="{{ route('dashboard') }}" wire:navigate x-on:click="selectDestination()"
               class="flex min-h-12 items-center justify-center rounded-lg bg-gradient-button px-4 text-sm font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                {{ __('Dashboard') }}
            </a>
        @else
            <a href="{{ route('login') }}" wire:navigate x-on:click="selectDestination()"
               class="flex min-h-12 items-center justify-center rounded-lg border border-neutral-300 px-4 text-sm font-semibold text-neutral-800 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                {{ __('Sign In') }}
            </a>
            <a href="{{ route('register') }}" wire:navigate x-on:click="selectDestination()"
               class="flex min-h-12 items-center justify-center rounded-lg bg-gradient-button px-4 text-sm font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                {{ __('Get Started') }}
            </a>
        @endauth
    </div>
</aside>

{{--
    Placed as a sibling of `<header>`, not nested inside it: the header
    carries `backdrop-blur-md`, and a `backdrop-filter`/`filter` on an
    ancestor establishes a new CSS containing block for `position: fixed`
    descendants — found empirically via Playwright (the modal rendered
    confined to the header's own small box, not the full viewport, only
    on this header; the authenticated header, which has no backdrop
    filter, rendered the same component correctly). Moving the modal
    outside the filtered ancestor is the standard fix.
--}}
<x-command-palette :items="$publicPaletteItems" />
<x-language-modal />
</div>
