<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Order::all() as $order) {
            // Metode pembayaran mengikuti pesanan; sebagian kecil memakai QRIS.
            $method = fake()->optional(0.15, $order->payment_method)->passthrough('qris')
                ?? $order->payment_method;

            // Konsisten dengan status bayar pesanan: yang lunas pasti terbayar,
            // yang belum lunas berarti masih menunggu atau pernah gagal.
            $status = match ($order->payment_status) {
                'lunas' => 'paid',
                default => fake()->randomElement(['pending', 'pending', 'pending', 'failed']),
            };

            Payment::create([
                'order_id' => $order->id,
                'method' => $method,
                'amount' => $order->total_price,
                'status' => $status,
                'transaction_id' => $method === 'qris' || $method === 'transfer'
                    ? 'TRX-' . $order->order_number
                    : null,
                'qr_code' => $method === 'qris'
                    ? 'qrcodes/' . $order->order_number . '.png'
                    : null,
                'paid_at' => $status === 'paid' ? $order->created_at : null,
            ]);
        }
    }
}
