<?php

namespace Database\Seeders\Demo;

/**
 * `PO-U17.0-001` — DW-01, SlipGuard's canonical demo workspace. This first
 * slice covers: the demo user, real analysed betting slips (run through
 * the actual deterministic engine, never fabricated), and journal entries
 * reflecting on a subset of them.
 *
 * Deliberately deferred, not silently dropped — see `TASKS.md`'s own entry
 * for the full disclosure: Planner sessions/planning history, the
 * commission's full 120-180 record volume, public-site showcase
 * integration and featured-record curation, profile/preference population
 * beyond the defaults, intake-method examples, the Mandatory Specialist
 * Design Capability Invocation (only relevant once there is a public
 * showcase to design), and the full 53-section completion report.
 */
class DemoWorkspaceSeeder
{
    /**
     * @return array{email: string, generated_password: ?string, slips: ?array, journal_entries: ?int, rebuilt: bool}
     */
    public function run(bool $fresh): array
    {
        $userResult = (new DemoUserSeeder)->run();
        $user = $userResult['user'];

        // §29/§30: plain runs are idempotent by *not* touching existing
        // slip data once it exists — only `--fresh` performs the
        // destructive delete-and-rebuild. Re-running without `--fresh`
        // must never create duplicates or silently discard a demo dataset
        // mid-presentation.
        $existingSlipCount = $user->bettingSlips()->count();

        if (! $fresh && $existingSlipCount > 0) {
            return [
                'email' => $user->email,
                'generated_password' => $userResult['generated_password'],
                'slips' => null,
                'journal_entries' => null,
                'rebuilt' => false,
            ];
        }

        $slipResult = (new DemoSlipSeeder)->run($user);
        $journalCount = (new DemoJournalSeeder)->run($user);

        return [
            'email' => $user->email,
            'generated_password' => $userResult['generated_password'],
            'slips' => $slipResult,
            'journal_entries' => $journalCount,
            'rebuilt' => true,
        ];
    }
}
