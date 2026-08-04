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

    /**
     * Command palette quick-navigation targets — SlipGuard's own real,
     * named routes (not a generic list), per the founder's own instruction
     * to personalise the OddStorm-inspired search modal's contents.
     */
    public function paletteItems(): array
    {
        return [
            ['label' => __('Dashboard'), 'description' => __('Your overview and recent activity'), 'url' => route('dashboard')],
            ['label' => __('Analyze a Slip'), 'description' => __("Check a slip's structural risk before you place it"), 'url' => route('analyze.create')],
            ['label' => __('History'), 'description' => __('Everything SlipGuard has already analysed for you'), 'url' => route('history')],
            ['label' => __('Journal'), 'description' => __('What you decided and what you learned'), 'url' => route('journal')],
            ['label' => __('Planning History'), 'description' => __('Your past accumulator planning sessions'), 'url' => route('planner.history')],
            ['label' => __('SlipGuard Labs'), 'description' => __("What's new and what's coming next"), 'url' => route('labs')],
            ['label' => __('Profile'), 'description' => __('Your account details'), 'url' => route('profile')],
            ['label' => __('Settings'), 'description' => __('Preferences and account settings'), 'url' => route('settings')],
            ['label' => __('Help'), 'description' => __('Get help using SlipGuard'), 'url' => route('help')],
        ];
    }
}; ?>

<nav
    x-data="{
        open: false,
        {{-- Founder direct instruction (2026-07-28): the logo now maps to the active theme here too, reusing the exact isDark + slipguard-theme-changed pattern already built for the public header and the guest auth layout — not a new mechanism. --}}
        isDark: window.SlipGuardTheme?.isDark()
            ?? (document.documentElement.getAttribute('data-theme') === 'dark'
                || (document.documentElement.getAttribute('data-theme') === null
                    && window.matchMedia('(prefers-color-scheme: dark)').matches)),
        {{-- The approved product mark is static. Scroll state remains only
            for the sticky header's restrained elevation change. --}}
        condensed: false,
        updateFromScroll() {
            this.condensed = window.scrollY > 40;
        },
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
    x-init="
        window.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'command-palette' }));
            }
        });
        updateFromScroll();
        let handleScroll = () => updateFromScroll();
        if (window.__slipguardHeaderScroll) { window.removeEventListener('scroll', window.__slipguardHeaderScroll); }
        window.__slipguardHeaderScroll = handleScroll;
        window.addEventListener('scroll', handleScroll, { passive: true });
    "
    x-on:slipguard-theme-changed.window="isDark = $event.detail.dark"
    @keydown.escape.window="closeDrawer()"
    @keydown.tab.window="trapFocus($event)"
    :class="condensed ? 'shadow-elevation-1' : ''"
    class="print:hidden sticky top-0 z-40 border-b border-neutral-200/70 backdrop-blur-md bg-surface-page/70 transition-[box-shadow] duration-standard ease-in-out"
>
    {{-- U-16.1 (Phase 10): was raw `max-w-7xl` — the exact same 1280px value as `container-marketing`, expressed as a second, older class. One container width, one class. --}}
    <div class="container-marketing mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    {{--
                        Founder direct instruction (2026-07-28): the portal
                        header matches the public header's static
                        icon/wordmark split, reusing the same accent icon
                        (`slipguard-icon-accent(-dark).svg`), not the navy
                        full lockup previously used here. This knowingly
                        reintroduces the colour mismatch already disclosed
                        and accepted when the public header adopted the
                        purple/white accent icon — the footer and other
                        remaining navy instances are unchanged, out of
                        scope for this header-only motion.
                    --}}
                    <a href="{{ route('dashboard') }}" wire:navigate aria-label="SlipGuard dashboard" class="flex items-center gap-2.5">
                        <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                             alt=""
                             aria-hidden="true"
                             class="h-8 w-auto shrink-0">
                        <span class="text-xl font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
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
                    <x-nav-link :href="route('planner.history')" :active="request()->routeIs('planner.history')" wire:navigate>
                        {{ __('Planning History') }}
                    </x-nav-link>
                    <x-nav-link :href="route('labs')" :active="request()->routeIs('labs')" wire:navigate>
                        {{ __('SlipGuard Labs') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
                <x-theme-toggle />

                {{--
                    Founder direct instruction (2026-07-28): "the header is
                    missing two features: search icon and language
                    selection," then "[image of OddStorm's search modal]
                    ... personalise the result to slipguard," then "make the
                    search and language feature sitewide." Search evolved
                    from a "coming soon" dropdown into a real command-palette
                    quick-navigation modal (`<x-command-palette>`,
                    `paletteItems()` above) once the reference screenshot was
                    supplied — see that component's own comment for what was
                    and wasn't adopted from the reference. Both this and
                    `<x-language-selector>` are shared components, also used
                    by the public header and the guest auth layout, so all
                    three headers behave identically.
                --}}
                <x-search-trigger-button />
                <x-language-selector />

                <!-- Notifications placeholder -->
                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button class="p-2 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('Notifications') }}">
                            <x-heroicon-o-bell class="h-5 w-5" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 text-sm text-neutral-500">
                            {{ __("You're all caught up. No new notifications.") }}
                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- User menu -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-500 bg-neutral-50 hover:text-neutral-700 focus:outline-none transition ease-in-out duration-150">
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
                        class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 hover:text-neutral-500 hover:bg-neutral-100 focus:outline-none focus:bg-neutral-100 focus:text-neutral-500 transition duration-150 ease-in-out" aria-label="{{ __('Toggle navigation') }}">
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
         class="fixed inset-y-0 right-0 z-50 w-full max-w-xs bg-surface-page shadow-elevation-3 border-l-2 border-neutral-300 flex flex-col sm:hidden"
         role="navigation" aria-label="{{ __('Main menu') }}"
         style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">

        <div class="flex items-center justify-between h-16 px-4 border-b border-neutral-200 shrink-0">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('brand/slipguard-icon-accent.svg') }}"
                     alt=""
                     aria-hidden="true"
                     class="h-8 w-auto shrink-0">
                <span class="text-lg font-semibold tracking-tight text-neutral-900">{{ __('SlipGuard') }}</span>
            </div>
            <div class="flex items-center gap-1">
                <x-search-trigger-button />
                <x-language-selector />
                <x-theme-toggle />
                <button x-ref="drawerClose" @click="closeDrawer()" aria-label="{{ __('Close menu') }}"
                        class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 focus:outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <div class="px-2 space-y-1">
                @foreach ([
                    ['route' => 'dashboard', 'label' => __('Dashboard')],
                    ['route' => 'analyze', 'label' => __('Analyze Slip')],
                    ['route' => 'history', 'label' => __('History')],
                    ['route' => 'journal', 'label' => __('Journal')],
                    ['route' => 'planner.history', 'label' => __('Planning History')],
                    ['route' => 'labs', 'label' => __('SlipGuard Labs')],
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

    <x-command-palette :items="$this->paletteItems()" />
    <x-language-modal />
</nav>
