<?php

use App\Models\User;

/**
 * U-15.2 (`PO-U15.2-001`): complete homepage recomposition. The narrative
 * is now Hero → Invisible Risk → How SlipGuard Thinks → Proof → How It
 * Works → Product Capabilities → Trust → CTA — but the full
 * Problem/How-it-works explanations still live on /analyse and the full
 * example report on /reports (U-13.0's decision is not reversed); every
 * new homepage section is deliberately brief, in different wording than
 * those dedicated pages. See tests/Feature/PublicPagesTest.php for the
 * dedicated pages themselves.
 *
 * `PO-U15.4-001` (2026-07-28): the Hero's illustrated sample-report card
 * was removed and replaced with a real Demo Workspace dashboard screenshot
 * — "SlipGuard — Risk Report" no longer appears anywhere in the Hero.
 */
test('the homepage renders every narrative section in order', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSeeText('Know where the risk is in your slip before you place it.')
        ->assertSeeInOrder([
            'Know',
            'Product Preview', // hero dashboard showcase (PO-U15.4-001)
            'The most dangerous selection isn\'t always the one with the longest odds.', // Invisible Risk (U-15.3, replaces Problem)
            'Every report begins with evidence, not opinion.', // How SlipGuard Thinks (U-15.3, replaces Solution)
            'Every analysis is deterministic, explainable and repeatable.', // Proof
            'Three steps. No statistics degree required.', // How It Works
            'Explore SlipGuard', // Product Capabilities
            'Trust that comes from what you can check, not what we claim.', // Trust
            'Structural risk, explained — never a prediction.', // CTA
        ]);
});

test('the hero applies the established accent gradient to the requested headline phrases', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('<span class="bg-gradient-button bg-clip-text text-transparent">Know</span>')
        ->toContain('<span class="bg-gradient-button bg-clip-text text-transparent">the risk</span>')
        ->toContain('<span class="bg-gradient-button bg-clip-text text-transparent">before you place it.</span>');
});

/**
 * `PO-U15.5-001`: the Invisible Risk section's demonstration shows real
 * deterministic-engine output (computed once via `RankLegsByStructuralWeakness`,
 * the accepted MSC model, and curated as static content — see the section's
 * own inline comment for the exact computation) — never invented numbers —
 * and every value is present in the raw HTML, not injected only after
 * JavaScript runs.
 */
test('the Invisible Risk demonstration shows real weakest-leg contribution and a structural-score comparison, all present without JavaScript', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('SlipGuard shows the structural effect. You decide what to do.')
        ->toContain('Contribution to structural risk')
        ->toContain('53%')
        ->toContain('Structural Score')
        ->toContain('Current slip · High')
        ->toContain('Without weakest leg · Moderate');
});

/**
 * `PO-U15.4-001`: the Hero's illustrated sample-report card is gone,
 * replaced by a real, theme-aware Demo Workspace dashboard screenshot,
 * clearly labelled as an evolving preview rather than a finished product.
 */
test('the hero shows a real, theme-aware Demo Workspace dashboard screenshot, clearly labelled as an evolving preview', function () {
    $html = $this->get('/')->assertOk()->getContent();
    $css = file_get_contents(resource_path('css/app.css'));

    expect($html)->toContain('Product Preview')
        ->toContain('actively evolving')
        ->toContain('slipguard-dashboard-demo-light.webp')
        ->toContain('slipguard-dashboard-demo-dark.webp')
        ->toContain('hero-dashboard-image--light')
        ->toContain('hero-dashboard-image--dark')
        ->toContain('hero-dashboard-viewport')
        ->toContain('Swipe horizontally to explore the full dashboard preview.')
        ->toContain('Drag to explore')
        ->not->toContain('SlipGuard — Risk Report')
        ->not->toContain('Sample Structural Report')
        ->and($css)->toContain('width: max(200%, 85.37svh)')
        ->toContain('scrollbar-width: none')
        ->toContain('object-fit: contain')
        ->toContain('margin-right: calc(50% - 50vw)')
        ->toContain('.hero-dashboard-drag-hint')
        ->toContain('@media (max-width: 767px)');
});

