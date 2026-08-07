<?php

namespace App\Domain\BettingSlip\Intake;

/**
 * One candidate leg detected by `ParseSlipText` from a block of pasted or
 * PDF-extracted text. Never a final, trusted leg — the customer always
 * reviews and corrects these in the existing Builder before the slip can
 * be marked Ready (`BettingSlipValidationRules::legIsComplete()` still
 * gates that, unchanged).
 */
final readonly class ParsedLeg
{
    public function __construct(
        public string $rawText,
        public string $eventName,
        public string $marketName,
        public string $selectionName,
        public string $decimalOdds,
        public bool $recognized,
    ) {}

    /**
     * `PO-U23-001` — a real, pre-existing bug fixed alongside the
     * conversational builder: `betting_slip_legs.decimal_odds` is a
     * non-nullable decimal column, and `''` (this class's own "not
     * detected" value) was never a valid one — every insert with
     * undetected odds crashed with a raw SQL error before this fix. `''`
     * → `null` here, matching the column's new nullable definition
     * (`2026_08_07_000002_make_decimal_odds_nullable_on_betting_slip_legs_table`);
     * `BettingSlipValidationRules::legIsComplete()` already treats a
     * missing value as incomplete, unchanged.
     *
     * @return array{sport: string, competition: string, event_name: string, market_name: string, selection_name: string, decimal_odds: string|null}
     */
    public function toLegAttributes(): array
    {
        return [
            'sport' => 'Football',
            'competition' => '',
            'event_name' => $this->eventName,
            'market_name' => $this->marketName,
            'selection_name' => $this->selectionName,
            'decimal_odds' => $this->decimalOdds !== '' ? $this->decimalOdds : null,
        ];
    }
}
