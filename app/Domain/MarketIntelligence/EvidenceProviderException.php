<?php

namespace App\Domain\MarketIntelligence;

use RuntimeException;
use Throwable;

class EvidenceProviderException extends RuntimeException
{
    public function __construct(
        public readonly EvidenceProviderFailure $failure,
        public readonly string $correlationId,
        ?Throwable $previous = null,
    ) {
        parent::__construct('Market evidence is temporarily unavailable.', previous: $previous);
    }
}
