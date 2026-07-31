<?php

namespace App\Domain\MarketIntelligence\Construction;

/**
 * U-17.6 §3 — the eligibility gate catalogue. Used both to exclude a leg
 * during construction and to explain the exclusion to the customer (§14) —
 * one enum, two uses, no second translation table.
 */
enum CandidateExclusionReason: string
{
    case CompetitionNotEnabled = 'CBE-001';
    case OutsideFixtureWindow = 'CBE-002';
    case FixtureNotActive = 'CBE-003';
    case MarketNotVerified = 'CBE-004';
    case CanonicalMappingUnconfident = 'CBE-005';
    case EvidenceNotFresh = 'CBE-006';
    case OddsOutOfBounds = 'CBE-007';
    case FixtureAlreadyUsed = 'CBE-008';
    case CustomerExcluded = 'CBE-009';

    public function label(): string
    {
        return match ($this) {
            self::CompetitionNotEnabled => 'This competition is not currently supported.',
            self::OutsideFixtureWindow => 'This fixture falls outside your planning window.',
            self::FixtureNotActive => 'This fixture is postponed, cancelled, or already completed.',
            self::MarketNotVerified => 'This market is not currently supported for automated planning.',
            self::CanonicalMappingUnconfident => "SlipGuard couldn't confidently match this selection to a supported outcome.",
            self::EvidenceNotFresh => 'The available evidence for this selection is not fresh enough to use.',
            self::OddsOutOfBounds => 'The odds for this selection fall outside the supported range.',
            self::FixtureAlreadyUsed => 'This fixture is already represented by another selection in this candidate.',
            self::CustomerExcluded => 'This selection was excluded by your own planning brief.',
        };
    }
}
