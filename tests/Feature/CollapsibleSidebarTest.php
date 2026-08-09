<?php

use App\Models\User;

/**
 * `PO-U24-004` — Collapsible Dashboard Navigation + Glass Sticky Header.
 * Server-rendered structural coverage only; actual collapse/expand
 * behaviour, CSS-driven width/padding, persistence, and visual glass
 * treatment are verified via real browser automation (see the
 * implementation report's Browser Evidence section) — a Pest HTTP test
 * cannot execute Alpine or read computed CSS.
 */
test('the collapse control renders with both accessible state labels', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    expect($html)->toContain('Collapse navigation')
        ->toContain('Expand navigation')
        ->toContain('toggleCollapsed()')
        ->toContain('aria-expanded');
});

test('the sidebar collapse preference pre-paint script is present', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    expect($html)->toContain('slipguard-sidebar-collapsed')
        ->toContain("document.documentElement.setAttribute('data-sidebar', 'collapsed')");
});

test('the desktop sidebar width is driven by the shared CSS variable, not a fixed class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('w-[var(--sidebar-w)]', false)
        ->assertDontSee('class="fixed inset-y-0 left-0 z-40 hidden w-72', false);
});

test('the workspace content wrapper reclaims width via the same shared CSS variable', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('lg:pl-[var(--sidebar-w)]', false);
});

test('collapsed-state tooltips exist for every primary and utility nav item, hidden until collapsed', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    expect(substr_count($html, 'role="tooltip"'))->toBeGreaterThanOrEqual(9); // 7 primary + 2 utility, at minimum
});

test('the sticky header uses the restrained glassmorphism recipe already established for this pattern', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

    expect($html)->toContain('bg-surface-page/70')
        ->toContain('backdrop-blur-md')
        ->toContain('border-neutral-200/70')
        ->not->toContain('bg-surface-page/90')
        ->not->toContain('backdrop-blur-xl" print:hidden');
});

test('active navigation and route destinations are unchanged by the collapse feature', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('journal'))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee(route('journal'), false)
        ->assertSee(route('dashboard'), false)
        ->assertSee(route('settings'), false)
        ->assertSee(route('help'), false);
});
