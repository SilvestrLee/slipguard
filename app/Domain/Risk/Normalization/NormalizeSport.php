<?php

namespace App\Domain\Risk\Normalization;

use App\Domain\Risk\Taxonomy\NormalizationStatus;

/**
 * Deterministic football-first sport normalizer. Exact alias matching only
 * — no fuzzy matching, no guessed misspellings.
 */
class NormalizeSport
{
    public const FOOTBALL_CODE = 'football';

    /**
     * @var array<int, string>
     */
    private const FOOTBALL_ALIASES = [
        'football',
        'soccer',
        'association football',
    ];

    /**
     * Real, named sports this taxonomy version does not yet support.
     * Recognized by name, but excluded on purpose — not the same as
     * unrecognized gibberish.
     *
     * @var array<int, string>
     */
    private const KNOWN_UNSUPPORTED_SPORTS = [
        'tennis',
        'basketball',
        'cricket',
        'baseball',
        'horse racing',
        'esports',
        'virtual sports',
    ];

    public function normalize(string $rawSport): NormalizedSport
    {
        $normalized = $this->normalizeText($rawSport);

        if (in_array($normalized, self::FOOTBALL_ALIASES, true)) {
            return new NormalizedSport(self::FOOTBALL_CODE, NormalizationStatus::Complete, $rawSport);
        }

        if (in_array($normalized, self::KNOWN_UNSUPPORTED_SPORTS, true)) {
            return new NormalizedSport(null, NormalizationStatus::Unsupported, $rawSport);
        }

        return new NormalizedSport(null, NormalizationStatus::Unrecognized, $rawSport);
    }

    private function normalizeText(string $raw): string
    {
        $text = mb_strtolower(trim($raw));

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
