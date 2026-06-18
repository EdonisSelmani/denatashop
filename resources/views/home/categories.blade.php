{{-- resources/views/home/categories.blade.php --}}
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Kategoritë Tona</h2>
            <p class="text-gray-600">Zbuloni produktet sipas kategorive</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            @foreach($categories as $category)
                <a href="{{ route('category.show', $category->slug) }}" 
                   class="group block">
                    <div class="bg-gray-50 rounded-2xl p-6 text-center transition-all duration-300 group-hover:shadow-xl group-hover:-translate-y-2">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-12 h-12 object-contain">
                            @else
                                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-1">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->subcategories_count }} produkte</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
