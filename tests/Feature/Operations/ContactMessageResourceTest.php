<?php

use App\Actions\Contact\SubmitContactMessage;
use App\Domain\Contact\ContactMessageCategory;
use App\Domain\Contact\ContactMessageStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Models\ContactMessage;
use App\Models\User;
use Livewire\Livewire;

/**
 * `PO-U22-001A` — the Contact Messages operations resource. Gated on
 * `is_internal` alone (not `CustomerResource`'s narrower
 * `can_manage_customer_data`) — a contact message is a general inbound
 * enquiry, not customer analysis data, so any internal staff member may
 * triage it. Explicitly tested below, not assumed, since it's a real
 * authorization boundary decision, not a default.
 */
test('a guest cannot access the Contact Messages resource', function () {
    $this->get('/operations/contact-messages')->assertRedirect();
});

test('a customer (non-internal user) cannot access the Contact Messages resource', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/operations/contact-messages')
        ->assertForbidden();
});

test('any internal user can view Contact Messages, even without the narrower can_manage_customer_data grant', function () {
    $staff = User::factory()->internal()->create();
    ContactMessage::factory()->create(['full_name' => 'Tunde Adeyemi', 'subject' => 'Question about the Planner']);

    $this->actingAs($staff)
        ->get('/operations/contact-messages')
        ->assertOk()
        ->assertSee('Tunde Adeyemi')
        ->assertSee('Question about the Planner');
});

test('a public submission persists and is immediately visible to staff', function () {
    $staff = User::factory()->internal()->create();

    // Mirrors what SubmitContactMessage actually does — the real action,
    // not a hand-rolled insert, so this test proves the real end-to-end
    // path a visitor's submission takes.
    (new SubmitContactMessage)->execute(
        'Ada Okafor', 'ada@example.com', 'Partnership enquiry',
        ContactMessageCategory::Partnership, 'We would like to discuss a partnership.',
    );

    $this->actingAs($staff)
        ->get('/operations/contact-messages')
        ->assertOk()
        ->assertSee('Ada Okafor')
        ->assertSee('Partnership enquiry');
});

test('opening a New message marks it Read, mirroring an ordinary inbox', function () {
    $staff = User::factory()->internal()->create();
    $message = ContactMessage::factory()->create(['status' => ContactMessageStatus::New]);

    $this->actingAs($staff)->get("/operations/contact-messages/{$message->id}")->assertOk();

    expect($message->fresh()->status)->toBe(ContactMessageStatus::Read);
});

test('marking a message Resolved from the list persists the status change', function () {
    $staff = User::factory()->internal()->create();
    $message = ContactMessage::factory()->create(['status' => ContactMessageStatus::Read]);

    Livewire::actingAs($staff)
        ->test(ListContactMessages::class)
        ->callTableAction('markResolved', $message);

    expect($message->fresh()->status)->toBe(ContactMessageStatus::Resolved);
});

test('a Resolved message can be reopened from the View page', function () {
    $staff = User::factory()->internal()->create();
    $message = ContactMessage::factory()->create(['status' => ContactMessageStatus::Resolved]);

    Livewire::actingAs($staff)
        ->test(ViewContactMessage::class, ['record' => $message->id])
        ->callAction('reopen');

    expect($message->fresh()->status)->toBe(ContactMessageStatus::Read);
});

test('staff cannot create or edit a contact message — triage only, never authoring or rewriting a submission', function () {
    expect(ContactMessageResource::canCreate())->toBeFalse();

    $message = ContactMessage::factory()->create();
    expect(ContactMessageResource::canEdit($message))->toBeFalse()
        ->and(ContactMessageResource::canDelete($message))->toBeFalse();
});

test('the status filter narrows the list to matching submissions only', function () {
    $staff = User::factory()->internal()->create();
    $resolved = ContactMessage::factory()->create(['full_name' => 'Resolved Person', 'status' => ContactMessageStatus::Resolved]);
    $new = ContactMessage::factory()->create(['full_name' => 'New Person', 'status' => ContactMessageStatus::New]);

    Livewire::actingAs($staff)
        ->test(ListContactMessages::class)
        ->filterTable('status', ContactMessageStatus::Resolved->value)
        ->assertCanSeeTableRecords([$resolved])
        ->assertCanNotSeeTableRecords([$new]);
});
