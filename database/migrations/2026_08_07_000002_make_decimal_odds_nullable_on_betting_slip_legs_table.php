<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `PO-U23-001` — a real, pre-existing bug, found while testing the
 * conversational builder's explicit-selection path, not introduced by it:
 * `CreateDraftSlipFromParsedText` (paste-text/PDF intake, already shipped)
 * can legitimately produce a leg with no detected odds
 * (`ParseSlipText::detectOdds()` returns `''` when nothing is found), and
 * `betting_slip_legs.decimal_odds` was a non-nullable `decimal(6,2)` —
 * an empty string was never a valid decimal, so that insert crashed with
 * a raw SQLSTATE error, confirmed by reproducing it directly against the
 * existing action with text containing no detectable odds. The
 * conversational builder's own explicit selections ("Arsenal to win")
 * hit the identical crash on every single message, since a chat message
 * never states odds. Making the column nullable is the honest fix — a
 * placeholder numeric value (e.g. `1.00`) would fabricate odds data the
 * customer never provided, which `BettingSlipValidationRules::legIsComplete()`
 * already correctly treats a null/missing value as ("is_numeric($leg['decimal_odds']
 * ?? null)"), unchanged by this migration — an incomplete leg still
 * cannot be marked Ready until the customer supplies real odds.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('betting_slip_legs', function (Blueprint $table): void {
            $table->decimal('decimal_odds', 6, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('betting_slip_legs', function (Blueprint $table): void {
            $table->decimal('decimal_odds', 6, 2)->nullable(false)->change();
        });
    }
};
