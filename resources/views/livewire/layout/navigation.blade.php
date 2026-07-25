<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav
    x-data="{
        open: false,
        openDrawer() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.drawerClose.focus());
        },
        closeDrawer() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.$refs.menuTrigger.focus());
        },
        selectDestination() {
            // §43's Drawer State Matrix: a destination click hands focus to
            // standard navigation, unlike Escape/close-button/overlay closes
            // — forcing focus back onto a trigger the user is navigating
            // away from would fight the browser's own focus handling.
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
    @keydown.escape.window="closeDrawer()"
    @keydown.tab.window="trapFocus($event)"
    class="bg-white border-b border-gray-200"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate aria-label="SlipGuard dashboard">
                        <img src="{{ asset('brand/slipguard-logo-light-transparent.svg') }}" alt="" aria-hidden="true" class="h-8 w-auto shrink-0">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('analyze')" :active="request()->routeIs('analyze')" wire:navigate>
                        {{ __('Analyze Slip') }}
                    </x-nav-link>
                    <x-nav-link :href="route('history')" :active="request()->routeIs('history')" wire:navigate>
                        {{ __('History') }}
                    </x-nav-link>
                    <x-nav-link :href="route('journal')" :active="request()->routeIs('journal')" wire:navigate>
                        {{ __('Journal') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
                <!-- Notifications placeholder -->
                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('Notifications') }}">
                            <x-heroicon-o-bell class="h-5 w-5" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 text-sm text-gray-500">
                            {{ __("You're all caught up. No new notifications.") }}
                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- User menu -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <x-heroicon-o-chevron-down class="ms-1 h-4 w-4" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings')" wire:navigate>
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('help')" wire:navigate>
                            {{ __('Help') }}
                        </x-dropdown-link>

                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button x-ref="menuTrigger" @click="openDrawer()"
                        :aria-expanded="open.toString()" aria-controls="mobile-drawer"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" aria-label="{{ __('Toggle navigation') }}">
                    <x-heroicon-o-bars-3 class="h-6 w-6" />
                </button>
            </div>
        </div>
    </div>

    {{-- U-03.2 §42–§46 — full-height mobile navigation drawer (mobile/tablet only; desktop nav above is unchanged, §45). --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition-opacity duration-300 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-300 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="closeDrawer()"
         class="fixed inset-0 z-40 bg-neutral-900/50 sm:hidden" aria-hidden="true"></div>

    <div id="mobile-drawer" x-ref="drawer" x-show="open" x-cloak
         x-transition:enter="transition-transform duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform duration-300 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-50 w-full max-w-xs bg-white shadow-xl flex flex-col sm:hidden"
         role="navigation" aria-label="{{ __('Main menu') }}"
         style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">

        <div class="flex items-center justify-between h-16 px-4 border-b border-neutral-200 shrink-0">
            <img src="{{ asset('brand/slipguard-logo-light-transparent.svg') }}" alt="SlipGuard" class="h-8 w-auto shrink-0">
            <button x-ref="drawerClose" @click="closeDrawer()" aria-label="{{ __('Close menu') }}"
                    class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 focus:outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <div class="px-2 space-y-1">
                @foreach ([
                    ['route' => 'dashboard', 'label' => __('Dashboard')],
                    ['route' => 'analyze', 'label' => __('Analyze Slip')],
                    ['route' => 'history', 'label' => __('History')],
                    ['route' => 'journal', 'label' => __('Journal')],
                ] as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate @click="selectDestination()"
                       @if ($isActive) aria-current="page" @endif
                       class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-md text-base
                           {{ $isActive ? 'font-semibold text-neutral-900 bg-neutral-100 border-s-4 border-accent-strong' : 'font-medium text-neutral-600 border-s-4 border-transparent hover:bg-neutral-50 hover:text-neutral-900' }}
                           focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="mt-4 pt-4 px-2 border-t border-neutral-200 space-y-1">
                <div class="px-3 pb-1 text-xs font-medium text-neutral-500">{{ auth()->user()->name }}</div>
                @foreach ([
                    ['route' => 'profile', 'label' => __('Profile')],
                    ['route' => 'settings', 'label' => __('Settings')],
                ] as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate @click="selectDestination()"
                       class="flex items-center min-h-11 px-3 py-2.5 rounded-md text-base font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <button wire:click="logout"
                        class="w-full text-start flex items-center min-h-11 px-3 py-2.5 rounded-md text-base font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    {{ __('Log Out') }}
                </button>
            </div>

            <div class="mt-4 pt-4 px-2 border-t border-neutral-200 space-y-1">
                <a href="{{ route('help') }}" wire:navigate @click="selectDestination()"
                   class="flex items-center min-h-11 px-3 py-2.5 rounded-md text-base font-medium text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    {{ __('Help') }}
                </a>
            </div>
        </div>

        <div class="p-4 border-t border-neutral-200 shrink-0">
            <a href="{{ route('analyze.create') }}" wire:navigate @click="selectDestination()"
               class="flex items-center justify-center min-h-11 w-full px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                {{ __('Analyse a slip') }}
            </a>
        </div>
    </div>
</nav>
