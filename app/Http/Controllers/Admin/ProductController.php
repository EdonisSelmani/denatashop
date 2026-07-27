<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('subcategory.category')->paginate(10);
        return view('admin.products.index', compact('products'));
    }
    
    public function create()
    {
        $categories = Category::with('subcategories')->get();
        return view('admin.products.create', compact('categories'));
    }
    
    public function store(Request $request, ProductImageService $images)
    {
        $request->validate([
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|unique:products',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);
        
        $data = $request->except(['gallery', 'image']);
        $data['slug'] = $this->uniqueSlug($request->name);
        
        if ($request->hasFile('image')) {
            $data['image'] = $images->store($request->file('image'));
        }
        
        if ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $image) {
                $gallery[] = $images->store($image, 'products/gallery');
            }
            $data['gallery'] = $gallery;
        }
        
        Product::create($data);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully');
    }
    
    public function edit(Product $product)
    {
        $categories = Category::with('subcategories')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }
    
    public function update(Request $request, Product $product, ProductImageService $images)
    {
        $request->validate([
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|gt:price',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|unique:products,sku,' . $product->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);
        
        $data = $request->except(['gallery', 'image']);
        $data['slug'] = $this->uniqueSlug($request->name, $product->id);
        
        if ($request->hasFile('image')) {
            $images->delete($product->image);

            $data['image'] = $images->store($request->file('image'));
        }
        
        if ($request->hasFile('gallery')) {
            $gallery = (array) $product->gallery;
            foreach ($request->file('gallery') as $image) {
                $gallery[] = $images->store($image, 'products/gallery');
            }
            $data['gallery'] = $gallery;
        }
        
        $product->update($data);
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully');
    }
    
    public function destroy(Product $product, ProductImageService $images)
    {
        $images->delete($product->image);

        if ($product->gallery) {
            foreach ((array) $product->gallery as $image) {
                $images->delete($image);
            }
        }

        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }
    
    public function getSubcategories($categoryId)
    {
        $subcategories = Subcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();
        
        return response()->json($subcategories);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
