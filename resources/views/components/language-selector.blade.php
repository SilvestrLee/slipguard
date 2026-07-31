{{--
    Founder direct instruction (2026-07-28): a language selector, sitewide.
    No second locale is configured (`config/app.php`'s `locale` is `en`
    only, no `lang/` translation set exists for anything else), so this is
    an honest current-state dropdown — English shown as the active (only)
    option, not a disabled dead end — reusing this app's existing
    Notifications-placeholder convention (a working `<x-dropdown>`
    revealing a static, truthful message) rather than inventing a new
    pattern.
--}}
<div {{ $attributes }}>
    <x-dropdown align="right" width="60">
        <x-slot name="trigger">
            <button type="button" class="p-2 rounded-md text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('Language') }}">
                <x-heroicon-o-language class="h-5 w-5" />
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between text-sm text-neutral-700">
                    <span>{{ __('English') }}</span>
                    <x-heroicon-o-check class="h-4 w-4 text-accent-strong" />
                </div>
                <div class="mt-1 text-xs text-neutral-500">
                    {{ __('More languages coming soon.') }}
                </div>
            </div>
        </x-slot>
    </x-dropdown>
</div>
