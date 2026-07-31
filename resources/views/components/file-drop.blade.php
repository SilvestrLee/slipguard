@props(['inputId', 'accept', 'label', 'hint' => null])

{{--
    U-12.0 (SGDS) — extracted from the intake shell's two near-identical
    dropzone blocks (Screenshot/PDF). COMPONENT_PRINCIPLES.md's Upload
    Areas principles: dashed border, generous padding, a visible
    keyboard-operable "choose file" fallback via the wrapping <label>.

    Bounded-scope intake (2026-07-28): `$attributes` (specifically
    `wire:model`) is now forwarded to the actual `<input type="file">`,
    not the wrapping `<label>` — a real upload wouldn't have bound to
    Livewire at all otherwise, since the label itself has no file value.
--}}
<label for="{{ $inputId }}" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-neutral-300 rounded-lg p-8 text-center cursor-pointer hover:bg-surface-soft transition-colors">
    <x-heroicon-o-arrow-up-tray class="size-8 text-neutral-400" aria-hidden="true" />
    <span class="text-sm text-neutral-600">{{ $label }}</span>
    @if ($hint)
        <span class="text-xs text-neutral-400">{{ $hint }}</span>
    @endif
    <input id="{{ $inputId }}" type="file" accept="{{ $accept }}" {{ $attributes->merge(['class' => 'sr-only']) }}>
</label>
