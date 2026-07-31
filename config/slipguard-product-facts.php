<?php

/**
 * `PO-U15.6-001` §29/§33/§34 — the single source of truth for the public
 * homepage's "Deterministic Proof" metrics. Previously hardcoded directly
 * in `resources/views/pages/home.blade.php` (including a stale test
 * count) — moved here so there is exactly one place to update after each
 * verified `php artisan test` run, not a value quietly duplicated in a
 * Blade template.
 *
 * Maintenance: after running `php artisan test`, update `automated_tests`
 * and `verified_at` below to match. `structural_factors` and `rule_set`
 * change only when `App\Domain\Risk\RuleSets\RuleSet2026_1` itself changes
 * (RF-001 through RF-006 are all defined; RF-006 — Relationship/
 * Correlation — is deliberately inactive/disabled in this rule set and
 * always contributes 0, per `docs/03-data-science/RISK_RULE_SET_2026_1.md`
 * §11 — still a real, defined factor, not a fabricated count).
 */
return [
    'rule_set' => '2026.1',
    'automated_tests' => 512,
    'structural_factors' => 6,
    'outcome_predictions' => 0,
    'deterministic' => true,
    'explainability' => true,
    'verified_at' => '2026-07-28',
];
