<?php

use App\Models\User;

$workspaceRoutes = ['dashboard', 'profile', 'analyze', 'history', 'journal', 'help', 'settings'];

test('guests are redirected to login from every workspace route', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with($workspaceRoutes);

test('authenticated users can access every workspace route', function (string $routeName) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk();
})->with($workspaceRoutes);

test('the dashboard greets the user and offers the primary call to action', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Jane')
        ->assertSee('Analyze your first slip')
        ->assertSee('No analyses yet.');
});

test('coming soon pages identify themselves and link back to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('history'))
        ->assertOk()
        ->assertSee('History')
        ->assertSee('Back to Dashboard');
});

test('the slip index shows an empty state for a new user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee('No slips yet.');
});
