{{--
    U-16.0 (Phase 1 — Theme Storage: "one source of truth only... no
    page-local implementation"). This exact script was previously
    duplicated verbatim across four layouts (app/public/guest/labs) — a
    real, verified case of "duplicate implementations" (Phase 13).
    Extracted once here; every layout now @includes this partial instead.

    Reads any explicit theme preference before first paint so the page
    never flashes the wrong theme (U-11.3 §7.2). No stored preference
    means "follow system preference" — the CSS token layer already
    handles that case unconditionally, unchanged from before this
    override existed.
--}}
<script>
    (function () {
        var stored = null;

        try {
            stored = localStorage.getItem('slipguard-theme');
        } catch (error) {
            // Storage can be unavailable in privacy-restricted contexts.
            // The CSS media query remains the safe system-theme fallback.
        }

        if (stored === 'light' || stored === 'dark') {
            document.documentElement.setAttribute('data-theme', stored);
        }
    })();
</script>
