<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('promotes an existing user to internal', function () {
    $user = User::factory()->create(['email' => 'staff@example.com']);

    $exitCode = Artisan::call('slipguard:make-internal-user', ['email' => 'staff@example.com']);

    expect($exitCode)->toBe(0);
    expect($user->fresh()->is_internal)->toBeTrue();
});

it('fails when the user does not exist', function () {
    $exitCode = Artisan::call('slipguard:make-internal-user', ['email' => 'missing@example.com']);

    expect($exitCode)->toBe(1);
});

it('refuses to run in production without --force', function () {
    $this->app['env'] = 'production';

    $user = User::factory()->create(['email' => 'staff@example.com']);

    $exitCode = Artisan::call('slipguard:make-internal-user', ['email' => 'staff@example.com']);

    expect($exitCode)->toBe(1);
    expect($user->fresh()->is_internal)->toBeFalse();
});

it('promotes a user in production when --force is passed', function () {
    $this->app['env'] = 'production';

    $user = User::factory()->create(['email' => 'staff@example.com']);

    $exitCode = Artisan::call('slipguard:make-internal-user', [
        'email' => 'staff@example.com',
        '--force' => true,
    ]);

    expect($exitCode)->toBe(0);
    expect($user->fresh()->is_internal)->toBeTrue();
});
