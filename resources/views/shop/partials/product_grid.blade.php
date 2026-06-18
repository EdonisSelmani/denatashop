<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($products as $product)
        @include('shop.partials.product-card', ['product' => $product])
    @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">No products found.</p>
        </div>
    @endforelse
</div>

<div class="mt-8">
    {{ $products->withQueryString()->links() }}
</div>
