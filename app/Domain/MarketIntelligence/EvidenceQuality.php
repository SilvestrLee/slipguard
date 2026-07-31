<?php

namespace App\Domain\MarketIntelligence;

/**
 * U-17.6 §13 — deliberately not `App\Domain\Risk\Results\AnalysisAvailability`.
 * That enum answers "how much can we trust this customer's own analysis
 * result"; this one answers "how much can we trust this piece of external
 * provider evidence" — a different question, per U-17.6's own note, not
 * a duplicate of an existing concept.
 */
enum EvidenceQuality: string
{
    case Fresh = 'fresh';
    case Stale = 'stale';
    case Conflicting = 'conflicting';
    case Unavailable = 'unavailable';
}
