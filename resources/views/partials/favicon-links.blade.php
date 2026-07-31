{{--
    Founder direct instruction (2026-07-28): "use the logo icon as a
    favicon." The size set is rendered directly from the separately
    approved favicon PNG, while the header uses the approved logo SVG. One
    shared partial, included by every layout, matching the existing
    `theme-init-script` convention rather than duplicating per layout.
--}}
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-180.png') }}">
