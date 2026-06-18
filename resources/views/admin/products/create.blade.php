{{-- resources/views/admin/products/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">Add New Product</h2>
                
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Category *</label>
                            <select name="category_id" id="category_id" class="w-full border rounded-lg px-3 py-2" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Subcategory *</label>
                            <select name="subcategory_id" id="subcategory_id" class="w-full border rounded-lg px-3 py-2" required>
                                <option value="">Select Category First</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Product Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="w-full border rounded-lg px-3 py-2" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Description *</label>
                        <textarea name="description" rows="5" class="w-full border rounded-lg px-3 py-2" required>{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Price *</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price') }}" 
                                   class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Compare Price</label>
                            <input type="number" step="0.01" name="compare_price" value="{{ old('compare_price') }}" 
                                   class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Stock *</label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" 
                                   class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">SKU *</label>
                            <input type="text" name="sku" value="{{ old('sku') }}" 
                                   class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Main Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Gallery Images</label>
                        <input type="file" name="gallery[]" accept="image/*" multiple class="w-full border rounded-lg px-3 py-2">
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center mr-4">
                            <input type="checkbox" name="is_active" value="1" checked class="mr-2">
                            <span class="text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" class="mr-2">
                            <span class="text-gray-700">Featured</span>
                        </label>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategorySelect = document.getElementById('subcategory_id');
        
        if (categoryId) {
            fetch(`/admin/subcategories/by-category/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                    data.forEach(sub => {
                        subcategorySelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
                    });
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">Select Category First</option>';
        }
    });
</script>
@endpush
@endsection