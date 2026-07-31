<?php

use App\Domain\BettingSlip\Intake\ParseSlipText;

/**
 * Bounded-scope intake (2026-07-28): deterministic, rule-based parsing
 * only — no ML, no fuzzy matching. These tests lock in the exact,
 * verified behaviour of the parser (including its known limitations)
 * so the Builder always receives either a confident value or a blank
 * field, never a guess.
 */
test('a blank-line-separated slip is split into one leg per paragraph', function () {
    $legs = (new ParseSlipText)->parse(
        "Chelsea vs Arsenal\nMatch Result\nChelsea Win @1.85\n\n".
        "Arsenal vs Tottenham\nOver 2.5 Goals @2.10"
    );

    expect($legs)->toHaveCount(2);
});

test('event name is detected from a "vs" pattern', function () {
    [$leg] = (new ParseSlipText)->parse('Chelsea vs Arsenal, Match Result, Chelsea Win @1.85');

    expect($leg->eventName)->toBe('Chelsea vs Arsenal');
});

test('event name is detected from a "v" pattern', function () {
    [$leg] = (new ParseSlipText)->parse('Real Madrid v Barcelona, Match Result, Real Madrid Win @1.75');

    expect($leg->eventName)->toBe('Real Madrid v Barcelona');
});

test('an explicit @ marker is preferred as the odds value', function () {
    [$leg] = (new ParseSlipText)->parse('Chelsea vs Arsenal, Over 2.5 Goals, Over @1.85');

    expect($leg->decimalOdds)->toBe('1.85');
});

test('a decimal immediately followed by "goals" is never mistaken for odds', function () {
    [$leg] = (new ParseSlipText)->parse('Arsenal vs Tottenham, Over 2.5 Goals @2.10');

    expect($leg->decimalOdds)->toBe('2.10');
});

test('without an @ marker, the last bare decimal not attached to "goals" is used as odds', function () {
    [$leg] = (new ParseSlipText)->parse('Chelsea vs Arsenal, Match Result, Chelsea Win 1.85');

    expect($leg->decimalOdds)->toBe('1.85');
});

test('market name is detected from the football taxonomy alias list', function () {
    $cases = [
        'Both Teams to Score' => 'Both Teams to Score',
        'BTTS' => 'Both Teams to Score',
        'Draw No Bet' => 'Draw No Bet',
        'Double Chance' => 'Double Chance',
        'Correct Score' => 'Correct Score',
    ];

    foreach ($cases as $phrase => $expectedMarket) {
        [$leg] = (new ParseSlipText)->parse("Chelsea vs Arsenal, {$phrase}, Selection @1.85");

        expect($leg->marketName)->toBe($expectedMarket);
    }
});

test('a fully confident block is marked recognized', function () {
    [$leg] = (new ParseSlipText)->parse('Chelsea vs Arsenal, Match Result, Chelsea Win @1.85');

    expect($leg->recognized)->toBeTrue();
});

test('text with no event, market or odds is left entirely blank rather than guessed, and is not recognized', function () {
    [$leg] = (new ParseSlipText)->parse('just some unrelated text');

    expect($leg->eventName)->toBe('')
        ->and($leg->marketName)->toBe('')
        ->and($leg->decimalOdds)->toBe('')
        ->and($leg->selectionName)->toBe('just some unrelated text')
        ->and($leg->recognized)->toBeFalse();
});

test('no text is ever silently discarded — unmatched words remain in the selection text', function () {
    [$leg] = (new ParseSlipText)->parse('Chelsea vs Arsenal, Some Unrecognised Market Text @1.85');

    expect($leg->selectionName)->toContain('Some Unrecognised Market Text');
});

test('one-leg-per-line fallback is used when the pasted text has no blank lines', function () {
    $legs = (new ParseSlipText)->parse(
        "Chelsea v Arsenal Match Result Chelsea Win @1.85\n".
        'Arsenal v Tottenham Both Teams To Score BTTS Yes @1.90'
    );

    expect($legs)->toHaveCount(2)
        ->and($legs[0]->decimalOdds)->toBe('1.85')
        ->and($legs[0]->marketName)->toBe('Match Result')
        ->and($legs[1]->decimalOdds)->toBe('1.90')
        ->and($legs[1]->marketName)->toBe('Both Teams to Score');
});
