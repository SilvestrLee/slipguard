<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'SlipGuard') }}</title>

        @include('partials.favicon-links')

        @include('partials.theme-init-script')
        @include('partials.sidebar-init-script')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        @auth
            @include('partials.authenticated-shell', ['slot' => $slot, 'header' => null])
        @else
            <div class="atmosphere" aria-hidden="true">
                <span style="width: 32rem; height: 32rem; top: -10rem; left: -8rem;"></span>
                <span style="width: 26rem; height: 26rem; top: 20rem; right: -10rem;"></span>
                <span style="width: 22rem; height: 22rem; bottom: -6rem; left: 15%;"></span>
                <span style="width: 18rem; height: 18rem; top: 55%; right: 20%;"></span>
            </div>
            <div class="relative z-10 min-h-screen flex flex-col">
                <x-public-nav />

                <main class="flex-1">
                    {{ $slot }}
                </main>

                <x-public-footer />
            </div>
        @endauth

        @livewireScripts
    </body>
</html>
