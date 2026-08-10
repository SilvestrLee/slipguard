<?php

use App\Domain\Risk\Normalization\NormalizedBettingSlip;
use App\Domain\Risk\Normalization\NormalizedBettingSlipLeg;
use App\Domain\Risk\Normalization\NormalizedMarket;
use App\Domain\Risk\Normalization\NormalizedSport;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Domain\Risk\Taxonomy\MarketComplexity;
use App\Domain\Risk\Taxonomy\MarketFamily;
use App\Domain\Risk\Taxonomy\NormalizationStatus;
use Tests\Support\GuardsMySqlTestDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

/*
 * `AO-D1` (`PO-RC1-013`) — `GuardsMySqlTestDatabase` replaces the plain
 * `RefreshDatabase` binding here, directory-wide, rather than being
 * layered on a single test file. It is a transparent superset: for every
 * connection except `mysql` (the default sqlite in-memory suite, for
 * every Feature test) it behaves exactly like `RefreshDatabase` always
 * did. The one added check only engages when the resolved connection
 * driver is `mysql` — see `tests/Support/GuardsMySqlTestDatabase.php` for
 * why the check must live at this exact binding point (not a per-test
 * `beforeEach()`) and `tests/Support/MySqlTestDatabaseGuard.php` for the
 * permitted/rejected rule itself. This also means any *future* destructive
 * MySQL-integration test file is protected automatically, not just
 * `tests/Feature/MySqlIntegrationTest.php`.
 */
pest()->extend(TestCase::class)
    ->use(GuardsMySqlTestDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Risk Engine test helpers — build NormalizedBettingSlip fixtures directly,
 * without going through the database or the football alias matcher. Used
 * by tests/Unit/Risk/Engine and tests/Unit/Risk/Factors, which exercise
 * the engine's own mathematics (Rule Set 2026.1), not normalization
 * (already covered separately in tests/Unit/Risk and tests/Feature/Risk).
 */
function footballLeg(int $id, int $order, string $odds, string $complexity = 'simple', NormalizationStatus $marketStatus = NormalizationStatus::Complete): NormalizedBettingSlipLeg
{
    $sport = new NormalizedSport('football', NormalizationStatus::Complete, 'Football');

    $market = new NormalizedMarket(
        marketCode: $complexity === 'unknown' ? null : 'football.match_result.1x2',
        marketFamily: $complexity === 'unknown' ? null : MarketFamily::MatchResult,
        complexity: MarketComplexity::from($complexity),
        status: $marketStatus,
        selectionFacts: [],
        rawMarketInput: 'Match Result',
        rawSelectionInput: 'Home',
        taxonomyVersion: FootballMarketTaxonomyV1::VERSION,
    );

    return new NormalizedBettingSlipLeg($id, $order, $sport, $market, $odds);
}

function unsupportedSportLeg(int $id, int $order, string $odds): NormalizedBettingSlipLeg
{
    $sport = new NormalizedSport(null, NormalizationStatus::Unsupported, 'Tennis');
    $market = NormalizedMarket::notClassifiedForSport('Match Winner', 'Player A', NormalizationStatus::Unsupported, FootballMarketTaxonomyV1::VERSION);

    return new NormalizedBettingSlipLeg($id, $order, $sport, $market, $odds);
}

/**
 * @param  array<int, NormalizedBettingSlipLeg>  $legs
 */
function normalizedSlip(array $legs, int $bettingSlipId = 1): NormalizedBettingSlip
{
    return new NormalizedBettingSlip($bettingSlipId, FootballMarketTaxonomyV1::VERSION, $legs);
}

/**
 * @param  array<int, array{0: string, 1: string}>  $oddsAndComplexity  [[odds, complexity], ...]
 */
function slipOf(array $oddsAndComplexity): NormalizedBettingSlip
{
    $legs = [];
    foreach ($oddsAndComplexity as $index => [$odds, $complexity]) {
        $legs[] = footballLeg($index + 1, $index, $odds, $complexity);
    }

    return normalizedSlip($legs);
}
