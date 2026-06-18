<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with('category')->paginate(10);
        return view('admin.subcategories.index', compact('subcategories'));
    }
    
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.subcategories.create', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|unique:subcategories|max:255',
            'description' => 'nullable'
        ]);
        
        Subcategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $this->uniqueSlug($request->name),
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);
        
        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory created successfully');
    }
    
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }
    
    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:255|unique:subcategories,name,' . $subcategory->id,
            'description' => 'nullable'
        ]);
        
        $subcategory->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $this->uniqueSlug($request->name, $subcategory->id),
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);
        
        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory updated successfully');
    }
    
    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();
        
        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory deleted successfully');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Subcategory::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
