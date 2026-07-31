<?php

namespace App\Actions\BettingSlip;

use App\Domain\BettingSlip\Intake\ParseSlipText;
use App\Models\BettingSlip;
use App\Models\User;

/**
 * Bounded-scope intake (2026-07-28) — shared by Paste Text and PDF Upload:
 * both hand raw text to the same deterministic `ParseSlipText`, then
 * converge on this one action to persist a Draft slip via the existing
 * `SaveBettingSlip` action, exactly like manual entry does. No separate
 * analysis pipeline — the caller redirects to `analyze.edit`, the same
 * Builder screen every other intake method already uses for review and
 * confirmation before the slip can be marked Ready.
 */
class CreateDraftSlipFromParsedText
{
    public function __construct(
        private readonly ParseSlipText $parser = new ParseSlipText,
        private readonly SaveBettingSlip $saveBettingSlip = new SaveBettingSlip,
    ) {}

    public function execute(User $user, string $rawText): BettingSlip
    {
        $legs = array_map(
            fn ($leg) => $leg->toLegAttributes(),
            $this->parser->parse($rawText),
        );

        return $this->saveBettingSlip->execute($user, null, ['name' => null], $legs);
    }
}
