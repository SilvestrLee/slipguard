@props(['status'])

{{-- U-16.0 (Phase 1/6): was raw Tailwind `green-600`, no dark-mode value — moved to the existing `alert-success` token. --}}
@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-alert-success-strong']) }}>
        {{ $status }}
    </div>
@endif
