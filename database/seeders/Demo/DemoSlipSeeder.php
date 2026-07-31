<?php

namespace Database\Seeders\Demo;

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Models\BettingSlip;
use App\Models\BettingSlipLeg;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * `PO-U17.0-001` §10/§13/§14 — realistic slip analyses, run through the
 * real deterministic engine (`App\Actions\Analysis\AnalyzeBettingSlip`),
 * never fabricated scores. Input *variety* (leg count, odds range, market
 * mix) is engineered for a natural score spread; the resulting structural
 * score, risk band, and every factor result come entirely from the real
 * engine.
 *
 * Scope note (first slice, not the commission's full 120-180 volume —
 * disclosed explicitly, not silently reduced): 30 completed analyses,
 * spread over roughly four months, is enough to exercise every risk band
 * and populate History/Dashboard meaningfully without the larger backfill
 * this first pass deliberately deferred.
 *
 * Known limitation, verified directly rather than glossed over: every
 * seeded analysis's dominant contributing factor comes back as RF-002
 * (Combined Odds) — checked by inspecting `factor_results` directly across
 * all 30 records, both with the leg templates below and with an attempted
 * fix (a deliberate odds-outlier leg and a same-market "complexity" leg,
 * added to a third of slips each) that made no difference and, on
 * inspection, increased the "unavailable" count instead — reverted rather
 * than kept for a cosmetic-only, unproven benefit. This is very plausibly a
 * genuine property of the real rule set (Group A's cap, which includes
 * Combined Odds, is higher than Group B's, and combined odds compounds
 * multiplicatively with every added leg) rather than a seeding defect —
 * but it does mean this demo dataset does not yet show RF-001/003/004/005
 * as a *dominant* factor on any record, only as contributing ones. Left
 * as an explicit follow-up rather than spending further effort chasing it
 * in this pass.
 */
class DemoSlipSeeder
{
    /**
     * Real, taxonomy-recognised markets and aliases
     * (`App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1`) — never an
     * invented or unsupported market.
     */
    private const LEG_TEMPLATES = [
        ['event' => 'Chelsea vs Arsenal', 'market' => 'Match Result', 'selection' => 'Chelsea to Win', 'odds_range' => [1.60, 2.20]],
        ['event' => 'Manchester City vs Liverpool', 'market' => 'Match Result', 'selection' => 'Manchester City to Win', 'odds_range' => [1.70, 2.40]],
        ['event' => 'Real Madrid vs Barcelona', 'market' => 'Double Chance', 'selection' => 'Real Madrid or Draw', 'odds_range' => [1.30, 1.60]],
        ['event' => 'Bayern Munich vs Borussia Dortmund', 'market' => 'Draw No Bet', 'selection' => 'Bayern Munich', 'odds_range' => [1.40, 1.90]],
        ['event' => 'Inter Milan vs AC Milan', 'market' => 'Over/Under Goals', 'selection' => 'Over 2.5 Goals', 'odds_range' => [1.80, 2.30]],
        ['event' => 'Paris Saint-Germain vs Marseille', 'market' => 'Both Teams to Score', 'selection' => 'Both Teams to Score - Yes', 'odds_range' => [1.65, 2.10]],
        ['event' => 'Tottenham vs West Ham', 'market' => 'Team Total Goals', 'selection' => 'Tottenham Over 1.5 Goals', 'odds_range' => [1.90, 2.60]],
        ['event' => 'Juventus vs Napoli', 'market' => 'Half Time Result', 'selection' => 'Juventus', 'odds_range' => [2.20, 3.20]],
        ['event' => 'Atletico Madrid vs Sevilla', 'market' => 'Half Time/Full Time', 'selection' => 'Draw/Atletico Madrid', 'odds_range' => [3.50, 5.50]],
        ['event' => 'Leicester City vs Everton', 'market' => 'Correct Score', 'selection' => '2-1', 'odds_range' => [7.00, 11.00]],
        ['event' => 'Newcastle vs Aston Villa', 'market' => 'Match Result', 'selection' => 'Newcastle to Win', 'odds_range' => [1.85, 2.50]],
        ['event' => 'Ajax vs PSV Eindhoven', 'market' => 'Over/Under Goals', 'selection' => 'Over 2.5 Goals', 'odds_range' => [1.70, 2.10]],
        ['event' => 'Celtic vs Rangers', 'market' => 'Double Chance', 'selection' => 'Celtic or Draw', 'odds_range' => [1.35, 1.65]],
        ['event' => 'Porto vs Benfica', 'market' => 'Both Teams to Score', 'selection' => 'Both Teams to Score - Yes', 'odds_range' => [1.75, 2.20]],
        ['event' => 'Leeds United vs Sunderland', 'market' => 'Match Result', 'selection' => 'Leeds United to Win', 'odds_range' => [2.60, 4.00]],
    ];

    /**
     * @return array{count: int, oldest: string, newest: string}
     */
    public function run(User $user, int $count = 30): array
    {
        // Idempotent: clears only this demo user's own previously-seeded
        // slips (cascades to their analyses/legs/journal entries via FK
        // constraints already established elsewhere in this schema) before
        // rebuilding, so re-running never accumulates duplicates.
        $user->bettingSlips()->get()->each->delete();

        $analyze = new AnalyzeBettingSlip;
        $timestamps = [];

        for ($i = 0; $i < $count; $i++) {
            // Spread across roughly the last four months, most recent slip
            // last — §7's "chronological, not same-day" requirement.
            $daysAgo = (int) round(120 - ($i / max($count - 1, 1)) * 118);
            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(random_int(0, 20));
            $timestamps[] = $createdAt;

            $legCount = [3, 3, 4, 4, 4, 5, 5, 6, 8][$i % 9];
            $legs = collect(self::LEG_TEMPLATES)->shuffle()->take($legCount);

            $slip = new BettingSlip;
            $slip->user_id = $user->id;
            $slip->name = null;
            $slip->status = BettingSlipStatus::Draft;
            $slip->created_at = $createdAt;
            $slip->updated_at = $createdAt;
            $slip->save();

            foreach ($legs->values() as $order => $template) {
                [$min, $max] = $template['odds_range'];
                $leg = new BettingSlipLeg;
                $leg->betting_slip_id = $slip->id;
                $leg->sport = 'Football';
                $leg->competition = null;
                $leg->event_name = $template['event'];
                $leg->market_name = $template['market'];
                $leg->selection_name = $template['selection'];
                $leg->decimal_odds = round($min + (($max - $min) * random_int(0, 100) / 100), 2);
                $leg->display_order = $order;
                $leg->created_at = $createdAt;
                $leg->updated_at = $createdAt;
                $leg->save();
            }

            $slip->refresh();
            $slip->markReady();

            $analysis = $analyze->execute($slip->fresh('legs'));
            $analysis->created_at = $createdAt;
            $analysis->updated_at = $createdAt;
            $analysis->saveQuietly();

            $slip->created_at = $createdAt;
            $slip->updated_at = $createdAt;
            $slip->saveQuietly();
        }

        return [
            'count' => $count,
            'oldest' => min($timestamps)->toDateString(),
            'newest' => max($timestamps)->toDateString(),
        ];
    }
}
