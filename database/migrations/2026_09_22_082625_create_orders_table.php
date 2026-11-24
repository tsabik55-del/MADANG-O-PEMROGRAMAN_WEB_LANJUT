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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->datetime('pickup_datetime');
            $table->enum('payment_method', ['tunai', 'transfer'])->default('tunai');
            $table->enum('payment_status', ['belum_lunas', 'menunggu_verifikasi', 'lunas'])->default('belum_lunas');
            $table->enum('pickup_status', ['belum_diambil', 'sudah_diambil'])->default('belum_diambil');
            $table->enum('source', ['online', 'manual'])->default('online');
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
