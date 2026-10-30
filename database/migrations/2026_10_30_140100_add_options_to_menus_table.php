<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Definisi varian menu (mis. varian nasi, topping minuman).
     *
     * Disimpan sebagai JSON agar tidak perlu tabel tambahan:
     * {
     *   "label": "Varian Nasi",
     *   "choices": [
     *     {"key": "putih",  "label": "Nasi Putih",       "delta": 0},
     *     {"key": "jeruk",  "label": "Nasi Daun Jeruk",  "delta": 2000},
     *     {"key": "kuning", "label": "Nasi Kuning",      "delta": 4000}
     *   ]
     * }
     *
     * `delta` adalah tambahan harga yang dihitung di server, bukan
     * nilai yang dikirim browser.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->json('options')->nullable()->after('status_ketersediaan');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
