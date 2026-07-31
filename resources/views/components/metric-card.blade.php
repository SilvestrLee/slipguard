@props(['label', 'inverse' => false, 'explanation' => null])

{{--
    U-12.0 (SGDS) — new component: a single numeric/label pairing, used by
    the homepage Intelligence Credibility Section (`inverse` variant, on
    the constant dark surface-inverse band) and reusable for any future
    stat display elsewhere in the product. The value is the default slot
    (not a plain prop) so a caller can nest a count-up animation target
    span inside it, as the Intelligence Section does.

    `PO-U15.6-001` §32: optional `explanation` prop, added backward-
    compatibly (defaults to null, only rendered when supplied — this
    component had no other call site to break) — always visible when
    present, never hover-only, since touch/keyboard users can't hover and
    this is trust information, not decoration.
--}}
<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <p @class([
        'text-3xl sm:text-4xl font-semibold font-tabular',
        'text-white' => $inverse,
        'text-neutral-900' => ! $inverse,
    ])>{{ $slot }}</p>
    <p @class([
        'mt-2 text-xs font-semibold uppercase tracking-wide',
        'text-neutral-300' => $inverse,
        'text-neutral-500' => ! $inverse,
    ])>{{ $label }}</p>
    @if ($explanation)
        <p @class([
            'mt-1.5 text-xs leading-snug',
            'text-slate-400' => $inverse,
            'text-neutral-500' => ! $inverse,
        ])>{{ $explanation }}</p>
    @endif
</div>
