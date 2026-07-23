<?php

namespace App\Console\Commands;

use App\Services\HealthCheckService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('slipguard:health')]
#[Description('Run SlipGuard operational diagnostics')]
class SlipguardHealth extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(HealthCheckService $healthCheckService): int
    {
        $results = $healthCheckService->run();

        $hasFailure = false;
        $hasWarning = false;

        foreach ($results as $check => $result) {
            $hasFailure = $hasFailure || $result['status'] === HealthCheckService::STATUS_FAIL;
            $hasWarning = $hasWarning || $result['status'] === HealthCheckService::STATUS_WARNING;

            $this->line(sprintf(
                '[%s] %s - %s',
                strtoupper($result['status']),
                $check,
                $result['message'],
            ));
        }

        $overall = match (true) {
            $hasFailure => 'FAIL',
            $hasWarning => 'WARNING',
            default => 'PASS',
        };

        $this->newLine();
        $this->line("Overall: {$overall}");

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
