<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        // Catatan: driver SQLite milik Laravel tetap membuat CHECK constraint
        // untuk kolom enum, jadi kolom harus ikut diubah agar tunai/transfer
        // bisa disimpan. Schema::table()->change() menangani rebuild tabel
        // secara otomatis untuk SQLite maupun MySQL/MariaDB.
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('method', ['tunai', 'transfer', 'qris'])
                ->default('tunai')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('method', ['qris'])->default('qris')->change();
        });
    }
};