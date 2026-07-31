<?php

namespace App\Actions\Labs;

use App\Domain\Labs\LabsInterestType;
use App\Models\LabsFeature;
use App\Models\LabsFeatureInterest;
use App\Models\User;

/**
 * `PO-U19.1-001` §15 — the public homepage's minimal-friction waitlist
 * entry point. Deliberately separate from the Labs page's own
 * `toggleInterest()` (which stays authenticated-only, unchanged): this
 * writes into the same `labs_feature_interests` table and reuses the same
 * `LabsInterestType::Notify` vocabulary, but accepts a guest email instead
 * of requiring an account first — the "minimal friction" the commission
 * asked for, not achievable by reusing the authenticated action as-is.
 *
 * Idempotent by (user_id or email) + feature + type: re-submitting the
 * same email only updates the platform preference, never creates a
 * second row — matching the existing Labs page's own already-joined
 * behaviour rather than inventing a new duplicate-handling rule.
 */
class RecordMobileWaitlistInterest
{
    public function execute(LabsFeature $feature, ?User $user, ?string $email, ?string $platformPreference): LabsFeatureInterest
    {
        $attributes = $user
            ? ['user_id' => $user->id, 'labs_feature_id' => $feature->id, 'type' => LabsInterestType::Notify->value]
            : ['email' => $email, 'labs_feature_id' => $feature->id, 'type' => LabsInterestType::Notify->value];

        return LabsFeatureInterest::updateOrCreate($attributes, [
            'platform_preference' => $platformPreference,
        ]);
    }
}