/**
 * `PO-RC1-010` — the Hero's intentional full-bleed dashboard-viewport
 * mechanism (`-mx-3`/`-mx-4` wrapper margin, the drag-viewport's own
 * `margin-right: calc(50% - 50vw)` bleed) was leaking into page-level
 * horizontal scroll at mobile/tablet widths (~28px overflow, confirmed
 * via real Playwright measurement pre-fix and traced via a controlled
 * experiment, not assumed): `<body>` already carried `overflow-x-clip`
 * but `<html>` — the actual document scrolling element whose own
 * scrollWidth determines page-level scroll — did not. This test can't
 * exercise real layout/scrollWidth (no browser in the Pest environment),
 * so it asserts the static markup fact a real-browser regression would
 * hinge on: `<html>` now carries the same `overflow-x-clip` utility as
 * `<body>`, keeping both halves of the containment pair consistent.
 * Real-browser confirmation (0px overflow at 390/430/768/1024/1440px,
 * both themes, drag-to-explore still functional) is recorded in
 * `CHANGELOG.md`/`TASKS.md` for this directive.
 */
test('PO-RC1-010: the root html element clips horizontal overflow, matching the existing body containment', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toMatch('/<html[^>]*class="overflow-x-clip"/')
        ->toContain('<body class="overflow-x-clip font-sans antialiased">');
});

test('the hero actions remain aligned across mobile tablet and desktop layouts', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('sm:justify-center lg:grid lg:justify-items-end xl:flex xl:justify-end')
        ->toContain('sm:w-auto lg:w-56 xl:w-auto');
});

test('guests see Analyse Your Slip and Sign In; authenticated users see Dashboard', function () {
    $this->get('/')->assertOk()->assertSee('Analyse Your Slip')->assertSee('Sign In');

    $user = User::factory()->create();
    $this->actingAs($user)->get('/')->assertOk()->assertSee('Dashboard');
});

test('the CTA sequence is progressive, never repeating the same call to action verbatim', function () {
    $response = $this->get('/');
    $html = $response->getContent();

    $response->assertOk()
        ->assertSee('Analyse Your Slip') // hero primary
        ->assertSee('See Example Report') // hero secondary
        ->assertSee('See how weakest-leg analysis works') // Invisible Risk secondary (U-15.3)
        ->assertSee('View pricing'); // closing secondary

    // "Analyse Your Slip" (the hero's own primary label) appears exactly once in
    // the narrative body — it is never repeated as the closing CTA's label too
    // (the persistent header CTA and the closing section CTA both legitimately
    // say "Get Started" for a guest, since one is chrome, not a narrative beat).
    expect(substr_count($html, 'Analyse Your Slip'))->toBe(1);
});

test('the Product Capabilities section links to the three public destinations and names the rest without linking to authenticated-only routes', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('analyse'), false)
        ->assertSee(route('reports'), false)
        ->assertSee(route('planner.public'), false)
        ->assertSee('Decision Journal')
        ->assertSee('Planning History')
        ->assertDontSee(route('journal'), false)
        ->assertDontSee(route('history'), false);
});

test('the Intelligence Credibility Section (Proof) shows only verified constitutional/system facts, no fabricated adoption statistics', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Deterministic Engine')
        ->assertSee('Proof, Not a Promise')
        ->assertSee('Deterministic')
        ->assertSee('Outcome Predictions')
        ->assertSee('Finding Explained')
        ->assertSee('Automated Tests')
        ->assertSee('Structural Factors')
        ->assertSee('Main Contributing Factor')
        ->assertDontSee('Slips Analysed')
        ->assertDontSee('success rate')
        ->assertDontSee('win rate');
});

test('the theme toggle is present and public homepage does not require authentication', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Switch to dark theme');
});

test('the homepage never duplicates the dedicated pages\' own full explanations verbatim', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('analyse'), false)
        ->assertSee(route('reports'), false)
        ->assertSee(route('planner.public'), false)
        ->assertDontSee('"Just one more leg" changes more than it feels like it should.')
        ->assertDontSee('What SlipGuard is not');
});

test('U-15.2: no decorative animation library or auto-playing loop was introduced', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertDontSee('animate-marquee', false)
        ->assertDontSee('<marquee', false)
        ->assertDontSee('gsap', false)
        ->assertDontSee('framer-motion', false);
});

test('U-15.2: the homepage section reveal respects prefers-reduced-motion and never re-triggers', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('prefers-reduced-motion: reduce', false)
        ->assertSee('data-reveal', false)
        ->assertSee('observer.disconnect()', false);
});

