<?php

namespace App\Domain\Planner\Presentation;

/**
 * `PO-U07.X.1-001` §3/§4/§6 — reads the controlled capability source
 * (`config/slipguard-planner-capabilities.php`) and groups it for the
 * "Analysis Inputs & Platform Readiness" panel. No hardcoded capability
 * claim lives in the template; this class only groups what the config
 * already declares.
 */
final class PlannerCapabilities
{
    /**
     * @return array{active: array<int, array<string, mixed>>, planned: array<int, array<string, mixed>>}
     */
    public function grouped(): array
    {
        $capabilities = collect(config('slipguard-planner-capabilities.capabilities', []));

        return [
            'active' => $capabilities->where('status', 'active')->values()->all(),
            'planned' => $capabilities->where('status', 'planned')->values()->all(),
        ];
    }

    /**
     * Reads the single existing rule-set source of truth directly
     * (`config/slipguard-product-facts.php`) rather than duplicating the
     * value in the capabilities config — a config file cannot safely read
     * another config file at load time (file load order is alphabetical,
     * not dependency-ordered), so this indirection happens here instead.
     */
    public function ruleSetVersion(): ?string
    {
        return config('slipguard-product-facts.rule_set');
    }
}
