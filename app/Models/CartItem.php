<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cart_items';
    
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity'
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
    ];
    
    // Relacioni me User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Relacioni me Product - KJO ËSHTË E RËNDËSISHME
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    // Metoda ndihmëse për subtotal
    public function getSubtotalAttribute()
    {
        return $this->product->price * $this->quantity;
    }
}