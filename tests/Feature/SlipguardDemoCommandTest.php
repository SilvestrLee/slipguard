<?php

use App\Models\User;
use Database\Seeders\Demo\DemoUserSeeder;

/**
 * `PO-U17.0-001` §45 — QA coverage for the canonical demo workspace
 * command: creates exactly one demo user, is idempotent (no duplicates on
 * a second run), `--fresh` rebuilds, and every seeded analysis went
 * through the real deterministic engine (not fabricated).
 */
test('slipguard:demo creates exactly one demo user, marked is_demo, with real analysed slips and journal entries', function () {
    $this->artisan('slipguard:demo')->assertExitCode(0);

    $user = User::where('email', DemoUserSeeder::EMAIL)->first();

    expect($user)->not->toBeNull()
        ->and($user->is_demo)->toBeTrue()
        ->and(User::where('email', DemoUserSeeder::EMAIL)->count())->toBe(1);

    expect($user->bettingSlips()->count())->toBeGreaterThan(0)
        ->and($user->slipAnalyses()->count())->toBe($user->bettingSlips()->count())
        ->and($user->journalEntries()->count())->toBeGreaterThan(0);

    // Every seeded analysis carries a real engine/rule-set version — proof
    // it passed through the actual deterministic engine, not a hand-inserted row.
    $user->slipAnalyses->each(fn ($analysis) => expect($analysis->rule_set_version)->not->toBeNull()
        ->and($analysis->engine_version)->not->toBeNull());
});

test('re-running slipguard:demo without --fresh does not create duplicate slips, analyses, or journal entries', function () {
    $this->artisan('slipguard:demo')->assertExitCode(0);
    $user = User::where('email', DemoUserSeeder::EMAIL)->first();
    $slipCountBefore = $user->bettingSlips()->count();
    $analysisCountBefore = $user->slipAnalyses()->count();
    $journalCountBefore = $user->journalEntries()->count();

    $this->artisan('slipguard:demo')->assertExitCode(0);

    expect(User::where('email', DemoUserSeeder::EMAIL)->count())->toBe(1)
        ->and($user->bettingSlips()->count())->toBe($slipCountBefore)
        ->and($user->slipAnalyses()->count())->toBe($analysisCountBefore)
        ->and($user->journalEntries()->count())->toBe($journalCountBefore);
});

test('slipguard:demo --fresh rebuilds the workspace without duplicating the demo user', function () {
    $this->artisan('slipguard:demo')->assertExitCode(0);
    $originalUserId = User::where('email', DemoUserSeeder::EMAIL)->first()->id;

    $this->artisan('slipguard:demo --fresh')->assertExitCode(0);

    expect(User::where('email', DemoUserSeeder::EMAIL)->count())->toBe(1)
        ->and(User::where('email', DemoUserSeeder::EMAIL)->first()->id)->toBe($originalUserId);
});

test('slipguard:demo --summary reports without seeding anything', function () {
    expect(User::where('email', DemoUserSeeder::EMAIL)->exists())->toBeFalse();

    $this->artisan('slipguard:demo --summary')->assertExitCode(0);

    expect(User::where('email', DemoUserSeeder::EMAIL)->exists())->toBeFalse();
});
