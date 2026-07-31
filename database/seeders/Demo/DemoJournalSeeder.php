<?php

namespace Database\Seeders\Demo;

use App\Models\JournalEntry;
use App\Models\SlipAnalysis;
use App\Models\User;

/**
 * `PO-U17.0-001` §18 — believable reflective journal entries, never
 * marketing copy, never a win/loss claim, never a guarantee. Attached to a
 * subset of the real analyses `DemoSlipSeeder` already produced through
 * the actual engine — a journal entry references genuine structural-score/
 * risk-band findings, not invented ones.
 */
class DemoJournalSeeder
{
    private const REFLECTIONS = [
        'I added another selection because the total odds looked low. The report showed it was carrying more structural risk than the other legs, so I removed it next time.',
        'I recognised I was choosing the same market repeatedly without checking whether it suited the fixture.',
        'I kept the slip smaller this weekend and used the Planner before saving it.',
        "The report didn't tell me what would happen. It showed me where my decision was structurally weakest.",
        'I ignored the main contributing factor on this one. Want to review similar selections before building another slip like it.',
        'Structural score was lower than I expected given how many legs I added — the spread across markets seems to have helped.',
        'Noticed the weakest leg was the one I added last, almost as an afterthought. Worth remembering for next time.',
        'This slip took longer to build than usual because I checked each selection against the report before adding the next one.',
        'Data quality came back limited on this one — a reminder to double check the odds I enter.',
        'Comparing this to slips from a few months ago, I\'m adding fewer selections overall and thinking more about each one.',
        'The moderate risk band made sense once I saw how concentrated the risk was in one selection.',
        'I nearly added a sixth leg out of habit. Stopped and reread the structural factors first.',
    ];

    public function run(User $user): int
    {
        // Idempotent: this demo user's own journal entries are removed and
        // rebuilt each run, same convention as `DemoSlipSeeder`.
        JournalEntry::where('user_id', $user->id)->delete();

        $analyses = SlipAnalysis::where('user_id', $user->id)
            ->orderBy('created_at')
            ->get();

        $written = 0;
        foreach ($analyses as $index => $analysis) {
            // Roughly every third analysis gets a reflection — increasing
            // in frequency toward more recent slips, matching §9's "Stage
            // Three: consistent journal usage" narrative progression.
            $shouldWrite = $index < $analyses->count() / 3
                ? $index % 5 === 0
                : $index % 2 === 0;

            if (! $shouldWrite) {
                continue;
            }

            $entry = new JournalEntry;
            $entry->user_id = $user->id;
            $entry->slip_analysis_id = $analysis->id;
            $entry->reflection = self::REFLECTIONS[$written % count(self::REFLECTIONS)];
            $entry->created_at = $analysis->created_at->addHours(random_int(2, 30));
            $entry->updated_at = $entry->created_at;
            $entry->save();
            $written++;
        }

        return $written;
    }
}
