<div class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($products as $product)
        <x-store.product-card :product="$product" />
    @empty
        <div class="col-span-full">
            <x-store.empty-state
                icon="search"
                title="Nuk u gjet asnje produkt"
                text="Provo te ndryshosh filtrat ose kerko me nje fjale tjeter."
                action="Pastro filtrat"
                :href="route('shop')" />
        </div>
    @endforelse
</div>

<div class="mt-8 max-w-full overflow-x-auto pb-1">
    {{ $products->withQueryString()->links() }}
</div>