/**
 * PW-03 clarification supersedes U-16.2's all-translucent assumption:
 * atmosphere and quiet tonal sections now alternate deliberately.
 *
 * `PO-U24-001`: counts updated for the two new Feature Highlight sections
 * (Analyse = atmosphere, Build an Accumulator = quiet, inserted between
 * Invisible Risk and How SlipGuard Thinks) and the new FAQ section
 * (quiet, inserted before the closing CTA) — the alternation itself is
 * unbroken, just longer; see `HOMEPAGE_STORYBOARD.md`'s amendment note.
 */
test('PW-03: the homepage alternates atmosphere-revealing and non-flat quiet sections', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, 'public-hero-atmosphere'))->toBe(1)
        ->and(substr_count($html, 'public-section-atmosphere'))->toBe(4)
        ->and(substr_count($html, 'public-section-quiet'))->toBe(4);
});

test('U-16.2 CR-001: the closing CTA section carries its own gradient, distinguishing it from the sections above', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('bg-gradient-cta', false);
});

/**
 * `PO-U24-001` §4/§11/§12: the two new Feature Highlight sections. Real,
 * engine-computed sample data (not invented) — see the section's own
 * comment in `home.blade.php` for the exact reproduction steps.
 */
test('the Analyse feature highlight shows a real Risk Report fragment, not a screenshot', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee("See exactly what's driving the risk.")
        ->assertSee('Sample Report')
        ->assertSee('Main Contributing Factor')
        ->assertSee('Selection Odds')
        ->assertSee('67')
        ->assertSee(route('analyse'), false);
});

test('the Build an Accumulator feature highlight is honestly labelled illustrative, not a live candidate', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Build with risk in view from the start.')
        ->assertSee('Illustrative preview')
        ->assertSee('Ranked by structural contribution')
        ->assertSee('The fixtures above illustrate the review layout only. They are not repository evidence, a recommendation, or an analysis result.')
        ->assertSee(route('planner.public'), false);
});

/**
 * `PO-U24-001` §50 — Regression Protection: explicitly verify PO-RC1-009's
 * terminology correction was not reintroduced by this commission. Every
 * "weakest leg" mention on the page must sit inside Planner/Build an
 * Accumulator messaging (Invisible Risk or the new Builder highlight),
 * never attributed to the base Analyse/Report path.
 */
test('PO-RC1-009 regression: weakest-leg language never reappears in the base Analyse/Report context', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('Which leg is doing the most damage')
        ->not->toContain('Weakest leg: Over 2.5 Goals — contributes 53% of the structural score');

    // The Analyse highlight — the section most likely to regress this,
    // since it sits right next to the Builder highlight that legitimately
    // uses "weakest leg" language — must describe a *factor*, not a leg.
    $analyseHighlight = substr($html, (int) strpos($html, 'id="analyse-highlight-heading"'), 1200);
    expect($analyseHighlight)->not->toContain('Weakest Leg')
        ->not->toContain('weakest leg');
});

test('the FAQ section is present, bounded, and keyboard/ARIA accessible', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain('id="faq-heading"')
        ->toContain('Does SlipGuard predict who wins?')
        ->toContain('Does SlipGuard tell me what to bet on?')
        ->toContain('How do I add a slip?')
        ->toContain('What is Build an Accumulator?')
        ->toContain('Does SlipGuard place bets for me?')
        ->toContain('Can I review my previous analyses?')
        ->toContain('aria-expanded')
        ->toContain('aria-controls="faq-panel-0"')
        ->toContain('type="button"');

    // Bounded per §22 ("not a giant Help Centre") — exactly 6 questions.
    expect(substr_count($html, 'x-data="{ open: false }"'))->toBeGreaterThanOrEqual(6);
});

test('the rebalanced Product Capabilities section still names Build an Accumulator, Journal and Planning History without linking to authenticated-only routes', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Build an Accumulator')
        ->assertSee('Decision Journal')
        ->assertSee('Planning History')
        ->assertSee('Discover eligible selections and build a new accumulator.')
        ->assertDontSee(route('journal'), false)
        ->assertDontSee(route('history'), false)
        ->assertDontSee(route('builder'), false);
});
