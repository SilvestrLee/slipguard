<?php

namespace App\Actions\BettingSlip;

use App\Exceptions\BettingSlipNotEditableException;
use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveBettingSlip
{
    /**
     * Create or update a slip and replace its legs in a single transaction.
     * Refuses to touch a slip that has left Draft — once a slip is Ready,
     * Analysed, or Archived, its legs are immutable input.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $legs
     */
    public function execute(User $user, ?BettingSlip $bettingSlip, array $attributes, array $legs): BettingSlip
    {
        if ($bettingSlip && ! $bettingSlip->isEditable()) {
            throw new BettingSlipNotEditableException;
        }

        return DB::transaction(function () use ($user, $bettingSlip, $attributes, $legs) {
            if (! $bettingSlip) {
                $bettingSlip = new BettingSlip;
                $bettingSlip->user_id = $user->id;
            }

            $bettingSlip->fill($attributes);
            $bettingSlip->save();

            $bettingSlip->legs()->delete();

            foreach (array_values($legs) as $index => $leg) {
                $bettingSlip->legs()->create([...$leg, 'display_order' => $index]);
            }

            return $bettingSlip->fresh('legs');
        });
    }
}
