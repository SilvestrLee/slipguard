{{--
    U-11.3 §7: a visible, explicit light/dark override — not hidden inside a
    profile menu. Before any explicit choice, the app follows system
    preference (already implemented at the CSS token level, unchanged).
    Clicking this sets an explicit preference that persists via
    localStorage and overrides system preference in both directions
    (resources/css/app.css's `data-theme` selectors).

    U-16.1 (Phase 7): added `aria-pressed` and press feedback to the
    original single-icon-swap button.

    `PO-U16.2-CR-001` §6 — rebuilt as a genuine pill/track/thumb switch,
    not a single button whose icon silently swaps. The prior form was
    exactly the directive's own named anti-pattern ("two unrelated
    standalone icons... icons with no visible selected state"): only one
    icon was ever in the DOM at a time, so there was no track, no
    indicator, and no persistent visual reference for "the other state."
    Both sun and moon are now always present inside one track, with a
    sliding indicator (`translate-x`, 200ms `ease-out` — within the
    directive's 150–250ms budget) showing which is active — the same
    metaphor as a native iOS/Android switch, which is why this now uses
    `role="switch"`/`aria-checked` (the more correct WAI-ARIA pattern for
    a sliding two-state control) rather than `aria-pressed`.
--}}
<button type="button"
        x-data="{
            effectiveIsDark: window.SlipGuardTheme?.isDark()
                ?? (document.documentElement.getAttribute('data-theme') === 'dark'
                    || (document.documentElement.getAttribute('data-theme') === null
                        && window.matchMedia('(prefers-color-scheme: dark)').matches)),
            toggle() {
                if (window.SlipGuardTheme) {
                    window.SlipGuardTheme.toggle();
                    return;
                }

                this.effectiveIsDark = ! this.effectiveIsDark;
                document.documentElement.setAttribute('data-theme', this.effectiveIsDark ? 'dark' : 'light');
            },
        }"
        x-on:click="toggle()"
        x-on:slipguard-theme-changed.window="effectiveIsDark = $event.detail.dark"
        role="switch"
        :aria-checked="effectiveIsDark.toString()"
        :aria-label="effectiveIsDark ? '{{ __('Switch to light theme') }}' : '{{ __('Switch to dark theme') }}'"
        {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 items-center h-8 w-14 rounded-full border border-neutral-300 bg-neutral-100 hover:bg-neutral-200 active:scale-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-[background-color,transform] duration-instant']) }}>
    {{-- The sliding indicator — restrained transform-only motion, 200ms, no bounce/overshoot (MOTION_SYSTEM.md). --}}
    <span aria-hidden="true"
          class="pointer-events-none absolute inset-y-1 left-1 size-6 rounded-full bg-surface-card shadow-elevation-1 border border-neutral-300 transition-transform duration-200 ease-out"
          :class="effectiveIsDark ? 'translate-x-[24px]' : 'translate-x-0'"></span>

    {{-- Both icons are always present — the track itself is the affordance; the indicator shows which is selected. --}}
    <span class="relative z-10 flex w-1/2 items-center justify-center">
        <x-heroicon-o-sun class="size-4 transition-colors duration-instant" x-bind:class="effectiveIsDark ? 'text-neutral-400' : 'text-accent-strong'" aria-hidden="true" />
    </span>
    <span class="relative z-10 flex w-1/2 items-center justify-center">
        <x-heroicon-o-moon class="size-4 transition-colors duration-instant" x-bind:class="effectiveIsDark ? 'text-accent-strong' : 'text-neutral-400'" aria-hidden="true" />
    </span>
</button>
