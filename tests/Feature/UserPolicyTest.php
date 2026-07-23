<?php

use App\Models\User;

test('a user may view and update their own profile', function () {
    $user = User::factory()->create();

    expect($user->can('view', $user))->toBeTrue();
    expect($user->can('update', $user))->toBeTrue();
});

test('a user may not view or update another users profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    expect($user->can('view', $other))->toBeFalse();
    expect($user->can('update', $other))->toBeFalse();
});
