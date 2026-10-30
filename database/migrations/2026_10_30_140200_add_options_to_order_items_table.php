<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mencatat varian yang dipilih pembeli pada satu baris pesanan.
     *
     * Kolom `price` dan `subtotal` pada order_items sudah menyimpan harga
     * final (termasuk tambahan varian), sehingga laporan penjualan tetap
     * benar walau definisi varian berubah di kemudian hari.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('options')->nullable()->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
