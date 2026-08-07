<?php

namespace App\Actions\AccumulatorConversation;

use App\Actions\BettingSlip\SaveBettingSlip;
use App\Domain\AccumulatorConversation\ExplicitSelectionDraft;
use App\Models\BettingSlip;
use App\Models\User;

/**
 * `PO-U23-001` Decision 4, Request Class B — mirrors
 * `CreateDraftSlipFromParsedText` exactly (same convergence point,
 * `SaveBettingSlip`, same one-leg Draft shape) rather than inventing a
 * second slip-creation path. The caller redirects to `analyze.edit`, the
 * same Builder every other intake method already uses for review and
 * confirmation before the slip can be marked Ready.
 */
class CreateDraftFromExplicitSelection
{
    public function __construct(
        private readonly SaveBettingSlip $saveBettingSlip = new SaveBettingSlip,
    ) {}

    public function execute(User $user, ExplicitSelectionDraft $selection): BettingSlip
    {
        $leg = [
            'sport' => 'Football',
            'competition' => '',
            'event_name' => $selection->eventNameSeed,
            'market_name' => $selection->marketName,
            'selection_name' => $selection->selectionName,
            'decimal_odds' => null,
        ];

        return $this->saveBettingSlip->execute($user, null, ['name' => null], [$leg]);
    }
}
