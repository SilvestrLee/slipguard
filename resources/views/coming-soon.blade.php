<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                <h3 class="text-base font-semibold text-gray-900">{{ __(':title is on the way.', ['title' => $title]) }}</h3>
                <p class="mt-2 text-sm text-gray-600 max-w-md mx-auto">
                    {{ __("This part of SlipGuard hasn't been built yet. Start with the dashboard while we finish it.") }}
                </p>
                <div class="mt-5">
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition">
                        {{ __('Back to Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
