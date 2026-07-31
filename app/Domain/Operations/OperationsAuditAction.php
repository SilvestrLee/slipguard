<?php

namespace App\Domain\Operations;

/**
 * U-10.2 §5: `action` is a fixed, enumerated set of codes, never free
 * text, so the audit trail stays queryable and consistent — mirrors how
 * `ReasonCode` is maintained as a living but structured list.
 */
enum OperationsAuditAction: string
{
    case CustomerViewed = 'customer_viewed';
    case JournalViewed = 'journal_viewed';
    case SupportNoteCreated = 'support_note_created';

    public function label(): string
    {
        return match ($this) {
            self::CustomerViewed => 'Customer record viewed',
            self::JournalViewed => 'Journal entries viewed',
            self::SupportNoteCreated => 'Support note created',
        };
    }
}
