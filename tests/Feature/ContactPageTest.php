<?php

use App\Domain\Contact\ContactMessageCategory;
use App\Models\ContactMessage;
use Livewire\Volt\Volt;

/**
 * `PO-U22-001` — the Contact page. Placeholder-handling decision (raised
 * via `AskUserQuestion`, resolved by the founder): fabricated business
 * specifics (address, phone) are never presented as real — every such
 * fact is visibly marked "to be confirmed," matching the same panel
 * `terms.blade.php`/`privacy.blade.php` already use for their own
 * pending-legal-review sections. Social handles (all platforms,
 * username "slipguardhq") were separately confirmed real by the founder
 * and are live links.
 */
test('the page is guest-accessible and does not claim unconfirmed business facts as real', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee("Let's talk.")
        ->assertSee('To be confirmed before launch')
        ->assertSee('Address to be confirmed')
        ->assertDontSee('14 Innovation Avenue')
        ->assertDontSee('+234 800 123 4567')
        ->assertDontSee('SlipGuard Technologies');
});

test('every FAQ answer matches real, current product behaviour', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Do I need an account?')
        ->assertSee('Does SlipGuard place bets?')
        ->assertSee('No. SlipGuard provides deterministic structural analysis only')
        ->assertSee('Can I upload a screenshot of my slip?')
        ->assertSee('type it in, paste copied text, or upload a PDF or screenshot');
});

test('all seven confirmed social handles render as live links to the real username', function () {
    $response = $this->get(route('contact'))->assertOk();

    foreach (['linkedin.com/company', 'x.com', 'instagram.com', 'facebook.com', 'tiktok.com', 'threads.net', 'youtube.com'] as $host) {
        $response->assertSee($host, false);
    }

    // Every profile uses the same confirmed username, never a guess.
    expect(substr_count($response->getContent(), 'slipguardhq'))->toBeGreaterThanOrEqual(7);
});

test('submitting the form with valid data persists a message and shows a success state', function () {
    Volt::test('contact')
        ->set('fullName', 'Tunde Adeyemi')
        ->set('email', 'tunde@example.com')
        ->set('subject', 'Question about the Planner')
        ->set('category', ContactMessageCategory::TechnicalSupport->value)
        ->set('message', 'How does the Planner decide which leg is weakest?')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->assertSet('fullName', '')
        ->assertSee("We'll get back to you");

    $message = ContactMessage::sole();

    expect($message->full_name)->toBe('Tunde Adeyemi')
        ->and($message->email)->toBe('tunde@example.com')
        ->and($message->category)->toBe(ContactMessageCategory::TechnicalSupport)
        ->and($message->message)->toBe('How does the Planner decide which leg is weakest?');
});

test('the form requires every field and rejects an invalid email', function () {
    Volt::test('contact')
        ->set('fullName', '')
        ->set('email', 'not-an-email')
        ->set('subject', '')
        ->set('category', '')
        ->set('message', 'short')
        ->call('send')
        ->assertHasErrors(['fullName', 'email', 'subject', 'category', 'message']);

    expect(ContactMessage::count())->toBe(0);
});

test('the category must be one of the fixed, documented values', function () {
    Volt::test('contact')
        ->set('fullName', 'Tunde Adeyemi')
        ->set('email', 'tunde@example.com')
        ->set('subject', 'Test')
        ->set('category', 'not-a-real-category')
        ->set('message', 'A message long enough to pass validation.')
        ->call('send')
        ->assertHasErrors(['category']);

    expect(ContactMessage::count())->toBe(0);
});
