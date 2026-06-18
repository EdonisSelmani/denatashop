<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'coupon_id',
        'coupon_code',
        'order_number',
        'status',
        'payment_method',
        'payment_status',
        'tracking_number',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_city',
        'shipping_address',
        'shipping_postal_code',
        'subtotal',
        'shipping_total',
        'discount_total',
        'member_discount_total',
        'total',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'member_discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            'unpaid' => 'Unpaid',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];
    }

    public function markStatusTimestamps(string $newStatus): array
    {
        return match ($newStatus) {
            self::STATUS_CONFIRMED => ['confirmed_at' => $this->confirmed_at ?? now()],
            self::STATUS_SHIPPED => [
                'confirmed_at' => $this->confirmed_at ?? now(),
                'shipped_at' => $this->shipped_at ?? now(),
            ],
            self::STATUS_DELIVERED => [
                'confirmed_at' => $this->confirmed_at ?? now(),
                'shipped_at' => $this->shipped_at ?? now(),
                'delivered_at' => $this->delivered_at ?? now(),
            ],
            self::STATUS_CANCELLED => ['cancelled_at' => $this->cancelled_at ?? now()],
            default => [],
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
