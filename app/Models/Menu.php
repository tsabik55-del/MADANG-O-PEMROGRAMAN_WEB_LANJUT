<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image',
        'price',
        'status_ketersediaan',
    ];

    /**
     * URL foto menu yang siap dipakai di tag <img>.
     *
     * Isi kolom image disimpan relatif terhadap disk "public", misalnya
     * images/menus/nasi-sambel-ayam.jpg. Jadi URLnya harus diawali
     * storage/, kalau tidak akan mengarah ke /images/... yang tidak ada dan
     * menghasilkan 404.
     *
     * Mengembalikan null bila kolom kosong atau filenya benar-benar tidak
     * ada, supaya tampilan bisa memakai cadangan dan tidak menampilkan
     * gambar rusak.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->image)) {
                return null;
            }

            $path = ltrim($this->image, '/');

            if (! Storage::disk('public')->exists($path)) {
                return null;
            }

            return asset('storage/' . $path);
        });
    }

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
