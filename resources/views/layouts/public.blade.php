<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' — SlipGuard' : 'SlipGuard — Betting Decision Intelligence' }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        @include('partials.favicon-links')

        @include('partials.theme-init-script')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        {{--
            U-08.1 (`PO-U08.1-AC-001` §14/§15) — fixed atmospheric background
            layer: four large, heavily blurred indigo forms (within the
            approved 3-5 range), different sizes and positions so no two
            read as an obvious repeated pattern, fixed behind all scrolling
            content, non-interactive (`.atmosphere` sets `pointer-events: none`
            in app.css). Public layout only — matches the icon motion's own
            public-only scope; not present in layouts/guest.blade.php's
            single-card auth pages or the authenticated shell.
        --}}
        <div class="atmosphere" data-atmosphere-scroll="page" data-atmosphere-cap="1400" aria-hidden="true">
            <span data-atmosphere-depth="-0.028" style="width: 32rem; height: 32rem; top: -10rem; left: -8rem;"></span>
            <span data-atmosphere-depth="0.040" style="width: 26rem; height: 26rem; top: 20rem; right: -10rem;"></span>
            <span data-atmosphere-depth="-0.020" style="width: 22rem; height: 22rem; bottom: -6rem; left: 15%;"></span>
            <span data-atmosphere-depth="0.032" style="width: 18rem; height: 18rem; top: 55%; right: 20%;"></span>
        </div>

        {{--
            U-08.1: `bg-surface-page bg-gradient-page` removed from here — that
            exact background now lives on `.atmosphere` itself (app.css), so
            this wrapper stays transparent and lets both the base gradient and
            the indigo glows behind it show through every section, including
            the fully opaque ones. A real bug caught before shipping: leaving
            the wrapper's own opaque fill in place would have sat between the
            fixed atmosphere and every section, hiding it completely.
        --}}
        <div class="relative z-10 min-h-screen flex flex-col">
            <x-public-nav />

            <main class="flex-1">
                {{ $slot }}
            </main>

            <x-public-footer />
        </div>

        {{--
            `PO-U16.2-CR-001` (browser-verification pass) — the actual root
            cause behind every "not perceptible" complaint (logo motion,
            theme toggle, theme flash): this page tree contains zero
            `<livewire:...>` components (only plain Blade components), so
            Livewire v3's own auto-injection of its JS bundle — which
            Alpine.js ships inside — never fired. `window.Alpine` was
            confirmed `undefined` on this exact page via a real headless
            browser (Playwright + the system's installed Chrome, since
            Playwright's own bundled Chromium doesn't support this
            environment's macOS version). Every `x-data`/`x-show`/`:class`
            binding on every public page was inert from the start —
            confirmed directly, not inferred from source review alone.
            `@livewireScripts` forces Livewire (and therefore Alpine) to
            load on every page regardless of whether a Livewire component
            is present, which is Livewire's own documented mechanism for
            exactly this situation.
        --}}
        @livewireScripts
    </body>
</html>
