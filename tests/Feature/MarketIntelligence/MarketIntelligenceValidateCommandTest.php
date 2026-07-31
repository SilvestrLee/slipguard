<?php

use Illuminate\Support\Facades\Http;

/**
 * U-17.5 §10 — the internal-only validation command must refuse to run
 * while the feature is disabled, matching `slipguard:demo`'s own
 * production-refusal convention.
 */
test('the command refuses to run while the feature flag is disabled', function () {
    config(['slipguard-market-intelligence.enabled' => false]);

    $this->artisan('market-intelligence:validate')
        ->assertFailed()
        ->expectsOutputToContain('MARKET_WIDE_PLANNER_ENABLED is false');

    Http::fake();
    Http::assertNothingSent();
});

test('the command refuses to run without a configured provider key', function () {
    config([
        'slipguard-market-intelligence.enabled' => true,
        'services.the_odds_api.key' => null,
    ]);

    $this->artisan('market-intelligence:validate')
        ->assertFailed()
        ->expectsOutputToContain('THE_ODDS_API_KEY is not set');
});

test('the command runs a real acquisition and reports counts when enabled', function () {
    config([
        'slipguard-market-intelligence.enabled' => true,
        'services.the_odds_api.key' => 'test-key',
    ]);

    Http::fake([
        'api.the-odds-api.com/*' => Http::response([
            [
                'id' => 'fixture-1',
                'commence_time' => '2026-08-01T15:00:00Z',
                'home_team' => 'Arsenal',
                'away_team' => 'Chelsea',
                'bookmakers' => [
                    [
                        'key' => 'pinnacle',
                        'markets' => [
                            ['key' => 'h2h', 'outcomes' => [['name' => 'Arsenal', 'price' => 1.85]]],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $this->artisan('market-intelligence:validate', ['--markets' => 'h2h'])
        ->assertSuccessful()
        ->expectsOutputToContain('Fixtures acquired: 1')
        ->expectsOutputToContain('Market quotes acquired: 1');
});
