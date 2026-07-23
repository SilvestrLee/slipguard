<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('defaults new users to not internal via the factory', function () {
    $user = User::factory()->create();

    expect($user->is_internal)->toBeFalse();
});

it('defaults the is_internal column to false at the database level', function () {
    $id = DB::table('users')->insertGetId([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $record = DB::table('users')->find($id);

    expect((bool) $record->is_internal)->toBeFalse();
});
