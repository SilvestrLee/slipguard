<?php

use App\Actions\Labs\RecordMobileWaitlistInterest;
use App\Models\LabsFeature;
use Database\Seeders\LabsMobileAppFeatureSeeder;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

/**
 * `PO-U19.1-001` §15 — "Clicking 'Notify Me' should open a lightweight
 * modal... No lengthy form. The goal is minimal friction." An
 * authenticated visitor is never asked for an email they've already
 * given SlipGuard — only the optional platform preference. A guest gives
 * an email and, optionally, a platform preference. Nothing else.
 */
new class extends Component
{
    public ?string $email = null;

    public ?string $platformPreference = null;

    public bool $submitted = false;

    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    public function joinWaitlist(RecordMobileWaitlistInterest $recordInterest): void
    {
        $this->validate([
            'email' => $this->isAuthenticated() ? ['nullable', 'email:rfc,dns', 'max:255'] : ['required', 'email:rfc,dns', 'max:255'],
            'platformPreference' => ['nullable', 'in:iphone,android,both'],
        ]);

        $feature = LabsFeature::where('slug', LabsMobileAppFeatureSeeder::SLUG)->firstOrFail();

        $recordInterest->execute($feature, Auth::user(), $this->email, $this->platformPreference);

        $this->submitted = true;
    }
}; ?>

<x-modal name="mobile-waitlist" maxWidth="sm" :centered="true">
    <div class="p-6">
        @if ($submitted)
            <div class="flex flex-col items-center text-center gap-3 py-4" role="status">
                <x-heroicon-o-check-circle class="h-10 w-10 text-accent-strong" aria-hidden="true" />
                <p class="text-sm font-medium text-neutral-900">{{ __("You're on the list.") }}</p>
                <p class="text-sm text-neutral-500">{{ __("We'll email you when the native apps are ready.") }}</p>
                <button type="button" @click="$dispatch('close-modal', 'mobile-waitlist')"
                        class="mt-2 text-sm font-medium text-accent-strong hover:underline">
                    {{ __('Close') }}
                </button>
            </div>
        @else
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-neutral-900">{{ __('Join the waitlist') }}</h2>
                <button type="button" @click="$dispatch('close-modal', 'mobile-waitlist')" aria-label="{{ __('Close') }}" class="text-neutral-400 hover:text-neutral-600">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <form wire:submit="joinWaitlist" class="space-y-4">
                @unless ($this->isAuthenticated())
                    <div>
                        <label for="mobile-waitlist-email" class="block text-sm font-medium text-neutral-700 mb-1">{{ __('Email address') }}</label>
                        <input id="mobile-waitlist-email" type="email" wire:model="email" required
                               placeholder="{{ __('you@example.com') }}"
                               class="block w-full rounded-md border-neutral-300 shadow-sm focus:border-accent focus:ring-accent text-sm">
                        @error('email') <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>
                @endunless

                <fieldset>
                    <legend class="block text-sm font-medium text-neutral-700 mb-2">{{ __('Preferred platform (optional)') }}</legend>
                    <div class="space-y-2">
                        @foreach (['iphone' => __('iPhone'), 'android' => __('Android'), 'both' => __('Both')] as $value => $label)
                            <label class="flex items-center gap-2 text-sm text-neutral-700">
                                <input type="radio" wire:model="platformPreference" value="{{ $value }}"
                                       class="text-accent-strong focus:ring-accent border-neutral-300">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <x-primary-button type="submit" class="w-full justify-center">
                    {{ __('Join the waitlist') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-modal>
