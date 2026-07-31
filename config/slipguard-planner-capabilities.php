<?php

/**
 * `PO-U07.X.1-001` §3/§4 — the single controlled source for the Planner
 * Workspace's "Analysis Inputs & Platform Readiness" panel. Every entry
 * here is either `active` (genuinely running in this codebase today,
 * verified against real classes/actions) or `planned` (a named future
 * product direction that is not connected to anything — no provider
 * integration, no fixture/competition/team/historical/market data source
 * exists anywhere in this repository). The panel renders from this array
 * rather than scattering capability claims across the template, so a
 * future capability can be flipped from `planned` to `active` in one
 * place once it is genuinely built — never by relabeling copy alone.
 *
 * Wording rule (§3): never use "verified", "retrieved", "matched",
 * "loaded", or "available" for a `planned` entry — the status badge and
 * description below are the only place that distinction is made, and it
 * must stay truthful as this file evolves.
 */
return [
    'capabilities' => [
        [
            'key' => 'customer_slip_data',
            'label' => 'Customer-provided slip data',
            'description' => 'The selections, markets and odds you entered yourself.',
            'status' => 'active',
        ],
        [
            'key' => 'selection_market_structure',
            'label' => 'Selection and market structure',
            'description' => 'Each selection normalised against the accepted football market taxonomy.',
            'status' => 'active',
        ],
        [
            'key' => 'deterministic_rules',
            'label' => 'Deterministic structural rules',
            'description' => 'The accepted rule set — the same rules used everywhere else in SlipGuard.',
            'status' => 'active',
        ],
        [
            'key' => 'weakest_leg_contribution',
            'label' => 'Weakest-leg contribution',
            'description' => 'The Marginal Structural Contribution model, applied to your current selections.',
            'status' => 'active',
        ],
        [
            'key' => 'revision_comparison',
            'label' => 'Revision comparison',
            'description' => 'Every revision you create in this session is preserved and can be compared.',
            'status' => 'active',
        ],
        [
            'key' => 'explainability',
            'label' => 'Explainability',
            'description' => 'Plain-language reasoning behind every structural score.',
            'status' => 'active',
        ],
        [
            'key' => 'fixture_intelligence',
            'label' => 'Fixture intelligence',
            'description' => 'Provider-backed fixture data to enrich structural context.',
            'status' => 'planned',
        ],
        [
            'key' => 'competition_intelligence',
            'label' => 'Competition intelligence',
            'description' => 'Competition-level context sourced from external providers.',
            'status' => 'planned',
        ],
        [
            'key' => 'team_intelligence',
            'label' => 'Team intelligence',
            'description' => 'Team-level context sourced from external providers.',
            'status' => 'planned',
        ],
        [
            'key' => 'historical_intelligence',
            'label' => 'Historical intelligence',
            'description' => 'Historical patterns sourced from external providers.',
            'status' => 'planned',
        ],
        [
            'key' => 'market_intelligence',
            'label' => 'Market intelligence',
            'description' => 'Market-level context sourced from external providers.',
            'status' => 'planned',
        ],
        [
            'key' => 'provider_evidence',
            'label' => 'External provider evidence',
            'description' => 'Combining third-party evidence with SlipGuard\'s own deterministic intelligence.',
            'status' => 'planned',
        ],
        [
            'key' => 'evidence_warehouse',
            'label' => 'SlipGuard Evidence Warehouse',
            'description' => 'A persisted store of provider evidence for future deterministic use.',
            'status' => 'planned',
        ],
    ],
];
