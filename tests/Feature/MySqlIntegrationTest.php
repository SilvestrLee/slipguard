<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Models\BettingSlip;
use App\Models\BettingSlipLeg;
use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use App\Models\PlannerRegenerationEvent;
use App\Models\PlannerSession;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * `I-01.2` (`PO-I01.2-001`) — MySQL-specific integration coverage the
 * standard SQLite-in-memory suite cannot exercise: MySQL enforces named
 * unique/foreign-key constraints and native JSON validation at the
 * connection level, which SQLite either doesn't enforce identically or
 * doesn't need identifier-length workarounds for in the first place.
 *
 * Deliberately excluded from the default `vendor/bin/pest` run — every
 * test here connects directly against whatever `DB_CONNECTION` the active
 * `.env` specifies, so it silently no-ops everywhere except a real MySQL
 * environment. Run explicitly:
 *
 *   php artisan test tests/Feature/MySqlIntegrationTest.php
 *
 * against a MySQL connection (ideally the disposable verification
 * database, never a database whose data matters) — never mixed into CI's
 * default SQLite pass, per this commission's own "do not silently modify
 * the entire test suite to MySQL" boundary.
 */
beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->markTestSkipped('MySQL integration coverage only runs against a real mysql connection.');
    }
});

test('the planner regeneration composite unique constraint rejects a duplicate sequence number', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $session = PlannerSession::create([
        'user_id' => $user->id,
        'status' => 'draft',
        'source_betting_slip_id' => $slip->id,
        'exported_betting_slip_id' => null,
    ]);

    $session->regenerationEvents()->create([
        'sequence_number' => 1,
        'availability' => 'full',
        'structural_score' => 40,
        'risk_band' => 'moderate',
        'attributions' => ['legs' => []],
    ]);

    expect(fn () => $session->regenerationEvents()->create([
        'sequence_number' => 1,
        'availability' => 'full',
        'structural_score' => 41,
        'risk_band' => 'moderate',
        'attributions' => ['legs' => []],
    ]))->toThrow(QueryException::class, 'planner_regen_session_sequence_unique');
});

test('deleting a planner session cascades to its regeneration events', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create();
    $session = PlannerSession::create([
        'user_id' => $user->id,
        'status' => 'draft',
        'source_betting_slip_id' => $slip->id,
        'exported_betting_slip_id' => null,
    ]);
    $event = $session->regenerationEvents()->create([
        'sequence_number' => 1,
        'availability' => 'full',
        'structural_score' => 40,
        'risk_band' => 'moderate',
        'attributions' => ['legs' => []],
    ]);

    $session->delete();

    expect(PlannerRegenerationEvent::find($event->id))->toBeNull();
});

test('the market intelligence quote foreign key rejects a nonexistent fixture', function () {
    expect(fn () => DB::table('market_intelligence_market_quotes')->insert([
        'market_intelligence_fixture_id' => 999999999,
        'canonical_market' => 'match_result',
        'provider_market_key' => 'x',
        'bookmaker_key' => 'x',
        'outcomes' => json_encode(['home' => 1.5]),
        'retrieved_at' => now(),
        'cache_expires_at' => now()->addHour(),
        'evidence_quality' => 'fresh',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class, 'mi_quotes_fixture_fk');
});

test('deleting a market intelligence fixture cascades to its quotes', function () {
    $fixture = MarketIntelligenceFixture::create([
        'provider_event_id' => 'test-'.uniqid(),
        'competition_key' => 'test-league',
        'home_team' => 'Home',
        'away_team' => 'Away',
        'commence_time' => now()->addDay(),
        'retrieved_at' => now(),
        'cache_expires_at' => now()->addHour(),
    ]);
    $quote = MarketIntelligenceMarketQuote::create([
        'market_intelligence_fixture_id' => $fixture->id,
        'canonical_market' => 'match_result',
        'provider_market_key' => 'x',
        'bookmaker_key' => 'x',
        'outcomes' => ['home' => 1.5, 'draw' => 3.2, 'away' => 5.0],
        'retrieved_at' => now(),
        'cache_expires_at' => now()->addHour(),
        'evidence_quality' => 'fresh',
    ]);

    $fixture->delete();

    expect(MarketIntelligenceMarketQuote::find($quote->id))->toBeNull();
});

test('market intelligence quote outcomes round-trip as JSON and reject invalid JSON', function () {
    $fixture = MarketIntelligenceFixture::create([
        'provider_event_id' => 'test-'.uniqid(),
        'competition_key' => 'test-league',
        'home_team' => 'Home',
        'away_team' => 'Away',
        'commence_time' => now()->addDay(),
        'retrieved_at' => now(),
        'cache_expires_at' => now()->addHour(),
    ]);

    $quote = MarketIntelligenceMarketQuote::create([
        'market_intelligence_fixture_id' => $fixture->id,
        'canonical_market' => 'match_result',
        'provider_market_key' => 'x',
        'bookmaker_key' => 'x',
        'outcomes' => ['home' => 1.91, 'draw' => 3.40, 'away' => 4.20],
        'retrieved_at' => now(),
        'cache_expires_at' => now()->addHour(),
        'evidence_quality' => 'fresh',
    ]);

    expect($quote->fresh()->outcomes)->toBeArray()
        ->and($quote->fresh()->outcomes['home'])->toBe(1.91);

    expect(fn () => DB::statement(
        'INSERT INTO market_intelligence_market_quotes
            (market_intelligence_fixture_id, canonical_market, provider_market_key, bookmaker_key, outcomes, retrieved_at, cache_expires_at, evidence_quality, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, NOW(), NOW())',
        [$fixture->id, 'match_result', 'x', 'x', 'not-valid-json', 'fresh']
    ))->toThrow(QueryException::class);
});

test('boolean columns store as tinyint(1) but cast to real PHP booleans through Eloquent', function () {
    $user = User::factory()->create(['is_demo' => true, 'is_internal' => true]);

    $raw = DB::table('users')->where('id', $user->id)->first();
    expect((int) $raw->is_demo)->toBe(1)
        ->and((int) $raw->is_internal)->toBe(1);

    expect($user->fresh()->is_demo)->toBeTrue()
        ->and($user->fresh()->is_internal)->toBeTrue();
});

test('a real analysis runs end to end against MySQL and produces a persisted result', function () {
    $user = User::factory()->create();
    $slip = new BettingSlip;
    $slip->user_id = $user->id;
    $slip->status = BettingSlipStatus::Draft;
    $slip->save();

    $leg = new BettingSlipLeg;
    $leg->betting_slip_id = $slip->id;
    $leg->sport = 'Football';
    $leg->event_name = 'MySQL Integration Test';
    $leg->market_name = 'Match Result';
    $leg->selection_name = 'Home to Win';
    $leg->decimal_odds = 1.85;
    $leg->display_order = 0;
    $leg->save();

    $slip->refresh();
    $slip->markReady();

    $analysis = (new AnalyzeBettingSlip)->execute($slip->fresh('legs'));

    expect($analysis->exists)->toBeTrue()
        ->and($analysis->structural_score)->toBeInt()
        ->and($analysis->risk_band)->not->toBeNull();
});
