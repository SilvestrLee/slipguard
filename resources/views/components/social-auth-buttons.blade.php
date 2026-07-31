{{--
    Founder direct instruction (2026-07-28): "Sign in with Google" and "Sign
    in with Apple" placeholders — the OAuth credentials/provider config
    don't exist yet, so these are intentionally disabled, not wired to
    Laravel Socialite (not installed) or any route.

    Placement decision (U-08.1 auth-screen work, evaluated against 21st.dev's
    `get_inspiration` — every returned reference placed social buttons either
    above or below a divider next to the email/password form; none showed a
    disabled/"coming soon" variant): placed BELOW the working email/password
    form, not above it, since these two buttons currently do nothing — leading
    with a dead end ahead of the one path that actually works would be worse
    UX than the generic pattern assumes. Adopted the divider + two-up button
    row layout; rejected "social-first" ordering for that reason.

    Disabled state follows the sitewide no-shadow/border convention (a
    contrasting border, not a shadow) and marks non-interactivity three ways
    (disabled attribute, aria-disabled, reduced-opacity + not-allowed cursor)
    per UI UX Pro Max's Interaction > Disabled States guideline.
--}}
<div class="mt-6">
    <div class="relative">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-neutral-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-3 bg-surface-card text-neutral-500">{{ __('Or continue with') }}</span>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3">
        <button type="button" disabled aria-disabled="true" title="{{ __('Coming soon') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md border border-neutral-300 text-sm font-medium text-neutral-500 opacity-60 cursor-not-allowed">
            <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.42 3.58v2.98h3.93c2.31-2.12 3.64-5.24 3.64-8.8z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.92l-3.93-2.98c-1.09.73-2.48 1.15-4 1.15-3.08 0-5.68-2.08-6.62-4.87H1.32v3.07C3.29 21.3 7.34 24 12 24z"/>
                <path fill="#FBBC05" d="M5.38 14.38A6.99 6.99 0 0 1 5 12c0-.83.14-1.63.38-2.38V6.55H1.32A11.98 11.98 0 0 0 0 12c0 1.94.46 3.77 1.32 5.45l4.06-3.07z"/>
                <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.94 1.19 15.24 0 12 0 7.34 0 3.29 2.7 1.32 6.55l4.06 3.07C6.32 6.85 8.92 4.77 12 4.77z"/>
            </svg>
            {{ __('Google') }}
        </button>
        <button type="button" disabled aria-disabled="true" title="{{ __('Coming soon') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md border border-neutral-300 text-sm font-medium text-neutral-500 opacity-60 cursor-not-allowed">
            <svg class="size-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16.365 1.43c0 1.14-.493 2.27-1.177 3.08-.744.9-1.99 1.57-2.987 1.57-.12 0-.23-.02-.3-.03-.01-.06-.04-.22-.04-.39 0-1.15.572-2.27 1.206-2.98.804-.94 2.142-1.64 3.248-1.68.03.13.05.28.05.43zm4.4 16.1c-.03.09-.51 1.75-1.68 3.45-1.01 1.46-2.06 2.92-3.72 2.95-1.63.03-2.15-.96-4.01-.96-1.86 0-2.44.93-3.98.99-1.6.06-2.82-1.58-3.84-3.03-2.08-3-3.67-8.48-1.53-12.19C3.03 5.9 5 4.72 7.11 4.69c1.56-.03 3.03 1.05 3.98 1.05.94 0 2.73-1.3 4.6-1.11.78.03 2.98.32 4.39 2.4-.11.07-2.62 1.53-2.6 4.57.04 3.63 3.19 4.83 3.23 4.85-.03.08-.5 1.72-1.65 3.28l.03-.15z"/>
            </svg>
            {{ __('Apple') }}
        </button>
    </div>
</div>
