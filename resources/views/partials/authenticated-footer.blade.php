{{--
    U-16.1 (Phase 11/15) — the authenticated app's minimal footer was
    duplicated verbatim across `layouts/app.blade.php` and
    `layouts/labs.blade.php` (a real, verified "duplicated footer" per
    the commission's own Phase 15). Extracted once here.

    Deliberately not the same component as `<x-public-footer>` — this is
    Classification C (Intentional), not drift: the public marketing
    footer's Product/Company/Resources link columns belong on marketing
    pages, not inside the authenticated workspace, which stays minimal
    per `DESIGN_LANGUAGE.md`'s "the workspace is not a dashboard to fill
    the screen" restraint. Making the two identical would clutter every
    authenticated screen with marketing navigation it doesn't need.
--}}
<footer class="border-t border-neutral-200 print:hidden">
    <div class="container-marketing mx-auto px-4 py-6 sm:px-6 lg:px-8 text-sm text-neutral-500 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <p>&copy; {{ now()->year }} SlipGuard. Structural risk analysis, not a prediction service.</p>
        <p class="text-neutral-400">SlipGuard evaluates decision risk. It does not predict outcomes.</p>
    </div>
</footer>
