<?php

namespace App\Actions\BettingSlip;

use App\Models\BettingSlip;
use App\Models\User;

/**
 * Bounded-scope Screenshot Upload (2026-07-28): no OCR exists or is
 * attempted here — the stored image is purely a reference the customer
 * transcribes from, displayed alongside the existing Builder's manual
 * leg-entry form (`layouts/app.blade.php`'s Builder, when
 * `source_screenshot_path` is present). One empty leg is seeded, matching
 * exactly what `builder.blade.php`'s own `mount()` already does for a
 * brand-new manual slip — no separate intake pipeline.
 */
class CreateDraftSlipWithScreenshot
{
    public function __construct(
        private readonly SaveBettingSlip $saveBettingSlip = new SaveBettingSlip,
    ) {}

    public function execute(User $user, string $storedScreenshotPath): BettingSlip
    {
        return $this->saveBettingSlip->execute(
            $user,
            null,
            ['name' => null, 'source_screenshot_path' => $storedScreenshotPath],
            [],
        );
    }
}
