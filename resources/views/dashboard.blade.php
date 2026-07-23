<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <section class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('Welcome, :name.', ['name' => explode(' ', auth()->user()->name)[0]]) }}
                </h3>
                <p class="mt-2 text-sm text-gray-600 max-w-2xl">
                    {{ __("SlipGuard looks at the structure of a betting slip and tells you where its unnecessary risk is — it doesn't predict who wins. Enter a slip and we'll show you which leg is carrying the most risk and why.") }}
                </p>
                <div class="mt-5">
                    <a href="{{ route('analyze') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition">
                        {{ __('Analyze your first slip') }}
                    </a>
                </div>
            </section>

            <section>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                    {{ __('Recent Analyses') }}
                </h3>
                <div class="mt-3 bg-white border border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <p class="text-sm text-gray-600">{{ __('No analyses yet.') }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Create your first analysis to see it here.') }}</p>
                </div>
            </section>

            <section>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                    {{ __('Journal') }}
                </h3>
                <div class="mt-3 bg-white border border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <p class="text-sm text-gray-600">{{ __('Nothing recorded yet.') }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('After your first analysis, you can record what you decided and what you learned.') }}</p>
                </div>
            </section>

            <section class="bg-gray-100 rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700">{{ __('Getting started') }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-600 list-disc list-inside">
                    <li>{{ __('SlipGuard evaluates structural risk in a slip — it never predicts a result.') }}</li>
                    <li>{{ __('Every report explains itself: what was found, why it matters, and what to review.') }}</li>
                    <li>{{ __('The final decision is always yours.') }}</li>
                </ul>
            </section>

        </div>
    </div>
</x-app-layout>
