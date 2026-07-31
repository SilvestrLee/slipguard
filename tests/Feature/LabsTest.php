<?php

use App\Domain\Labs\LabsInterestType;
use App\Models\LabsFeature;
use App\Models\LabsFeatureInterest;
use App\Models\User;
use Livewire\Volt\Volt;

test('the labs page shows only published features', function () {
    $user = User::factory()->create();
    $published = LabsFeature::factory()->create(['title' => 'Published Feature']);
    LabsFeature::factory()->unpublished()->create(['title' => 'Hidden Feature']);

    $this->actingAs($user)
        ->get(route('labs'))
        ->assertOk()
        ->assertSee('Published Feature')
        ->assertDontSee('Hidden Feature');
});

test('the labs page shows the empty state when nothing is published', function () {
    $user = User::factory()->create();
    LabsFeature::factory()->unpublished()->create();

    $this->actingAs($user)
        ->get(route('labs'))
        ->assertOk()
        ->assertSee('Nothing published yet.');
});

test('a customer can register and withdraw Notify Me interest', function () {
    $user = User::factory()->create();
    $feature = LabsFeature::factory()->create(['notify_enabled' => true]);

    $this->actingAs($user);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify')
        ->assertOk();

    expect(LabsFeatureInterest::where([
        'user_id' => $user->id,
        'labs_feature_id' => $feature->id,
        'type' => LabsInterestType::Notify,
    ])->exists())->toBeTrue();

    $this->actingAs($user);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify');

    expect(LabsFeatureInterest::where([
        'user_id' => $user->id,
        'labs_feature_id' => $feature->id,
        'type' => LabsInterestType::Notify,
    ])->exists())->toBeFalse();
});

test('a customer can register Join Beta interest independently of Notify Me', function () {
    $user = User::factory()->create();
    $feature = LabsFeature::factory()->create(['notify_enabled' => true, 'beta_enabled' => true]);

    $this->actingAs($user);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify')
        ->call('toggleInterest', $feature->id, 'beta');

    expect(LabsFeatureInterest::where(['user_id' => $user->id, 'labs_feature_id' => $feature->id])->count())->toBe(2);
});

test('toggling interest is rejected when the feature has that action disabled', function () {
    $user = User::factory()->create();
    $feature = LabsFeature::factory()->create(['notify_enabled' => false]);

    $this->actingAs($user);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify')
        ->assertForbidden();

    expect(LabsFeatureInterest::count())->toBe(0);
});

test('toggling interest is rejected for an unpublished feature', function () {
    $user = User::factory()->create();
    $feature = LabsFeature::factory()->unpublished()->create(['notify_enabled' => true]);

    $this->actingAs($user);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify')
        ->assertStatus(404);
});

test('a customer only ever toggles their own interest, never another user\'s', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $feature = LabsFeature::factory()->create(['notify_enabled' => true]);

    LabsFeatureInterest::factory()->create([
        'user_id' => $otherUser->id,
        'labs_feature_id' => $feature->id,
        'type' => LabsInterestType::Notify,
    ]);

    $this->actingAs($user);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify');

    expect(LabsFeatureInterest::where('user_id', $otherUser->id)->count())->toBe(1)
        ->and(LabsFeatureInterest::where('user_id', $user->id)->count())->toBe(1);
});

test('the navigation includes SlipGuard Labs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('SlipGuard Labs');
});

/**
 * U-14.2 — SlipGuard Labs is now guest-visible (read-only). Guests see
 * published features and status, but never an interest-toggle button —
 * only a "sign in" link — and the underlying action itself remains
 * blocked, defence in depth, since `labs_feature_interests.user_id` is
 * not nullable.
 */
test('guests see published Labs features but no interest-toggle buttons, only a sign-in link', function () {
    LabsFeature::factory()->create(['title' => 'Guest Visible Feature', 'notify_enabled' => true]);

    $this->get(route('labs'))
        ->assertOk()
        ->assertSee('Guest Visible Feature')
        ->assertSee('Sign in to register interest')
        ->assertDontSee('wire:click="toggleInterest', false);
});

test('guests cannot register Labs interest even by calling the action directly', function () {
    $feature = LabsFeature::factory()->create(['notify_enabled' => true]);

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify')
        ->assertStatus(401);

    expect(LabsFeatureInterest::count())->toBe(0);
});
