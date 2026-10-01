<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perluas enum method pada tabel payments.
     *
     * Sesuai aturan mitra: metode pembayaran meliputi tunai dan transfer,
     * bukan hanya QRIS. Dengan begitu setiap pembayaran (tunai maupun
     * non-tunai) tercatat di tabel payments.
     */
    public function up(): void
    {
        // SQLite (dipakai saat testing) memperlakukan ENUM sebagai TEXT,
        // sehingga tidak perlu ALTER. Statement ini hanya untuk MySQL/MariaDB.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE payments MODIFY method ENUM('tunai', 'transfer', 'qris') NOT NULL DEFAULT 'qris'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Kembalikan hanya record 'qris' sebelum menyempitkan enum,
        // agar tidak ada data yang terpotong.
        DB::table('payments')->whereNotIn('method', ['qris'])->update(['method' => 'qris']);

        DB::statement("ALTER TABLE payments MODIFY method ENUM('qris') NOT NULL DEFAULT 'qris'");
    }
};
