<?php

use App\Models\User;

test('the mobile drawer exposes its accessibility contract', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="customer-navigation-drawer"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('aria-label="Customer navigation"', false)
        ->assertSee('aria-controls="customer-navigation-drawer"', false)
        ->assertSee('Close navigation')
        ->assertSee('Open navigation');
});

test('the mobile drawer surfaces every real primary and utility destination, with no invented ones', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder([
        'Dashboard',
        'Analyse Slip',
        'Build an Accumulator',
        'Analysis History',
        'Journal',
        'Planning History',
        'SlipGuard Labs',
        'Help & methodology',
        'Settings',
        'Log out',
    ]);

    $response->assertDontSee('How SlipGuard Works');
    $response->assertDontSee('Responsible Use');
});

test('the desktop sidebar is a distinct, always-present element from the mobile drawer', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        // The fixed desktop sidebar (lg:flex, hidden below lg) is a
        // structurally separate element from the mobile drawer (lg:hidden) —
        // both render in the response, CSS decides which is visible.
        // `PO-U24-004`: fixed w-72 replaced by the collapsible rail's
        // CSS-variable width (see CollapsibleSidebarTest for that behaviour).
        ->assertSee('hidden w-[var(--sidebar-w)] flex-col border-r border-neutral-200 bg-surface-card/95 backdrop-blur-xl transition-[width] duration-standard ease-in-out lg:flex', false)
        ->assertSee('lg:hidden', false);
});
