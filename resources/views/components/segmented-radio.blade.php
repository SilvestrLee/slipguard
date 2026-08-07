{{--
    U-19.1 (`PO-UX-001` UX refinement pass) — new component pattern,
    documented in `docs/05-ux/COMPONENT_PRINCIPLES.md`'s Segmented Radio
    entry per the Frontend Work Rule. Real `<input type="radio">` elements
    (native keyboard nav, screen-reader semantics for free), visually styled
    as a segmented control via the standard sr-only + peer pattern — no new
    JS, no Alpine, works with `wire:model.live` exactly like any other
    input. For a small (2-3), mutually exclusive, single-tap set of
    options — never a replacement for `<select>` once the option count
    grows past what fits comfortably as touch targets on a phone.
--}}
@props(['name', 'wireModel', 'options', 'disabled' => false])

<div role="radiogroup" {{ $attributes->class(['inline-flex w-full rounded-md border border-neutral-300 bg-neutral-50 p-1 gap-1']) }}>
    @foreach ($options as $value => $label)
        <label class="flex-1">
            <input type="radio" name="{{ $name }}" value="{{ $value }}" wire:model.live="{{ $wireModel }}"
                   class="peer sr-only" @disabled($disabled) />
            <span class="flex min-h-10 cursor-pointer items-center justify-center rounded px-3 text-sm font-medium text-neutral-600 transition-colors duration-instant hover:bg-neutral-100 peer-checked:bg-accent-strong peer-checked:text-white peer-checked:hover:bg-accent-strong peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent peer-disabled:cursor-not-allowed peer-disabled:opacity-50">
                {{ $label }}
            </span>
        </label>
    @endforeach
</div>
