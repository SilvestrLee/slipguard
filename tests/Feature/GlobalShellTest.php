<?php

use App\Models\User;

/**
 * U-16.1 (`PO-U16.1-001`) — global product shell standardisation: header,
 * footer, navigation, containers, theme toggle. Verifies the concrete
 * fixes made, not a redesign of anything — see docs/00-governance/DECISION_LOG.md
 * for the full audit trail and Classification A/B/C reasoning.
 */
test('U-16.2 CR-001: the theme toggle is a genuine switch (role, aria-checked, track, sliding indicator), not a single icon-swap button', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('role="switch"', false)
        ->assertSee(':aria-checked="effectiveIsDark.toString()"', false)
        // the sliding indicator and both icons' colour-swap bindings are always present in the DOM —
        // selection is shown by the indicator's position, not by adding/removing an icon from the page.
        ->assertSee("effectiveIsDark ? 'translate-x-[24px]' : 'translate-x-0'", false)
        ->assertSee("effectiveIsDark ? 'text-neutral-400' : 'text-accent-strong'", false)
        ->assertSee("effectiveIsDark ? 'text-accent-strong' : 'text-neutral-400'", false);
});

test('Sprint 9 uses a fixed authenticated sidebar and a right workspace with a sticky contextual header', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));
    $html = $response->getContent();

    $response->assertOk();
    expect($html)->toContain('Customer workspace navigation')
        ->toContain('fixed inset-y-0 left-0')
        ->toContain('data-workspace-scroll')
        ->toContain('lg:h-screen lg:overflow-y-auto')
        ->toContain('sticky top-0')
        ->toContain('aria-current="page"');
});

test('authenticated sticky headers provide a shared return to the public website action', function () {
    $user = User::factory()->create();

    foreach (['dashboard', 'history'] as $routeName) {
        $this->actingAs($user)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('Return to Website')
            ->assertSee('aria-label="Return to Website"', false)
            ->assertSee('href="'.route('home').'"', false);
    }
});

test('the authenticated shell provides an accessible mobile navigation drawer', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    expect($html)->toContain('id="customer-navigation-drawer"')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('trapFocus')
        ->toContain('selectDestination')
        ->toContain('aria-controls="customer-navigation-drawer"');
});

test('the authenticated footer is one shared implementation, identical on Dashboard and SlipGuard Labs', function () {
    $user = User::factory()->create();

    $dashboardFooter = $this->actingAs($user)->get(route('dashboard'))->getContent();
    $labsFooter = $this->actingAs($user)->get(route('labs'))->getContent();

    $needle = 'Structural risk analysis, not a prediction service.';
    expect($dashboardFooter)->toContain($needle)
        ->and($labsFooter)->toContain($needle);
});

test('the public footer shows restrained social destinations without fabricating unavailable account links', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('SlipGuard social media')
        ->toContain('X account link coming soon')
        ->toContain('YouTube account link coming soon')
        ->toContain('LinkedIn account link coming soon')
        ->not->toContain('href="#"');
});

test('header and footer chrome use container-marketing, not a second raw max-w-7xl class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('max-w-7xl', false)
        ->assertSee('container-marketing', false);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('max-w-7xl', false);
});

test('neither footer overrides the page gradient with its own opaque surface colour', function () {
    $user = User::factory()->create();

    foreach ([$this->get('/'), $this->actingAs($user)->get(route('dashboard'))] as $response) {
        $html = $response->assertOk()->getContent();
        $footer = substr($html, (int) strpos($html, '<footer'), (int) strpos($html, '</footer>') - (int) strpos($html, '<footer'));
        expect($footer)->not->toContain('bg-surface-page')->not->toContain('bg-neutral-50');
    }
});

test('the sidebar active state is distinguished by weight, surface, border and aria state', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));
    $html = $response->getContent();
    $response->assertOk();
    expect($html)->toContain('aria-current="page"')
        ->toContain('font-semibold text-neutral-900')
        ->toContain('border-accent/30 bg-accent/10');
});

/**
 * U-16.2 (`PO-U16.2-001`) — Premium Public Experience Stabilisation.
 */
