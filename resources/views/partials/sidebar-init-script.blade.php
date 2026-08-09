{{--
    `PO-U24-004` — mirrors `theme-init-script.blade.php` exactly, for the
    identical reason: read the collapse preference before first paint so
    the authenticated shell never flashes expanded and then jumps to
    collapsed once JavaScript settles. No stored preference means
    expanded (the current, unchanged default) — nothing to apply.
--}}
<script>
    (function () {
        var collapsed = false;

        try {
            collapsed = localStorage.getItem('slipguard-sidebar-collapsed') === 'true';
        } catch (error) {
            // Storage can be unavailable in privacy-restricted contexts.
            // Expanded remains the safe default.
        }

        if (collapsed) {
            document.documentElement.setAttribute('data-sidebar', 'collapsed');
        }
    })();
</script>
