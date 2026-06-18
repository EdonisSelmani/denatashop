<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENT = 'percent';

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'minimum_order_total',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_total' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_FIXED => 'Fixed amount',
            self::TYPE_PERCENT => 'Percentage',
        ];
    }

    public function isUsableFor(float $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return $subtotal >= (float) $this->minimum_order_total;
    }

    public function discountFor(float $subtotal): float
    {
        if ($this->type === self::TYPE_PERCENT) {
            return round($subtotal * min((float) $this->value, 100) / 100, 2);
        }

        return round(min((float) $this->value, $subtotal), 2);
    }
}
