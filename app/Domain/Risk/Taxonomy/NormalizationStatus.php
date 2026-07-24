<?php

namespace App\Domain\Risk\Taxonomy;

enum NormalizationStatus: string
{
    /**
     * Every required supported field was deterministically classified.
     */
    case Complete = 'complete';

    /**
     * The sport or market family was recognized, but one or more secondary
     * facts (e.g. a total-goals line) could not be parsed.
     */
    case Partial = 'partial';

    /**
     * The input does not match anything in the approved alias catalogue.
     */
    case Unrecognized = 'unrecognized';

    /**
     * The input is understood at a broad level (a real, named sport) but is
     * intentionally outside this taxonomy version's support — e.g. Tennis.
     */
    case Unsupported = 'unsupported';
}
