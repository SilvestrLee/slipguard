<?php

use App\Domain\BettingSlip\BettingSlipStatus;
use App\Models\BettingSlip;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public BettingSlip $bettingSlip;

    public string $currentStage = 'receiving';

    /** @var array<string, mixed> */
    public array $facts = [];

    public ?string $failureType = null;

    public string $failureMessage = '';

    public function mount(BettingSlip $bettingSlip): void
    {
        $this->authorize('view', $bettingSlip);

        $this->bettingSlip = $bettingSlip->load('legs', 'analysis');
        $this->facts = $this->initialFacts();

        if ($this->bettingSlip->analysis) {
            $this->redirect(route('analyze.report', $this->bettingSlip), navigate: true);

            return;
        }

        if ($this->bettingSlip->status !== BettingSlipStatus::Ready) {
            $this->failureType = 'validation';
            $this->failureMessage = __('This slip is not ready for analysis. Review its selections before trying again.');
        }
    }

    /** @return array<string, int> */
    private function initialFacts(): array
    {
        return [
            'selections_received' => $this->bettingSlip->legs->count(),
            'competitions_recorded' => $this->bettingSlip->legs
                ->pluck('competition')
                ->filter(fn ($competition) => filled($competition))
                ->unique()
                ->count(),
            'markets_recorded' => $this->bettingSlip->legs
                ->pluck('market_name')
                ->filter(fn ($market) => filled($market))
                ->unique()
                ->count(),
        ];
    }

    public function failureTitle(): string
    {
        return match ($this->failureType) {
            'validation' => __('This slip needs attention'),
            'persistence' => __('The report could not be saved'),
            default => __('Analysis could not be completed'),
        };
    }
}; ?>

<div class="workspace-page"
    x-data="analysisProcessing({
        endpoint: @js(route('analyze.processing.run', $bettingSlip)),
        reportFallback: @js(route('analyze.report', $bettingSlip)),
    })"
    @if (! $failureType) x-init="start()" @endif>
    <div class="container-standard workspace-gutter mx-auto workspace-stack">
        <x-page-header :title="__('Analysing Slip')"
            :description="__('SlipGuard is applying its deterministic structural rule set. No outcome is being predicted.')" />

        <div wire:offline
            class="rounded-lg border border-alert-caution/30 bg-alert-caution/10 workspace-card-padding"
            role="alert">
            <p class="workspace-card-title text-alert-caution-strong">{{ __('Connection interrupted') }}</p>
            <p class="mt-1 workspace-helper">
                {{ __('The analysis status is uncertain because this browser lost its connection. Check Analysis History before retrying so you do not mistake an interrupted response for a failed analysis.') }}
            </p>
            <a href="{{ route('history') }}" wire:navigate
                class="mt-4 inline-flex min-h-10 items-center text-sm font-semibold text-accent-strong hover:text-accent">
                {{ __('Check Analysis History') }}
            </a>
        </div>

        <template x-if="failureType">
            <div class="rounded-lg border border-alert-danger/30 bg-alert-danger/10 workspace-card-padding" role="alert">
                <p class="workspace-card-title text-alert-danger-strong" x-text="failureTitle"></p>
                <p class="mt-2 workspace-body" x-text="failureMessage"></p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-primary-button type="button" x-on:click="start()" x-bind:disabled="processing">
                        <span x-show="! processing">{{ __('Retry analysis') }}</span>
                        <span x-show="processing">{{ __('Retrying…') }}</span>
                    </x-primary-button>
                    <a href="{{ route('analyze.edit', $bettingSlip) }}" wire:navigate
                        class="inline-flex min-h-10 items-center rounded-md border border-neutral-300 bg-neutral-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        {{ __('Review submitted slip') }}
                    </a>
                    <a href="{{ route('analyze') }}" wire:navigate
                        class="inline-flex min-h-10 items-center text-sm font-semibold text-neutral-700 hover:text-neutral-900">
                        {{ __('Return to slips') }}
                    </a>
                </div>
            </div>
        </template>

        @if ($failureType)
            <x-workspace.inline-error :title="$this->failureTitle()">
                <p>{{ $failureMessage }}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('analyze.edit', $bettingSlip) }}" wire:navigate
                        class="inline-flex min-h-10 items-center rounded-md border border-neutral-300 bg-neutral-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                        {{ __('Review submitted slip') }}
                    </a>
                    <a href="{{ route('analyze') }}" wire:navigate
                        class="inline-flex min-h-10 items-center text-sm font-semibold text-neutral-700 hover:text-neutral-900">
                        {{ __('Return to slips') }}
                    </a>
                </div>
            </x-workspace.inline-error>
        @else
            <div x-ref="progress" role="status" aria-live="polite" aria-atomic="false">
                @include('partials.analysis-processing-status', [
                    'bettingSlip' => $bettingSlip,
                    'currentStage' => $currentStage,
                    'facts' => $facts,
                ])
            </div>
        @endif
    </div>
</div>

@script
<script>
    Alpine.data('analysisProcessing', ({ endpoint, reportFallback }) => ({
        endpoint,
        reportFallback,
        processing: false,
        failureType: null,
        failureMessage: '',
        failureTitle: '',

        async start() {
            if (this.processing) return;

            this.processing = true;
            this.failureType = null;
            this.failureMessage = '';

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/x-ndjson',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (! response.ok || ! response.body) throw new Error('processing-request-failed');

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

                while (true) {
                    const { value, done } = await reader.read();
                    buffer += decoder.decode(value || new Uint8Array(), { stream: ! done });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (const line of lines) {
                        if (! line.trim()) continue;
                        this.handleEvent(JSON.parse(line));
                    }

                    if (done) break;
                }
            } catch (error) {
                this.showFailure(
                    'connection',
                    @js(__('The connection was interrupted before the browser received the final analysis status. Check Analysis History before retrying.')),
                );
            } finally {
                this.processing = false;
            }
        },

        handleEvent(event) {
            if (event.type === 'progress' && event.html) {
                this.$refs.progress.innerHTML = event.html;
            } else if (event.type === 'failure') {
                this.showFailure(event.failure_type, event.message);
            } else if (event.type === 'complete') {
                window.location.assign(event.report_url || this.reportFallback);
            }
        },

        showFailure(type, message) {
            this.failureType = type;
            this.failureMessage = message;
            this.failureTitle = type === 'validation'
                ? @js(__('This slip needs attention'))
                : (type === 'persistence'
                    ? @js(__('The report could not be saved'))
                    : (type === 'connection'
                        ? @js(__('Connection interrupted'))
                        : @js(__('Analysis could not be completed'))));
        },
    }));
</script>
@endscript
