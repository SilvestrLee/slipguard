<?php

namespace App\Domain\Contact;

/**
 * `PO-U22-001A` — the minimal workflow the directive itself specifies:
 * New → Read → Resolved. No richer status vocabulary invented (no
 * "In Progress," no "Escalated") — matching the directive's own explicit
 * "Minimal workflow only" instruction.
 */
enum ContactMessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Read => 'Read',
            self::Resolved => 'Resolved',
        };
    }

    /** Filament badge colour — semantic, not decorative (matches this codebase's own token discipline for status colour). */
    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Read => 'gray',
            self::Resolved => 'success',
        };
    }
}
