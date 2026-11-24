<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_id',
        'menu_name',
        'quantity',
        'price',
        'subtotal',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'options' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Nama menu yang ditampilkan di riwayat.
     *
     * Prioritas: snapshot saat transaksi, lalu relasi menu (data lama),
     * terakhir teks cadangan bila menu benar-benar sudah tidak ada.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->menu_name
            ?? $this->menu?->name
            ?? 'Menu dihapus';
    }
}
