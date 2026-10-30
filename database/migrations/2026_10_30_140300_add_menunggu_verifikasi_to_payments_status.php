<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah status "menunggu_verifikasi" pada tabel payments.
     *
     * Pelanggan yang mengaku sudah membayar tidak boleh langsung
     * menandai pesanannya lunas — itu wewenang kasir. Pelanggan hanya
     * dapat mengubah status menjadi menunggu_verifikasi, lalu karyawan
     * memverifikasi lewat layar yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'menunggu_verifikasi',
                'paid',
                'failed',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'failed'])
                ->default('pending')
                ->change();
        });
    }
};
