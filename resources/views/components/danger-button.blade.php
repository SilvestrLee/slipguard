{{-- U-16.0 (Phase 1/6): was raw Tailwind `red-*`, the one button with no dark-mode value — moved to the existing `alert-error` token (DESIGN_TOKENS.md), consistent with every other error-coloured element in the app. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-alert-error border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-alert-error-strong active:bg-alert-error-strong focus:outline-none focus:ring-2 focus:ring-alert-error focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
