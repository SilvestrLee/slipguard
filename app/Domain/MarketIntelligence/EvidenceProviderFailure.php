<?php

namespace App\Domain\MarketIntelligence;

enum EvidenceProviderFailure: string
{
    case AuthenticationFailed = 'authentication_failed';
    case ConfigurationFailure = 'configuration_failure';
    case QuotaExhausted = 'quota_exhausted';
    case RateLimited = 'rate_limited';
    case ResponseInvalid = 'response_invalid';
    case Unavailable = 'unavailable';
}
