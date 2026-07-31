<?php

namespace App\Domain\BettingSlip\Intake;

use Smalot\PdfParser\Parser;
use Throwable;

/**
 * Bounded-scope PDF Upload (2026-07-28): extracts real, selectable text
 * only — no OCR, no image rendering. A scanned/image-only PDF (or any
 * file the parser can't read) legitimately produces empty text; the
 * caller must treat that as an honest "could not extract text" outcome,
 * never silently proceed as if nothing were wrong.
 */
class ExtractPdfText
{
    public function execute(string $absolutePath): string
    {
        try {
            $pdf = (new Parser)->parseFile($absolutePath);

            return trim($pdf->getText());
        } catch (Throwable) {
            // A corrupt, encrypted, or otherwise unparseable PDF is the
            // same honest "nothing extracted" outcome as a scanned image —
            // never surfaced as a generic error to the customer.
            return '';
        }
    }
}
