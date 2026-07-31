<?php

use App\Domain\Labs\LabsFeatureStatus;
use App\Models\LabsFeature;
use App\Models\User;

test('denies non-internal users access to the Labs feature admin resource', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/operations/labs-features')
        ->assertForbidden();
});

test('allows internal users to manage Labs features', function () {
    $user = User::factory()->internal()->create();
    $feature = LabsFeature::factory()->create(['title' => 'Smart Bet Transfer', 'status' => LabsFeatureStatus::Planned]);

    $this->actingAs($user)
        ->get('/operations/labs-features')
        ->assertOk()
        ->assertSee('Smart Bet Transfer');
});
