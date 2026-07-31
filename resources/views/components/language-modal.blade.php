@props(['id' => 'language-modal'])
{{--
    Refines the language selector (founder direct instruction, 2026-07-28
    original; this pass: "inspired by the search modal," 2026-07-31) by
    reusing `<x-command-palette>`'s established shell — the same `<x-modal>`
    centered overlay, header row (icon + title + close button), bordered
    list rows — per `VISUAL_INSPIRATION.md`'s "borrowed for characteristics,
    not copied screens" rule already applied to that component. No search
    input here: six languages is too small a list to need filtering, so
    that specific control isn't reused, only the modal's visual grammar.

    Still exactly what the original dropdown already said, just structured
    rather than a single line: SlipGuard is English-only today
    (`config/app.php`'s locale, no other `lang/` set exists). Every
    non-English row is explicitly labelled "Coming Soon" and is inert —
    not a disabled button pretending to be interactive, just clearly
    unavailable content, the same honesty `Labs Feature Cards` already
    apply to future product capability. No new locale, translation set, or
    switching logic is introduced — this is presentation only, unchanged
    from the MVP boundary the original component already respected.

    Specialist Design Capability Invocation: UI UX Pro Max's "Disabled
    States" guidance (reduced opacity + not-allowed cursor, never the same
    treatment as an enabled control) is adopted directly below. 21st.dev's
    top result for this query used flag icons per language — rejected:
    `ICONOGRAPHY.md`/this codebase's existing restraint (no emoji-as-icon,
    the current dropdown already uses text only) argues against them, and
    flags introduce a real correctness problem several languages here
    don't have a single obvious one for (Arabic, for a start) — plain
    language names avoid that entirely rather than solving it decoratively.
--}}
@php
    $comingSoon = [
        ['english' => 'Spanish', 'native' => 'Español'],
        ['english' => 'Portuguese', 'native' => 'Português'],
        ['english' => 'French', 'native' => 'Français'],
        ['english' => 'German', 'native' => 'Deutsch'],
        ['english' => 'Arabic', 'native' => 'العربية'],
    ];
@endphp
<x-modal :name="$id" maxWidth="md" :centered="true">
    <div>
        <div class="flex items-center gap-3 px-4 py-3 border-b border-neutral-200">
            <x-heroicon-o-language class="h-5 w-5 text-neutral-400 shrink-0" />
            <span class="flex-1 text-sm font-medium text-neutral-900">{{ __('Language') }}</span>
            <button type="button" @click="$dispatch('close-modal', '{{ $id }}')" aria-label="{{ __('Close') }}" class="text-neutral-400 hover:text-neutral-600">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="px-4 pt-3 pb-1 text-xs font-semibold tracking-wide text-neutral-500 uppercase">
            {{ __('Available now') }}
        </div>

        <div class="px-2 pb-2">
            <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-md">
                <span class="text-sm font-medium text-neutral-900">{{ __('English') }}</span>
                <x-heroicon-o-check class="h-4 w-4 text-accent-strong shrink-0" aria-hidden="true" />
                <span class="sr-only">{{ __('Currently selected') }}</span>
            </div>
        </div>

        <div class="px-4 pt-2 pb-1 text-xs font-semibold tracking-wide text-neutral-500 uppercase">
            {{ __('Coming soon') }}
        </div>

        <div class="px-2 pb-3">
            @foreach ($comingSoon as $language)
                <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-md opacity-50 cursor-not-allowed" aria-disabled="true">
                    <span class="text-sm text-neutral-700">
                        {{ $language['english'] }}
                        <span class="text-neutral-400">({{ $language['native'] }})</span>
                    </span>
                    <x-badge tone="neutral">{{ __('Coming Soon') }}</x-badge>
                </div>
            @endforeach
        </div>

        <div class="px-4 py-2.5 border-t border-neutral-200 text-xs text-neutral-500">
            {{ __('More languages are on the way — this list will grow as they\'re ready.') }}
        </div>
    </div>
</x-modal>
