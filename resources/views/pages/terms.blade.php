{{--
    `CO-MVP-001`/`PO-CO-002` — production Terms of Service, replacing the
    honest `public-coming-soon` stub. Drafted by Compliance Office as a
    complete, jurisdiction-neutral governance document, accurately
    describing this application's real behaviour — not a substitute for
    review by qualified legal counsel. Clauses genuinely requiring that
    review are marked inline, not silently presented as settled; see
    `docs/09-compliance/CO-005-LEGAL-REVIEW-REGISTER.md` for the complete,
    consolidated list and status of every such item.
--}}
<x-public-layout title="Terms of Service" description="The terms governing your use of SlipGuard.">

    <section class="public-hero-atmosphere bg-gradient-hero" aria-labelledby="terms-hero-heading">
        <div class="container-marketing mx-auto px-6 py-16 sm:py-20 text-center">
            <p class="text-xs font-semibold tracking-widest text-accent-strong uppercase">{{ __('Terms of Service') }}</p>
            <h1 id="terms-hero-heading" class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-neutral-900 max-w-2xl mx-auto">
                {{ __('The terms governing your use of SlipGuard') }}
            </h1>
            <p class="mt-4 text-sm text-neutral-500">{{ __('Last updated: :date', ['date' => 'August 2026']) }}</p>
        </div>
    </section>

    <section class="public-section-quiet" aria-label="{{ __('Terms of Service') }}" data-reveal>
        <div class="container-reading mx-auto px-4 sm:px-6 lg:px-8 py-20 space-y-10">

            <div>
                <p class="text-sm text-neutral-600">
                    {{ __('These Terms of Service ("Terms") govern your access to and use of SlipGuard (the "Service"), operated by SlipGuard ("we", "us", "our"). By creating an account or otherwise using the Service, you agree to these Terms. If you do not agree, do not use the Service.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('1. What SlipGuard Is') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('SlipGuard is a deterministic decision-intelligence platform. It evaluates the structural risk of a betting slip you provide — the number of selections, combined odds, individual odds, risk concentration, and market complexity — and explains, in plain language, which factor contributes most to that risk. The same slip and the same evidence always produce the same result.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('2. What SlipGuard Is Not') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __('SlipGuard does not, and will not:') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-neutral-600 list-disc list-outside pl-5">
                    <li>{{ __('Predict the outcome of any sporting event, or claim that any selection, team, or slip is "safe," "guaranteed," or "likely to win."') }}</li>
                    <li>{{ __('Accept deposits, hold funds, or process any payment on behalf of a bookmaker or other gambling operator.') }}</li>
                    <li>{{ __('Place, adjust, increase, or automate a wager on your behalf, under any circumstance.') }}</li>
                    <li>{{ __('Act as a bookmaker, gambling operator, payment processor, or betting exchange.') }}</li>
                    <li>{{ __('Allow any affiliate agreement, bookmaker partnership, or commercial incentive to influence a calculation, classification, or recommendation.') }}</li>
                </ul>
                <p class="mt-3 text-sm text-neutral-600">
                    {{ __('Every wager you place happens entirely outside SlipGuard, directly with your own licensed operator. SlipGuard is never a party to that transaction.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('3. Eligibility') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('You must be at least 18 years old to create an account or use the Service. By using the Service, you represent that you meet this requirement and that you are legally permitted, in your jurisdiction, both to use a service of this kind and to engage with betting content generally.') }}
                </p>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('SlipGuard does not currently verify your specific jurisdiction or the legality of betting activity where you live — this is your responsibility.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('4. Your Account') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __("You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account. Tell us promptly if you believe your account has been compromised. You may delete your account at any time from your Profile page; see our Privacy Policy for what happens to your data when you do.") }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('5. Acceptable Use') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">{{ __('You agree not to:') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-neutral-600 list-disc list-outside pl-5">
                    <li>{{ __('Use the Service for any unlawful purpose, or in a way that violates the rights of others.') }}</li>
                    <li>{{ __('Attempt to circumvent, disable, or interfere with any part of the Service, including rate limits or authentication.') }}</li>
                    <li>{{ __('Scrape, reverse-engineer, or systematically extract data from the Service beyond your own account activity.') }}</li>
                    <li>{{ __('Upload content you do not have the right to upload, or that contains malicious code.') }}</li>
                    <li>{{ __('Misrepresent your identity or impersonate another person.') }}</li>
                </ul>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('6. Your Content') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('You retain ownership of the slips, screenshots, documents, and journal reflections you submit to the Service ("Your Content"). You grant SlipGuard a limited licence to store and process Your Content solely to provide the Service to you. We do not sell Your Content, and we do not use it to train external, general-purpose models.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('7. No Financial Intermediation') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('SlipGuard never enters your financial relationship with any bookmaker or gambling operator. We do not process, transmit, or hold stakes or winnings, and we accept no commission or fee tied to any bet you place. Any subscription fee we may charge in the future would be solely for access to the Service itself, not connected to betting activity in any way.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('8. Intellectual Property') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('© :year SlipGuard. The SlipGuard name, logo, and the Service\'s underlying software, design, and methodology are owned by SlipGuard or its licensors and protected by applicable intellectual property law. Nothing in these Terms grants you any right to use SlipGuard\'s trademarks or branding without our prior written consent.', ['year' => now()->year]) }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('9. Disclaimers') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('The Service is provided "as is." SlipGuard\'s structural risk analysis is deterministic and evidence-based, but it is not, and must never be treated as, financial, betting, or professional advice, and it does not guarantee any outcome. You remain solely responsible for every decision you make, including whether and how much to wager.') }}
                </p>
            </div>

            <div class="rounded-xl border border-amber-300 bg-amber-50 p-5" role="note">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('Clauses requiring legal review') }}</p>
                <p class="mt-2 text-sm text-amber-900">
                    {{ __('The following sections are placeholders pending confirmation by qualified legal counsel once Product Office names SlipGuard\'s launch market(s) — they are deliberately not finalised here.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('10. Limitation of Liability') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('[To be finalised with legal counsel once launch jurisdiction is confirmed.] To the fullest extent permitted by applicable law, SlipGuard will not be liable for any indirect, incidental, or consequential loss arising from your use of the Service, including any loss connected to a betting decision you made.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('11. Governing Law and Dispute Resolution') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('[Governing law, venue, and dispute-resolution mechanism to be confirmed by Product Office and legal counsel once SlipGuard\'s launch market(s) are determined. This section is intentionally incomplete rather than naming a jurisdiction that has not actually been decided.]') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('12. Termination') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('You may stop using the Service and delete your account at any time. We may suspend or terminate your access if you materially breach these Terms, including the Acceptable Use section above.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('13. Changes to These Terms') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('We may update these Terms as the Service evolves. We will update the "Last updated" date above when we do; continued use of the Service after a change constitutes acceptance of the updated Terms.') }}
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-neutral-900">{{ __('14. Contact') }}</h2>
                <p class="mt-2 text-sm text-neutral-600">
                    {{ __('[A dedicated support contact channel has not been established yet — see the Contact page.] Questions about these Terms should be directed there once available.') }}
                </p>
            </div>

        </div>
    </section>

</x-public-layout>
