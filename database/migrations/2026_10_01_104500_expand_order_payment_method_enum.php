<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        // SQLite (dipakai saat testing) memperlakukan ENUM sebagai TEXT.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('tunai', 'transfer', 'qris') NOT NULL DEFAULT 'tunai'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::table('orders')->where('payment_method', 'qris')->update(['payment_method' => 'tunai']);

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('tunai', 'transfer') NOT NULL DEFAULT 'tunai'");
    }
};
