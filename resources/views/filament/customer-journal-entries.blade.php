<div class="space-y-4">
    @forelse ($entries as $entry)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ $entry->slipAnalysis->bettingSlip->name ?: 'Untitled slip' }}
                &middot;
                {{ $entry->created_at->format('j M Y, H:i') }}
            </p>
            <p class="mt-2 text-sm text-gray-900 dark:text-gray-100">{{ $entry->reflection }}</p>
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">This customer has no journal entries.</p>
    @endforelse
</div>
