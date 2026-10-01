<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $method = fake()->randomElement(['tunai', 'transfer', 'qris']);
        $status = fake()->randomElement(['pending', 'paid', 'failed']);

        return [
            'order_id' => Order::factory(),
            'method' => $method,
            'amount' => fake()->numberBetween(10000, 500000),
            'status' => $status,
            'transaction_id' => $method === 'tunai' ? null : fake()->unique()->bothify('TRX-####-????'),
            'qr_code' => $method === 'qris' ? fake()->filePath() : null,
            'paid_at' => $status === 'paid' ? fake()->dateTimeThisMonth() : null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => fake()->dateTimeThisMonth(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }
}
