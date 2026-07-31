<?php

namespace App\Domain\MarketIntelligence;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * U-17.5 §3 — the `MarketEvidenceProvider` adapter for The Odds API,
 * the only provider integrated in this Engineering Foundation Package
 * (U-17.5 Decision Register #4). This is the one and only place The
 * Odds API's own field names/endpoints are known — nothing above this
 * class ever sees a provider-specific shape (U-17.5 §3's own boundary:
 * this adapter never calculates eligibility, ranking, or structural risk).
 *
 * Endpoint behaviour confirmed for real during `U-17.3`: bulk markets
 * (`h2h`, `totals`) are available via one request per competition/window;
 * every other confirmed market requires one authenticated request PER
 * FIXTURE via the per-event endpoint — this is not a design choice, it
 * is how the provider's real API actually behaves.
 */
class OddsApiProvider implements MarketEvidenceProvider
{
    private const BASE_URL = 'https://api.the-odds-api.com/v4';

    public function __construct(
        private readonly MapOddsApiMarket $marketMapper = new MapOddsApiMarket,
    ) {}

    /**
     * One bulk request returns both fixture identity AND bookmaker quotes
     * together — deliberately a single method, not two, so a caller can
     * never accidentally issue the same bulk request twice for data one
     * response already contained. Only `h2h`/`totals` are valid here for
     * European regions — requesting anything else raises a real `422
     * INVALID_MARKET` error from the provider (U-17.3's own finding).
     *
     * @return array{fixtures: array<int, CanonicalFixture>, quotes: array<int, CanonicalMarketQuote>}
     */
    public function bulkAcquire(string $competitionKey, array $markets): array
    {
        return $this->guarded(
            operation: 'bulk_acquisition',
            competitionKey: $competitionKey,
            markets: $markets,
            callback: function () use ($competitionKey, $markets): array {
                $events = $this->request("/sports/{$competitionKey}/odds", $markets);

                if (! array_is_list($events)) {
                    throw new EvidenceProviderException(
                        EvidenceProviderFailure::ResponseInvalid,
                        (string) Str::uuid(),
                    );
                }

                $now = CarbonImmutable::now();
                $fixtures = collect($events)
                    ->map(fn (array $event) => new CanonicalFixture(
                        providerEventId: $event['id'],
                        competitionKey: $competitionKey,
                        homeTeam: $event['home_team'],
                        awayTeam: $event['away_team'],
                        commenceTime: CarbonImmutable::parse($event['commence_time']),
                        retrievedAt: $now,
                    ))
                    ->all();

                return ['fixtures' => $fixtures, 'quotes' => $this->extractQuotes($events)];
            },
        );
    }

    /**
     * One request PER FIXTURE — the expensive path U-17.3/U-17.5 both
     * name as the real architectural cost this feature must budget for
     * (`AcquireMarketEvidence`'s request-budget guard exists specifically
     * because of this method's cost).
     *
     * @return array<int, CanonicalMarketQuote>
     */
    public function eventMarketQuotes(string $competitionKey, string $providerEventId, array $markets): array
    {
        return $this->guarded(
            operation: 'event_market_acquisition',
            competitionKey: $competitionKey,
            markets: $markets,
            callback: function () use ($competitionKey, $providerEventId, $markets): array {
                $event = $this->request("/sports/{$competitionKey}/events/{$providerEventId}/odds", $markets);

                if (array_is_list($event) || ! isset($event['id'])) {
                    throw new EvidenceProviderException(
                        EvidenceProviderFailure::ResponseInvalid,
                        (string) Str::uuid(),
                    );
                }

                return $this->extractQuotes([$event]);
            },
        );
    }

    /** @return array<string, mixed>|array<int, mixed> */
    private function request(string $path, array $markets): array
    {
        $apiKey = config('services.the_odds_api.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new EvidenceProviderException(
                EvidenceProviderFailure::ConfigurationFailure,
                (string) Str::uuid(),
            );
        }

        $response = Http::timeout(10)->get(self::BASE_URL.$path, [
            'apiKey' => $apiKey,
            'regions' => 'eu,uk',
            'markets' => implode(',', $markets),
            'oddsFormat' => 'decimal',
        ]);

        if ($response->failed()) {
            $response->throw();
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new EvidenceProviderException(
                EvidenceProviderFailure::ResponseInvalid,
                (string) Str::uuid(),
            );
        }

        return $payload;
    }

    private function guarded(string $operation, string $competitionKey, array $markets, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (EvidenceProviderException $exception) {
            $this->logFailure($exception, $operation, $competitionKey, $markets);

            throw $exception;
        } catch (RequestException $exception) {
            $failure = $this->classifyResponseFailure($exception);
            $translated = new EvidenceProviderException($failure, (string) Str::uuid(), $exception);
            $this->logFailure($translated, $operation, $competitionKey, $markets, $exception->response->status());

            throw $translated;
        } catch (ConnectionException $exception) {
            $translated = new EvidenceProviderException(
                EvidenceProviderFailure::Unavailable,
                (string) Str::uuid(),
                $exception,
            );
            $this->logFailure($translated, $operation, $competitionKey, $markets);

            throw $translated;
        } catch (Throwable $exception) {
            $translated = new EvidenceProviderException(
                EvidenceProviderFailure::ResponseInvalid,
                (string) Str::uuid(),
                $exception,
            );
            $this->logFailure($translated, $operation, $competitionKey, $markets);

            throw $translated;
        }
    }

    private function classifyResponseFailure(RequestException $exception): EvidenceProviderFailure
    {
        $status = $exception->response->status();
        $providerCode = $exception->response->json('error_code');

        if (is_string($providerCode) && str_contains(strtoupper($providerCode), 'USAGE')) {
            return EvidenceProviderFailure::QuotaExhausted;
        }

        return match (true) {
            in_array($status, [401, 403], true) => EvidenceProviderFailure::AuthenticationFailed,
            $status === 429 => EvidenceProviderFailure::RateLimited,
            default => EvidenceProviderFailure::Unavailable,
        };
    }

    private function logFailure(
        EvidenceProviderException $exception,
        string $operation,
        string $competitionKey,
        array $markets,
        ?int $upstreamStatus = null,
    ): void {
        Log::warning('Market evidence provider request failed.', [
            'provider' => 'odds_api',
            'correlation_id' => $exception->correlationId,
            'failure_category' => $exception->failure->value,
            'operation' => $operation,
            'competition' => $competitionKey,
            'markets' => array_values($markets),
            'upstream_status' => $upstreamStatus,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @return array<int, CanonicalMarketQuote>
     */
    private function extractQuotes(array $events): array
    {
        $now = CarbonImmutable::now();
        $quotes = [];

        foreach ($events as $event) {
            foreach ($event['bookmakers'] ?? [] as $bookmaker) {
                foreach ($bookmaker['markets'] ?? [] as $market) {
                    $family = $this->marketMapper->toCanonicalFamily($market['key']);

                    if (! $family) {
                        // An unrecognized provider key — skipped, never guessed
                        // at, per U-17.5/U-17.6's shared "leave it out rather
                        // than invent a mapping" discipline.
                        continue;
                    }

                    $quotes[] = new CanonicalMarketQuote(
                        providerEventId: $event['id'],
                        canonicalMarket: $family->value,
                        providerMarketKey: $market['key'],
                        bookmakerKey: $bookmaker['key'],
                        outcomes: $market['outcomes'] ?? [],
                        providerLastUpdate: isset($market['last_update']) ? CarbonImmutable::parse($market['last_update']) : null,
                        retrievedAt: $now,
                        evidenceQuality: EvidenceQuality::Fresh,
                    );
                }
            }
        }

        return $quotes;
    }
}
