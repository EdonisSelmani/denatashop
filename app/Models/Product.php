<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'subcategory_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'stock',
        'sku',
        'image',
        'gallery',
        'attributes',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'gallery' => 'array',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, Subcategory::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')
            ->withTimestamps();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function usersInCart(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cart_items', 'product_id', 'user_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getFormattedPriceAttribute(): string
    {
        return '€' . number_format((float) $this->price, 2);
    }

    public function getSalePriceAttribute()
    {
        return $this->compare_price && $this->compare_price < $this->price
            ? $this->compare_price
            : null;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && file_exists(storage_path('app/public/' . $this->image))) {
            return asset('storage/' . $this->image);
        }

        return asset('images/placeholder-product.svg');
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->image) {
            $pathInfo = pathinfo($this->image);
            $thumbnailPath = 'product-thumbs/' . ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . $pathInfo['filename'] . '.webp';

            if (file_exists(storage_path('app/public/' . $thumbnailPath))) {
                return asset('storage/' . $thumbnailPath);
            }
        }

        return $this->image_url;
    }

    public function getSalesCountAttribute(): int
    {
        return $this->cartItems()->sum('quantity');
    }
}
