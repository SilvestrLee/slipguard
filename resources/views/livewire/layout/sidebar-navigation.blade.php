<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function paletteItems(): array
    {
        return [
            ['label' => __('Dashboard'), 'description' => __('Your overview and recent activity'), 'url' => route('dashboard')],
            ['label' => __('Analyse a Slip'), 'description' => __("Review a slip's structural risk"), 'url' => route('analyze.intake')],
            ['label' => __('Build an Accumulator'), 'description' => __('Discover eligible selections and build a new accumulator'), 'url' => route('builder')],
            ['label' => __('Analysis History'), 'description' => __('Open completed structural risk reports'), 'url' => route('history')],
            ['label' => __('Journal'), 'description' => __('Review decisions and reflections'), 'url' => route('journal')],
            ['label' => __('Planning History'), 'description' => __('Resume or review planning sessions'), 'url' => route('planner.history')],
            ['label' => __('SlipGuard Labs'), 'description' => __("See what's being explored next"), 'url' => route('labs')],
            ['label' => __('Profile'), 'description' => __('Manage your account details'), 'url' => route('profile')],
            ['label' => __('Settings'), 'description' => __('Preferences and account settings'), 'url' => route('settings')],
            ['label' => __('Help'), 'description' => __('Product guidance and methodology'), 'url' => route('help')],
        ];
    }
}; ?>

@php
    $primaryItems = [
        ['route' => 'dashboard', 'matches' => ['dashboard'], 'label' => __('Dashboard'), 'icon' => 'heroicon-o-squares-2x2'],
        ['route' => 'analyze.intake', 'matches' => ['analyze', 'analyze.*'], 'label' => __('Analyse Slip'), 'icon' => 'heroicon-o-document-magnifying-glass'],
        ['route' => 'builder', 'matches' => ['builder'], 'label' => __('Build an Accumulator'), 'icon' => 'heroicon-o-circle-stack', 'preview' => ! config('slipguard-market-intelligence.enabled')],
        ['route' => 'history', 'matches' => ['history'], 'label' => __('Analysis History'), 'icon' => 'heroicon-o-clock'],
        ['route' => 'journal', 'matches' => ['journal', 'journal.*'], 'label' => __('Journal'), 'icon' => 'heroicon-o-book-open'],
        ['route' => 'planner.history', 'matches' => ['planner.*'], 'label' => __('Planning History'), 'icon' => 'heroicon-o-arrow-path-rounded-square'],
        ['route' => 'labs', 'matches' => ['labs'], 'label' => __('SlipGuard Labs'), 'icon' => 'heroicon-o-beaker'],
    ];

    $utilityItems = [
        ['route' => 'help', 'matches' => ['help'], 'label' => __('Help & methodology'), 'icon' => 'heroicon-o-question-mark-circle'],
        ['route' => 'settings', 'matches' => ['settings'], 'label' => __('Settings'), 'icon' => 'heroicon-o-cog-6-tooth'],
    ];
@endphp

<div
    x-data="{
        open: false,
        isDark: window.SlipGuardTheme?.isDark()
            ?? (document.documentElement.getAttribute('data-theme') === 'dark'
                || (document.documentElement.getAttribute('data-theme') === null
                    && window.matchMedia('(prefers-color-scheme: dark)').matches)),
        openDrawer() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.drawerClose.focus());
        },
        closeDrawer(restoreFocus = true) {
            if (! this.open) return;
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            if (restoreFocus) this.$nextTick(() => this.$refs.menuTrigger.focus());
        },
        selectDestination() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        trapFocus(event) {
            if (! this.open) return;
            const focusables = this.$refs.drawer.querySelectorAll('a[href], button:not([disabled])');
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
        window.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'command-palette' }));
            }
        });
    "
    x-on:slipguard-theme-changed.window="isDark = $event.detail.dark"
    @keydown.escape.window="closeDrawer()"
    @keydown.tab.window="trapFocus($event)"
    class="print:hidden"
