<?php

namespace App\Console\Commands;

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * `OP-06` (RC1 Operations Programme). Release-verification smoke test — the
 * codified version of the manual login→create-slip→analyse→view-report
 * check `I-01.2` and `PO-MVP-005`'s UX/Intelligence audit each performed by
 * hand. Exercises the real deterministic pipeline (not a mock), then rolls
 * back everything it created — safe to run against a real production
 * database as part of every deploy, never leaves synthetic data behind.
 *
 * This checks the *functional* layer (DB reachable, migrations present,
 * the analysis pipeline actually produces a persisted result). It does not
 * check the HTTP/web-server layer (routing, SSL, Nginx) — that is
 * `ops/smoke-test.sh`'s job, deliberately kept separate so a failure here
 * points at the application, not the web server in front of it.
 */
class VerifyDeployment extends Command
{
    protected $signature = 'slipguard:verify-deployment';

    protected $description = 'Post-deploy functional smoke test: DB connectivity, migrations present, and a full create-slip -> analyse -> persisted-report cycle. Rolls back everything it creates.';

    public function handle(AnalyzeBettingSlip $analyze): int
    {
        $this->info('Verifying deployment...');

        if (! $this->checkDatabaseConnection()) {
            return self::FAILURE;
        }

        if (! $this->checkMigrationsTable()) {
            return self::FAILURE;
        }

        return $this->checkAnalysisCycle($analyze) ? self::SUCCESS : self::FAILURE;
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->line('  [OK] Database connection');

            return true;
        } catch (Throwable $e) {
            $this->error('  [FAIL] Database connection: '.$e->getMessage());

            return false;
        }
    }

    private function checkMigrationsTable(): bool
    {
        if (! Schema::hasTable('migrations')) {
            $this->error('  [FAIL] migrations table does not exist — has `migrate --force` ever run?');

            return false;
        }

        $ran = DB::table('migrations')->count();

        if ($ran === 0) {
            $this->error('  [FAIL] migrations table exists but has zero rows.');

            return false;
        }

        $this->line("  [OK] Migrations table present ({$ran} migrations recorded)");

        return true;
    }

    private function checkAnalysisCycle(AnalyzeBettingSlip $analyze): bool
    {
        DB::beginTransaction();

        try {
            $user = User::factory()->create(['email' => 'deployment-verify+'.uniqid().'@slipguard.local']);

            // Deliberately not the bare factory default — BettingSlipLegFactory
            // randomises `sport` across Football/Basketball/Tennis, but the
            // deterministic engine's taxonomy is football-only today
            // (confirmed directly: this exact mismatch made the first version
            // of this command fail with availability=Unavailable). Uses the
            // same known-good, football/recognised-market shape
            // tests/Feature/Analysis/AnalyzeBettingSlipTest.php's own
            // `analysisLegAttributes()` helper establishes.
            $slip = BettingSlip::factory()->for($user)->create();
            $slip->legs()->create(['sport' => 'Football', 'competition' => 'Premier League', 'event_name' => 'Arsenal vs Chelsea', 'market_name' => 'Match Result', 'selection_name' => 'Arsenal', 'decimal_odds' => '1.90', 'display_order' => 0]);
            $slip->legs()->create(['sport' => 'Football', 'competition' => 'Premier League', 'event_name' => 'Liverpool vs Everton', 'market_name' => 'Over 2.5 Goals', 'selection_name' => 'Over 2.5', 'decimal_odds' => '1.65', 'display_order' => 1]);
            $slip->legs()->create(['sport' => 'Football', 'competition' => 'La Liga', 'event_name' => 'Real Madrid vs Barcelona', 'market_name' => 'Both Teams to Score', 'selection_name' => 'Yes', 'decimal_odds' => '1.80', 'display_order' => 2]);
            $slip->markReady();

            $this->line("  [OK] Created a real, disposable Ready slip ({$slip->legs()->count()} legs)");

            $analysis = $analyze->execute($slip->fresh('legs'));

            if (! $analysis->exists) {
                $this->error('  [FAIL] AnalyzeBettingSlip did not persist a SlipAnalysis.');
                DB::rollBack();

                return false;
            }

            if (! in_array($analysis->availability, [AnalysisAvailability::Full, AnalysisAvailability::Limited], true)) {
                $this->error("  [FAIL] Unexpected analysis availability: {$analysis->availability->value}");
                DB::rollBack();

                return false;
            }

            if (empty($analysis->factor_results)) {
                $this->error('  [FAIL] Analysis persisted with no factor_results.');
                DB::rollBack();

                return false;
            }

            $this->line("  [OK] Full cycle: slip created -> analysed -> persisted (availability: {$analysis->availability->value}, structural_score: {$analysis->structural_score})");

            DB::rollBack();
            $this->line('  [OK] Verification data rolled back — zero residue left in the database');

            $this->info('Deployment verification passed.');

            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('  [FAIL] '.$e::class.': '.$e->getMessage());

            return false;
        }
    }
}
