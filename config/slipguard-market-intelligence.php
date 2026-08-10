<?php

/**
 * `U-17.5`/`PO-U17.5-AC-001` — Capability B's Engineering Foundation
 * Package. `enabled` gates the feature's visible discovery action (the
 * route itself always registers; a real "Experience preview" state
 * renders when this is `false`, not a 404 — `MVP_SCOPE_LOCK.md` §4.14).
 *
 * `AO-D3` (`PO-RC1-013`) — this comment previously read "false by default,
 * and must stay false until Product Office explicitly authorizes a public
 * launch," a restriction from `U-17.4`'s own "no public launch"
 * compliance constraint. That restriction has since been superseded:
 * Product Office explicitly authorized public launch (`PO-MVP-004`,
 * 2026-08-03, `docs/00-governance/DECISION_LOG.md`), and both `.env` and
 * `.env.example` already correctly set `MARKET_WIDE_PLANNER_ENABLED=true`
 * — only this comment had not caught up. The `env(..., false)` fallback
 * immediately below is unchanged and intentional — a safe fail-closed
 * default for any environment that omits the variable entirely, unrelated
 * to the now-resolved launch-visibility decision this comment describes.
 *
 * Confirmed against real `U-17.3` trial data, not assumed:
 * `allowed_competitions` and `default_markets` match exactly what was
 * verified reachable on The Odds API's free tier for EPL/La Liga/Serie A.
 */
return [
    'enabled' => (bool) env('MARKET_WIDE_PLANNER_ENABLED', false),

    'allowed_competitions' => [
        'soccer_epl' => 'Premier League',
        'soccer_spain_la_liga' => 'La Liga',
        'soccer_italy_serie_a' => 'Serie A',
    ],

    /**
     * Match Result and Total Goals are available via one bulk request per
     * competition/window (U-17.3). Everything else here requires one
     * authenticated request PER FIXTURE (U-17.3's own single most
     * important finding) — see AcquireMarketEvidence's bulk-first
     * sequencing, which exists specifically to minimise how often the
     * expensive path below is ever reached.
     */
    'bulk_markets' => ['h2h', 'totals'],

    'per_event_markets' => ['double_chance', 'draw_no_bet', 'btts', 'correct_score', 'h2h_h1'],

    // Half-Time Result (h2h_h1) is opt-in only per U-17.6/U-17.7 — not
    // requested unless a future caller explicitly asks for it.
    'default_markets' => ['h2h', 'totals', 'double_chance', 'draw_no_bet', 'btts'],

    /**
     * Conservative pending U-17.4's still-unanswered written provider
     * clarification on caching duration — short and centrally configured
     * here specifically so that answer, whenever it arrives, changes one
     * value instead of triggering a redesign (U-17.5 §7).
     */
    'cache_ttl_hours' => 2,

    /**
     * U-17.5 §8 — a real, quantified cost finding from U-17.3: 5 of 7
     * confirmed markets cost one request per fixture. This bounds how
     * many per-event requests a single acquisition run may issue before
     * refusing, rather than letting an unbounded planning brief exhaust
     * real API quota.
     */
    'max_per_event_requests' => 100,
];
