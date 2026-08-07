{{--
    U-19.1 (`PO-UX-001` UX refinement pass) — new component pattern,
    documented in `docs/05-ux/COMPONENT_PRINCIPLES.md`'s Number Stepper
    entry per the Frontend Work Rule. Decrement/increment buttons flank a
    normal, still-directly-editable numeric text input — a one-handed-mobile
    alternative to bringing up the keyboard for a small, bounded value,
    never the only way to set it. Deliberately no drag-to-adjust or
    animated digit-roll motion (both real patterns in wider UI practice) —
    `MOTION_SYSTEM.md` restricts motion to what confirms/guides/clarifies,
    and a plain increment already does that.
--}}
@props(['decrementAction', 'incrementAction', 'wireModel', 'ariaLabel', 'disabled' => false])

<div {{ $attributes->class(['inline-flex items-center gap-2']) }}>
    <button type="button" wire:click="{{ $decrementAction }}"
            class="flex size-11 shrink-0 items-center justify-center rounded-md border border-neutral-300 bg-neutral-50 text-lg leading-none text-neutral-600 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40"
            @disabled($disabled) aria-label="{{ __('Decrease') }}">
        <span aria-hidden="true">&minus;</span>
    </button>
    <input type="text" inputmode="decimal" wire:model.live="{{ $wireModel }}" aria-label="{{ $ariaLabel }}"
           class="w-16 shrink-0 rounded-md border-neutral-300 bg-neutral-50 text-center font-tabular text-sm focus:border-accent focus:ring-accent"
           @disabled($disabled) />
    <button type="button" wire:click="{{ $incrementAction }}"
            class="flex size-11 shrink-0 items-center justify-center rounded-md border border-neutral-300 bg-neutral-50 text-lg leading-none text-neutral-600 hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40"
            @disabled($disabled) aria-label="{{ __('Increase') }}">
        <span aria-hidden="true">+</span>
    </button>
</div>
