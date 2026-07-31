{{-- Founder direct instruction (2026-07-27): gradients for button backgrounds — `bg-gradient-button` (DESIGN_TOKENS.md), replacing the flat `bg-accent-strong` fill. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-button border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:brightness-110 focus:brightness-110 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