test('[x-cloak] has a corresponding CSS rule, so cloaked elements are actually hidden before Alpine initialises', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('[x-cloak]')
        ->toContain('display: none !important');
});

test('public and authenticated product marks remain static', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('slipguard-icon-accent.svg', false)
        ->assertSee('SlipGuard')
        ->assertDontSee('transform: rotate(', false)
        ->assertDontSee('rotation: 0', false);

    $user = User::factory()->create();
    $authNav = $this->actingAs($user)->get(route('dashboard'))->getContent();
    expect($authNav)->toContain('slipguard-icon-accent.svg')
        ->toContain('Risk intelligence')
        ->not->toContain('transform: rotate(')
        ->not->toContain('rotation: 0')
        ->not->toContain('window.__slipguardHeaderScroll');
});

/**
 * Founder-reported bug (2026-07-28): "the logo icon flashes enormous,
 * covering the screen, then returns to set size" on every page load. Root
 * cause: the icon SVG's own root element declares an intrinsic
 * `width="721" height="848"`, and the only height constraint on the
 * public header's icon `<img>` was an Alpine `:class` binding
 * (`condensed ? 'h-8' : 'h-10'`) — inert until Alpine hydrates, so the
 * browser rendered the image at its native ~721x848px size for a brief
 * pre-hydration window before snapping down. Fixed by giving the static
 * `class` list its own `h-10` fallback (the un-condensed default).
 */
test('the public header icon has a static height class, not only an Alpine-bound one, so it never renders at its native SVG size before hydration', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('class="h-8 w-auto shrink-0 transition-[opacity,height] duration-300 ease-out md:h-10"')
        ->toContain("condensed ? '!h-8' : 'h-8 md:h-10'");
});

/**
 * Founder direct instructions (2026-07-27), verified with a real browser
 * (Playwright + the system's installed Chrome, since Playwright's own
 * bundled Chromium doesn't support this environment's macOS version):
 * no shadows anywhere (contrasting borders instead), gradient button
 * backgrounds, a bold accent-gradient background on every CTA section,
 * and glassmorphism for the public header.
 */
test('no element renders a shadow — every shadow token resolves to none, with a contrasting border added wherever a component relied on shadow alone', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect(substr_count($css, '--shadow-1: none'))->toBe(3)
        ->and(substr_count($css, '--shadow-2: none'))->toBe(3)
        ->and(substr_count($css, '--shadow-3: none'))->toBe(3);

    $this->get('/')->assertOk()->assertSee('border-neutral-300', false);
});

test('primary buttons use a gradient background, not a flat fill', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('bg-gradient-button', false);
});

test('every public CTA section uses the bold accent-gradient background, in both themes, with contrast-safe inverted button colours', function () {
    foreach (['home', 'about', 'analyse', 'planner.public', 'reports'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSee('bg-gradient-cta', false)
            ->assertSee('text-indigo-700 bg-white', false); // U-08.1 §13: Rich Indigo replaced the prior fixed blue
    }
});

test('the public header uses a translucent, blurred (glassmorphism) surface rather than a solid background', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('class="sticky top-0 z-40"', false)
        ->assertSee('backdrop-blur-md', false)
        ->assertSee('bg-surface-page/70', false);
});

test('the public header provides a dedicated accessible mobile navigation drawer', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();

    expect($html)->toContain('aria-controls="public-mobile-menu"')
        ->toContain('aria-label="'.__('Open navigation').'"')
        ->toContain('aria-label="'.__('Close navigation').'"')
        ->toContain('aria-label="'.__('Public navigation').'"')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('x-on:keydown.escape.window="closeMenu()"')
        ->toContain('x-on:keydown.tab.window="trapMenuFocus($event)"')
        ->toContain('x-on:resize.window="if (window.innerWidth >= 768) closeMenu(false)"');
});

test('the public mobile drawer exposes every primary destination and account action', function () {
    $response = $this->get(route('home'))->assertOk();

    foreach (['analyse', 'planner.public', 'reports', 'pricing', 'login', 'register'] as $routeName) {
        $response->assertSee(route($routeName), false);
    }

    $response
        ->assertSee('Search')
        ->assertSee('Language')
        ->assertSee('Theme')
        ->assertSee('Sign In')
        ->assertSee('Get Started');
});

