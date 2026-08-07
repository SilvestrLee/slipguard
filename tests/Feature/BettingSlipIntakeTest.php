<?php

use App\Models\BettingSlip;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

/**
 * U-11.3 §8-15/§20: the multi-method intake shell.
 *
 * Bounded-scope intake (2026-07-28, founder direct instruction): Paste
 * Text and PDF Upload now run a real, deterministic text parser and
 * converge on the same `SaveBettingSlip` action and Builder review
 * screen every other intake method already uses. Screenshot Upload
 * accepts and stores the image (no OCR — explicitly out of scope) and
 * hands the customer to the Builder with the image shown alongside the
 * manual entry form. Bet Code remains deferred and unchanged.
 */
test('guests are redirected to login', function () {
    $this->get(route('analyze.intake'))->assertRedirect(route('login'));
});

test('the intake shell lists every method and Manual Entry links straight to the builder', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analyze.intake'))
        ->assertOk()
        ->assertSee('Enter Manually')
        ->assertSee('Upload Screenshot')
        ->assertSee('Paste Slip Text')
        ->assertSee('Upload PDF')
        ->assertSee('Bet Code or Share Link')
        ->assertSee(route('analyze.create'), false);
});

test('selecting screenshot reveals a real file input', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'screenshot')
        ->assertSee('Choose a file, or drag and drop');
});

test('dashboard intake links can open the requested upload method directly', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analyze.intake', ['method' => 'pdf']))
        ->assertOk()
        ->assertSee('Choose a PDF file');
});

test('the dashboard Bet Code link opens the honest unavailable state directly', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analyze.intake', ['method' => 'betcode']))
        ->assertOk()
        ->assertSee('Future capability')
        ->assertSee('There is nothing to paste or check yet.')
        ->assertDontSee('Paste a bet code or share link', false);
});

test('selecting paste text reveals a textarea', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'paste')
        ->assertSee('Paste the copied slip text here', false);
});

test('selecting PDF reveals a real file input', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'pdf')
        ->assertSee('Choose a PDF file');
});

test('selecting bet code or share link immediately presents it as a future capability', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'betcode')
        ->assertSee('Coming soon')
        ->assertSee('Future capability')
        ->assertSee('There is nothing to paste or check yet.')
        ->assertDontSee('Paste a bet code or share link', false)
        ->assertSee('No supported bookmaker bet-code or share-link formats are recognised yet')
        ->assertSee(route('analyze.create'), false);
});

test('switching methods clears the previous method\'s screen', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'paste')
        ->assertSee('Paste the copied slip text here', false)
        ->call('selectMethod', 'pdf')
        ->assertDontSee('Paste the copied slip text here', false)
        ->assertSee('Choose a PDF file');
});

test('the betting slips index New Slip button routes to the intake shell', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analyze'))
        ->assertOk()
        ->assertSee(route('analyze.intake'), false);
});

test('ready slips enter a dedicated truthful processing workspace without fake progress', function () {
    $user = User::factory()->create();
    $slip = BettingSlip::factory()->for($user)->create(['status' => \App\Domain\BettingSlip\BettingSlipStatus::Ready]);

    $this->actingAs($user)->get(route('analyze'))
        ->assertOk()
        ->assertSee(route('analyze.processing', $slip), false)
        ->assertDontSee('Reading the submitted slip')
        ->assertDontSee('92%');

    $this->actingAs($user)->get(route('analyze.processing', $slip))
        ->assertOk()
        ->assertSee('Receiving your slip')
        ->assertSee('Checking your selections')
        ->assertSee('Evaluating structural risk')
        ->assertSee('Preparing your report')
        ->assertSee('Analysis complete')
        ->assertSee('aria-live="polite"', false)
        ->assertSee('wire:offline', false)
        ->assertDontSee('92%')
        ->assertDontSee('analysisTimer');
});

test('pasted text is parsed into a draft slip and the customer is sent to the builder to confirm it', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'paste')
        ->set('pastedText', "Chelsea vs Arsenal\nMatch Result\nChelsea Win @1.85\n\nArsenal vs Tottenham\nOver 2.5 Goals @2.10")
        ->call('submitPastedText')
        ->assertRedirect();

    $bettingSlip = BettingSlip::where('user_id', $user->id)->firstOrFail();

    expect($bettingSlip->legs)->toHaveCount(2);
    expect($bettingSlip->legs[0]->event_name)->toBe('Chelsea vs Arsenal');
    expect($bettingSlip->legs[0]->decimal_odds)->toBe('1.85');
    expect($bettingSlip->legs[1]->event_name)->toBe('Arsenal vs Tottenham');
});

