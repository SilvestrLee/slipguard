<?php

use App\Domain\MarketIntelligence\AcquireMarketEvidence;
use App\Domain\MarketIntelligence\EvidenceQuality;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use Illuminate\Support\Facades\Http;

/**
 * U-17.5 §4 — the bulk-first, per-event-reduced acquisition sequence.
 * Every fake response shape here matches what U-17.3 actually observed
 * from The Odds API — never an invented shape.
 */
function bulkOddsApiResponse(): array
{
    return [
        [
            'id' => 'fixture-1',
            'commence_time' => '2026-08-01T15:00:00Z',
            'home_team' => 'Arsenal',
            'away_team' => 'Chelsea',
            'bookmakers' => [
                [
                    'key' => 'pinnacle',
                    'title' => 'Pinnacle',
                    'markets' => [
                        [
                            'key' => 'h2h',
                            'last_update' => '2026-07-29T04:50:07Z',
                            'outcomes' => [
                                ['name' => 'Arsenal', 'price' => 1.85],
                                ['name' => 'Chelsea', 'price' => 4.5],
                                ['name' => 'Draw', 'price' => 3.6],
                            ],
                        ],
                        [
                            'key' => 'totals',
                            'last_update' => '2026-07-29T04:49:28Z',
                            'outcomes' => [
                                ['name' => 'Over', 'price' => 1.92, 'point' => 3],
                                ['name' => 'Under', 'price' => 1.9, 'point' => 3],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function eventOddsApiResponse(string $marketKey): array
{
    return [
        'id' => 'fixture-1',
        'bookmakers' => [
            [
                'key' => 'onexbet',
                'title' => '1xBet',
                'markets' => [
                    [
                        'key' => $marketKey,
                        'last_update' => '2026-07-29T04:50:07Z',
                        'outcomes' => [
                            ['name' => 'Yes', 'price' => 2.49],
                            ['name' => 'No', 'price' => 1.48],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

test('bulk-only acquisition persists real fixtures and quotes', function () {
    Http::fake([
        'api.the-odds-api.com/v4/sports/soccer_epl/odds*' => Http::response(bulkOddsApiResponse()),
    ]);

    $result = (new AcquireMarketEvidence)->execute('soccer_epl', ['h2h', 'totals']);

    expect($result)->toBe(['fixtures' => 1, 'quotes' => 2]);
    expect(MarketIntelligenceFixture::where('provider_event_id', 'fixture-1')->exists())->toBeTrue();
    expect(MarketIntelligenceMarketQuote::where('canonical_market', 'match_result')->exists())->toBeTrue();
    expect(MarketIntelligenceMarketQuote::where('canonical_market', 'total_goals')->first()->outcomes[0]['point'])->toBe(3);
    expect(MarketIntelligenceMarketQuote::first()->evidence_quality)->toBe(EvidenceQuality::Fresh);
});

test('per-event markets trigger one request per fixture, only after bulk reduction', function () {
    Http::fake([
        'api.the-odds-api.com/v4/sports/soccer_epl/odds*' => Http::response(bulkOddsApiResponse()),
        'api.the-odds-api.com/v4/sports/soccer_epl/events/fixture-1/odds*' => Http::response(eventOddsApiResponse('btts')),
    ]);

    $result = (new AcquireMarketEvidence)->execute('soccer_epl', ['h2h', 'totals', 'btts']);

    expect($result['quotes'])->toBe(3); // 2 bulk + 1 per-event
    expect(MarketIntelligenceMarketQuote::where('canonical_market', 'both_teams_to_score')->exists())->toBeTrue();

    Http::assertSentCount(2); // one bulk call, one per-event call for the single fixture
});

test('an unsupported competition is rejected before any request is made', function () {
    Http::fake();

    expect(fn () => (new AcquireMarketEvidence)->execute('soccer_bundesliga', ['h2h']))
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});

test('the request-credit budget guard refuses before issuing per-event requests', function () {
    config(['slipguard-market-intelligence.max_per_event_requests' => 0]);

    Http::fake([
        'api.the-odds-api.com/v4/sports/soccer_epl/odds*' => Http::response(bulkOddsApiResponse()),
    ]);

    expect(fn () => (new AcquireMarketEvidence)->execute('soccer_epl', ['h2h', 'btts']))
        ->toThrow(RuntimeException::class, 'exceed the configured budget');

    Http::assertSentCount(1); // only the bulk call — no per-event request was ever issued
});

test('an unrecognized provider market key is skipped, never guessed at', function () {
    $response = bulkOddsApiResponse();
    $response[0]['bookmakers'][0]['markets'][] = [
        'key' => 'team_totals',
        'last_update' => '2026-07-29T04:50:07Z',
        'outcomes' => [['name' => 'Over', 'price' => 1.5]],
    ];

    Http::fake([
        'api.the-odds-api.com/v4/sports/soccer_epl/odds*' => Http::response($response),
    ]);

    $result = (new AcquireMarketEvidence)->execute('soccer_epl', ['h2h', 'totals']);

    expect($result['quotes'])->toBe(2); // team_totals silently excluded, not a crash and not a guessed mapping
});
