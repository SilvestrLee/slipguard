<?php

namespace App\Domain\BettingSlip\Intake;

use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;

/**
 * Bounded-scope intake parser (2026-07-28): a deterministic, rule-based
 * text splitter for pasted slip text and PDF-extracted text — never an
 * ML/fuzzy-matching guess, consistent with this codebase's own
 * deterministic-risk-mathematics discipline. Its only job is to pre-fill
 * the *existing* Builder's leg-entry fields with best-effort values; every
 * result is reviewed and corrected by the customer there before a slip
 * can be marked Ready (`BettingSlipValidationRules::legIsComplete()` still
 * gates that, unchanged, for every intake method).
 *
 * A field that cannot be confidently detected is left empty rather than
 * guessed — an empty required field is itself the "flag" (the Builder's
 * own existing validation surfaces it), not a new UI concept invented for
 * this.
 *
 * Known, disclosed limitation: real bookmaker slip text varies enormously
 * between apps and operators. This parser recognises two common shapes —
 * one leg per blank-line-separated paragraph, or one leg per line when no
 * blank lines are present — and one market per leg, matched by a plain
 * substring search against `FootballMarketTaxonomyV1`'s own alias lists
 * (not `NormalizeFootballMarket`'s exact-match logic, which normalizes an
 * already-isolated market string — this parser is instead trying to find
 * *where in a free-text sentence* a known market phrase appears). It will
 * not correctly handle every real-world format, by design — it prefers
 * leaving a field empty over risking a wrong guess.
 */
final class ParseSlipText
{
    /**
     * @return array<int, ParsedLeg>
     */
    public function parse(string $text): array
    {
        return array_map(
            fn (string $block) => $this->parseBlock($block),
            $this->splitIntoBlocks($text),
        );
    }

    /**
     * @return array<int, string>
     */
    private function splitIntoBlocks(string $text): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);

        $paragraphs = preg_split('/\n\s*\n/', trim($normalized)) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), fn (string $p) => $p !== ''));

        if (count($paragraphs) > 1) {
            return $paragraphs;
        }

        $lines = explode("\n", trim($normalized));
        $lines = array_values(array_filter(array_map('trim', $lines), fn (string $l) => $l !== ''));

        return $lines;
    }

    private function parseBlock(string $block): ParsedLeg
    {
        $decimalOdds = $this->detectOdds($block);
        $eventName = $this->detectEventName($block);
        [$marketName, $marketDetected] = $this->detectMarket($block);
        $selectionName = $this->deriveSelectionText($block, $eventName, $decimalOdds, $marketName);

        return new ParsedLeg(
            rawText: $block,
            eventName: $eventName,
            marketName: $marketName,
            selectionName: $selectionName,
            decimalOdds: $decimalOdds,
            recognized: $eventName !== '' && $decimalOdds !== '' && ($marketDetected || $selectionName !== ''),
        );
    }

    /**
     * Prefers a number explicitly marked as odds ("@1.85", "@ 1.85") over
     * a bare decimal, and excludes any decimal immediately followed by
     * "goal(s)" — a market threshold like "2.5 Goals," not odds — before
     * falling back to the last remaining decimal in the block (odds are
     * conventionally stated last in copied slip text).
     */
    private function detectOdds(string $block): string
    {
        if (preg_match('/@\s*(\d{1,3}\.\d{1,2})\b/', $block, $matches)) {
            return $matches[1];
        }

        preg_match_all('/\b(\d{1,3}\.\d{1,2})\b(\s*goals?)?/i', $block, $matches, PREG_SET_ORDER);

        $candidates = array_values(array_filter(
            $matches,
            fn (array $m) => ! isset($m[2]) || trim($m[2]) === '',
        ));

        if ($candidates === []) {
            return '';
        }

        return end($candidates)[1];
    }

    /**
     * Matches "Team A vs Team B" / "Team A v Team B" — the one event-name
     * shape common across bookmaker text exports. No match, no guess.
     */
    private function detectEventName(string $block): string
    {
        if (preg_match('/([\p{L}0-9 .\'\-]{2,60}?\s+v(?:s)?\.?\s+[\p{L}0-9 .\'\-]{2,60})/iu', $block, $matches)) {
            return trim(preg_replace('/\s+/', ' ', $matches[1]));
        }

        return '';
    }

    /**
     * Plain substring search against the approved taxonomy's own alias
     * lists — not a new, separate market vocabulary.
     *
     * @return array{0: string, 1: bool}
     */
    private function detectMarket(string $block): array
    {
        $normalized = mb_strtolower($block);

        foreach ((new FootballMarketTaxonomyV1)->definitions() as $definition) {
            foreach ($definition->aliases as $alias) {
                if (str_contains($normalized, $alias)) {
                    return [$definition->displayName, true];
                }
            }
        }

        return ['', false];
    }

    /**
     * Whatever text remains once the event name and odds are stripped out
     * — never discarded, since it may be the only readable description of
     * the selection even when no known market alias was found in it.
     */
    private function deriveSelectionText(string $block, string $eventName, string $decimalOdds, string $marketName): string
    {
        $remaining = $block;

        if ($eventName !== '') {
            $remaining = str_ireplace($eventName, ' ', $remaining);
        }

        if ($decimalOdds !== '') {
            $remaining = preg_replace('/@?\s*'.preg_quote($decimalOdds, '/').'/', ' ', $remaining) ?? $remaining;
        }

        $remaining = preg_replace('/[\s\-–—|,]+/u', ' ', $remaining) ?? $remaining;
        $remaining = trim($remaining);

        return $remaining;
    }
}
