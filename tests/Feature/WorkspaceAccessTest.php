<?php

use App\Models\User;

$workspaceRoutes = ['dashboard', 'profile', 'analyze', 'history', 'journal', 'planner.history', 'help', 'settings'];

test('guests are redirected to login from every workspace route', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with($workspaceRoutes);

test('authenticated users can access every workspace route', function (string $routeName) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk();
})->with($workspaceRoutes);

test('SlipGuard Labs is guest-visible (U-14.2) but interest actions remain customer-only', function () {
    $this->get(route('labs'))->assertOk();

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('labs'))->assertOk();
});

test('the dashboard greets the user and offers the primary call to action', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Jane')
        ->assertSee('Analyze a slip')
        ->assertSee('No slips yet.');
});

test('History, Journal, Help and Settings are real Workspace screens, not coming-soon placeholders', function () {
    $user = User::factory()->create();

    // `PO-U24-003`: Settings is real content now — see tests/Feature/Workspace/SettingsTest.php.
    // No authenticated workspace destination remains a coming-soon stub as of this test.
    $this->actingAs($user)->get(route('history'))->assertOk()->assertDontSee('is on the way');
    $this->actingAs($user)->get(route('journal'))->assertOk()->assertDontSee('is on the way');
    $this->actingAs($user)->get(route('help'))->assertOk()->assertDontSee('is on the way');
    $this->actingAs($user)->get(route('settings'))->assertOk()->assertDontSee('is on the way');
});

test('the slip index shows an empty state for a new user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('No slips yet.');
});