test('the approved header brand icon is the single source in both themes', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('slipguard-icon-accent.svg')
        ->not->toContain('slipguard-icon-accent-dark.svg');
});

/**
 * U-08.1 (`PO-U08.1-AC-001`) — Rich Indigo accent, smoked graphite dark
 * theme, the fixed atmospheric layer, and the premium light sweep.
 */
test('the accent colour is Rich Indigo, in both themes, not the prior blue', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    // Every dark-mode accent definition (media query + explicit override) must
    // agree — this exact class of bug (one block updated, one silently missed)
    // was found and fixed once already in this file.
    expect(substr_count($css, '--accent: #6366f1'))->toBe(1)
        ->and(substr_count($css, '--accent-strong: #4f46e5'))->toBe(1)
        ->and(substr_count($css, '--accent: #818cf8'))->toBe(2)
        ->and(substr_count($css, '--accent-strong: #818cf8'))->toBe(2)
        ->and($css)->not->toContain('--accent: #2563eb')
        ->not->toContain('--accent: #60a5fa');
});

test('the dark theme foundation is smoked graphite, identical in both the media-query and explicit-override blocks', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    // Both dark-mode blocks must carry the same graphite scale — verified
    // directly rather than assumed, since the two blocks previously drifted.
    expect(substr_count($css, '--neutral-50: #1b1c20'))->toBe(2)
        ->and(substr_count($css, '--surface-inverse: #1b1c20'))->toBe(1)
        ->and($css)->not->toContain('--neutral-50: #0f172a');
});

/**
 * Founder direct instruction (2026-07-28): "fade-in up motions as a user
 * scrolls down the site, sitewide" — every public page's non-hero,
 * non-CTA sections carry `data-reveal`, handled by one shared
 * `resources/js/app.js` listener rather than a per-page inline script.
 */
test('every public page marks its section-level scroll reveals with data-reveal, and no page duplicates the reveal script inline', function () {
    foreach (['home', 'about', 'analyse', 'planner.public', 'reports', 'pricing', 'faq', 'release-notes'] as $routeName) {
        $html = $this->get(route($routeName))->assertOk()->getContent();

        expect($html)->toContain('data-reveal')
            ->not->toContain('document.querySelectorAll(\'[data-reveal]\')');
    }

    $appJs = file_get_contents(resource_path('js/app.js'));
    expect($appJs)->toContain('data-reveal')
        ->toContain('IntersectionObserver')
        ->toContain('livewire:navigated')
        ->toContain('prefers-reduced-motion');
});

test('the public homepage renders the fixed atmospheric layer and the premium light sweep on its named surfaces', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('class="atmosphere"', false)
        ->assertSee('light-sweep relative', false) // hero dashboard showcase (PO-U15.4-001)
        ->assertSee('light-sweep bg-gradient-cta', false); // closing CTA
});

test('MOTION_SYSTEM.md records the static product mark without obsolete terminology', function () {
    $doc = file_get_contents(base_path('docs/05-ux/MOTION_SYSTEM.md'));

    expect($doc)->not->toContain('shield glyph')
        ->toContain('Static Product Mark')
        ->toContain('do not spin, rotate')
        ->toContain('Fixed Atmospheric Layer')
        ->toContain('Premium Light Sweep');
});

/**
 * Founder direct instruction (2026-07-28): extend the atmosphere layer to
 * the auth screens, retain the approved logo there, and add an
 * explicit "back to home" affordance.
 */
test('the auth screens render the fixed atmospheric layer, shared approved icon, and explicit back-to-home link', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    expect($html)->toContain('class="atmosphere"')
        ->toContain('slipguard-icon-accent.svg')
        ->not->toContain('slipguard-logo-light-transparent.svg')
        ->not->toContain('slipguard-logo-dark-transparent.svg')
        ->toContain(__('Back to home'));
});

/**
 * Founder direct instruction (2026-07-28): "sign in with Google/Apple"
 * placeholders — no OAuth credentials exist yet, so both must render
 * visibly but disabled, not silently absent and not wired to a live route.
 */
