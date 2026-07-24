<?php

namespace App\Console\Commands;

use App\Domain\Risk\Normalization\NormalizeBettingSlip;
use App\Domain\Risk\Taxonomy\FootballMarketTaxonomyV1;
use App\Exceptions\BettingSlipNotReadyException;
use App\Models\BettingSlip;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('slipguard:normalize-slip {bettingSlip : The BettingSlip ID}')]
#[Description('Developer diagnostic: show the normalized taxonomy result for a Ready slip')]
class SlipguardNormalizeSlip extends Command
{
    public function handle(NormalizeBettingSlip $normalizeBettingSlip): int
    {
        $bettingSlip = BettingSlip::with('legs')->find($this->argument('bettingSlip'));

        if (! $bettingSlip) {
            $this->error('No betting slip found with that ID.');

            return self::FAILURE;
        }

        try {
            $normalized = $normalizeBettingSlip->execute($bettingSlip);
        } catch (BettingSlipNotReadyException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Taxonomy version: {$normalized->taxonomyVersion} (football market taxonomy ".FootballMarketTaxonomyV1::VERSION.')');
        $this->newLine();

        $rows = array_map(function ($leg) {
            return [
                $leg->displayOrder,
                $leg->sport->rawInput,
                $leg->sport->sportCode ?? '—',
                $leg->sport->status->value,
                $leg->market->rawMarketInput,
                $leg->market->marketCode ?? '—',
                $leg->market->complexity->value,
                $leg->market->status->value,
                json_encode($leg->market->selectionFacts),
            ];
        }, $normalized->legs);

        $this->table(
            ['#', 'Sport (raw)', 'Sport Code', 'Sport Status', 'Market (raw)', 'Market Code', 'Complexity', 'Market Status', 'Selection Facts'],
            $rows,
        );

        return self::SUCCESS;
    }
}
