<?php

use App\Models\User;

it('denies guests access to the operations panel', function () {
    $this->get('/operations')->assertRedirect('/operations/login');
});

it('denies non-internal users access to the operations panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/operations')
        ->assertForbidden();
});

it('allows internal users to access the operations panel', function () {
    $user = User::factory()->internal()->create();

    $this->actingAs($user)
        ->get('/operations')
        ->assertOk();
});
