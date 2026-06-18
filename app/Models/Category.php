<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
    
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    
    public function getActiveSubcategoriesAttribute()
    {
        return $this->subcategories()->where('is_active', true)->get();
    }
    public function products()
{
    return $this->hasManyThrough(
        Product::class,
        Subcategory::class,
        'category_id', // Foreign key on subcategories table
        'subcategory_id', // Foreign key on products table
        'id', // Local key on categories table
        'id' // Local key on subcategories table
    );
}
}