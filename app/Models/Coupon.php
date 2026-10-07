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
        return $this->isUsableForCents($this->moneyToCents(number_format($subtotal, 2, '.', '')));
    }

    public function isUsableForCents(int $subtotalCents): bool
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

        return $subtotalCents >= $this->moneyToCents($this->minimum_order_total);
    }

    public function discountFor(float $subtotal): float
    {
        return $this->discountCentsFor($this->moneyToCents(number_format($subtotal, 2, '.', ''))) / 100;
    }

    public function discountCentsFor(int $subtotalCents): int
    {
        if ($this->type === self::TYPE_PERCENT) {
            $percentHundredths = min($this->moneyToCents($this->value), 10000);

            return intdiv($subtotalCents * $percentHundredths + 5000, 10000);
        }

        return min($this->moneyToCents($this->value), $subtotalCents);
    }

    private function moneyToCents(string $amount): int
    {
        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', trim($amount), $matches)) {
            throw new \InvalidArgumentException("Invalid decimal money value: {$amount}");
        }

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }
}
