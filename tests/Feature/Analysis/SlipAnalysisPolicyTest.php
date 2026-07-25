<?php

use App\Models\SlipAnalysis;
use App\Models\User;

test('the owner can view their own slip analysis', function () {
    $analysis = SlipAnalysis::factory()->create();

    expect($analysis->user->can('view', $analysis))->toBeTrue();
});

test('another user cannot view someone elses slip analysis', function () {
    $analysis = SlipAnalysis::factory()->create();
    $intruder = User::factory()->create();

    expect($intruder->can('view', $analysis))->toBeFalse();
});
