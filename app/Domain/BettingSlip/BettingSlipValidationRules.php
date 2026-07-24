<?php

namespace App\Domain\BettingSlip;

use App\Rules\NoDuplicateBettingSlipLegs;

/**
 * The single source of truth for what makes a betting slip's input valid.
 * Used by the Livewire builder today; any future entry point (an API, an
 * importer) must validate against the same rules rather than re-deriving
 * them.
 */
class BettingSlipValidationRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function legRules(): array
    {
        return [
            'sport' => ['required', 'string', 'max:255'],
            'competition' => ['nullable', 'string', 'max:255'],
            'event_name' => ['required', 'string', 'max:255'],
            'market_name' => ['required', 'string', 'max:255'],
            'selection_name' => ['required', 'string', 'max:255'],
            'decimal_odds' => ['required', 'numeric', 'min:1.01', 'max:1000'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function slipRules(int $minLegs, int $maxLegs): array
    {
        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'legs' => ['required', 'array', "min:{$minLegs}", "max:{$maxLegs}", new NoDuplicateBettingSlipLegs],
        ];

        foreach (self::legRules() as $field => $fieldRules) {
            $rules["legs.*.{$field}"] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function messages(int $minLegs, int $maxLegs): array
    {
        return [
            'legs.min' => __('Add at least :min leg to save this slip.', ['min' => $minLegs]),
            'legs.max' => __('A slip can have at most :max legs.', ['max' => $maxLegs]),
            'legs.*.sport.required' => __('Enter the sport for every leg.'),
            'legs.*.event_name.required' => __('Enter the event for every leg.'),
            'legs.*.market_name.required' => __('Enter the market for every leg.'),
            'legs.*.selection_name.required' => __('Enter the selection for every leg.'),
            'legs.*.decimal_odds.required' => __('Enter the decimal odds for every leg.'),
            'legs.*.decimal_odds.numeric' => __('Decimal odds must be a number.'),
            'legs.*.decimal_odds.min' => __('Decimal odds must be greater than 1.00.'),
        ];
    }

    /**
     * Whether a single leg's data (already validated or from the database)
     * is complete enough for analysis. Defense in depth: the Risk Engine
     * should never have to wonder whether a leg is half-filled.
     *
     * @param  array<string, mixed>  $leg
     */
    public static function legIsComplete(array $leg): bool
    {
        foreach (['sport', 'event_name', 'market_name', 'selection_name'] as $field) {
            if (blank($leg[$field] ?? null)) {
                return false;
            }
        }

        return is_numeric($leg['decimal_odds'] ?? null) && (float) $leg['decimal_odds'] >= 1.01;
    }
}
