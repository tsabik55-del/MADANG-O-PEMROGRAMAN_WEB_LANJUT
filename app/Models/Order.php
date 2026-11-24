<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'menunggu_pembayaran',
        'diproses',
        'siap',
        'dikirim',
        'selesai',
        'dibatalkan',
    ];

    /**
     * Transisi status yang sah untuk karyawan (hanya boleh maju).
     *
     * Owner tidak dibatasi daftar ini karena berperan sebagai admin yang
     * boleh mengoreksi status yang salah input.
     */
    public const STATUS_TRANSITIONS = [
        'menunggu_pembayaran' => ['diproses', 'dibatalkan'],
        'diproses' => ['siap', 'dibatalkan'],
        'siap' => ['dikirim', 'selesai', 'dibatalkan'],
        'dikirim' => ['selesai', 'dibatalkan'],
        'selesai' => [],
        'dibatalkan' => [],
    ];

    public const PAYMENT_STATUS_TRANSITIONS = [
        'belum_lunas' => ['menunggu_verifikasi', 'lunas'],
        'menunggu_verifikasi' => ['lunas'],
        'lunas' => [],
    ];

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

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', 'dibatalkan');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'lunas');
    }

    /**
     * Status alur yang boleh dipilih berikutnya.
     */
    public function allowedNextStatuses(): array
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? [];
    }

    /**
     * Status pembayaran yang boleh dipilih berikutnya.
     */
    public function allowedNextPaymentStatuses(): array
    {
        return self::PAYMENT_STATUS_TRANSITIONS[$this->payment_status] ?? [];
    }

    public function isCancelled(): bool
    {
        return $this->status === 'dibatalkan';
    }
}
