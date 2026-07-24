<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects a slip where two legs describe the same event, market, and
 * selection — an accidental duplicate rather than a deliberate second bet.
 */
class NoDuplicateBettingSlipLegs implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $seen = [];

        foreach ($value as $leg) {
            $fields = array_map(
                fn ($field) => mb_strtolower(trim($leg[$field] ?? '')),
                ['event_name', 'market_name', 'selection_name'],
            );

            $key = implode('|', $fields);

            if ($key === '||') {
                continue;
            }

            if (in_array($key, $seen, true)) {
                $fail('This slip has two legs with the same event, market, and selection.');

                return;
            }

            $seen[] = $key;
        }
    }
}
