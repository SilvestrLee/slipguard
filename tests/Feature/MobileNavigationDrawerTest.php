<?php

use App\Models\User;

test('the mobile drawer exposes its accessibility contract', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="mobile-drawer"', false)
        ->assertSee('role="navigation"', false)
        ->assertSee('aria-label="Main menu"', false)
        ->assertSee('aria-controls="mobile-drawer"', false)
        ->assertSee('Close menu')
        ->assertSee('Toggle navigation');
});

test('the mobile drawer uses Group 1/2/3 exactly, with no invented destinations', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder([
        'Dashboard',
        'Analyze Slip',
        'History',
        'Journal',
        'Profile',
        'Settings',
        'Log Out',
        'Help',
        'Analyse a slip',
    ]);

    // OI-07: illustrative-only destinations from the source addendum stay excluded.
    $response->assertDontSee('How SlipGuard Works');
    $response->assertDontSee('Responsible Use');
});

test('the desktop navigation bar is unchanged by the mobile drawer', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        // The pre-existing desktop nav container (§45 — desktop retains the
        // full horizontal bar, the drawer pattern is never used there).
        ->assertSee('hidden space-x-8 sm:-my-px sm:ms-10 sm:flex', false)
        ->assertSee('hidden sm:flex sm:items-center sm:ms-6 sm:gap-2', false);
});
