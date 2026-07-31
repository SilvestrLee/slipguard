@php
    $workspaceTitle = match (true) {
        request()->routeIs('dashboard') => __('Dashboard'),
        request()->routeIs('analyze.intake') => __('Analyse a Slip'),
        request()->routeIs('analyze.create') => __('New Slip'),
        request()->routeIs('analyze.edit') => __('Slip Workspace'),
        request()->routeIs('analyze.processing') => __('Analysing Slip'),
        request()->routeIs('analyze.report') => __('Risk Report'),
        request()->routeIs('analyze') => __('Analyse Slip'),
        request()->routeIs('builder') => __('Build Accumulator'),
        request()->routeIs('history') => __('Analysis History'),
        request()->routeIs('journal.create') => __('New Journal Entry'),
        request()->routeIs('journal.edit') => __('Edit Journal Entry'),
        request()->routeIs('journal') => __('Decision Journal'),
        request()->routeIs('planner.session') => __('Accumulator Planner'),
        request()->routeIs('planner.history') => __('Planning History'),
        request()->routeIs('labs') => __('SlipGuard Labs'),
        request()->routeIs('profile') => __('Profile'),
        request()->routeIs('settings') => __('Settings'),
        request()->routeIs('help') => __('Help'),
        default => config('app.name', 'SlipGuard'),
    };
@endphp

<a href="#main-content"
   class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-md focus:bg-accent-strong focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
    {{ __('Skip to content') }}
</a>

<div class="atmosphere" aria-hidden="true">
    <span data-atmosphere-depth="-0.018" style="width: 32rem; height: 32rem; top: -10rem; left: -8rem;"></span>
    <span data-atmosphere-depth="0.024" style="width: 26rem; height: 26rem; top: 20rem; right: -10rem;"></span>
    <span data-atmosphere-depth="-0.012" style="width: 22rem; height: 22rem; bottom: -6rem; left: 15%;"></span>
    <span data-atmosphere-depth="0.016" style="width: 18rem; height: 18rem; top: 55%; right: 20%;"></span>
</div>

<div class="relative z-10 min-h-screen lg:h-screen lg:overflow-hidden">
    <livewire:layout.sidebar-navigation />

    <div class="min-w-0 pt-16 lg:h-screen lg:overflow-y-auto lg:pl-72 lg:pt-0" data-workspace-scroll>
        <header class="sticky top-0 z-30 border-b border-neutral-200/80 bg-surface-page/90 backdrop-blur-xl print:hidden">
            <div class="mx-auto flex min-h-16 max-w-[88rem] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="min-w-0">
                    @if (isset($header))
                        {{ $header }}
                    @else
                        <h1 class="truncate text-lg font-semibold tracking-tight text-neutral-900 sm:text-xl">
                            {{ $workspaceTitle }}
                        </h1>
                    @endif
                </div>

                @if (isset($workspaceActions))
                    <div class="shrink-0">
                        {{ $workspaceActions }}
                    </div>
                @endif
            </div>
        </header>

        <main id="main-content" class="min-h-[calc(100vh-4rem)]">
            {{ $slot }}
        </main>

        @include('partials.authenticated-footer')
    </div>
</div>
