<?php

namespace App\Actions\Contact;

use App\Domain\Contact\ContactMessageCategory;
use App\Models\ContactMessage;

/**
 * `PO-U22-001` — persists a Contact form submission. No email is sent:
 * this codebase has no Mailable class anywhere and `MAIL_MAILER` is
 * `log`, not a real delivery channel, so persistence is the honest
 * minimum that doesn't silently discard what a visitor wrote. Routing
 * a persisted message to the right inbox/team is a separate, not-yet-
 * commissioned piece of work — see the completion report.
 */
class SubmitContactMessage
{
    public function execute(string $fullName, string $email, string $subject, ContactMessageCategory $category, string $message): ContactMessage
    {
        return ContactMessage::create([
            'full_name' => $fullName,
            'email' => $email,
            'subject' => $subject,
            'category' => $category,
            'message' => $message,
        ]);
    }
}
