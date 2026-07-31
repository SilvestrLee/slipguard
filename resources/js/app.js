/*
 * One runtime authority for SlipGuard theme state. The inline head partial
 * still handles first paint; this controller owns interaction and
 * synchronization once JavaScript loads.
 */
const slipGuardTheme = (() => {
    const storageKey = 'slipguard-theme';
    const systemPreference = window.matchMedia('(prefers-color-scheme: dark)');

    const readPreference = () => {
        try {
            const stored = window.localStorage.getItem(storageKey);

            return stored === 'light' || stored === 'dark' ? stored : null;
        } catch (error) {
            return null;
        }
    };

    const writePreference = (theme) => {
        try {
            window.localStorage.setItem(storageKey, theme);
        } catch (error) {
            // The active page can still change theme when persistence is
            // unavailable. System preference remains the next-load fallback.
        }
    };

    const effectiveTheme = () => readPreference() ?? (systemPreference.matches ? 'dark' : 'light');

    const apply = (theme, persist = false) => {
        if (theme !== 'light' && theme !== 'dark') {
            return;
        }

        if (persist) {
            writePreference(theme);
        }

        document.documentElement.setAttribute('data-theme', theme);
        window.dispatchEvent(new CustomEvent('slipguard-theme-changed', {
            detail: { theme, dark: theme === 'dark' },
        }));
    };

    const synchronize = () => {
        const stored = readPreference();

        if (stored) {
            apply(stored);
            return;
        }

        document.documentElement.removeAttribute('data-theme');
        const theme = effectiveTheme();
        window.dispatchEvent(new CustomEvent('slipguard-theme-changed', {
            detail: { theme, dark: theme === 'dark' },
        }));
    };

    window.addEventListener('storage', (event) => {
        if (event.key === storageKey || event.key === null) {
            synchronize();
        }
    });

    systemPreference.addEventListener('change', () => {
        if (readPreference() === null) {
            synchronize();
        }
    });

    return {
        current: effectiveTheme,
        isDark: () => effectiveTheme() === 'dark',
        set: (theme) => apply(theme, true),
        toggle: () => apply(effectiveTheme() === 'dark' ? 'light' : 'dark', true),
        synchronize,
    };
})();

window.SlipGuardTheme = slipGuardTheme;

/**
 * Founder direct instruction (2026-07-28): "fade-in-up motions as a user
 * scrolls down the site, sitewide." Extracted from a `<script>` block that
 * previously lived only in `resources/views/pages/home.blade.php` (U-15.2)
 * — same exact mechanism (plain IntersectionObserver, no animation
 * library, `MOTION_SYSTEM.md`'s Reveal Animations: opacity 0→1,
 * translate-y 8px→0, 300ms ease-out, once, never re-triggering, section
 * level only) — moved here so every page marking a section `data-reveal`
 * gets it, not just the homepage, without duplicating the script per page.
 *
 * Runs on `livewire:navigated`, which Livewire v3 dispatches on the very
 * first page load AND after every subsequent `wire:navigate` transition —
 * one listener covers both cases, matching this app's own established
 * "no duplicate per-page implementations" discipline (see the shared
 * `theme-init-script` partial for the same pattern applied to theming).
 */
function initScrollReveal() {
    document.querySelectorAll('[data-reveal]').forEach((el) => {
        if (el.dataset.revealBound) {
            return;
        }
        el.dataset.revealBound = 'true';

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        el.classList.add('opacity-0', 'translate-y-2', 'transition', 'duration-300', 'ease-out');
        const observer = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                el.classList.remove('opacity-0', 'translate-y-2');
                observer.disconnect();
            }
        }, { threshold: 0.15 });
        observer.observe(el);
    });
}

document.addEventListener('livewire:navigated', initScrollReveal);

/**
 * U-12.1-S10 / PW-03 — one restrained, user-driven atmosphere controller
 * for both product environments. Authenticated routes read their existing
 * workspace scroll region; public pages read document scroll. Each form has
 * an explicit depth, displacement is hard-capped, and one RAF batches each
 * scroll frame. There are no timers, pointer listeners, easing, or inertia.
 */
let removeAtmosphereListener = null;

function initAtmosphereParallax() {
    removeAtmosphereListener?.();
    removeAtmosphereListener = null;

    const atmosphere = document.querySelector('.atmosphere');
    const layers = atmosphere?.querySelectorAll('[data-atmosphere-depth]') ?? [];

    if (!atmosphere || !layers.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        layers.forEach((layer) => layer.style.removeProperty('--atmosphere-shift'));
        return;
    }

    const workspace = document.querySelector('[data-workspace-scroll]');
    const source = atmosphere.dataset.atmosphereScroll === 'page' ? window : workspace;

    if (!source) {
        return;
    }

    const cap = Number(atmosphere.dataset.atmosphereCap || 1200);
    let frame = null;

    const update = () => {
        const position = source === window ? window.scrollY : source.scrollTop;
        const scroll = Math.min(Math.max(position, 0), cap);

        layers.forEach((layer) => {
            const depth = Number(layer.dataset.atmosphereDepth || 0);
            layer.style.setProperty('--atmosphere-shift', `${Math.round(scroll * depth)}px`);
        });

        frame = null;
    };

    const onScroll = () => {
        if (frame === null) {
            frame = window.requestAnimationFrame(update);
        }
    };

    source.addEventListener('scroll', onScroll, { passive: true });
    removeAtmosphereListener = () => {
        source.removeEventListener('scroll', onScroll);

        if (frame !== null) {
            window.cancelAnimationFrame(frame);
        }
    };
    update();
}

document.addEventListener('livewire:navigated', initAtmosphereParallax);
