<?php

use App\Actions\AccumulatorConversation\CreateDraftFromExplicitSelection;
use App\Domain\AccumulatorConversation\AccumulatorIntentInterpreter;
use App\Domain\AccumulatorConversation\RequestClass;
use App\Domain\Risk\Results\RiskBand;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

/**
 * `PO-U23-001` — the conversational entry layer for Capability B, embedded
 * as a Dashboard module (§4), not a separate page — matching "the
 * conversational builder must become a prominent entry method without
 * replacing the existing dashboard or builder" (§3.1).
 *
 * Decision 2 (`PO-U23-001` response): this module inherits every existing
 * Capability B restriction exactly. Request Class A (criteria discovery)
 * genuinely needs live evidence and short-circuits with the Builder's own
 * existing "not available in this workspace yet" wording when the flag is
 * off, rather than round-tripping to a page that would just say the same
 * thing. Request Class B (explicit construction) does not depend on
 * Capability B at all — it uses Capability A's existing manual Builder —
 * so it keeps working regardless of the flag, same as every other intake
 * method already does.
 */
new class extends Component
{
    public string $message = '';

    public ?string $lastSummary = null;

    public ?string $clarificationQuestion = null;

    public ?string $unsupportedExplanation = null;

    public ?string $errorMessage = null;

    public function capabilityEnabled(): bool
    {
        return (bool) config('slipguard-market-intelligence.enabled');
    }

    public function useStarterPrompt(string $prompt): void
    {
        $this->message = $prompt;
        $this->resetTransientState();
    }

    private function resetTransientState(): void
    {
        $this->lastSummary = null;
        $this->clarificationQuestion = null;
        $this->unsupportedExplanation = null;
        $this->errorMessage = null;
    }

    public function submit(AccumulatorIntentInterpreter $interpreter, CreateDraftFromExplicitSelection $createDraft): void
    {
        // `PO-U23-001` §30/§11 — this component is only ever embedded on
        // the already-`auth`-gated Dashboard route today, but does not
        // rely on that alone: same defensive-in-depth guard the existing
        // guest-capable Labs waitlist entry point already uses
        // (`abort_unless(Auth::check(), 401)`), so a future reuse of this
        // component elsewhere can't silently lose the check.
        abort_unless(Auth::check(), 401);

        $this->validate(['message' => ['required', 'string', 'max:500']]);

        $this->resetTransientState();

        try {
            $result = $interpreter->interpret($this->message);
        } catch (\Throwable) {
            // `PO-U23-001` §24 — AI temporarily unavailable fallback. The
            // deterministic interpreter shouldn't throw in normal
            // operation; this guards the customer from ever seeing a raw
            // error if it somehow does (or once a live provider is wired
            // in a future commission and a real network failure occurs).
            $this->errorMessage = __("I couldn't interpret that request right now.");

            return;
        }

        match ($result->requestClass) {
            RequestClass::CriteriaDiscovery => $this->handleCriteriaDiscovery($result),
            RequestClass::ExplicitConstruction => $this->handleExplicitConstruction($result, $createDraft),
            RequestClass::NeedsClarification => $this->clarificationQuestion = $result->clarificationQuestion,
            RequestClass::Unsupported => $this->unsupportedExplanation = $result->explanation,
        };
    }

    private function handleCriteriaDiscovery($result): void
    {
        if (! $this->capabilityEnabled()) {
            $this->errorMessage = __('Building around live fixtures is not available in this workspace yet. Try the Guided Builder instead.');

            return;
        }

        $brief = $result->planningBrief;

        // `PO-U23-001` §15/§16 — the conversational layer only ever hands
        // a seed to the *existing* Builder; the Builder's own `mount()`
        // applies it and calls its own, completely unmodified
        // `findCandidate()` — no candidate-discovery logic is duplicated
        // here.
        session()->flash('conversational_planning_brief', [
            'competitions' => $brief->competitions,
            'windowDays' => $brief->windowDays,
            'legCount' => $brief->legCountTarget,
            'markets' => $brief->requestedMarkets,
            'riskCeiling' => $brief->riskCeiling->value,
        ]);
        session()->flash('conversational_summary', $result->summary);

        $this->redirect(route('builder'), navigate: true);
    }

    private function handleExplicitConstruction($result, CreateDraftFromExplicitSelection $createDraft): void
    {
        $slip = $createDraft->execute(Auth::user(), $result->explicitSelection);

        session()->flash('status', $result->summary);

        $this->redirect(route('analyze.edit', $slip), navigate: true);
    }
}; ?>

