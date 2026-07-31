<?php

use App\Domain\Planner\Presentation\PlannerCapabilities;

/**
 * `PO-U07.X.1-001` §3 — every `planned` capability must read as honestly
 * not-yet-connected. Locks in the exact wording rule the commission itself
 * specified, so a future edit to the config can't silently reintroduce
 * words that imply a planned capability already ran.
 */
test('capabilities are grouped into active and planned', function () {
    $grouped = (new PlannerCapabilities)->grouped();

    expect($grouped['active'])->not->toBeEmpty();
    expect($grouped['planned'])->not->toBeEmpty();

    foreach ($grouped['active'] as $capability) {
        expect($capability['status'])->toBe('active');
    }

    foreach ($grouped['planned'] as $capability) {
        expect($capability['status'])->toBe('planned');
    }
});

test('no planned capability uses wording that implies it already ran', function () {
    $grouped = (new PlannerCapabilities)->grouped();

    $bannedWords = ['verified', 'retrieved', 'matched', 'loaded', 'available'];

    foreach ($grouped['planned'] as $capability) {
        $haystack = mb_strtolower($capability['label'].' '.$capability['description']);

        foreach ($bannedWords as $word) {
            expect($haystack)->not->toContain($word, "planned capability '{$capability['key']}' uses the banned word '{$word}'");
        }
    }
});

test('every capability has a unique key', function () {
    $all = collect(config('slipguard-planner-capabilities.capabilities'));

    expect($all->pluck('key')->unique())->toHaveCount($all->count());
});

test('the rule set version reuses the single existing source of truth', function () {
    expect((new PlannerCapabilities)->ruleSetVersion())->toBe(config('slipguard-product-facts.rule_set'));
});