>
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-neutral-200 bg-surface-card/95 backdrop-blur-xl lg:flex"
           aria-label="{{ __('Customer workspace navigation') }}">
        <div class="flex h-20 shrink-0 items-center border-b border-neutral-200 px-6">
            <a href="{{ route('dashboard') }}" wire:navigate aria-label="{{ __('SlipGuard dashboard') }}" class="flex items-center gap-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent">
                <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                     alt="" aria-hidden="true" class="h-9 w-auto shrink-0">
                <span>
                    <span class="block text-lg font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
                    <span class="block text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-neutral-500">{{ __('Risk intelligence') }}</span>
                </span>
            </a>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-y-auto px-4 py-5">
            <p class="px-3 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-neutral-400">{{ __('Workspace') }}</p>
            <nav class="mt-3 space-y-1" aria-label="{{ __('Primary') }}">
                @foreach ($primaryItems as $item)
                    @php $isActive = request()->routeIs(...$item['matches']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate
                       @if ($isActive) aria-current="page" @endif
                       @class([
                           'group flex min-h-11 items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                           'border-accent/30 bg-accent/10 font-semibold text-neutral-900' => $isActive,
                           'border-transparent font-medium text-neutral-600 hover:border-neutral-200 hover:bg-neutral-100 hover:text-neutral-900' => ! $isActive,
                       ])>
                        <span @class([
                            'flex size-8 shrink-0 items-center justify-center rounded-md',
                            'bg-accent-strong text-white' => $isActive,
                            'bg-neutral-100 text-neutral-500 group-hover:text-neutral-700' => ! $isActive,
                        ])>
                            <x-dynamic-component :component="$item['icon']" class="size-4.5" aria-hidden="true" />
                        </span>
                        <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                        @if ($item['preview'] ?? false)
                            <span class="rounded-full border border-neutral-300 px-2 py-0.5 text-[0.62rem] font-semibold uppercase tracking-wide text-neutral-500">
                                {{ __('Preview') }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto pt-7">
                <nav class="space-y-1 border-t border-neutral-200 pt-4" aria-label="{{ __('Utilities') }}">
                    <div class="flex min-h-10 items-center justify-between gap-2 px-2 py-1">
                        <span class="text-xs font-medium text-neutral-500">{{ __('Find & language') }}</span>
                        <span class="flex items-center gap-1">
                            <x-search-trigger-button />
                            <x-language-selector />
                        </span>
                    </div>
                    @foreach ($utilityItems as $item)
                        @php $isActive = request()->routeIs(...$item['matches']); @endphp
                        <a href="{{ route($item['route']) }}" wire:navigate
                           @if ($isActive) aria-current="page" @endif
                           @class([
                               'flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                               'bg-accent/10 font-semibold text-neutral-900' => $isActive,
                               'font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900' => ! $isActive,
                           ])>
                            <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" aria-hidden="true" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <div class="flex min-h-11 items-center justify-between gap-3 px-3 py-2">
                        <span class="flex items-center gap-3 text-sm font-medium text-neutral-600">
                            <x-heroicon-o-sun class="size-5 shrink-0" aria-hidden="true" />
                            {{ __('Theme') }}
                        </span>
                        <x-theme-toggle />
                    </div>
                </nav>

                <div class="mt-3 border-t border-neutral-200 pt-3">
                    <a href="{{ route('profile') }}" wire:navigate
                       @if (request()->routeIs('profile')) aria-current="page" @endif
                       class="flex min-h-12 items-center gap-3 rounded-lg px-3 py-2 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-accent/10 text-sm font-semibold text-accent-strong">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-neutral-900">{{ auth()->user()->name }}</span>
                            <span class="block truncate text-xs text-neutral-500">{{ __('Profile & account') }}</span>
                        </span>
                        <x-heroicon-o-chevron-right class="size-4 shrink-0 text-neutral-400" aria-hidden="true" />
                    </a>
                    <button type="button" wire:click="logout"
                            class="mt-1 flex min-h-10 w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        <x-heroicon-o-arrow-left-on-rectangle class="size-5" aria-hidden="true" />
                        {{ __('Log out') }}
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <header class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-neutral-200 bg-surface-card/95 px-4 backdrop-blur-xl lg:hidden">
        <a href="{{ route('dashboard') }}" wire:navigate aria-label="{{ __('SlipGuard dashboard') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                 alt="" aria-hidden="true" class="h-8 w-auto">
            <span class="text-lg font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
        </a>
        <div class="flex items-center gap-1">
            <x-search-trigger-button />
            <button x-ref="menuTrigger" type="button" @click="openDrawer()"
                    :aria-expanded="open.toString()" aria-controls="customer-navigation-drawer"
                    class="inline-flex size-11 items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    aria-label="{{ __('Open navigation') }}">
                <x-heroicon-o-bars-3 class="size-6" aria-hidden="true" />
            </button>
        </div>
    </header>

    <div x-show="open" x-cloak @click="closeDrawer()"
         x-transition.opacity
         class="fixed inset-0 z-40 bg-neutral-950/55 lg:hidden" aria-hidden="true"></div>

    <aside id="customer-navigation-drawer" x-ref="drawer" x-show="open" x-cloak
           x-transition:enter="transition-transform duration-200 ease-out"
           x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition-transform duration-150 ease-in"
           x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 flex w-[min(22rem,88vw)] flex-col border-r border-neutral-300 bg-surface-card lg:hidden"
           role="dialog" aria-modal="true" aria-label="{{ __('Customer navigation') }}"
           style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-neutral-200 px-4">
            <span class="flex items-center gap-2.5">
                <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                     alt="" aria-hidden="true" class="h-8 w-auto">
                <span class="font-semibold text-neutral-900">{{ __('SlipGuard workspace') }}</span>
            </span>
            <button x-ref="drawerClose" type="button" @click="closeDrawer()" aria-label="{{ __('Close navigation') }}"
                    class="inline-flex size-11 items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-4">
            <nav class="space-y-1" aria-label="{{ __('Primary') }}">
                @foreach ($primaryItems as $item)
                    @php $isActive = request()->routeIs(...$item['matches']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate @click="selectDestination()"
                       @if ($isActive) aria-current="page" @endif
                       @class([
                           'flex min-h-12 items-center gap-3 rounded-lg border px-3 py-2.5 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                           'border-accent/30 bg-accent/10 font-semibold text-neutral-900' => $isActive,
                           'border-transparent font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900' => ! $isActive,
                       ])>
                        <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" aria-hidden="true" />
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if ($item['preview'] ?? false)
                            <span class="text-xs text-neutral-500">{{ __('Preview') }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <nav class="mt-5 space-y-1 border-t border-neutral-200 pt-5" aria-label="{{ __('Utilities') }}">
                @foreach ($utilityItems as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate @click="selectDestination()"
                       class="flex min-h-12 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        <x-dynamic-component :component="$item['icon']" class="size-5" aria-hidden="true" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="flex min-h-12 items-center justify-between px-3 py-2">
                    <span class="text-sm font-medium text-neutral-600">{{ __('Theme') }}</span>
                    <x-theme-toggle />
                </div>
            </nav>
        </div>

        <div class="shrink-0 border-t border-neutral-200 p-4">
            <a href="{{ route('profile') }}" wire:navigate @click="selectDestination()"
               class="flex min-h-12 items-center gap-3 rounded-lg px-3 py-2 hover:bg-neutral-100">
                <span class="flex size-9 items-center justify-center rounded-full bg-accent/10 text-sm font-semibold text-accent-strong">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-neutral-900">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-neutral-500">{{ __('Profile & account') }}</span>
                </span>
            </a>
            <button type="button" wire:click="logout"
                    class="mt-1 flex min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900">
                <x-heroicon-o-arrow-left-on-rectangle class="size-5" aria-hidden="true" />
                {{ __('Log out') }}
            </button>
        </div>
    </aside>

    <x-command-palette :items="$this->paletteItems()" />
    <x-language-modal />
</div>
