<?php

namespace App\Domain\Risk\Results;

/**
 * Rule Set 2026.1 §17 — the three possible outcomes of the analysis
 * availability gate. Unavailable always means: no structural score, slip
 * stays Ready (the caller's job, not the engine's — see CalculateStructuralRisk).
 */
enum AnalysisAvailability: string
{
    case Full = 'full';
    case Limited = 'limited';
    case Unavailable = 'unavailable';
}
