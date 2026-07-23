<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SlipGuard — Betting Risk Intelligence</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col bg-gray-50">
            <header class="border-b border-gray-200">
                <div class="max-w-5xl mx-auto px-6 py-5 flex items-center justify-between">
                    <span class="text-lg font-semibold tracking-tight text-gray-900">SlipGuard</span>

                    <nav class="flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate
                               class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                {{ __('Sign In') }}
                            </a>
                            <a href="{{ route('register') }}" wire:navigate
                               class="px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-md hover:bg-gray-700">
                                {{ __('Get Started') }}
                            </a>
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-3xl mx-auto px-6 py-20 text-center">
                    <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-gray-900">
                        {{ __('Know where the risk is in your slip before you place it.') }}
                    </h1>
                    <p class="mt-4 text-base text-gray-600 max-w-xl mx-auto">
                        {{ __("SlipGuard analyzes the structure of a betting slip and explains where its unnecessary risk comes from — which leg is weakest and why. It does not predict who wins.") }}
                    </p>

                    <div class="mt-8 flex items-center justify-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate
                               class="px-5 py-3 text-sm font-semibold text-white bg-gray-900 rounded-md hover:bg-gray-700">
                                {{ __('Go to Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" wire:navigate
                               class="px-5 py-3 text-sm font-semibold text-white bg-gray-900 rounded-md hover:bg-gray-700">
                                {{ __('Get Started') }}
                            </a>
                            <a href="{{ route('login') }}" wire:navigate
                               class="px-5 py-3 text-sm font-semibold text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                                {{ __('Sign In') }}
                            </a>
                        @endauth
                    </div>

                    <p class="mt-10 text-xs text-gray-400 max-w-md mx-auto">
                        {{ __('SlipGuard evaluates decision risk. It does not predict outcomes. Sport remains uncertain, and the final decision is always yours.') }}
                    </p>
                </div>
            </main>

            <footer class="border-t border-gray-200">
                <div class="max-w-5xl mx-auto px-6 py-6 text-sm text-gray-500">
                    &copy; {{ now()->year }} SlipGuard
                </div>
            </footer>
        </div>
    </body>
</html>
