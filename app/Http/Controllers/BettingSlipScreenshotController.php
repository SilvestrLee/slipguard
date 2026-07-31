<?php

namespace App\Http\Controllers;

use App\Models\BettingSlip;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bounded-scope Screenshot Upload (2026-07-28): the only way to retrieve
 * an uploaded slip screenshot — never a public disk/URL. `view` is the
 * same ownership check every other slip-viewing surface already uses
 * (`BettingSlipPolicy::view`).
 */
class BettingSlipScreenshotController extends Controller
{
    public function show(BettingSlip $bettingSlip): StreamedResponse|Response
    {
        $this->authorize('view', $bettingSlip);

        if (! $bettingSlip->source_screenshot_path || ! Storage::disk('local')->exists($bettingSlip->source_screenshot_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($bettingSlip->source_screenshot_path);
    }
}
