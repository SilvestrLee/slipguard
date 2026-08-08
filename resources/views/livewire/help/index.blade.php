<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * `PO-U24-002` — replaces the `coming-soon` stub `/help` previously
 * rendered. Every claim in this component is grounded against the real
 * RC1 implementation (see the directive's own Content Truth Matrix,
 * returned in the implementation report), not inferred from older
 * documentation. No dynamic state beyond the one capability-gate read
 * the Builder itself already exposes — everything else is static,
 * reviewed content.
 */
new #[Layout('layouts.app')] class extends Component
{
    /** Mirrors `market-intelligence.builder`'s own gate check exactly — same source of truth, not duplicated logic. */
    public function capabilityEnabled(): bool
    {
        return (bool) config('slipguard-market-intelligence.enabled');
    }
}; ?>

<div class="workspace-page">
    <div class="container-standard workspace-gutter mx-auto workspace-stack">

        <x-page-header :title="__('Help & Methodology')"
            :description="__('Learn how SlipGuard analyses accumulator structure, how to interpret your reports, and what its findings mean.')" />

        {{--
            `PO-U24-002` §5: a compact secondary line, not a second
            headline — the header stays informational, not a marketing
            Hero (this page has no CTA row, no gradient, no illustration).
        --}}
        <p class="-mt-4 text-sm font-medium text-neutral-700">
            {{ __('SlipGuard explains structural risk. It does not predict match outcomes.') }}
        </p>

        {{-- §16 Contextual Navigation / §4 IA — a compact jump list, not a documentation sidebar. --}}
        <nav aria-label="{{ __('Help sections') }}" class="workspace-section-panel workspace-section rounded-lg border border-neutral-200/60 bg-surface-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ __('On this page') }}</p>
            <ul class="mt-3 grid grid-cols-1 gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                <li><a href="#getting-started" class="font-medium text-accent-strong hover:text-accent">{{ __('Getting Started') }}</a></li>
                <li><a href="#analysing-a-slip" class="font-medium text-accent-strong hover:text-accent">{{ __('Analysing a Slip') }}</a></li>
                <li><a href="#understanding-your-report" class="font-medium text-accent-strong hover:text-accent">{{ __('Understanding Your Report') }}</a></li>
                <li><a href="#build-an-accumulator" class="font-medium text-accent-strong hover:text-accent">{{ __('Build an Accumulator') }}</a></li>
                <li><a href="#how-slipguard-thinks" class="font-medium text-accent-strong hover:text-accent">{{ __('How SlipGuard Thinks') }}</a></li>
                <li><a href="#what-slipguard-does-not-do" class="font-medium text-accent-strong hover:text-accent">{{ __('What SlipGuard Does Not Do') }}</a></li>
                <li><a href="#common-questions" class="font-medium text-accent-strong hover:text-accent">{{ __('Common Questions') }}</a></li>
                <li><a href="#need-more-help" class="font-medium text-accent-strong hover:text-accent">{{ __('Need More Help?') }}</a></li>
            </ul>
        </nav>

        {{-- §6 Getting Started --}}
        <section id="getting-started" aria-labelledby="getting-started-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="getting-started-heading" :title="__('Getting Started')"
                :description="__('The core journey, start to finish.')" />

            <ol class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['n' => '1', 'title' => __('Add your slip'), 'body' => __('Type your selections in manually, paste copied slip text, or upload a PDF or screenshot.')],
                    ['n' => '2', 'title' => __('Analyse'), 'body' => __('SlipGuard normalises the selections and applies the current deterministic rule set.')],
                    ['n' => '3', 'title' => __('Read your report'), 'body' => __('The report explains the resulting structural risk and the factors contributing to it.')],
                    ['n' => '4', 'title' => __('Decide'), 'body' => __('SlipGuard provides information. You keep the decision.')],
                ] as $step)
                    <li class="rounded-lg border border-neutral-200/60 bg-surface-card p-4">
                        <span class="flex size-7 items-center justify-center rounded-full bg-accent-strong/10 text-xs font-bold text-accent-strong">{{ $step['n'] }}</span>
                        <p class="mt-3 text-sm font-semibold text-neutral-900">{{ $step['title'] }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- §7 Analysing a Slip --}}
        <section id="analysing-a-slip" aria-labelledby="analysing-a-slip-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="analysing-a-slip-heading" :title="__('Analysing a Slip')"
                :description="__('How SlipGuard reads what you give it.')" />

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('What SlipGuard reads today') }}</h3>
                    <ul class="mt-2 space-y-1.5 text-sm text-neutral-600">
                        <li>{{ __('Manual entry — type each selection in yourself.') }}</li>
                        <li>{{ __('Pasted text — a deterministic parser detects what it confidently can.') }}</li>
                        <li>{{ __('PDF upload — for PDFs with real, selectable text, the same deterministic parser applies.') }}</li>
                        <li>{{ __('Screenshot upload — stored privately alongside a manual entry form; SlipGuard cannot read the image automatically yet.') }}</li>
                    </ul>
                </x-card>
                <x-card variant="soft">
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('What SlipGuard does not read yet') }}</h3>
                    <ul class="mt-2 space-y-1.5 text-sm text-neutral-600">
                        <li>{{ __('Automatic image-to-text extraction (OCR) — screenshots are stored for reference; you complete the selections yourself.') }}</li>
                        <li>{{ __('Bet codes or bookmaker share links — planned, not yet available.') }}</li>
                    </ul>
                    <p class="mt-3 text-xs text-neutral-500">
                        {{ __("Whatever the deterministic parser can't confidently detect is left blank for you to complete — nothing is guessed.") }}
                    </p>
                </x-card>
            </div>
        </section>

        {{-- §8 Understanding Your Report --}}
        <section id="understanding-your-report" aria-labelledby="understanding-your-report-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="understanding-your-report-heading" :title="__('Understanding Your Report')"
                :description="__('What each part of a report actually means.')" />

            <div class="mt-4 space-y-4">
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Structural Risk Score') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ __('A score from 0 to 100 that measures structural characteristics of your accumulator under the current rule set — the number of selections, the odds involved, how concentrated the risk is, and how complex the markets are.') }}
                    </p>
                    <p class="mt-2 text-sm text-neutral-600">
                        {{ __('The score does not represent a probability of winning, a predicted outcome, a bookmaker\'s implied probability, an expected return, or certainty of any kind. It measures structure, not the sport.') }}
                    </p>
                </x-card>

                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Risk Band') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Your score maps to one of four bands:') }}</p>
                    <dl class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ([
                            ['band' => __('Low'), 'range' => '0–24', 'tone' => 'low'],
                            ['band' => __('Moderate'), 'range' => '25–49', 'tone' => 'moderate'],
                            ['band' => __('High'), 'range' => '50–74', 'tone' => 'high'],
                            ['band' => __('Very High'), 'range' => '75–100', 'tone' => 'very-high'],
                        ] as $b)
                            {{--
                                `PO-U24-002` — found via axe-core, not assumed: `text-risk-{tone}`
                                on `bg-risk-{tone}/10` fails WCAG AA at this compact size (2.87–3.33:1
                                measured, all below the 4.5:1 small-text and even the 3:1 large-text
                                threshold). Rather than fight this specific colour pairing at a size
                                it wasn't designed for, meaning is carried by solid, high-contrast
                                text — colour is a decorative accent (the dot) only, consistent with
                                "no information communicated exclusively by colour"
                                (`COMPONENT_PRINCIPLES.md`'s Risk Indicators rule).
                            --}}
                            <div class="rounded-lg border border-neutral-200/60 p-3 text-center">
                                <dt class="flex items-center justify-center gap-1.5 text-sm font-semibold text-neutral-900">
                                    <span class="size-1.5 shrink-0 rounded-full bg-risk-{{ $b['tone'] }}" aria-hidden="true"></span>
                                    {{ $b['band'] }}
                                </dt>
                                <dd class="mt-1.5 text-xs text-neutral-500 font-tabular">{{ $b['range'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="mt-3 text-xs text-neutral-500">{{ __('A higher band means more structural characteristics that add risk are present — never a lower chance of winning.') }}</p>
                </x-card>

                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Main Contributing Factor') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ __("The single structural factor contributing most to your report's score — not a per-selection ranking. It answers \"what about this slip's structure is driving the score,\" not \"which selection is weakest.\"") }}
                    </p>
                    <p class="mt-2 text-sm font-medium text-neutral-700">
                        {{ __('This is different from the structural selection-ranking capability inside Build an Accumulator — see that section below.') }}
                    </p>
                </x-card>

                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Structural Factors') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Every report is evaluated against the same five active factors:') }}</p>
                    <dl class="mt-3 divide-y divide-neutral-200/60">
                        @foreach ([
                            [__('Number of Selections'), __('Reflects the number of selections in your slip — more selections structurally add risk.')],
                            [__('Combined Odds'), __('Reflects the combined odds across all your selections — higher combined odds structurally add risk.')],
                            [__('Selection Odds'), __('Reflects how high the odds are on your individual selections — a small number of high-odds selections adds risk even in an otherwise short slip.')],
                            [__('Risk Concentration'), __("Reflects how much of the slip's risk sits in a small number of selections, rather than being spread evenly.")],
                            [__('Market Complexity'), __('Reflects how complex the markets in your slip are — some markets carry more structural uncertainty than others.')],
                        ] as [$name, $explanation])
                            <div class="py-2.5">
                                <dt class="text-sm font-medium text-neutral-900">{{ $name }}</dt>
                                <dd class="mt-0.5 text-sm text-neutral-600">{{ $explanation }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="mt-3 text-xs text-neutral-500">{{ __('A sixth factor, Selection Relationships, exists in the rule set but is not active yet — it never contributes to a current score.') }}</p>
                </x-card>

                <x-card variant="soft">
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('How an explanation is built') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Every finding on your report follows the same shape:') }}</p>
                    <div class="mt-4 flex flex-col items-center gap-2 text-center sm:flex-row sm:justify-center sm:gap-4">
                        @foreach ([__('Finding'), __('Evidence'), __('Reasoning'), __('Conclusion')] as $i => $stage)
                            <div class="flex items-center gap-2 sm:gap-4">
                                <span class="rounded-md border border-neutral-300 bg-surface-card px-3 py-1.5 text-xs font-semibold text-neutral-700">{{ $stage }}</span>
                                @unless ($loop->last)
                                    <x-heroicon-o-arrow-right class="size-3.5 text-neutral-400 sm:hidden" aria-hidden="true" />
                                    <x-heroicon-o-arrow-right class="hidden size-3.5 text-neutral-400 sm:block" aria-hidden="true" />
                                @endunless
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-neutral-500">{{ __('An illustration of the principle behind every explanation — not a literal diagram reproduced on the report itself.') }}</p>
                </x-card>
            </div>
        </section>

        {{-- §9/§10/§11 Build an Accumulator --}}
        <section id="build-an-accumulator" aria-labelledby="build-an-accumulator-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="build-an-accumulator-heading" :title="__('Build an Accumulator')"
                :description="__('A guided way to build a new accumulator with structural risk visible from the start.')" />

            <div class="mt-4 space-y-4">
                <x-card>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('How it works') }}</h3>
                    <ol class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ([
                            __('Set your conditions — competitions, time window, leg count, markets and a maximum structural risk band.'),
                            __('SlipGuard checks eligible fixtures and market evidence against those conditions.'),
                            __('Review the candidate and choose an outcome for each slot yourself — SlipGuard never chooses for you.'),
                            __('Accept the candidate, or ask for a different one if it isn\'t right.'),
                        ] as $i => $step)
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-accent-strong/10 text-xs font-bold text-accent-strong">{{ $i + 1 }}</span>
                                <span class="text-sm text-neutral-600">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                    @unless ($this->capabilityEnabled())
                        <p class="mt-4 rounded-md border border-neutral-200 bg-surface-soft p-3 text-xs text-neutral-600">
                            {{ __('Live evaluation is not enabled in this workspace yet — you\'ll see an honest preview of this workflow rather than a real candidate until it is.') }}
                        </p>
                    @endunless
                </x-card>

                <x-card variant="soft">
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Describing what you want in your own words') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ __('From your Dashboard, you can describe what you\'re looking for in plain language — for example, "3 Premier League selections" or "5 selections, lower risk" — instead of setting every condition by hand.') }}
                    </p>
                    <p class="mt-2 text-sm text-neutral-600">
                        {{ __('This is a deterministic interpreter for a bounded set of requests, not a general assistant — it recognises specific kinds of asks and hands them to the same guided workflow above. If it doesn\'t recognise what you\'ve described, it says so and points you to the guided workflow instead of guessing.') }}
                    </p>
                </x-card>

                <x-card variant="soft">
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Ranking your own selections') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ __('Once you have a slip in the Planner, SlipGuard can rank your own selections by structural contribution — showing which one currently contributes the most to the slip\'s structural risk, so you can decide whether to keep, review or remove it.') }}
                    </p>
                    <p class="mt-2 text-sm font-medium text-neutral-700">
                        {{ __('This ranking is separate from the base report\'s Main Contributing Factor above: the report names a structural factor; the Planner ranks your own selections. Neither one predicts anything, and neither replaces the other.') }}
                    </p>
                </x-card>
            </div>
        </section>

        {{-- §12/§13 How SlipGuard Thinks --}}
        <section id="how-slipguard-thinks" aria-labelledby="how-slipguard-thinks-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="how-slipguard-thinks-heading" :title="__('How SlipGuard Thinks')"
                :description="__('The methodology behind every analysis.')" />

            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ([
                    [__('Deterministic'), __('The same inputs, under the same rule-set version, always produce the same result.')],
                    [__('Explainable'), __('SlipGuard shows the reasoning behind a finding, never an unexplained score.')],
                    [__('Structural'), __('SlipGuard evaluates characteristics of the accumulator and its selections — never the sport itself.')],
                    [__('Non-Predictive'), __('SlipGuard does not claim to know what will happen in a match.')],
                    [__('Versioned'), __('Every report records the exact rule set, engine, and taxonomy versions used to produce it.')],
                ] as [$term, $body])
                    <div class="rounded-lg border border-neutral-200/60 bg-surface-card p-4">
                        <dt class="text-sm font-semibold text-neutral-900">{{ $term }}</dt>
                        <dd class="mt-1 text-sm text-neutral-600">{{ $body }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-4 rounded-lg border border-neutral-200/60 bg-surface-card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">{{ __('The pipeline') }}</p>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-5">
                    @foreach ([
                        ['label' => __('Your Slip'), 'icon' => 'document-text'],
                        ['label' => __('Normalisation'), 'icon' => 'squares-2x2'],
                        ['label' => __('Deterministic Rules'), 'icon' => 'cog-6-tooth'],
                        ['label' => __('Explainability'), 'icon' => 'chat-bubble-left-right'],
                        ['label' => __('Risk Report'), 'icon' => 'shield-check'],
                    ] as $stage)
                        <div class="rounded-lg border border-neutral-200/60 bg-surface-soft px-3 py-4 text-center">
                            <x-dynamic-component :component="'heroicon-o-'.$stage['icon']" class="mx-auto size-5 text-accent-strong" aria-hidden="true" />
                            <p class="mt-2 text-xs font-semibold text-neutral-900">{{ $stage['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- §14 What SlipGuard Does Not Do --}}
        <section id="what-slipguard-does-not-do" aria-labelledby="does-not-do-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="does-not-do-heading" :title="__('What SlipGuard Does Not Do')" />

            {{-- A permanent, non-transient statement — `<x-card variant="soft">`, not `<x-alert>` (COMPONENT_PRINCIPLES.md's Alerts rule: alerts are for transient messages, not permanent page content). --}}
            <x-card variant="soft" class="mt-4">
                <p class="text-sm text-neutral-600">{{ __('SlipGuard does not:') }}</p>
                <ul class="mt-3 space-y-1.5 text-sm text-neutral-600">
                    @foreach ([
                        __('predict match outcomes'),
                        __('guarantee winning bets'),
                        __('place bets'),
                        __('control customer funds'),
                        __('automatically make betting decisions for you'),
                        __('present structural risk as a probability'),
                    ] as $item)
                        <li class="flex gap-2">
                            <x-heroicon-o-x-mark class="mt-0.5 size-4 shrink-0 text-neutral-400" aria-hidden="true" />
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </section>

        {{-- §15 Common Questions --}}
        <section id="common-questions" aria-labelledby="common-questions-heading" class="workspace-section scroll-mt-24">
            <x-workspace.section-heading id="common-questions-heading" :title="__('Common Questions')" />

            {{-- Disclosure/Accordion pattern, `COMPONENT_PRINCIPLES.md` — reused verbatim from `report.blade.php`/the homepage FAQ, not a new pattern. --}}
            <div class="mt-4 divide-y divide-neutral-200 rounded-lg border border-neutral-200/60 bg-surface-card">
                @foreach ([
                    ['q' => __('Does a lower Structural Risk Score mean my bet will win?'), 'a' => __('No. The score measures structural characteristics of your accumulator — it never estimates a chance of winning, however low the score.')],
                    ['q' => __('Does SlipGuard predict matches?'), 'a' => __('No. SlipGuard never claims to know what will happen in a match — it evaluates how your slip is built, not who wins.')],
                    ['q' => __('What is the Main Contributing Factor?'), 'a' => __("The single structural factor — such as Combined Odds or Risk Concentration — contributing most to your report's score. See Understanding Your Report above for the full list.")],
                    ['q' => __('Why can two similar accumulators receive different assessments?'), 'a' => __('Because the five structural factors respond to real differences between them — odds, selection count, how concentrated the risk is, and market complexity all vary even between slips that look alike at a glance.')],
                    ['q' => __('Can SlipGuard change my accumulator?'), 'a' => __('No. In Analyse, you choose every selection. In Build an Accumulator, SlipGuard proposes eligible candidates but you choose the outcome for every slot — SlipGuard never finalises a selection on your behalf.')],
                    ['q' => __('What is Build an Accumulator?'), 'a' => __('A guided way to build a new accumulator with structural risk visible from the start, instead of discovering it afterwards. See the Build an Accumulator section above.')],
                    ['q' => __('Does SlipGuard place bets for me?'), 'a' => __('Never. SlipGuard never accepts deposits, holds funds, or places a wager on your behalf. Every financial transaction stays between you and your licensed operator.')],
                    ['q' => __('Can I reanalyse a slip?'), 'a' => __('Not in place. Once a slip is analysed, its report is a fixed, permanent record and the slip itself can no longer be edited. To analyse a different set of selections, start a new slip.')],
                    ['q' => __('Why might a report change after I edit my slip?'), 'a' => __("It won't — an existing report never changes. If you edit a slip before it's been analysed and then run a fresh analysis, you'll get a new, separate report reflecting the new structure, not an update to an old one.")],
                ] as $i => $faq)
                    <div x-data="{ open: false }" class="p-5">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="help-faq-panel-{{ $i }}"
                                class="flex min-h-11 w-full items-center justify-between gap-4 text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                            <span class="text-sm font-semibold text-neutral-900">{{ $faq['q'] }}</span>
                            <x-heroicon-o-chevron-down class="size-4 shrink-0 text-neutral-400 transition-transform duration-instant" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true" />
                        </button>
                        <div id="help-faq-panel-{{ $i }}" x-show="open" x-cloak class="mt-3 text-sm text-neutral-600">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- §17 Need More Help --}}
        <section id="need-more-help" aria-labelledby="need-more-help-heading" class="workspace-section scroll-mt-24">
            <x-card variant="soft" class="text-center">
                <h2 id="need-more-help-heading" class="text-base font-semibold text-neutral-900">{{ __('Still need help?') }}</h2>
                <p class="mt-1 text-sm text-neutral-600">{{ __("If you have a question about SlipGuard or something doesn't look right, contact us.") }}</p>
                <a href="{{ route('contact') }}" wire:navigate
                   class="mt-4 inline-flex items-center justify-center h-11 px-6 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-colors duration-instant">
                    {{ __('Contact SlipGuard') }}
                </a>
            </x-card>
        </section>

    </div>
</div>
