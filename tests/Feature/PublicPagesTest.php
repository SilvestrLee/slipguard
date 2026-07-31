<?php

use App\Models\User;

/**
 * U-13.0 — Public Website Experience, Information Architecture & Premium
 * SaaS Presence. Every navigation destination is a real, independent
 * route (never a same-page anchor), all guest-accessible. Pricing,
 * Contact, Privacy, and Terms are honest stubs — no fabricated pricing
 * model or legal text (see public-coming-soon.blade.php's own comment).
 */
test('every public page is guest-accessible and renders the shared public navigation', function () {
    foreach (['home', 'analyse', 'planner.public', 'reports', 'about', 'pricing', 'contact', 'privacy', 'terms', 'faq', 'release-notes', 'labs'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSee(route('analyse'), false)
            ->assertSee(route('reports'), false)
            ->assertSee(route('planner.public'), false);
    }
});

test('the public navigation uses real routes for every destination, never a same-page anchor', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertDontSee('href="#problem"', false)
        ->assertDontSee('href="#how-it-works"', false)
        ->assertDontSee('href="#pricing"', false);
});

test('U-14.2: primary navigation is trimmed to Home, Analyse, Planner, Reports, Pricing — About and Labs moved to the footer only', function () {
    $response = $this->get('/');

    $response->assertOk();
    $html = $response->getContent();
    $nav = substr($html, strpos($html, '<nav'), strpos($html, '</nav>') - strpos($html, '<nav'));

    expect($nav)->toContain(route('analyse'))
        ->toContain(route('planner.public'))
        ->toContain(route('reports'))
        ->toContain(route('pricing'))
        ->not->toContain(route('about'))
        ->not->toContain(route('labs'));
});

test('SlipGuard Labs is public and read-only for guests, with interest actions gated to authenticated customers', function () {
    $this->get(route('labs'))
        ->assertOk()
        ->assertSee('SlipGuard Labs');
});

test('the Analyse page explains methodology, factors, and the deterministic-not-predictive distinction', function () {
    $this->get(route('analyse'))
        ->assertOk()
        ->assertSee('"Just one more leg" changes more than it feels like it should.')
        ->assertSee('How SlipGuard works')
        ->assertSee('Number of Selections')
        ->assertSee('Combined Odds')
        ->assertSee('Selection Odds')
        ->assertSee('Risk Concentration')
        ->assertSee('Market Complexity')
        ->assertSee('Which leg is doing the most damage?')
        ->assertSee('No outcome prediction — SlipGuard evaluates structural risk, not who wins.');
});

test('the Reports page shows a full sample report clearly labelled as sample data, using container-reading for the report body', function () {
    $response = $this->get(route('reports'));

    $response->assertOk()
        ->assertSee('Sample — Saturday Accumulator')
        ->assertSee('Moderate')
        ->assertSee('Main Contributing Factor')
        ->assertSee('Data quality')
        ->assertSee('Methodology')
        ->assertSee('container-reading', false);
});

test('the Planner page explains customer-sourced planning and never implies SlipGuard generates selections', function () {
    $this->get(route('planner.public'))
        ->assertOk()
        ->assertSee('You choose every selection. SlipGuard helps you see it clearly.')
        ->assertSee('Start from a slip you built')
        ->assertSee('See the weakest leg')
        ->assertSee('Compare, then export')
        ->assertSee('No generated selections — SlipGuard never builds a slip from scratch.');
});

test('the About page states the ADR-012 constitutional boundaries', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Hold customer money')
        ->assertSee('Decide for you')
        ->assertSee('Bet autonomously')
        ->assertSee('Take a side');
});

test('Pricing honestly communicates availability without inventing numbers; Contact, Privacy, and Terms remain honest stubs', function () {
    $pricingResponse = $this->get(route('pricing'))
        ->assertOk()
        ->assertSee('Free')
        ->assertSee('Professional')
        ->assertSee('Enterprise')
        ->assertSee("We haven't finalised pricing yet")
        ->assertDontSee('/month')
        ->assertDontSee('per month');

    // A literal currency amount (e.g. "$29") would be a fabricated price — distinct
    // from the JS template-literal `${rotation}` the U-14.3 logo motion legitimately
    // renders on every public page, which is not currency and must not false-positive.
    expect(preg_match('/\$\d/', $pricingResponse->getContent()))->toBe(0);

    $this->get(route('privacy'))->assertOk()->assertSee('Privacy Policy is on the way.');
    $this->get(route('terms'))->assertOk()->assertSee('Terms of Service is on the way.');
    $this->get(route('contact'))->assertOk()->assertSee('Contact is on the way.');
});

test('the public footer is a genuine product footer with Product, Company, and Resources link columns', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Product')
        ->assertSee('Company')
        ->assertSee('Resources')
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('release-notes'), false)
        ->assertDontSee('Community');
});

