<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * `PO-U24-003` — replaces the `coming-soon` stub previously rendered at
 * `/settings`. Deliberately small: repository inspection (see the
 * directive's own Settings Truth Matrix, returned in the implementation
 * report) confirmed only one genuinely exclusive, safely-exposable
 * preference exists — Appearance (theme). Name/email/password/account
 * deletion are already fully owned and implemented by `profile.blade.php`
 * (`route('profile')`) — Account and Security below summarise the real
 * current values and point there rather than duplicating a second, competing
 * editor for the same fields (§28 "Settings vs Profile"). No server-side
 * mutation happens on this page at all — it renders real current values
 * and one client-side theme preference, nothing else.
 */
new #[Layout('layouts.app')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }
}; ?>

<div class="workspace-page">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">

        <x-page-header :title="__('Settings')"
            :description="__('Manage your account and personal SlipGuard preferences.')" />

        {{-- Appearance — the one setting genuinely exclusive to this page, not owned by Profile. --}}
        <section aria-labelledby="appearance-heading" class="workspace-section">
            <x-workspace.section-heading id="appearance-heading" :title="__('Appearance')"
                :description="__('Choose how SlipGuard looks on this device.')" />

            <x-card class="mt-4">
                <h3 class="text-sm font-semibold text-neutral-900">{{ __('Theme') }}</h3>
                <p class="mt-1 text-sm text-neutral-600">
                    {{ __('Applies immediately and stays this way the next time you visit — the same preference used by the theme switch in the header.') }}
                </p>

                {{--
                    `PO-U24-003` §15/§40: the same underlying mechanism as
                    the sitewide theme switch (`window.SlipGuardTheme`,
                    `resources/js/app.js`), not a second theme state.
                    Client-side localStorage only — never a server-side
                    Livewire property, per §40's own "theme/sidebar
                    presentation preferences may use client-side storage"
                    allowance. Visually matches `<x-segmented-radio>`'s
                    established segmented-control language exactly (same
                    classes) without reusing the component itself, since
                    that component is wired for `wire:model` server
                    round-trips and this preference is deliberately never
                    sent to the server. Only Light/Dark are offered — the
                    sitewide switch itself has no third "System" state to
                    match (it falls back to system preference only until an
                    explicit choice is made, with no exposed way back to
                    that unset state anywhere in the product today), so
                    adding one here would be a new theme state, not a
                    reflection of an existing one.

                    One deliberate departure from `<x-segmented-radio>`'s own
                    classes, found via real axe-core verification in dark
                    theme (not present in light theme, so easy to miss by
                    inspection alone): its static `peer-checked:text-white`
                    only clears WCAG AA against light theme's
                    `--accent-strong` (#4f46e5, 6.3:1) — dark theme's
                    `--accent-strong` is a much lighter indigo-400 (#818cf8,
                    tuned for light text/icons *against* a dark page, per
                    `app.css`'s own documented `--gradient-cta` reasoning),
                    so white-on-it is only 2.98:1. Fixed here, in this page's
                    own control only, by tracking `isDark` alongside `current`
                    and switching the checked option's text between
                    `text-white` and the existing constant `text-surface-inverse`
                    token (`#1b1c20`, already documented as deliberately equal
                    in both themes) — 6.3:1 and 5.7:1 respectively, both
                    passing. Not back-ported into the shared component or
                    into Builder's own usage of it (out of this bounded
                    commission's scope, `<x-segmented-radio>` and Builder
                    both untouched) — disclosed as a found, pre-existing
                    defect in that shared pattern rather than silently
                    carried forward unfixed on this page too.
                --}}
                <div
                    x-data="{
                        current: window.SlipGuardTheme?.current() ?? 'light',
                        isDark: window.SlipGuardTheme?.isDark() ?? false,
                        select(theme) {
                            this.current = theme;
                            window.SlipGuardTheme?.set(theme);
                        },
                    }"
                    x-on:slipguard-theme-changed.window="current = $event.detail.theme; isDark = $event.detail.dark"
                    role="radiogroup"
                    aria-label="{{ __('Theme') }}"
                    class="mt-4 inline-flex w-full max-w-xs gap-1 rounded-md border border-neutral-300 bg-neutral-50 p-1 sm:w-auto"
                >
                    @foreach (['light' => __('Light'), 'dark' => __('Dark')] as $value => $label)
                        <label class="flex-1">
                            <input type="radio" name="theme" value="{{ $value }}"
                                   x-on:change="select('{{ $value }}')"
                                   x-bind:checked="current === '{{ $value }}'"
                                   class="peer sr-only" />
                            <span class="flex min-h-10 cursor-pointer items-center justify-center rounded px-4 text-sm font-medium text-neutral-600 transition-colors duration-instant hover:bg-neutral-100 peer-checked:bg-accent-strong peer-checked:hover:bg-accent-strong peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent"
                                  x-bind:class="current === '{{ $value }}' ? (isDark ? 'text-surface-inverse' : 'text-white') : ''">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </x-card>
        </section>

        {{-- Account — real current values, summary only; Profile remains the one place these are edited (§28). --}}
        <section aria-labelledby="account-heading" class="workspace-section">
            <x-workspace.section-heading id="account-heading" :title="__('Account')"
                :description="__('Your current account details.')" />

            <x-card class="mt-4">
                <dl class="divide-y divide-neutral-200/60">
                    <div class="py-3 first:pt-0">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ __('Name') }}</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $name }}</dd>
                    </div>
                    <div class="py-3 last:pb-0">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ __('Email') }}</dt>
                        <dd class="mt-1 text-sm text-neutral-900">{{ $email }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-sm text-neutral-600">
                    {{ __('To update your name or email, visit your') }}
                    <a href="{{ route('profile') }}" wire:navigate class="font-semibold text-accent-strong hover:text-accent">{{ __('Profile') }}</a>.
                </p>
            </x-card>
        </section>

        {{-- Security — password and account deletion already live on Profile; not duplicated here. --}}
        <section aria-labelledby="security-heading" class="workspace-section">
            <x-workspace.section-heading id="security-heading" :title="__('Security')"
                :description="__('Manage your password and account.')" />

            <x-card class="mt-4">
                <p class="text-sm text-neutral-600">
                    {{ __('Change your password or delete your account from your') }}
                    <a href="{{ route('profile') }}" wire:navigate class="font-semibold text-accent-strong hover:text-accent">{{ __('Profile') }}</a>{{ __(' page.') }}
                </p>
            </x-card>
        </section>

        {{-- `PO-U24-003` §29 — a small contextual link, not embedded methodology. --}}
        <p class="text-sm text-neutral-500">
            {{ __('Need help with these settings? Visit') }}
            <a href="{{ route('help') }}" wire:navigate class="font-semibold text-accent-strong hover:text-accent">{{ __('Help & Methodology') }}</a>.
        </p>

    </div>
</div>
