<?php

namespace App\Console\Commands;

use App\Domain\MarketIntelligence\AcquireMarketEvidence;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * U-17.5 §10/§41 — the internal-only validation interface for Capability
 * B's Engineering Foundation Package, matching `slipguard:demo`'s own
 * production-refusal convention. This is not a customer-facing feature;
 * it exists so a real, authenticated acquisition run can be inspected
 * manually while `MARKET_WIDE_PLANNER_ENABLED` stays false.
 *
 * Manual verification (not part of the default test suite, since it
 * makes a real network call against the founder's own trial key):
 *   php artisan market-intelligence:validate --competition=soccer_epl --markets=h2h,totals,btts
 */
#[Signature('market-intelligence:validate {--competition=soccer_epl : Provider competition key} {--markets=h2h,totals : Comma-separated provider market keys}')]
#[Description('Internal-only: run a real Capability B evidence acquisition against The Odds API for manual inspection')]
class MarketIntelligenceValidate extends Command
{
    public function handle(AcquireMarketEvidence $acquire): int
    {
        if (! config('slipguard-market-intelligence.enabled')) {
            $this->error('MARKET_WIDE_PLANNER_ENABLED is false — refusing to run. This command is internal-only while Capability B is not authorized for public launch (U-17.4).');

            return self::FAILURE;
        }

        if (! config('services.the_odds_api.key')) {
            $this->error('THE_ODDS_API_KEY is not set.');

            return self::FAILURE;
        }

        $competition = $this->option('competition');
        $markets = array_filter(array_map('trim', explode(',', $this->option('markets'))));

        try {
            $result = $acquire->execute($competition, $markets);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Fixtures acquired: {$result['fixtures']}");
        $this->info("Market quotes acquired: {$result['quotes']}");

        return self::SUCCESS;
    }
}
