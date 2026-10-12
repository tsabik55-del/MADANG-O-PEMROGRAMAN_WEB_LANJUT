<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perluas enum payment_method pada tabel orders agar mencakup QRIS.
     *
     * Konsisten dengan payments.method (tunai/transfer/qris) dan pilihan
     * pada formulir checkout pelanggan.
     */
    public function up(): void
    {
        // Driver SQLite milik Laravel tetap membuat CHECK constraint untuk
        // kolom enum, jadi kolom harus ikut diubah agar qris bisa disimpan.
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['tunai', 'transfer', 'qris'])
                ->default('tunai')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['tunai', 'transfer'])->default('tunai')->change();
        });
    }
};