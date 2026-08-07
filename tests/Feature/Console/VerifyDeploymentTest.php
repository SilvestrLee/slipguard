<?php

use App\Models\BettingSlip;
use App\Models\User;

test('slipguard:verify-deployment passes and leaves zero residue', function () {
    $usersBefore = User::count();
    $slipsBefore = BettingSlip::count();

    $this->artisan('slipguard:verify-deployment')
        ->expectsOutputToContain('[OK] Database connection')
        ->expectsOutputToContain('[OK] Migrations table present')
        ->expectsOutputToContain('Deployment verification passed.')
        ->assertSuccessful();

    expect(User::count())->toBe($usersBefore)
        ->and(BettingSlip::count())->toBe($slipsBefore);
});
