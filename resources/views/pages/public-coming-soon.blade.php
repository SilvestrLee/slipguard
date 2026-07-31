{{--
    U-13.0 — honest public stub for pages this commission named but that
    have no real content to present yet: Pricing (no pricing model has
    ever been established — PROJECT.md's MVP Non-Goals explicitly
    excludes "advanced subscriptions", and `PO-U11.2-CL-002` explicitly
    rejects "pricing presentation" as an OddStorm-derived pattern),
    Privacy, and Terms (PRODUCT_GUARDRAILS.md's own standing note: final
    legal/regulatory wording requires review before public launch, which
    hasn't happened). Never fabricates commercial or legal content.
--}}
<x-public-layout :title="$title">
    <section class="public-section-quiet">
        <div class="container-standard mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <x-empty-state :title="__(':title is on the way.', ['title' => $title])" :description="$description" />
        </div>
    </section>
</x-public-layout>
