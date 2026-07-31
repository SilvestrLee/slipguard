@props(['reason'])

{{--
    U-11.3 §10 option 4: the honest "not yet available" state shared by
    every unsupported intake method. Never implies the attempt failed or
    that something went wrong — it didn't fail, it simply isn't built
    yet — and always routes back to Manual Entry, the one method that
    actually works.
--}}
<div role="status" class="mt-4 flex items-start gap-3 bg-neutral-100 border border-neutral-200 rounded-lg p-4">
    <x-heroicon-o-clock class="size-5 text-neutral-400 shrink-0 mt-0.5" aria-hidden="true" />
    <div>
        <p class="text-sm font-semibold text-neutral-900">{{ __('Automated reading isn\'t available yet') }}</p>
        <p class="mt-1 text-sm text-neutral-600">{{ $reason }}</p>
        <a href="{{ route('analyze.create') }}" wire:navigate
           class="mt-3 inline-flex items-center text-sm font-medium text-accent-strong hover:underline">
            {{ __('Enter this slip manually instead →') }}
        </a>
    </div>
</div>
