<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SlipGuard') }}</title>

        @include('partials.favicon-links')

        @include('partials.theme-init-script')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-neutral-900 antialiased">
        {{--
            Founder direct instruction (2026-07-28): the atmosphere layer now
            also applies to the auth screens — a prior explicit decision
            (see docs/05-ux/MOTION_SYSTEM.md's Fixed Atmospheric Layer
            section) scoped it out of this layout specifically, reasoning
            that a focused single-card auth screen should stay minimal;
            the founder has now directly overridden that scoping decision.
            Same technique as layouts/public.blade.php: `.atmosphere` carries
            the page's own `--gradient-page` background so the wrapper below
            can stay transparent, letting both the base tint and the indigo
            glows show through.
        --}}
        <div class="atmosphere" aria-hidden="true">
            <span style="width: 28rem; height: 28rem; top: -8rem; left: -6rem;"></span>
            <span style="width: 22rem; height: 22rem; bottom: -6rem; right: -8rem;"></span>
            <span style="width: 16rem; height: 16rem; top: 40%; left: 65%;"></span>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            {{--
                2026-07-30 approved identity: the same icon asset is used in
                both themes, with the wordmark rendered as accessible text.
            --}}
            <div class="flex items-center gap-4">
                <a href="/" wire:navigate aria-label="SlipGuard home" class="flex items-center gap-2.5">
                    <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                         alt="" aria-hidden="true" class="h-10 w-auto shrink-0">
                    <span class="text-xl font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
                </a>
            </div>

            {{--
                Founder direct instruction (2026-07-28): search + language
                were briefly added here (sitewide), then explicitly removed
                from the auth screens ("search and language features should
                be removed from the auth screens"); the theme toggle was
                then also explicitly removed ("remove the theme toggle from
                the auth screens"). This layout still reflects whichever
                theme is globally active through the shared theme tokens,
                while the approved icon remains constant in both themes.
            --}}

            {{-- Founder direct instruction (2026-07-28): an explicit "back to home" affordance, distinct from relying on the logo alone being clickable. --}}
            <a href="/" wire:navigate class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-neutral-600 hover:text-neutral-900">
                <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />
                {{ __('Back to home') }}
            </a>

            <x-card class="w-full sm:max-w-md mt-6 overflow-hidden">
                {{ $slot }}
            </x-card>
        </div>

        {{-- `PO-U16.2-CR-001` (browser-verification pass): explicit for consistency/safety across every layout — see layouts/public.blade.php's comment for the confirmed root cause this addresses. --}}
        @livewireScripts
    </body>
</html>
