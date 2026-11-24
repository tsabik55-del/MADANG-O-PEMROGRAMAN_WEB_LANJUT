<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    private static int $counter = 1;

    public function definition(): array
    {
        $pickupDate = fake()->dateTimeBetween('+1 day', '+7 days');
        $pickupDate->setTime(
            fake()->numberBetween(8, 17),
            fake()->randomElement([0, 15, 30, 45])
        );

        $orderNumber = 'MAD-'.$pickupDate->format('Ymd').'-'.str_pad(self::$counter++, 4, '0', STR_PAD_LEFT);

        return [
            'order_number' => $orderNumber,
            'user_id' => fake()->optional(0.7)->passthrough(User::factory()->pelanggan()),
            'customer_name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
            'pickup_datetime' => $pickupDate,
            'payment_method' => fake()->randomElement(['tunai', 'transfer']),
            'payment_status' => fake()->randomElement(['belum_lunas', 'lunas']),
            'pickup_status' => fake()->randomElement(['belum_diambil', 'sudah_diambil']),
            'source' => fake()->randomElement(['online', 'manual']),
            'total_price' => fake()->numberBetween(10000, 500000),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
