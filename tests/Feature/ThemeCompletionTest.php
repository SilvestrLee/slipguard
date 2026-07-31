<?php

use App\Models\JournalEntry;
use App\Models\SlipAnalysis;
use App\Models\User;

/**
 * U-11.5: extends the light/dark theme system (U-11.3) to the remaining
 * authenticated screens that previously used hardcoded `gray-*`/`white`
 * classes, which don't respond to the manual theme toggle or the
 * automatic dark-mode CSS. Verifies the migration server-side (no
 * hardcoded class survives on the migrated screens' rendered output);
 * true client-side repaint still can't be verified without a browser,
 * which isn't available in this environment — the same limitation
 * already stated for U-11.3.
 */
test('the betting slip builder no longer renders hardcoded gray or white classes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analyze.create'));

    $response->assertOk()
        ->assertDontSee('bg-gray-', false)
        ->assertDontSee('text-gray-', false)
        ->assertDontSee('border-gray-', false)
        ->assertDontSee('bg-white', false);
});

test('the betting slip index no longer renders hardcoded gray or white classes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('analyze'));

    $response->assertOk()
        ->assertDontSee('bg-gray-', false)
        ->assertDontSee('text-gray-', false)
        ->assertDontSee('border-gray-', false)
        ->assertDontSee('bg-white', false);
});

test('History, Journal, and Planning History no longer render hardcoded gray or white classes', function () {
    $user = User::factory()->create();

    foreach (['history', 'journal', 'planner.history'] as $routeName) {
        $this->actingAs($user)->get(route($routeName))
            ->assertOk()
            ->assertDontSee('bg-gray-', false)
            ->assertDontSee('text-gray-', false)
            ->assertDontSee('border-gray-', false)
            ->assertDontSee('bg-white', false);
    }
});

test('the journal entry form no longer renders hardcoded gray, white, or indigo classes', function () {
    $user = User::factory()->create();
    $analysis = SlipAnalysis::factory()->for($user)->create();
    $entry = JournalEntry::factory()->for($user)->create(['slip_analysis_id' => $analysis->id]);

    $this->actingAs($user)->get(route('journal.create'))
        ->assertOk()
        ->assertDontSee('bg-gray-', false)
        ->assertDontSee('text-gray-', false)
        ->assertDontSee('indigo-', false);

    $this->actingAs($user)->get(route('journal.edit', $entry))
        ->assertOk()
        ->assertDontSee('bg-gray-', false)
        ->assertDontSee('text-gray-', false)
        ->assertDontSee('indigo-', false);
});

test('the profile page and its three forms no longer render hardcoded gray, white, or indigo classes', function () {
    // Excludes bg-gray- specifically: x-modal's own backdrop scrim
    // (`bg-gray-500 opacity-75`) is a deliberate, theme-independent
    // overlay left unchanged — see components/modal.blade.php.
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile'))
        ->assertOk()
        ->assertDontSee('text-gray-', false)
        ->assertDontSee('border-gray-', false)
        ->assertDontSee('bg-white', false)
        ->assertDontSee('indigo-', false);
});

test('the login, register, and password-reset request pages no longer render hardcoded gray or indigo classes', function () {
    foreach (['login', 'register', 'password.request'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertDontSee('text-gray-', false)
            ->assertDontSee('indigo-', false);
    }
});

test('the shared navigation and dropdown components no longer render hardcoded gray or white classes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('bg-gray-', false)
        ->assertDontSee('text-gray-', false)
        ->assertDontSee('bg-white', false);
});
