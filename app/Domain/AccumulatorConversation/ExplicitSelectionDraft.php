<?php

namespace App\Domain\AccumulatorConversation;

/**
 * A single explicit selection extracted from a Class B ("Arsenal to win")
 * message — seed values for the existing manual/guided Builder's leg
 * fields, never a resolved, authoritative fixture. Capability A has no
 * fixture/team database (confirmed during Phase 1 reconnaissance — this
 * is unchanged, deliberate scope, not something this feature adds); the
 * customer completes and confirms every field in the Builder, exactly as
 * every other intake method already requires.
 */
final readonly class ExplicitSelectionDraft
{
    public function __construct(
        public string $eventNameSeed,
        public string $marketName,
        public string $selectionName,
    ) {}
}
