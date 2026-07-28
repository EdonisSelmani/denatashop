<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
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

<div class="mt-8">
    {{ $products->withQueryString()->links() }}
</div>
