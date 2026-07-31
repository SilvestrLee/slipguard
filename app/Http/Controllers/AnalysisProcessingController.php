<?php

namespace App\Http\Controllers;

use App\Actions\Analysis\AnalyzeBettingSlip;
use App\Domain\BettingSlip\BettingSlipStatus;
use App\Exceptions\BettingSlipNotAnalysableException;
use App\Models\BettingSlip;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalysisProcessingController extends Controller
{
    public function __invoke(BettingSlip $bettingSlip, AnalyzeBettingSlip $action): StreamedResponse
    {
        $this->authorize('view', $bettingSlip);

        return response()->stream(function () use ($bettingSlip, $action): void {
            $bettingSlip->refresh()->load('legs', 'analysis');

            if ($bettingSlip->analysis) {
                $this->emit(['type' => 'complete', 'report_url' => route('analyze.report', $bettingSlip)]);

                return;
            }

            if ($bettingSlip->status !== BettingSlipStatus::Ready) {
                $this->emitFailure(
                    'validation',
                    __('This slip is not ready for analysis. Review its selections before trying again.'),
                );

                return;
            }

            $stage = 'receiving';
            $lastInternalEvent = 'request_received';
            $facts = $this->initialFacts($bettingSlip);

            try {
                $analysis = $action->execute(
                    $bettingSlip,
                    function (string $event, array $eventFacts = []) use (
                        $bettingSlip,
                        &$facts,
                        &$lastInternalEvent,
                        &$stage,
                    ): void {
                        $lastInternalEvent = $event;
                        $facts = [...$facts, ...$eventFacts];
                        $nextStage = $this->customerStage($event, $stage);

                        if ($nextStage !== $stage
                            || in_array($event, ['normalization_complete', 'structural_evaluation_complete'], true)) {
                            $stage = $nextStage;
                            $this->emitProgress($bettingSlip, $stage, $facts);
                        }
                    },
                );
            } catch (BettingSlipNotAnalysableException $exception) {
                $this->emitFailure(
                    'validation',
                    __('This slip could not be analysed because its required conditions were not met. :reason', [
                        'reason' => implode(' ', $exception->eligibility->messages()),
                    ]),
                );

                return;
            } catch (QueryException $exception) {
                report($exception);
                $this->emitFailure(
                    'persistence',
                    __('The analysis finished, but the risk report could not be saved. Your submitted slip is still available and ready to retry.'),
                );

                return;
            } catch (\Throwable $exception) {
                report($exception);
                $bettingSlip->refresh()->load('analysis');

                if ($bettingSlip->analysis) {
                    $this->emit(['type' => 'complete', 'report_url' => route('analyze.report', $bettingSlip)]);

                    return;
                }

                $persistenceFailure = in_array(
                    $lastInternalEvent,
                    ['report_construction_started', 'persistence_started'],
                    true,
                );

                $this->emitFailure(
                    $persistenceFailure ? 'persistence' : 'processing',
                    $persistenceFailure
                        ? __('The analysis finished, but the risk report could not be saved. Your submitted slip is still available and ready to retry.')
                        : __('SlipGuard could not complete this structural analysis. Your submitted slip has been preserved.'),
                );

                return;
            }

            session()->flash('analysis_completion', [
                'selections_processed' => $analysis->legAnalyses->count(),
                'structural_factors_evaluated' => count($analysis->factor_results),
                'availability' => $analysis->availability->value,
            ]);

            $this->emitProgress($bettingSlip, 'complete', [
                ...$facts,
                'report_availability' => $analysis->availability->value,
            ]);
            $this->emit(['type' => 'complete', 'report_url' => route('analyze.report', $bettingSlip)]);
        }, 200, [
            'Cache-Control' => 'no-cache, no-store',
            'Content-Type' => 'application/x-ndjson',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /** @return array<string, int> */
    private function initialFacts(BettingSlip $bettingSlip): array
    {
        return [
            'selections_received' => $bettingSlip->legs->count(),
            'competitions_recorded' => $bettingSlip->legs->pluck('competition')->filter()->unique()->count(),
            'markets_recorded' => $bettingSlip->legs->pluck('market_name')->filter()->unique()->count(),
        ];
    }

    private function customerStage(string $event, string $current): string
    {
        return match ($event) {
            'request_received' => 'receiving',
            'eligibility_validation_started',
            'selections_validated',
            'normalization_started',
            'normalization_complete' => 'checking',
            'structural_evaluation_started',
            'structural_evaluation_complete' => 'evaluating',
            'report_construction_started',
            'persistence_started' => 'preparing',
            'analysis_complete' => 'complete',
            default => $current,
        };
    }

    /** @param array<string, mixed> $facts */
    private function emitProgress(BettingSlip $bettingSlip, string $stage, array $facts): void
    {
        $this->emit([
            'type' => 'progress',
            'stage' => $stage,
            'html' => view('partials.analysis-processing-status', [
                'bettingSlip' => $bettingSlip,
                'currentStage' => $stage,
                'facts' => $facts,
            ])->render(),
        ]);
    }

    private function emitFailure(string $failureType, string $message): void
    {
        $this->emit([
            'type' => 'failure',
            'failure_type' => $failureType,
            'message' => $message,
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function emit(array $payload): void
    {
        echo json_encode($payload, JSON_THROW_ON_ERROR)."\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
