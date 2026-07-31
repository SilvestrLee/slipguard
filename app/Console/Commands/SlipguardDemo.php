<?php

namespace App\Console\Commands;

use App\Models\MarketIntelligenceFixture;
use App\Models\MarketIntelligenceMarketQuote;
use App\Models\User;
use Database\Seeders\Demo\DemoUserSeeder;
use Database\Seeders\Demo\DemoWorkspaceSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * `PO-U17.0-001` §28/§29/§37 — creates or refreshes DW-01, the canonical
 * demo workspace. Production-gated by `SLIPGUARD_DEMO_ENABLED` (never
 * auto-created in production) and, matching `slipguard:make-internal-user`'s
 * own existing convention, an explicit `--force` override.
 */
#[Signature('slipguard:demo {--fresh : Delete this demo user\'s existing slips/analyses/journal entries and rebuild them} {--summary : Show record counts and configuration without seeding} {--force : Allow running in a non-local environment}')]
#[Description('Create or refresh the canonical SlipGuard demo workspace (DW-01)')]
class SlipguardDemo extends Command
{
    public function handle(): int
    {
        if ($this->option('summary')) {
            return $this->showSummary();
        }

        if (! app()->environment('local', 'testing') && ! $this->option('force')) {
            $this->error('Refusing to seed the demo workspace outside local/testing without --force.');

            return self::FAILURE;
        }

        if (app()->isProduction() && ! (bool) env('SLIPGUARD_DEMO_ENABLED', false)) {
            $this->error('SLIPGUARD_DEMO_ENABLED is not set — refusing to create the demo workspace in production.');

            return self::FAILURE;
        }

        $result = (new DemoWorkspaceSeeder)->run(fresh: (bool) $this->option('fresh'));

        $this->info("Demo user: {$result['email']}");

        if ($result['generated_password']) {
            $this->warn("Generated password (shown once): {$result['generated_password']}");
        }

        if (! $result['rebuilt']) {
            $this->comment('Demo workspace already populated — no changes made. Pass --fresh to rebuild it.');

            return self::SUCCESS;
        }

        $this->info("Betting slips seeded: {$result['slips']['count']} ({$result['slips']['oldest']} to {$result['slips']['newest']})");
        $this->info("Journal entries seeded: {$result['journal_entries']}");
        $this->info("Market Intelligence fixtures seeded: {$result['market_intelligence']['fixtures']} ({$result['market_intelligence']['quotes']} quotes)");

        if (! config('slipguard-market-intelligence.enabled')) {
            $this->comment('MARKET_WIDE_PLANNER_ENABLED is false — the Builder route is not registered. Set it in .env for this local review session to reach /market-intelligence/build.');
        }

        return self::SUCCESS;
    }

    private function showSummary(): int
    {
        $user = User::where('email', DemoUserSeeder::EMAIL)->first();

        if (! $user) {
            $this->comment('No demo workspace exists yet. Run `php artisan slipguard:demo` to create one.');

            return self::SUCCESS;
        }

        $this->info("Demo user: {$user->email} (id {$user->id})");
        $this->info('Betting slips: '.$user->bettingSlips()->count());
        $this->info('Slip analyses: '.$user->slipAnalyses()->count());
        $this->info('Journal entries: '.$user->journalEntries()->count());
        $this->info('Market Intelligence fixtures: '.MarketIntelligenceFixture::count().' ('.MarketIntelligenceMarketQuote::count().' quotes)');
        $this->comment('SLIPGUARD_DEMO_ENABLED='.(env('SLIPGUARD_DEMO_ENABLED') ? 'true' : 'false (default)'));
        $this->comment('MARKET_WIDE_PLANNER_ENABLED='.(config('slipguard-market-intelligence.enabled') ? 'true' : 'false (default)'));

        return self::SUCCESS;
    }
}
