{{-- U-16.0 (Phase 1/6): default content surface moved to `bg-surface-card` (was raw `bg-neutral-50`, indistinguishable from the page behind it); the ring/shadow below were also raw pre-SGDS values. --}}
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-surface-card'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-elevation-2 {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        {{-- Founder direct instruction (2026-07-27): no shadows — the faint `ring-neutral-900/5` (barely a shadow substitute) replaced with a real, visible border. --}}
        <div class="rounded-md border border-neutral-300 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
