<?php

use App\Actions\Contact\SubmitContactMessage;
use App\Domain\Contact\ContactMessageCategory;
use App\View\Components\PublicLayout;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * `PO-U22-001` — real Contact page, replacing the honest "hasn't been
 * built yet" stub. Guest-accessible (`labs.index`'s own precedent for a
 * public, non-authenticated Volt component), since the form needs real
 * server-side validation Blade alone can't provide.
 *
 * Placeholder-handling decision (raised via `AskUserQuestion`, resolved
 * by the founder): the directive's office address, phone number, and
 * exact business hours are not real. Rather than publish fabricated
 * specifics that only look real, every such fact below is visibly
 * marked "to be confirmed" to an actual visitor — matching this
 * codebase's own established pattern for exactly this situation
 * (`terms.blade.php`/`privacy.blade.php`'s "Clauses requiring legal
 * review" panel). Email addresses are the one exception: a plausible,
 * consistently-named `@slipguard.ai` alias is a safe, honest default
 * (it's just an address format, not a claim about being staffed), but
 * no response-time promise is stated as settled fact either — see
 * Support Expectations below, and `SubmitContactMessage`'s own note
 * that no Mailable/mail delivery exists yet, only persistence.
 */
new #[Layout(PublicLayout::class, ['title' => 'Contact', 'description' => 'SlipGuard is an independent intelligence layer between the bettor and the bookmaker — get in touch with questions, support requests, or partnership enquiries.'])] class extends Component
{
    public string $fullName = '';

    public string $email = '';

    public string $subject = '';

    public string $category = '';

    public string $message = '';

    public bool $submitted = false;

    /** @return array<int, ContactMessageCategory> */
    public function categories(): array
    {
        return ContactMessageCategory::cases();
    }

    protected function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:'.implode(',', array_column(ContactMessageCategory::cases(), 'value'))],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'fullName' => __('full name'),
        ];
    }

    public function send(SubmitContactMessage $action): void
    {
        $validated = $this->validate();

        $action->execute(
            $validated['fullName'],
            $validated['email'],
            $validated['subject'],
            ContactMessageCategory::from($validated['category']),
            $validated['message'],
        );

        $this->reset(['fullName', 'email', 'subject', 'category', 'message']);
        $this->submitted = true;
    }
}; ?>

