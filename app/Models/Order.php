<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'phone',
        'pickup_datetime',
        'payment_method',
        'payment_status',
        'status',
        'pickup_status',
        'source',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias ringkas untuk orderItems(), dipakai di seluruh view
     * dan eager-loading (with/load 'items.menu').
     */
    public function items(): HasMany
    {
        return $this->orderItems();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
