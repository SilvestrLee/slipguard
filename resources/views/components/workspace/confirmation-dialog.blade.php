@props(['name', 'title'])

<x-modal :name="$name" focusable>
    <div class="p-6">
        <h2 class="workspace-title">{{ $title }}</h2>
        <div class="mt-3 workspace-helper">{{ $slot }}</div>
        <div class="mt-6 flex justify-end gap-3">{{ $actions }}</div>
    </div>
</x-modal>
