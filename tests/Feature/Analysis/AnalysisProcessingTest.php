<?php

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Domain\Risk\Results\AnalysisAvailability;
use App\Models\BettingSlip;
use App\Models\User;

function processingLeg(array $overrides = []): array
{
    return array_merge([
        'sport' => 'Football',
        'competition' => 'Premier League',
        'event_name' => 'Arsenal vs Chelsea',
        'market_name' => 'Match Result',
        'selection_name' => 'Arsenal to win',
        'decimal_odds' => '1.90',
        'display_order' => 0,
    ], $overrides);
}

function processingSlip(User $user, array $legs): BettingSlip
{
    $slip = BettingSlip::factory()->for($user)->create();

    foreach ($legs as $index => $attributes) {
        $slip->legs()->create(processingLeg(['display_order' => $index, ...$attributes]));
    }

    $slip->markReady();

    return $slip->fresh('legs');
}

function processingEvents($testCase, User $user, BettingSlip $slip): array
{
    $content = $testCase->actingAs($user)
        ->post(route('analyze.processing.run', $slip))
        ->assertOk()
        ->streamedContent();

    return collect(explode("\n", trim($content)))
        ->filter()
        ->map(fn (string $line) => json_decode($line, true, flags: JSON_THROW_ON_ERROR))
        ->values()
        ->all();
}

test('processing routes require authentication and enforce slip ownership', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $slip = processingSlip($owner, [processingLeg()]);

    $this->get(route('analyze.processing', $slip))
        ->assertRedirect(route('login'));

    $this->actingAs($otherUser)
        ->get(route('analyze.processing', $slip))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->post(route('analyze.processing.run', $slip))
        ->assertForbidden();
});

test('an already completed slip bypasses processing and opens its persisted report', function () {
    $user = User::factory()->create();
    $slip = processingSlip($user, [processingLeg()]);
    (new AnalyzeBettingSlip)->execute($slip);

    $this->actingAs($user)
        ->get(route('analyze.processing', $slip))
        ->assertRedirect(route('analyze.report', $slip));
});

test('validation failure preserves the submitted Ready slip and offers recovery', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => BettingSlipStatus::Ready]);

    $events = processingEvents($this, $user, $slip);

    $failure = collect($events)->last();

    expect($failure['failure_type'])->toBe('validation')
        ->and($failure['message'])->toContain('required conditions were not met');

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready)
        ->and($slip->fresh()->analysis)->toBeNull();
});

test('a processing failure is recoverable and a retry can complete without losing the slip', function () {
    $user = User::factory()->create();
    $slip = processingSlip($user, [processingLeg()]);
    $realAction = new AnalyzeBettingSlip;
    $attempt = 0;

    $action = Mockery::mock(AnalyzeBettingSlip::class);
    $action->shouldReceive('execute')->twice()->andReturnUsing(
        function (BettingSlip $candidate, ?Closure $progress = null) use (&$attempt, $realAction) {
            $attempt++;

            if ($attempt === 1) {
                $progress?->__invoke('structural_evaluation_started', []);
                throw new RuntimeException('Internal diagnostic detail');
            }

            return $realAction->execute($candidate, $progress);
        },
    );
    app()->instance(AnalyzeBettingSlip::class, $action);

    $events = processingEvents($this, $user, $slip);

    $failure = collect($events)->last();

    expect($failure['failure_type'])->toBe('processing')
        ->and($failure['message'])->toContain('submitted slip has been preserved')
        ->and(json_encode($events))->not->toContain('Internal diagnostic detail');

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready)
        ->and($slip->fresh()->analysis)->toBeNull();

    $retryEvents = processingEvents($this, $user, $slip);

    expect(collect($retryEvents)->last()['report_url'])->toBe(route('analyze.report', $slip));

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Analysed)
        ->and($slip->fresh()->analysis)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('analyze.report', $slip))
        ->assertOk()
        ->assertSee('1 selection processed')
        ->assertSee('6 structural factors evaluated')
        ->assertSee('Report prepared');
});

test('a persistence-stage failure is distinguished and keeps the Ready slip retryable', function () {
    $user = User::factory()->create();
    $slip = processingSlip($user, [processingLeg()]);

    $action = Mockery::mock(AnalyzeBettingSlip::class);
    $action->shouldReceive('execute')->once()->andReturnUsing(
        function (BettingSlip $candidate, ?Closure $progress = null) {
            $progress?->__invoke('persistence_started', []);
            throw new RuntimeException('Database host detail');
        },
    );
    app()->instance(AnalyzeBettingSlip::class, $action);

    $events = processingEvents($this, $user, $slip);

    $failure = collect($events)->last();

    expect($failure['failure_type'])->toBe('persistence')
        ->and($failure['message'])->toContain('ready to retry')
        ->and(json_encode($events))->not->toContain('Database host detail');

    expect($slip->fresh()->status)->toBe(BettingSlipStatus::Ready)
        ->and($slip->fresh()->analysis)->toBeNull();
});

test('Limited and Unavailable analyses both transition into their persisted reports', function (string $expectedAvailability, array $legs) {
    $user = User::factory()->create();
    $slip = processingSlip($user, $legs);

    $events = processingEvents($this, $user, $slip);

    expect(collect($events)->last()['report_url'])->toBe(route('analyze.report', $slip));

    expect($slip->fresh()->analysis->availability)->toBe(AnalysisAvailability::from($expectedAvailability));
})->with([
    'limited' => [
        AnalysisAvailability::Limited->value,
        [
            processingLeg(['event_name' => 'Fixture 1']),
            processingLeg(['event_name' => 'Fixture 2']),
            processingLeg(['event_name' => 'Fixture 3']),
            processingLeg(['event_name' => 'Fixture 4']),
            processingLeg([
                'event_name' => 'Fixture 5',
                'market_name' => 'First Goal Between 10 and 20 Minutes',
                'selection_name' => 'Yes',
            ]),
        ],
    ],
    'unavailable' => [
        AnalysisAvailability::Unavailable->value,
        [
            processingLeg([
                'sport' => 'Tennis',
                'event_name' => 'Djokovic vs Alcaraz',
                'market_name' => 'Match Winner',
                'selection_name' => 'Djokovic',
            ]),
        ],
    ],
]);
