<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-neutral-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="container-standard mx-auto px-4 sm:px-6 lg:px-8">
            <x-empty-state :title="__(':title is on the way.', ['title' => $title])"
                :description="__('This part of SlipGuard hasn\'t been built yet. Start with the dashboard while we finish it.')">
                <x-slot name="action">
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-surface-card shadow-elevation-1 border border-neutral-200/60 rounded-md font-semibold text-sm text-neutral-700 hover:bg-surface-soft focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition">
                        {{ __('Back to Dashboard') }}
                    </a>
                </x-slot>
            </x-empty-state>
        </div>
    </div>
</x-app-layout>
