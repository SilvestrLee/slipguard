<?php

namespace App\Domain\Contact;

/**
 * `PO-U22-001`'s own fixed category vocabulary for the Contact form —
 * every submission carries exactly one of these, never a free-text
 * category.
 */
enum ContactMessageCategory: string
{
    case GeneralEnquiry = 'general_enquiry';
    case TechnicalSupport = 'technical_support';
    case Billing = 'billing';
    case Partnership = 'partnership';
    case Feedback = 'feedback';
    case BugReport = 'bug_report';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::GeneralEnquiry => 'General Enquiry',
            self::TechnicalSupport => 'Technical Support',
            self::Billing => 'Billing',
            self::Partnership => 'Partnership',
            self::Feedback => 'Feedback',
            self::BugReport => 'Bug Report',
            self::Other => 'Other',
        };
    }
}