test('pasted text with no detectable odds creates a draft slip instead of crashing — a real bug fixed alongside PO-U23-001', function () {
    // `betting_slip_legs.decimal_odds` used to be non-nullable; ParseSlipText's
    // own "not detected" value ('') was never a valid decimal, so this exact
    // input crashed with a raw SQLSTATE error before the column was made
    // nullable. The customer completes the odds themselves in the Builder,
    // same as every other incomplete field this parser already leaves blank.
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'paste')
        ->set('pastedText', 'Just some notes about a slip with no vs pattern or odds at all')
        ->call('submitPastedText')
        ->assertRedirect();

    $bettingSlip = BettingSlip::where('user_id', $user->id)->firstOrFail();

    expect($bettingSlip->legs)->toHaveCount(1)
        ->and($bettingSlip->legs[0]->decimal_odds)->toBeNull();
});

test('pasted text with an empty value fails validation rather than creating an empty slip', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'paste')
        ->set('pastedText', '')
        ->call('submitPastedText')
        ->assertHasErrors(['pastedText']);

    expect(BettingSlip::where('user_id', $user->id)->count())->toBe(0);
});

test('a PDF with real selectable text is parsed into a draft slip and the customer is sent to the builder', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    // A minimal, valid one-page PDF whose content stream literally contains
    // selectable text — real bookmaker PDF exports are more complex, but
    // this proves the extraction + parsing + redirect pipeline end to end
    // without needing a binary fixture file in the repository.
    $pdfContents = pdfWithSelectableText('Chelsea vs Arsenal, Match Result, Chelsea Win @1.85');
    $file = UploadedFile::fake()->createWithContent('slip.pdf', $pdfContents);

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'pdf')
        ->set('pdfFile', $file)
        ->call('submitPdf')
        ->assertRedirect();

    $bettingSlip = BettingSlip::where('user_id', $user->id)->firstOrFail();
    expect($bettingSlip->legs)->toHaveCount(1);
    expect($bettingSlip->legs[0]->event_name)->toBe('Chelsea vs Arsenal');
});

test('a PDF with no extractable text produces an honest failure state instead of a fabricated slip', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    // A syntactically valid but textless PDF (no content stream) — the
    // same honest outcome as a scanned/image-only PDF.
    $file = UploadedFile::fake()->createWithContent('scan.pdf', textlessPdf());

    $component = Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'pdf')
        ->set('pdfFile', $file)
        ->call('submitPdf')
        ->assertSee('No text could be extracted from this PDF')
        ->assertSee('Try Screenshot Upload instead');

    expect($component->get('pdfExtractionFailed'))->toBeTrue();
    expect(BettingSlip::where('user_id', $user->id)->count())->toBe(0);
});

test('an uploaded screenshot is stored on the private disk and the customer is sent to the builder to transcribe it', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('slip.png');

    Volt::actingAs($user)
        ->test('betting-slips.intake')
        ->call('selectMethod', 'screenshot')
        ->set('screenshotFile', $file)
        ->call('submitScreenshot')
        ->assertRedirect();

    $bettingSlip = BettingSlip::where('user_id', $user->id)->firstOrFail();

    expect($bettingSlip->source_screenshot_path)->not->toBeNull();
    expect($bettingSlip->legs)->toHaveCount(0);
    Storage::disk('local')->assertExists($bettingSlip->source_screenshot_path);
});

test('the screenshot route only serves the image to its owner', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $path = UploadedFile::fake()->image('slip.png')->store('betting-slip-screenshots/'.$owner->id, 'local');
    $bettingSlip = BettingSlip::factory()->for($owner)->create(['source_screenshot_path' => $path]);

    $this->actingAs($owner)
        ->get(route('analyze.screenshot', $bettingSlip))
        ->assertOk();

    $this->actingAs($stranger)
        ->get(route('analyze.screenshot', $bettingSlip))
        ->assertForbidden();
});

test('the screenshot route 404s when no screenshot was ever stored', function () {
    $user = User::factory()->create();
    $bettingSlip = BettingSlip::factory()->for($user)->create(['source_screenshot_path' => null]);

    $this->actingAs($user)
        ->get(route('analyze.screenshot', $bettingSlip))
        ->assertNotFound();
});

/**
 * Builds the minimal PDF byte stream needed for smalot/pdfparser to
 * extract real text from it — a deliberately tiny, valid single-page PDF
 * whose content stream is the given literal text.
 */
function pdfWithSelectableText(string $text): string
{
    $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    $stream = "BT /F1 12 Tf 72 720 Td\n({$escaped}) Tj\nET";
    $streamLength = strlen($stream);

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 612 792] /Contents 5 0 R >>',
        4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        5 => "<< /Length {$streamLength} >>\nstream\n{$stream}\nendstream",
    ];

    return buildPdf($objects);
}

function textlessPdf(): string
{
    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        3 => '<< /Type /Page /Parent 2 0 R /Resources << >> /MediaBox [0 0 612 792] >>',
    ];

    return buildPdf($objects);
}

function buildPdf(array $objects): string
{
    $pdf = "%PDF-1.4\n";
    $offsets = [];

    foreach ($objects as $number => $body) {
        $offsets[$number] = strlen($pdf);
        $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $count = count($objects) + 1;
    $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
    foreach ($objects as $offset) {
        // placeholder, replaced below with real offsets
    }
    foreach (array_keys($objects) as $number) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
    }
    $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

    return $pdf;
}