<div>
    {{-- 1. Hero — matches the Analyse/Reports/Planner/Pricing/About/FAQ hero pattern exactly, no new treatment. --}}
    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="contact-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Contact') }}</p>
            <h1 id="contact-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __("Let's talk.") }}
            </h1>
            <p class="mt-4 text-base text-neutral-600 max-w-xl mx-auto">
                {{ __("Whether you have questions about SlipGuard, need help with your account, want to discuss a partnership, or just want to learn more — we're here to help. We aim to reply as quickly as we genuinely can, with a clear, specific answer.") }}
            </p>
        </div>
    </section>

    {{-- 2. Contact Options — four cards, same primitive as the homepage's Product Capabilities cards. --}}
    <section class="public-section-quiet" aria-labelledby="contact-options-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20">
            <h2 id="contact-options-heading" class="sr-only">{{ __('Ways to reach us') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach ([
                    ['icon' => 'chat-bubble-left-right', 'title' => __('General Enquiries'), 'email' => 'hello@slipguard.ai', 'description' => __('General questions about the platform.')],
                    ['icon' => 'wrench-screwdriver', 'title' => __('Technical Support'), 'email' => 'support@slipguard.ai', 'description' => __('Help with your account, an analysis, or something that looks wrong.')],
                    ['icon' => 'briefcase', 'title' => __('Business Partnerships'), 'email' => 'partners@slipguard.ai', 'description' => __('Commercial partnerships, affiliates and collaborations.')],
                    ['icon' => 'newspaper', 'title' => __('Media'), 'email' => 'media@slipguard.ai', 'description' => __('Press and media enquiries.')],
                ] as $option)
                    <x-card>
                        <span class="inline-flex items-center justify-center size-10 rounded-full bg-accent-strong/10 text-accent-strong">
                            <x-dynamic-component :component="'heroicon-o-'.$option['icon']" class="size-5" aria-hidden="true" />
                        </span>
                        <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ $option['title'] }}</h3>
                        <p class="mt-1 text-sm text-neutral-600">{{ $option['description'] }}</p>
                        <a href="mailto:{{ $option['email'] }}" class="mt-3 inline-block text-sm font-semibold text-accent-strong hover:text-accent">
                            {{ $option['email'] }}
                        </a>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. Contact Form --}}
    <section id="contact-form" class="public-section-atmosphere" aria-labelledby="contact-form-heading" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <div class="text-center max-w-xl mx-auto">
                <h2 id="contact-form-heading" class="text-2xl font-semibold text-neutral-900">{{ __('Send us a message') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __("Tell us what's going on and we'll get back to you at the email address you provide.") }}</p>
            </div>

            <x-card class="mt-8">
                @if ($submitted)
                    <x-alert variant="success" role="status">
                        {{ __("Message received. We'll get back to you at the email address you provided.") }}
                    </x-alert>
                @else
                    <form wire:submit="send" class="space-y-4" novalidate>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="contact-full-name" :value="__('Full Name')" />
                                <x-text-input id="contact-full-name" type="text" wire:model.blur="fullName" class="mt-1 block w-full" required autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('fullName')" />
                            </div>
                            <div>
                                <x-input-label for="contact-email" :value="__('Email Address')" />
                                <x-text-input id="contact-email" type="email" wire:model.blur="email" class="mt-1 block w-full" required autocomplete="email" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="contact-subject" :value="__('Subject')" />
                            <x-text-input id="contact-subject" type="text" wire:model.blur="subject" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('subject')" />
                        </div>

                        <div>
                            <x-input-label for="contact-category" :value="__('Category')" />
                            <select id="contact-category" wire:model.blur="category" required
                                    class="mt-1 min-h-11 block w-full rounded-md border-neutral-300 bg-surface-card text-neutral-900 focus:border-accent focus:ring-accent">
                                <option value="" disabled>{{ __('Choose a category') }}</option>
                                @foreach ($this->categories() as $categoryOption)
                                    <option value="{{ $categoryOption->value }}">{{ $categoryOption->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>

                        <div>
                            <x-input-label for="contact-message" :value="__('Message')" />
                            <textarea id="contact-message" wire:model.blur="message" rows="6" required
                                      class="mt-1 block w-full bg-neutral-50 border-neutral-300 focus:border-accent focus:ring-accent rounded-md shadow-sm text-sm"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('message')" />
                        </div>

                        <div>
                            <button type="submit" wire:loading.attr="disabled" wire:target="send"
                                    class="inline-flex items-center justify-center h-12 px-6 rounded-md bg-accent-strong text-white font-semibold text-sm hover:bg-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent transition-colors duration-instant disabled:opacity-50">
                                <span wire:loading.remove wire:target="send">{{ __('Send Message') }}</span>
                                <span wire:loading wire:target="send" role="status">{{ __('Sending…') }}</span>
                            </button>
                            <p class="mt-3 text-xs text-neutral-500">
                                {{ __('We read every message. See Support Expectations below for what to expect next.') }}
                            </p>
                        </div>
                    </form>
                @endif
            </x-card>
        </div>
    </section>

    {{--
        4/5/6. Office Information, Business Hours, Map — grouped in one
        section since all three depend on business facts that don't
        exist yet. Every unconfirmed value is visibly marked, not
        presented as settled — same panel treatment already established
        by `terms.blade.php`/`privacy.blade.php`'s "Clauses requiring
        legal review" note, reused verbatim for consistency rather than
        inventing a second "pending" visual language.
    --}}
    <section class="public-section-quiet" aria-labelledby="office-info-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20">
            <h2 id="office-info-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('Office & hours') }}</h2>

            <div class="mt-4 max-w-2xl mx-auto rounded-xl border border-amber-300 bg-amber-50 p-5" role="note">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('To be confirmed before launch') }}</p>
                <p class="mt-2 text-sm text-amber-900">
                    {{ __('SlipGuard has not yet named a registered office, phone line, or company registration details. The information below is grouped here so it can be replaced with the real values once Product Office confirms them — nothing here should be treated as current.') }}
                </p>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-3xl mx-auto text-center">
                <div>
                    <x-heroicon-o-building-office-2 class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-semibold text-neutral-900">{{ __('Headquarters') }}</p>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('SlipGuard') }}</p>
                    <p class="mt-1 text-sm text-neutral-500">{{ __('Address to be confirmed') }}</p>
                </div>
                <div>
                    <x-heroicon-o-phone class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-semibold text-neutral-900">{{ __('Phone') }}</p>
                    <p class="mt-1 text-sm text-neutral-500">{{ __('To be confirmed') }}</p>
                </div>
                <div>
                    <x-heroicon-o-clock class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-semibold text-neutral-900">{{ __('Business Hours') }}</p>
                    <p class="mt-1 text-sm text-neutral-500">{{ __('To be confirmed — email reaches us any time') }}</p>
                </div>
            </div>

            {{--
                Interactive Map — directive explicitly asks for a styled
                placeholder, not a live embed. A plain, non-interactive
                container (no fabricated pin, no invented coordinates).
            --}}
            <div class="mt-8 max-w-3xl mx-auto rounded-xl border border-neutral-300 bg-surface-soft h-56 flex items-center justify-center" role="img" aria-label="{{ __('Map placeholder — office location not yet confirmed') }}">
                <div class="text-center">
                    <x-heroicon-o-map class="size-6 mx-auto text-neutral-400" aria-hidden="true" />
                    <p class="mt-2 text-xs font-medium text-neutral-500">{{ __('Map will appear here once an office location is confirmed') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. FAQ — every answer checked against real, current product behaviour. --}}
    <section class="public-section-atmosphere" aria-label="{{ __('Frequently asked questions') }}" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
            <h2 class="text-2xl font-semibold text-neutral-900 text-center">{{ __('Frequently asked questions') }}</h2>
            <div class="mt-10 space-y-6">
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Do I need an account?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Yes. An account lets you save analyses, use the Planner and Journal, and keep a history of your decisions.') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Does SlipGuard place bets?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('No. SlipGuard provides deterministic structural analysis only — it never places a bet or holds customer funds. Every wager stays between you and your licensed operator.') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Can I upload a screenshot of my slip?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Yes. SlipGuard supports several ways to add a slip — type it in, paste copied text, or upload a PDF or screenshot.') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900">{{ __('Is my information secure?') }}</h3>
                    <p class="mt-1 text-sm text-neutral-600">{{ __('Your session is protected the way any secure web application protects it, and SlipGuard sets no analytics, advertising, or third-party tracking cookie. See the Privacy Policy for the full detail.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{--
        8. Support Expectations — response-time ranges reworded as a
        target, not a current guarantee: no support operation exists
        yet to actually staff these numbers (`SubmitContactMessage`'s
        own note — no Mailable, no delivery, persistence only).
    --}}
    <section class="public-section-quiet" aria-labelledby="support-expectations-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20">
            <h2 id="support-expectations-heading" class="text-2xl font-semibold text-neutral-900 text-center">{{ __('What to expect') }}</h2>
            <p class="mt-3 text-sm text-neutral-600 text-center max-w-lg mx-auto">
                {{ __('Target response times, once support is fully staffed — not a guarantee we can meet yet.') }}
            </p>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([
                    ['title' => __('Technical Support'), 'target' => __('Within 24 hours')],
                    ['title' => __('General Enquiries'), 'target' => __('1–2 business days')],
                    ['title' => __('Partnerships'), 'target' => __('2–5 business days')],
                    ['title' => __('Bug Reports'), 'target' => __('Acknowledged within one business day')],
                ] as $expectation)
                    <x-card class="text-center">
                        <p class="text-sm font-semibold text-neutral-900">{{ $expectation['title'] }}</p>
                        <p class="mt-1 text-sm text-neutral-600">{{ $expectation['target'] }}</p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    {{--
        9. Social Links — real handles confirmed by Product Office
        (username "slipguardhq" on every platform), same URLs now live
        in `public-footer.blade.php`. All seven brand glyphs in
        `<x-social-icon>` are real official marks (Simple Icons project,
        MIT-licensed), not hand-drawn — see that component's own note.
    --}}
    <section class="public-section-atmosphere" aria-labelledby="social-links-heading" data-reveal>
        <div class="container-marketing mx-auto px-6 py-12 text-center">
            <h2 id="social-links-heading" class="text-xs font-semibold tracking-widest text-neutral-400 uppercase">{{ __('Find us online') }}</h2>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                @foreach ([
                    ['brand' => 'linkedin', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/slipguardhq'],
                    ['brand' => 'x', 'label' => 'X', 'url' => 'https://x.com/slipguardhq'],
                    ['brand' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/slipguardhq'],
                    ['brand' => 'facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/slipguardhq'],
                    ['brand' => 'tiktok', 'label' => 'TikTok', 'url' => 'https://tiktok.com/@slipguardhq'],
                    ['brand' => 'threads', 'label' => 'Threads', 'url' => 'https://threads.net/@slipguardhq'],
                    ['brand' => 'youtube', 'label' => 'YouTube', 'url' => 'https://youtube.com/@slipguardhq'],
                ] as $social)
                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                       class="flex size-10 items-center justify-center rounded-full border border-neutral-300 bg-surface-card text-neutral-500 transition-colors hover:border-neutral-400 hover:bg-surface-soft hover:text-neutral-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                       aria-label="{{ __($social['label']) }}">
                        <x-social-icon :brand="$social['brand']" class="size-4" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Security Notice --}}
    <section class="public-section-quiet" aria-labelledby="security-notice-heading" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <x-card variant="soft">
                <h2 id="security-notice-heading" class="text-sm font-semibold text-neutral-900">{{ __('Found a security vulnerability?') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('Please email') }}
                    <a href="mailto:security@slipguard.ai" class="font-semibold text-accent-strong hover:text-accent">security@slipguard.ai</a>
                    {{ __('and include a description of the issue, steps to reproduce it, and screenshots where applicable. We take every report seriously.') }}
                </p>
            </x-card>
        </div>
    </section>

    {{-- Footer CTA — anchors back to the form rather than a second, circular "Contact" destination. --}}
    <section class="light-sweep bg-gradient-cta py-20" aria-labelledby="contact-cta-heading">
        <div class="container-marketing mx-auto px-6 text-center">
            <h2 id="contact-cta-heading" class="text-2xl sm:text-3xl font-semibold text-white">{{ __('Still have questions?') }}</h2>
            <p class="mt-3 text-sm text-blue-100">{{ __('Our team is always happy to help.') }}</p>
            <div class="mt-7">
                <x-marketing-cta-button href="#contact-form" :fixed-height="true">
                    {{ __('Contact Support') }}
                </x-marketing-cta-button>
            </div>
        </div>
    </section>
</div>
