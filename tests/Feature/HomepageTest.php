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
 */
test('PW-03: the homepage alternates atmosphere-revealing and non-flat quiet sections', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, 'public-hero-atmosphere'))->toBe(1)
        ->and(substr_count($html, 'public-section-atmosphere'))->toBe(3)
        ->and(substr_count($html, 'public-section-quiet'))->toBe(2);
});

test('U-16.2 CR-001: the closing CTA section carries its own gradient, distinguishing it from the sections above', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('bg-gradient-cta', false);
});