test('the footer Community column is dropped entirely rather than rendered empty, and no feature-request or forum destination exists anywhere', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('Feature Request')
        ->assertDontSee('Forum')
        ->assertDontSee('Community Discussion');
});

test('the FAQ and Release Notes pages render real, honest content', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('Does SlipGuard predict who wins?')
        ->assertSee('Does SlipGuard hold my money or place bets for me?');

    $this->get(route('release-notes'))
        ->assertOk()
        ->assertSee('Risk Engine')
        ->assertSee('Planner')
        ->assertSee('Why it matters');
});

/**
 * U-14.3 — Signature Motion System. The rotation is scroll-position-driven
 * (never time/CSS-loop-based), bounded to MOTION_SYSTEM.md's 15-20°
 * maximum, and disabled entirely under prefers-reduced-motion — verified
 * against the actual rendered markup rather than assumed. True client-side
 * scroll/rotation behaviour can't be exercised without a browser, which
 * isn't available in this environment — stated explicitly, per U-14.3's
 * own allowance for this case.
 */
test('the public logo motion is scroll-linked, bounded, and disables entirely under reduced motion', function () {
    $response = $this->get('/')->assertOk();
    $html = $response->getContent();

    expect($html)->toContain('reduceMotion: window.matchMedia')
        ->toContain('rotate(${rotation}deg)')
        ->toContain('maxDegrees = 18')
        ->and($html)->not->toContain('animation:')
        ->not->toContain('@keyframes');
});

/**
 * Founder direct instruction (2026-07-28) originally put scroll-linked
 * rotation on both the public site and the authenticated portal's header.
 * That was superseded once the authenticated portal moved to a fixed
 * sidebar layout: a persistently-visible sidebar reacting to main-content
 * scroll reads as jittery rather than purposeful, so the sidebar brand is
 * deliberately stable/compact instead — see GlobalShellTest's "the public
 * header retains motion while the authenticated shell uses a stable
 * compact sidebar brand", which is the current, real, tested behavior.
 * This test now only re-confirms the public-site half, which never
 * changed — the authenticated-portal half is GlobalShellTest's own job,
 * not duplicated here.
 */
test('the logo motion remains present on the public site', function () {
    $this->get('/')->assertOk()->assertSee('updateFromScroll', false);
});

test('PW-03 preserves the public atmosphere composition while adding layered capped document-scroll depth', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();
    $js = file_get_contents(resource_path('js/app.js'));
    $css = file_get_contents(resource_path('css/app.css'));

    expect($html)->toContain('data-atmosphere-scroll="page"')
        ->toContain('data-atmosphere-cap="1400"')
        ->toContain('data-atmosphere-depth="-0.028"')
        ->toContain('data-atmosphere-depth="0.040"')
        ->toContain('data-atmosphere-depth="-0.020"')
        ->toContain('data-atmosphere-depth="0.032"');

    foreach ([
        'width: 32rem; height: 32rem; top: -10rem; left: -8rem;',
        'width: 26rem; height: 26rem; top: 20rem; right: -10rem;',
        'width: 22rem; height: 22rem; bottom: -6rem; left: 15%;',
        'width: 18rem; height: 18rem; top: 55%; right: 20%;',
    ] as $canonicalLayer) {
        expect($html)->toContain($canonicalLayer);
    }

    expect($js)->toContain("atmosphere.dataset.atmosphereScroll === 'page'")
        ->toContain('window.requestAnimationFrame')
        ->toContain("prefers-reduced-motion: reduce")
        ->not->toContain('mousemove');
    expect($css)->toContain('transform: none !important');
});

test('PW-03 gives public pages an alternating atmosphere and tonal section rhythm', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    foreach ([
        route('home'),
        route('about'),
        route('analyse'),
        route('planner.public'),
        route('pricing'),
    ] as $route) {
        $html = $this->get($route)->assertOk()->getContent();

        expect($html)
            ->toContain('public-section-atmosphere')
            ->toContain('public-section-quiet');
    }

    expect($css)
        ->toContain('.public-section-atmosphere')
        ->toContain('var(--surface-public-atmosphere)')
        ->toContain('.public-hero-atmosphere')
        ->toContain('background-color: transparent')
        ->toContain('.public-section-quiet')
        ->toContain('var(--gradient-public-quiet)')
        ->toContain('border-block: 1px solid var(--public-section-edge)');
});

test('MOTION_SYSTEM.md documents the approved logo rotation mechanics', function () {
    $doc = file_get_contents(base_path('docs/05-ux/MOTION_SYSTEM.md'));

    expect($doc)->toContain('15°–20°')
        ->toContain('prefers-reduced-motion')
        ->toContain('never time-based');
});
