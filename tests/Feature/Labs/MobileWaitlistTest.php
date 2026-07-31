<?php

use App\Actions\Labs\RecordMobileWaitlistInterest;
use App\Domain\Labs\LabsFeatureStatus;
use App\Domain\Labs\LabsInterestType;
use App\Models\LabsFeature;
use App\Models\LabsFeatureInterest;
use App\Models\User;
use Database\Seeders\LabsMobileAppFeatureSeeder;
use Livewire\Volt\Volt;

/**
 * `PO-U19.1-001` — proves the guest-capable waitlist entry point works
 * without touching the existing, authenticated-only Labs page behaviour
 * (`toggleInterest()`'s own `abort_unless(Auth::check(), 401)` is
 * unaffected by anything here — separately tested elsewhere).
 */
test('the mobile app labs feature seeds with the approved commission copy', function () {
    $feature = (new LabsMobileAppFeatureSeeder)->run();

    expect($feature->slug)->toBe('native-mobile-apps')
        ->and($feature->status)->toBe(LabsFeatureStatus::InDevelopment)
        ->and($feature->is_published)->toBeTrue()
        ->and($feature->notify_enabled)->toBeTrue()
        ->and($feature->beta_enabled)->toBeFalse();
});

test('the seeder is idempotent, not creating a duplicate feature on re-run', function () {
    (new LabsMobileAppFeatureSeeder)->run();
    (new LabsMobileAppFeatureSeeder)->run();

    expect(LabsFeature::where('slug', 'native-mobile-apps')->count())->toBe(1);
});

test('a guest can join the waitlist with just an email', function () {
    $feature = (new LabsMobileAppFeatureSeeder)->run();

    $interest = (new RecordMobileWaitlistInterest)->execute($feature, null, 'guest@example.com', null);

    expect($interest->user_id)->toBeNull()
        ->and($interest->email)->toBe('guest@example.com')
        ->and($interest->type)->toBe(LabsInterestType::Notify)
        ->and($interest->platform_preference)->toBeNull();
});

test('a guest may optionally record a platform preference', function () {
    $feature = (new LabsMobileAppFeatureSeeder)->run();

    $interest = (new RecordMobileWaitlistInterest)->execute($feature, null, 'guest@example.com', 'iphone');

    expect($interest->platform_preference)->toBe('iphone');
});

test('an authenticated user joins by user_id, never asked for their email', function () {
    $feature = (new LabsMobileAppFeatureSeeder)->run();
    $user = User::factory()->create();

    $interest = (new RecordMobileWaitlistInterest)->execute($feature, $user, null, 'android');

    expect($interest->user_id)->toBe($user->id)
        ->and($interest->email)->toBeNull()
        ->and($interest->platform_preference)->toBe('android');
});

test('resubmitting the same guest email updates the platform preference rather than duplicating the row', function () {
    $feature = (new LabsMobileAppFeatureSeeder)->run();

    (new RecordMobileWaitlistInterest)->execute($feature, null, 'guest@example.com', 'iphone');
    (new RecordMobileWaitlistInterest)->execute($feature, null, 'guest@example.com', 'both');

    expect(LabsFeatureInterest::where('email', 'guest@example.com')->count())->toBe(1)
        ->and(LabsFeatureInterest::where('email', 'guest@example.com')->first()->platform_preference)->toBe('both');
});

test('the existing authenticated-only Labs page interest toggle still requires authentication, unaffected by the new nullable columns', function () {
    $feature = (new LabsMobileAppFeatureSeeder)->run();

    Volt::test('labs.index')
        ->call('toggleInterest', $feature->id, 'notify')
        ->assertUnauthorized();
});

test('an authenticated user who joins via the homepage waitlist is correctly reflected as already notified on the Labs page', function () {
    // Both entry points write the same (user_id, labs_feature_id, type)
    // shape into the same table — a real, useful consequence of reusing
    // the Labs model rather than a coincidence this test only confirms.
    $feature = (new LabsMobileAppFeatureSeeder)->run();
    $user = User::factory()->create();

    (new RecordMobileWaitlistInterest)->execute($feature, $user, null, 'both');

    Volt::actingAs($user)->test('labs.index')
        ->assertSee('Notified');
});
