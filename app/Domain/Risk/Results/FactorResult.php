<?php

namespace App\Domain\Risk\Results;

use App\Domain\Risk\Trace\FactorTrace;
use Brick\Math\BigDecimal;

/**
 * Rule Set 2026.1 §10. `baseContribution` is what the factor itself
 * calculated; `adjustedContribution` starts equal to it and only changes if
 * an interaction group cap (§12) scales it down — never the other way
 * around, and never by more than one group adjustment.
 */
final readonly class FactorResult
{
    /**
     * @param  array<int, ReasonCode>  $reasonCodes
     */
    public function __construct(
        public string $factorCode,
        public BigDecimal $baseContribution,
        public BigDecimal $cap,
        public BigDecimal $adjustedContribution,
        public array $reasonCodes,
        public FactorTrace $trace,
    ) {}

    public function withAdjustedContribution(BigDecimal $adjustedContribution): self
    {
        return new self(
            $this->factorCode,
            $this->baseContribution,
            $this->cap,
            $adjustedContribution,
            $this->reasonCodes,
            $this->trace,
        );
    }
}
