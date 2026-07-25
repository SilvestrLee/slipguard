<?php

namespace App\Domain\Risk\Results;

use Brick\Math\BigDecimal;

/**
 * Rule Set 2026.1 §12 — one record per interaction group (Group A, Group B),
 * always present even when the cap didn't bind, so the trace shows that the
 * group was checked, not just what happened when it activated.
 */
final readonly class InteractionAdjustment
{
    /**
     * @param  array<int, string>  $factorCodes
     * @param  array<string, BigDecimal>  $postCapContributions  Keyed by factor code.
     */
    public function __construct(
        public string $groupName,
        public array $factorCodes,
        public BigDecimal $preCapTotal,
        public BigDecimal $cap,
        public bool $wasCapped,
        public BigDecimal $adjustmentRatio,
        public array $postCapContributions,
    ) {}
}
