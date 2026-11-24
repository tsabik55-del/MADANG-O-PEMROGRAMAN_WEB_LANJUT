<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Menu tidak boleh dihapus bila masih dipakai di riwayat pesanan.
            // Sebelumnya cascadeOnDelete membuat item pesanan lama ikut
            // terhapus sehingga laporan penjualan kehilangan data.
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();

            // Salinan nama menu saat transaksi terjadi. Dengan begitu riwayat
            // tetap terbaca meskipun menu kelak diganti nama atau dinonaktifkan.
            $table->string('menu_name')->nullable();
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
