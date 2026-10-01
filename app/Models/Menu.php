<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image',
        'price',
        'status_ketersediaan',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status_ketersediaan' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
