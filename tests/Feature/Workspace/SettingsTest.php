<?php

use App\Models\User;

/**
 * `PO-U24-003` — Settings Completion. Replaces the `coming-soon` stub
 * previously rendered at `/settings`. Repository inspection (see the
 * directive's own Settings Truth Matrix, returned in the implementation
 * report) found exactly one genuinely exclusive, safely-exposable
 * preference — Appearance (theme), a client-side-only mechanism shared with
 * the sitewide theme switch. Name, email, password and account deletion are
 * already fully owned and implemented by `profile.blade.php` (`route('profile')`)
 * — Settings summarises the real current values and links there rather than
 * duplicating a second, competing editor (§28 "Settings vs Profile"). No
 * server-side mutation happens on this page — every assertion below traces
 * to that real, bounded scope, not an invented preferences catalogue.
 */
test('a guest is redirected away from Settings', function () {
    $this->get(route('settings'))->assertRedirect(route('login'));
});

test('an authenticated user sees the real Settings page, not the coming-soon stub', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertDontSee('Settings is on the way')
        ->assertDontSee("This part of SlipGuard hasn't been built yet");
});

test('every section from the commissioning directive is present', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('Appearance')
        ->assertSee('Account')
        ->assertSee('Security');
});

test('Appearance exposes the real Light/Dark theme control, not a fabricated third state', function () {
    $user = User::factory()->create();
    $html = $this->actingAs($user)->get(route('settings'))->assertOk()->getContent();

    expect($html)->toContain('Theme')
        ->toContain('role="radiogroup"')
        ->toContain('value="light"')
        ->toContain('value="dark"')
        ->not->toContain('value="system"');
});

test('Account shows the real current name and email, and links to Profile rather than duplicating the editor', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee('jane@example.com')
        ->assertSee(route('profile'), false);
});

test('Security links to Profile for password and account management rather than duplicating those controls', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('password')
        ->assertSee(route('profile'), false);
});

test('no fake or disabled future settings are rendered', function () {
    // Note: the authenticated shell's shared header legitimately renders its own
    // honest "more languages coming soon" language selector on every workspace
    // page (§29/§43 concerns Settings-owned filler, not that pre-existing,
    // unrelated shell chrome) — so this asserts against Settings-specific
    // invented sections rather than the word "coming soon" itself.
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertDontSee('Language & Region')
        ->assertDontSee('Notifications')
        ->assertDontSee('Delete Account');
});

test('a contextual link to Help is present, without embedding methodology content', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('Help & Methodology')
        ->assertSee(route('help'), false);
});

test('the authenticated shell shows the correct sticky page title for Settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings'))
        ->assertOk()
        ->assertSee('<h1 class="truncate text-lg font-semibold tracking-tight text-neutral-900 sm:text-xl">', false)
        ->assertSeeInOrder(['Settings'], false);
});