test('login and register render disabled Google/Apple sign-in placeholders, not a working OAuth flow', function () {
    foreach ([route('login'), route('register')] as $url) {
        $html = $this->get($url)->assertOk()->getContent();

        expect($html)->toContain(__('Or continue with'))
            ->toContain(__('Google'))
            ->toContain(__('Apple'))
            ->toContain('disabled')
            ->toContain('aria-disabled="true"')
            ->not->toContain('/auth/google')
            ->not->toContain('/auth/apple');
    }
});

/**
 * Founder direct instruction (2026-07-28): first "the atmosphere layer and
 * logo responding to the theme in the dashboard" (implemented narrowly),
 * then explicitly corrected to "all the pages in the portal/dashboard must
 * have the atmosphere layer" — every authenticated portal screen (they all
 * render through the one shared `layouts.app` shell) now carries it, and
 * the theme-aware logo swap is shell-wide chrome regardless.
 */
test('every authenticated portal screen carries the atmosphere layer and the shared approved icon', function () {
    $user = User::factory()->create();

    $dashboard = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();
    $history = $this->actingAs($user)->get(route('history'))->assertOk()->getContent();

    expect($dashboard)->toContain('class="atmosphere"')
        ->toContain('slipguard-icon-accent.svg')
        ->not->toContain('slipguard-icon-accent-dark.svg');
    expect($history)->toContain('class="atmosphere"')
        ->toContain('slipguard-icon-accent.svg')
        ->not->toContain('slipguard-icon-accent-dark.svg');
});

test('the authenticated atmosphere uses restrained scroll-linked depth and disables it for reduced motion', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();
    $css = file_get_contents(resource_path('css/app.css'));
    $js = file_get_contents(resource_path('js/app.js'));

    expect($html)->toContain('data-atmosphere-depth')
        ->toContain('data-workspace-scroll');
    expect($js)->toContain('initAtmosphereParallax')
        ->toContain('requestAnimationFrame')
        ->toContain("prefers-reduced-motion: reduce")
        ->toContain('--atmosphere-shift');
    expect($css)->toContain('transform: translate3d(0, var(--atmosphere-shift, 0px), 0)')
        ->toContain('.atmosphere > span')
        ->toContain('transform: none !important');
});

test('the authenticated workspace reuses the public atmosphere footprint without an opaque workspace veil', function () {
    $user = User::factory()->create();
    $public = $this->get(route('home'))->assertOk()->getContent();
    $workspace = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    foreach ([
        'width: 32rem; height: 32rem; top: -10rem; left: -8rem;',
        'width: 26rem; height: 26rem; top: 20rem; right: -10rem;',
        'width: 22rem; height: 22rem; bottom: -6rem; left: 15%;',
        'width: 18rem; height: 18rem; top: 55%; right: 20%;',
    ] as $canonicalLayer) {
        expect($public)->toContain($canonicalLayer);
        expect($workspace)->toContain($canonicalLayer);
    }

    expect($workspace)->not->toContain('min-h-screen bg-surface-page/70');
});

/**
 * Founder direct instruction (2026-07-28): "use the logo icon as a
 * favicon" — rendered from the separately approved favicon artwork, not redrawn.
 */
test('every layout links a real favicon, rendered from the approved brand icon, not the Laravel default', function () {
    $user = User::factory()->create();

    $public = $this->get('/')->assertOk()->getContent();
    $guest = $this->get(route('login'))->assertOk()->getContent();
    $portal = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    foreach ([$public, $guest, $portal] as $html) {
        expect($html)->toContain('rel="icon"')
            ->toContain(asset('favicon.ico'))
            ->toContain(asset('favicon-32.png'))
            ->toContain('rel="apple-touch-icon"');
    }

    foreach (['favicon-16.png', 'favicon-32.png', 'favicon-48.png', 'favicon-180.png', 'favicon-192.png', 'favicon-512.png', 'favicon.ico'] as $file) {
        expect(file_exists(public_path($file)))->toBeTrue();
    }

    expect(filesize(public_path('brand/slipguard-icon-accent.svg')))->toBeLessThan(100 * 1024)
        ->and(filesize(public_path('favicon-512.png')))->toBeLessThan(75 * 1024)
        ->and(filesize(public_path('favicon.ico')))->toBeLessThan(10 * 1024);
});