<div class="rounded-2xl border workspace-structural-border bg-surface-card p-6 sm:p-8" aria-labelledby="accumulator-conversation-heading">
    <div class="flex items-center justify-between gap-3">
        <p id="accumulator-conversation-heading" class="text-xs font-semibold uppercase tracking-[0.16em] text-accent-strong">
            {{ __('Describe your accumulator') }}
        </p>
        @unless ($this->capabilityEnabled())
            <span class="rounded-full border border-neutral-300 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-wide text-neutral-500">{{ __('Preview') }}</span>
        @endunless
    </div>

    <h2 class="mt-3 text-xl font-semibold text-neutral-900">{{ __('What are you thinking of playing today?') }}</h2>
    <p class="mt-2 text-sm text-neutral-600">
        {{ __("Tell SlipGuard the teams, leagues, markets or kind of accumulator you want. We'll structure the first draft.") }}
    </p>

    <form wire:submit="submit" class="mt-5">
        <label for="accumulator-conversation-input" class="sr-only">{{ __('Describe your accumulator') }}</label>
        <div class="flex flex-col gap-3 sm:flex-row">
            <input id="accumulator-conversation-input" type="text" wire:model="message"
                   placeholder="{{ __('Describe your accumulator…') }}"
                   class="min-h-12 flex-1 rounded-lg border-neutral-300 bg-surface-soft text-sm text-neutral-900 placeholder:text-neutral-500 focus:border-accent focus:ring-accent" />
            <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="submit" class="min-h-12 justify-center !normal-case !tracking-normal">
                <span wire:loading.remove wire:target="submit">{{ __('Build') }}</span>
                <span wire:loading wire:target="submit" role="status">{{ __('Understanding your request…') }}</span>
            </x-primary-button>
        </div>
        @if ($errors->has('message'))
            <p class="mt-2 text-sm text-alert-error" role="alert">{{ $errors->first('message') }}</p>
        @endif
    </form>

    @if ($clarificationQuestion)
        <div class="mt-4 rounded-lg border border-neutral-200 bg-surface-soft p-4" role="status">
            <p class="text-sm text-neutral-800">{{ $clarificationQuestion }}</p>
        </div>
    @elseif ($unsupportedExplanation)
        <div class="mt-4 rounded-lg border border-neutral-200 bg-surface-soft p-4" role="status">
            <p class="text-sm text-neutral-800">{{ $unsupportedExplanation }}</p>
            <a href="{{ route('builder') }}" wire:navigate class="mt-2 inline-block text-sm font-semibold text-accent-strong hover:text-accent">
                {{ __('Use the Guided Builder instead') }} →
            </a>
        </div>
    @elseif ($errorMessage)
        <div class="mt-4 rounded-lg border border-neutral-200 bg-surface-soft p-4" role="alert">
            <p class="text-sm text-neutral-800">{{ $errorMessage }}</p>
            <a href="{{ route('builder') }}" wire:navigate class="mt-2 inline-block text-sm font-semibold text-accent-strong hover:text-accent">
                {{ __('Use the Guided Builder instead') }} →
            </a>
        </div>
    @endif

    <div class="mt-5 flex flex-wrap gap-2" role="group" aria-label="{{ __('Example requests') }}">
        @foreach ([
            __('3 Premier League selections'),
            __('5 selections, lower risk'),
            __('4 games from La Liga this weekend'),
            __('Arsenal to win'),
        ] as $prompt)
            <button type="button" wire:click="useStarterPrompt('{{ $prompt }}')"
                    class="rounded-full border border-neutral-300 bg-surface-card px-3 py-1.5 text-xs font-medium text-neutral-700 transition-colors hover:border-neutral-400 hover:bg-surface-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                {{ $prompt }}
            </button>
        @endforeach
    </div>
</div>
