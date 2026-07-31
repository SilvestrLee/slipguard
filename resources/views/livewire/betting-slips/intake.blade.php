<?php

use App\Actions\BettingSlip\CreateDraftSlipFromParsedText;
use App\Actions\BettingSlip\CreateDraftSlipWithScreenshot;
use App\Domain\BettingSlip\Intake\ExtractPdfText;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

/**
 * U-11.3 §8-15: the multi-method intake shell. Bounded-scope intake
 * (2026-07-28, founder direct instruction): Paste Text and PDF Upload now
 * run a real, deterministic text parser (`App\Domain\BettingSlip\Intake\ParseSlipText`)
 * and converge on the same `SaveBettingSlip` action and Builder review
 * screen every other intake method already uses — no new pipeline, no
 * bypass of validation or taxonomy normalisation. Screenshot Upload
 * accepts and securely stores the image (no OCR — explicitly out of
 * scope) and hands the customer straight to the Builder with the image
 * shown alongside the manual entry form. Bet Code remains deferred,
 * unchanged from its original "not yet available" state.
 */
new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?string $selectedMethod = null;

    public bool $attempted = false;

    public string $pastedText = '';

    public $pdfFile = null;

    public $screenshotFile = null;

    public bool $pdfExtractionFailed = false;

    public function mount(): void
    {
        $method = request()->query('method');

        if (in_array($method, ['screenshot', 'pdf', 'paste', 'betcode'], true)) {
            $this->selectedMethod = $method;
        }
    }

    public function selectMethod(string $method): void
    {
        $this->selectedMethod = $method;
        $this->attempted = false;
        $this->pdfExtractionFailed = false;
        $this->resetValidation();
    }

    /**
     * Bet Code remains explicitly deferred — this method's own behaviour
     * is unchanged from before this work package.
     */
    public function attemptUnsupportedMethod(): void
    {
        $this->attempted = true;
    }

    public function submitPastedText(CreateDraftSlipFromParsedText $action): void
    {
        $validated = $this->validate([
            'pastedText' => ['required', 'string', 'min:3', 'max:10000'],
        ]);

        $bettingSlip = $action->execute(Auth::user(), $validated['pastedText']);

        session()->flash('status', __('Text parsed — review and confirm the selections below.'));

        $this->redirect(route('analyze.edit', $bettingSlip), navigate: true);
    }

    public function submitPdf(CreateDraftSlipFromParsedText $action, ExtractPdfText $extractPdfText): void
    {
        $this->validate([
            'pdfFile' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $text = $extractPdfText->execute($this->pdfFile->getRealPath());

        if (trim($text) === '') {
            $this->pdfExtractionFailed = true;

            return;
        }

        $bettingSlip = $action->execute(Auth::user(), $text);

        session()->flash('status', __('PDF text extracted — review and confirm the selections below.'));

        $this->redirect(route('analyze.edit', $bettingSlip), navigate: true);
    }

    public function submitScreenshot(CreateDraftSlipWithScreenshot $action): void
    {
        $this->validate([
            'screenshotFile' => ['required', 'image', 'max:10240'],
        ]);

        $path = $this->screenshotFile->store('betting-slip-screenshots/'.Auth::id(), 'local');

        $bettingSlip = $action->execute(Auth::user(), $path);

        session()->flash('status', __('Screenshot uploaded — transcribe and confirm the selections below.'));

        $this->redirect(route('analyze.edit', $bettingSlip), navigate: true);
    }

    public function with(): array
    {
        return [
            'isMobile' => false,
        ];
    }
}; ?>

<div class="workspace-page">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">

        <x-page-header :title="__('How would you like to add your slip?')">
            <x-slot name="action">
                <a href="{{ route('analyze') }}" wire:navigate class="text-sm font-medium text-neutral-600 hover:text-neutral-900">
                    {{ __('Back to Slips') }}
                </a>
            </x-slot>
        </x-page-header>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button type="button" wire:click="selectMethod('screenshot')"
                    class="flex min-h-28 items-start gap-4 rounded-xl border bg-surface-card p-5 text-left transition-colors hover:bg-surface-soft {{ $selectedMethod === 'screenshot' ? 'border-accent-strong ring-2 ring-accent/20' : 'border-neutral-200' }}">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-accent/10 text-accent-strong">
                    <x-heroicon-o-photo class="size-5" aria-hidden="true" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Upload Screenshot') }}</p>
                    <p class="mt-1 text-xs leading-5 text-neutral-500">{{ __('Add a photo or screen capture. It remains private and available beside the review form.') }}</p>
                    <span class="mt-2 inline-flex text-[0.65rem] font-semibold uppercase tracking-wide text-accent-strong">{{ __('Recommended intake') }}</span>
                </div>
            </button>

            <button type="button" wire:click="selectMethod('pdf')"
                    class="flex min-h-28 items-start gap-4 rounded-xl border bg-surface-card p-5 text-left transition-colors hover:bg-surface-soft {{ $selectedMethod === 'pdf' ? 'border-accent-strong ring-2 ring-accent/20' : 'border-neutral-200' }}">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-accent/10 text-accent-strong">
                    <x-heroicon-o-document-arrow-up class="size-5" aria-hidden="true" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Upload PDF') }}</p>
                    <p class="mt-1 text-xs leading-5 text-neutral-500">{{ __('Use an exported PDF containing selectable text. SlipGuard extracts it for your review.') }}</p>
                    <span class="mt-2 inline-flex text-[0.65rem] font-semibold uppercase tracking-wide text-accent-strong">{{ __('Recommended intake') }}</span>
                </div>
            </button>

            <button type="button" wire:click="selectMethod('paste')"
                    class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-surface-card p-4 text-left hover:bg-surface-soft {{ $selectedMethod === 'paste' ? 'ring-2 ring-accent/30' : '' }}">
                <x-heroicon-o-clipboard-document class="size-6 shrink-0 text-neutral-400" aria-hidden="true" />
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Paste Slip Text') }}</p>
                    <p class="text-xs text-neutral-500">{{ __('Paste copied text for deterministic parsing and review') }}</p>
                </div>
            </button>

            <a href="{{ route('analyze.create') }}" wire:navigate
               class="flex items-center gap-3 rounded-xl border border-neutral-200 bg-surface-card p-4 hover:bg-surface-soft">
                <x-heroicon-o-pencil-square class="size-6 shrink-0 text-neutral-400" aria-hidden="true" />
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Enter Manually') }}</p>
                    <p class="text-xs text-neutral-500">{{ __('Fallback when an upload or copied slip is unavailable') }}</p>
                </div>
            </a>

            <button type="button" wire:click="selectMethod('betcode')"
                    class="flex items-center gap-3 rounded-xl border border-dashed border-neutral-300 bg-neutral-100/60 p-4 text-left hover:bg-neutral-100 {{ $selectedMethod === 'betcode' ? 'ring-2 ring-neutral-300' : '' }}">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-neutral-300 bg-surface-card text-neutral-400">
                    <x-heroicon-o-clock class="size-5" aria-hidden="true" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-neutral-700">{{ __('Bet Code or Share Link') }}</p>
                        <span class="rounded-full border border-neutral-300 bg-surface-card px-2 py-0.5 text-[0.62rem] font-semibold uppercase tracking-wide text-neutral-500">
                            {{ __('Coming soon') }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-neutral-500">{{ __('Future capability — no bookmaker formats are supported yet') }}</p>
                </div>
            </button>
        </div>

        @if ($selectedMethod === 'screenshot')
            <x-card role="region" aria-labelledby="screenshot-heading">
                <h3 id="screenshot-heading" class="text-sm font-semibold text-neutral-900">{{ __('Upload Screenshot') }}</h3>
                <p class="mt-1 text-xs text-neutral-500">
                    {{ __("SlipGuard can't read a screenshot automatically yet. Upload it for reference, then you'll transcribe the selections yourself on the next screen, with the image shown alongside the form.") }}
                </p>
                <x-file-drop inputId="screenshot-input" accept="image/png,image/jpeg,image/webp"
                    :label="__('Choose a file, or drag and drop')" :hint="__('PNG, JPG, or WEBP — up to 10MB')" class="mt-4"
                    wire:model="screenshotFile" />
                @error('screenshotFile') <p class="mt-2 text-sm text-alert-error">{{ $message }}</p> @enderror
                <div wire:loading wire:target="screenshotFile" class="mt-2 text-xs text-neutral-500">{{ __('Uploading…') }}</div>
                @if ($screenshotFile)
                    <p class="mt-2 text-xs text-neutral-600">{{ __('Selected:') }} {{ $screenshotFile->getClientOriginalName() }}</p>
                @endif
                <button type="button" wire:click="submitScreenshot" wire:loading.attr="disabled" wire:target="submitScreenshot"
                        class="mt-4 inline-flex items-center h-10 px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent disabled:opacity-50">
                    <span wire:loading.remove wire:target="submitScreenshot">{{ __('Continue') }}</span>
                    <span wire:loading wire:target="submitScreenshot" role="status">{{ __('Uploading…') }}</span>
                </button>
            </x-card>
        @elseif ($selectedMethod === 'paste')
            <x-card role="region" aria-labelledby="paste-heading">
                <h3 id="paste-heading" class="text-sm font-semibold text-neutral-900">{{ __('Paste Slip Text') }}</h3>
                <p class="mt-1 text-xs text-neutral-500">
                    {{ __("SlipGuard will try to detect each selection's event, market and odds. Anything it can't confidently detect is left blank for you to fill in on the next screen — nothing is guessed.") }}
                </p>
                <textarea wire:model="pastedText" rows="6" placeholder="{{ __('Paste the copied slip text here…') }}"
                          class="mt-4 block w-full bg-neutral-50 border-neutral-300 focus:border-accent focus:ring-accent rounded-md shadow-sm text-sm"></textarea>
                @error('pastedText') <p class="mt-2 text-sm text-alert-error">{{ $message }}</p> @enderror
                <button type="button" wire:click="submitPastedText" wire:loading.attr="disabled" wire:target="submitPastedText"
                        class="mt-4 inline-flex items-center h-10 px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent disabled:opacity-50">
                    <span wire:loading.remove wire:target="submitPastedText">{{ __('Continue') }}</span>
                    <span wire:loading wire:target="submitPastedText" role="status">{{ __('Reading…') }}</span>
                </button>
            </x-card>
        @elseif ($selectedMethod === 'pdf')
            <x-card role="region" aria-labelledby="pdf-heading">
                <h3 id="pdf-heading" class="text-sm font-semibold text-neutral-900">{{ __('Upload PDF') }}</h3>
                @if (! $pdfExtractionFailed)
                    <p class="mt-1 text-xs text-neutral-500">
                        {{ __('Works for PDFs with real, selectable text — not a scanned photo saved as a PDF.') }}
                    </p>
                    <x-file-drop inputId="pdf-input" accept="application/pdf" :label="__('Choose a PDF file')" :hint="__('Up to 10MB')" class="mt-4"
                        wire:model="pdfFile" />
                    @error('pdfFile') <p class="mt-2 text-sm text-alert-error">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="pdfFile" class="mt-2 text-xs text-neutral-500">{{ __('Uploading…') }}</div>
                    @if ($pdfFile)
                        <p class="mt-2 text-xs text-neutral-600">{{ __('Selected:') }} {{ $pdfFile->getClientOriginalName() }}</p>
                    @endif
                    <button type="button" wire:click="submitPdf" wire:loading.attr="disabled" wire:target="submitPdf"
                            class="mt-4 inline-flex items-center h-10 px-4 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent disabled:opacity-50">
                        <span wire:loading.remove wire:target="submitPdf">{{ __('Continue') }}</span>
                        <span wire:loading wire:target="submitPdf" role="status">{{ __('Reading…') }}</span>
                    </button>
                @else
                    <div role="status" class="mt-4 flex items-start gap-3 bg-neutral-100 border border-neutral-200 rounded-lg p-4">
                        <x-heroicon-o-exclamation-circle class="size-5 text-neutral-400 shrink-0 mt-0.5" aria-hidden="true" />
                        <div>
                            <p class="text-sm font-semibold text-neutral-900">{{ __("No text could be extracted from this PDF") }}</p>
                            <p class="mt-1 text-sm text-neutral-600">{{ __("This usually means the PDF is a scanned image rather than real text — SlipGuard can't read that automatically.") }}</p>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1">
                                <a href="{{ route('analyze.create') }}" wire:navigate class="text-sm font-medium text-accent-strong hover:underline">
                                    {{ __('Enter this slip manually →') }}
                                </a>
                                <button type="button" wire:click="selectMethod('screenshot')" class="text-sm font-medium text-accent-strong hover:underline">
                                    {{ __('Try Screenshot Upload instead →') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </x-card>
        @elseif ($selectedMethod === 'betcode')
            <x-card role="region" aria-labelledby="betcode-heading">
                <span class="inline-flex rounded-full border border-neutral-300 bg-neutral-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-neutral-600">
                    {{ __('Future capability') }}
                </span>
                <h3 id="betcode-heading" class="mt-3 text-base font-semibold text-neutral-900">{{ __('Bet Code or Share Link') }}</h3>
                <p class="mt-1 text-sm text-neutral-600">
                    {{ __('SlipGuard does not currently recognise any bookmaker bet-code or share-link format. There is nothing to paste or check yet.') }}
                </p>
                <x-betting-slips.intake-unavailable-notice reason="{{ __('No supported bookmaker bet-code or share-link formats are recognised yet.') }}" />
            </x-card>
        @endif
    </div>
</div>
