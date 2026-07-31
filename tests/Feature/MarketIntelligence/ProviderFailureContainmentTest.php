<?php

use App\Domain\MarketIntelligence\EvidenceProviderException;
use App\Domain\MarketIntelligence\EvidenceProviderFailure;
use App\Domain\MarketIntelligence\OddsApiProvider;
use App\Models\BettingSlip;
use App\Models\PlannerSession;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

beforeEach(function () {
    config([
        'services.the_odds_api.key' => 'safe-test-key',
        'slipguard-market-intelligence.enabled' => true,
    ]);
});

test('provider HTTP failures are translated into safe domain categories', function (
    int $status,
    array $payload,
    EvidenceProviderFailure $expected,
) {
    Http::fake(['*' => Http::response($payload, $status)]);

    try {
        (new OddsApiProvider)->bulkAcquire('soccer_epl', ['h2h']);
        $this->fail('Expected a contained evidence-provider failure.');
    } catch (EvidenceProviderException $exception) {
        expect($exception->failure)->toBe($expected)
            ->and($exception->getMessage())->toBe('Market evidence is temporarily unavailable.')
            ->and($exception->getMessage())->not->toContain('safe-test-key')
            ->and($exception->getMessage())->not->toContain('Illuminate');
    }
})->with([
    'quota exhausted' => [401, ['error_code' => 'OUT_OF_USAGE_CREDITS', 'message' => 'provider detail'], EvidenceProviderFailure::QuotaExhausted],
    'unauthorised' => [401, ['message' => 'provider detail'], EvidenceProviderFailure::AuthenticationFailed],
    'forbidden' => [403, ['message' => 'provider detail'], EvidenceProviderFailure::AuthenticationFailed],
    'rate limited' => [429, ['message' => 'provider detail'], EvidenceProviderFailure::RateLimited],
    'upstream failure' => [503, ['message' => 'provider detail'], EvidenceProviderFailure::Unavailable],
]);

test('connection failures are contained', function () {
    Http::fake(fn () => throw new ConnectionException('network internals'));

    expect(fn () => (new OddsApiProvider)->bulkAcquire('soccer_epl', ['h2h']))
        ->toThrow(
            EvidenceProviderException::class,
            'Market evidence is temporarily unavailable.',
        );
});

test('malformed responses are contained', function () {
    Http::fake(['*' => Http::response('not-json', 200)]);

    try {
        (new OddsApiProvider)->bulkAcquire('soccer_epl', ['h2h']);
        $this->fail('Expected malformed provider evidence to be rejected.');
    } catch (EvidenceProviderException $exception) {
        expect($exception->failure)->toBe(EvidenceProviderFailure::ResponseInvalid);
    }
});

test('missing provider configuration is contained before an external request', function () {
    config(['services.the_odds_api.key' => null]);
    Http::fake();

    try {
        (new OddsApiProvider)->bulkAcquire('soccer_epl', ['h2h']);
        $this->fail('Expected missing provider configuration to be contained.');
    } catch (EvidenceProviderException $exception) {
        expect($exception->failure)->toBe(EvidenceProviderFailure::ConfigurationFailure);
    }

    Http::assertNothingSent();
});

test('sanitized provider diagnostics retain classification without secrets or raw responses', function () {
    Log::spy();
    Http::fake(['*' => Http::response([
        'error_code' => 'OUT_OF_USAGE_CREDITS',
        'message' => 'raw provider quota response',
        'api_key' => 'safe-test-key',
    ], 401)]);

    expect(fn () => (new OddsApiProvider)->bulkAcquire('soccer_epl', ['h2h']))
        ->toThrow(EvidenceProviderException::class);

    Log::shouldHaveReceived('warning')->once()->with(
        'Market evidence provider request failed.',
        Mockery::on(function (array $context): bool {
            $encoded = json_encode($context);

            return $context['failure_category'] === 'quota_exhausted'
                && $context['competition'] === 'soccer_epl'
                && $context['upstream_status'] === 401
                && filled($context['correlation_id'])
                && ! str_contains($encoded, 'safe-test-key')
                && ! str_contains($encoded, 'raw provider quota response');
        }),
    );
});

test('the Planner preserves choices and renders a recoverable provider state without technical disclosure', function () {
    Http::fake(['*' => Http::response([
        'error_code' => 'OUT_OF_USAGE_CREDITS',
        'message' => 'RequestException /vendor/ APP_DEBUG provider-key-secret',
    ], 401)]);
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('windowDays', 3)
        ->set('legCount', 4)
        ->set('markets', ['h2h'])
        ->set('riskCeiling', 'moderate')
        ->call('findCandidate')
        ->assertSet('evidenceFailureType', 'provider_unavailable')
        ->assertSet('competitions', ['soccer_epl'])
        ->assertSet('windowDays', 3)
        ->assertSet('legCount', 4)
        ->assertSet('markets', ['h2h'])
        ->assertSet('riskCeiling', 'moderate')
        ->assertSet('discovered', false)
        ->assertSet('noOpportunitiesReason', null)
        ->assertSee('Live fixture information is temporarily unavailable')
        ->assertSee('Your planning choices have been preserved')
        ->assertSee('Try Again')
        ->assertSee('Review Planning Choices');

    foreach (['Illuminate', 'RequestException', '/vendor/', '/app/', 'APP_DEBUG', 'provider-key-secret', 'stack trace'] as $term) {
        $component->assertDontSee($term);
    }

    expect(PlannerSession::count())->toBe(0)
        ->and(BettingSlip::count())->toBe(0);
});

test('retry reuses the preserved planning brief and can recover into a genuine empty-pool result', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push(['error_code' => 'OUT_OF_USAGE_CREDITS'], 401)
            ->push([], 200),
    ]);
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('market-intelligence.builder')
        ->set('competitions', ['soccer_epl'])
        ->set('windowDays', 3)
        ->set('legCount', 4)
        ->set('markets', ['h2h'])
        ->call('findCandidate')
        ->assertSet('evidenceFailureType', 'provider_unavailable');

    $component->call('findCandidate')
        ->assertSet('evidenceFailureType', null)
        ->assertSet('competitions', ['soccer_epl'])
        ->assertSet('windowDays', 3)
        ->assertSet('legCount', 4)
        ->assertSet('markets', ['h2h'])
        ->assertSet('discovered', false)
        ->assertSet('noOpportunitiesReason', 'Not enough supported fixtures were found within your planning window and competitions.')
        ->assertSee('No accumulator candidate satisfies your current planning constraints.');
});
