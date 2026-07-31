@props(['items' => [], 'id' => 'command-palette'])
{{--
    Founder direct instruction (2026-07-28), personalised per that same
    instruction: a quick-navigation search modal, reusing the existing
    `<x-modal>` shell (backdrop, `bg-surface-card`, border-based elevation
    — no new modal pattern invented) rather than a bespoke overlay.
    `$items` is always this page's own real, named routes — never
    OddStorm's betting-market content, which was reference material for
    the interaction pattern only (overlay + input + "quick navigation"
    list + keyboard hints), per `VISUAL_INSPIRATION.md`'s existing rule
    that reference brands are borrowed for characteristics, not copied
    screens. No full-text search across slip/analysis data exists — this
    is client-side filtering over a small, fixed list of destinations,
    not a real search index.
--}}
<x-modal :name="$id" maxWidth="lg" :centered="true">
    <div
        x-data="{
            query: '',
            items: {{ Illuminate\Support\Js::from($items) }},
            activeIndex: 0,
            get filtered() {
                if (! this.query.trim()) return this.items;
                const q = this.query.toLowerCase();
                return this.items.filter(i => i.label.toLowerCase().includes(q) || i.description.toLowerCase().includes(q));
            },
            openPalette() {
                this.query = '';
                this.activeIndex = 0;
                this.$nextTick(() => this.$refs.paletteInput && this.$refs.paletteInput.focus());
            },
            navigate(delta) {
                const max = this.filtered.length - 1;
                this.activeIndex = Math.max(0, Math.min(max, this.activeIndex + delta));
            },
            go() {
                const item = this.filtered[this.activeIndex];
                if (item) { window.location.href = item.url; }
            },
        }"
        x-on:open-modal.window="$event.detail === '{{ $id }}' && openPalette()"
        @keydown.arrow-down.prevent="navigate(1)"
        @keydown.arrow-up.prevent="navigate(-1)"
        @keydown.enter.prevent="go()"
    >
        <div class="flex items-center gap-3 px-4 py-3 border-b border-neutral-200">
            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-neutral-400 shrink-0" />
            <input x-ref="paletteInput" x-model="query" @input="activeIndex = 0" type="text"
                   placeholder="{{ __('Search SlipGuard — jump to a page…') }}"
                   class="flex-1 border-0 focus:ring-0 bg-transparent text-sm text-neutral-900 placeholder:text-neutral-400">
            <button type="button" @click="$dispatch('close-modal', '{{ $id }}')" aria-label="{{ __('Close') }}" class="text-neutral-400 hover:text-neutral-600">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="px-4 pt-3 pb-1 text-xs font-semibold tracking-wide text-neutral-500 uppercase">
            {{ __('Quick Navigation') }}
        </div>

        <div class="max-h-96 overflow-y-auto px-2 pb-2">
            <template x-for="(item, index) in filtered" :key="item.url">
                <a :href="item.url" wire:navigate
                   @mouseenter="activeIndex = index"
                   :class="activeIndex === index ? 'bg-neutral-100' : ''"
                   class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-md">
                    <span class="flex items-center gap-3 min-w-0">
                        <x-heroicon-o-arrow-right class="h-4 w-4 text-accent-strong shrink-0" />
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-neutral-900" x-text="item.label"></span>
                            <span class="block text-xs text-neutral-500 truncate" x-text="item.description"></span>
                        </span>
                    </span>
                    <span class="shrink-0 text-[10px] font-semibold tracking-wide text-neutral-400 border border-neutral-300 rounded px-1.5 py-0.5">{{ __('JUMP') }}</span>
                </a>
            </template>
            <div x-show="filtered.length === 0" class="px-3 py-6 text-center text-sm text-neutral-500">
                {{ __('No matching page.') }}
            </div>
        </div>

        <div class="flex items-center gap-4 px-4 py-2.5 border-t border-neutral-200 text-xs text-neutral-400">
            <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded border border-neutral-300 text-[10px]">↑↓</kbd> {{ __('navigate') }}</span>
            <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded border border-neutral-300 text-[10px]">Enter</kbd> {{ __('open') }}</span>
            <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded border border-neutral-300 text-[10px]">Esc</kbd> {{ __('close') }}</span>
        </div>
    </div>
</x-modal>
