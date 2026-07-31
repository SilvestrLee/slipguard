@props(['label', 'state' => 'pending'])

<li @class([
    'flex items-center gap-3 rounded-md border px-3 py-2.5 text-sm',
    'border-accent/30 bg-accent/10 text-neutral-900' => $state === 'current',
    'border-neutral-200 bg-surface-card text-neutral-700' => $state === 'complete',
    'border-transparent text-neutral-500' => $state === 'pending',
]) aria-current="{{ $state === 'current' ? 'step' : 'false' }}">
    @if ($state === 'complete')
        <x-heroicon-o-check-circle class="size-5 shrink-0 text-accent-strong" aria-hidden="true" />
    @elseif ($state === 'current')
        <span class="size-2.5 shrink-0 rounded-full bg-accent-strong" aria-hidden="true"></span>
    @else
        <span class="size-2.5 shrink-0 rounded-full border border-neutral-400" aria-hidden="true"></span>
    @endif
    <span class="{{ $state === 'current' ? 'font-semibold' : 'font-medium' }}">{{ $label }}</span>
</li>
