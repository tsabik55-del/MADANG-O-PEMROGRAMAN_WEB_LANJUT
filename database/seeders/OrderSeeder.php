<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('role', 'pelanggan')->get();
        $menus = Menu::all();

        for ($i = 0; $i < 20; $i++) {
            $pickupDate = fake()->dateTimeBetween('+1 day', '+14 days');
            $pickupDate->setTime(
                fake()->numberBetween(8, 17),
                fake()->randomElement([0, 15, 30, 45])
            );

            $orderNumber = 'MAD-' . $pickupDate->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $source = fake()->randomElement(['online', 'manual']);

            $user = $source === 'online' ? $pelanggan->random() : null;
            $customerName = $source === 'online' ? $user->name : fake()->name();

            $paymentStatus = fake()->randomElement(['belum_lunas', 'lunas']);
            $pickupStatus = fake()->randomElement(['belum_diambil', 'sudah_diambil']);

            // Status alur pesanan harus konsisten dengan status bayar & ambil.
            if ($paymentStatus === 'belum_lunas' && $pickupStatus === 'belum_diambil') {
                $status = fake()->randomElement(['menunggu_pembayaran', 'menunggu_pembayaran', 'dibatalkan']);
            } elseif ($paymentStatus === 'lunas' && $pickupStatus === 'belum_diambil') {
                $status = fake()->randomElement(['diproses', 'siap']);
            } else {
                $status = 'selesai';
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user?->id,
                'customer_name' => $customerName,
                'phone' => '08' . fake()->numerify('##########'),
                'pickup_datetime' => $pickupDate,
                'payment_method' => fake()->randomElement(['tunai', 'transfer']),
                'payment_status' => $paymentStatus,
                'status' => $status,
                'pickup_status' => $pickupStatus,
                'source' => $source,
                'total_price' => 0,
                'notes' => fake()->optional(0.3)->sentence(),
            ]);

            $itemCount = fake()->numberBetween(1, 3);
            $totalPrice = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $menu = $menus->random();
                $quantity = fake()->numberBetween(1, 10);
                $subtotal = $menu->price * $quantity;
                $totalPrice += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $quantity,
                    'price' => $menu->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);
        }
    }
}
