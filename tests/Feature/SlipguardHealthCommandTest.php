<?php

use Illuminate\Support\Facades\Artisan;

it('exits successfully when no check fails', function () {
    $exitCode = Artisan::call('slipguard:health');

    expect($exitCode)->toBe(0);
    expect(Artisan::output())->not->toContain('Overall: FAIL');
});

it('never leaks the application key value', function () {
    Artisan::call('slipguard:health');

    expect(Artisan::output())->not->toContain(config('app.key'));
});
