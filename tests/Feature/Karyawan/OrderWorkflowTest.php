<?php

namespace Tests\Feature\Karyawan;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function karyawan(): User
    {
        return User::factory()->karyawan()->create();
    }

    private function order(array $overrides = []): Order
    {
        return Order::factory()->create(array_merge([
            'user_id' => null,
            'customer_name' => 'Pembeli Uji',
            'payment_method' => 'tunai',
            'payment_status' => 'belum_lunas',
            'status' => 'menunggu_pembayaran',
            'pickup_status' => 'belum_diambil',
            'source' => 'online',
            'total_price' => 20000,
        ], $overrides));
    }

    private function payment(Order $order, string $status = 'pending'): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => $order->payment_method,
            'amount' => $order->total_price,
            'status' => $status,
        ]);
    }

    private function update(Order $order, array $overrides = [])
    {
        return $this->actingAs($this->karyawan())
            ->patch(route('karyawan.orders.update-status', $order), array_merge([
                'payment_status' => $order->payment_status,
                'pickup_status' => $order->pickup_status,
                'status' => $order->status,
            ], $overrides));
    }

    public function test_karyawan_tidak_bisa_melompati_status_alur(): void
    {
        $order = $this->order(['status' => 'menunggu_pembayaran']);
        $this->payment($order);

        $this->update($order, ['status' => 'selesai'])->assertSessionHasErrors('status');

        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);
    }

    public function test_karyawan_bisa_memajukan_satu_tahap(): void
    {
        $order = $this->order(['status' => 'menunggu_pembayaran']);
        $this->payment($order);

        $this->update($order, ['status' => 'diproses'])->assertSessionHasNoErrors();

        $this->assertSame('diproses', $order->fresh()->status);
    }

    public function test_status_lunas_disinkronkan_ke_payments(): void
    {
        $order = $this->order();
        $payment = $this->payment($order);

        $this->update($order, ['payment_status' => 'lunas'])->assertSessionHasNoErrors();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('lunas', $order->fresh()->payment_status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_karyawan_tidak_bisa_menurunkan_status_lunas(): void
    {
        $order = $this->order(['payment_status' => 'lunas', 'status' => 'diproses']);
        $this->payment($order, 'paid');

        $this->update($order, ['payment_status' => 'belum_lunas'])->assertSessionHasErrors('payment_status');

        $this->assertSame('lunas', $order->fresh()->payment_status);
    }

    public function test_karyawan_bisa_mencatat_pesanan_manual(): void
    {
        $menu = Menu::factory()->create([
            'price' => 15000,
            'status_ketersediaan' => true,
            'options' => [
                'label' => 'Varian Nasi',
                'choices' => [
                    ['key' => 'putih', 'label' => 'Nasi Putih', 'delta' => 0],
                    ['key' => 'kuning', 'label' => 'Nasi Kuning', 'delta' => 3000],
                ],
            ],
        ]);

        $response = $this->actingAs($this->karyawan())->post(route('karyawan.orders.store'), [
            'customer_name' => 'Pesanan Telepon',
            'phone' => '081200000000',
            'pickup_datetime' => now()->addDay()->format('Y-m-d H:i'),
            'payment_method' => 'tunai',
            'payment_status' => 'lunas',
            'notes' => 'tanpa sambel',
            'items' => [
                $menu->id => ['quantity' => 2, 'option' => 'kuning'],
            ],
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('karyawan.orders.show', $order));

        $this->assertSame('manual', $order->source);
        $this->assertSame('lunas', $order->payment_status);
        $this->assertSame(36000.0, (float) $order->total_price, '(15000 + 3000) x 2.');
        $this->assertSame($menu->name, $order->items->first()->menu_name);
        $this->assertSame('paid', $order->payment->status);
    }

    public function test_pesanan_manual_tanpa_item_ditolak(): void
    {
        $menu = Menu::factory()->create(['status_ketersediaan' => true]);

        $this->actingAs($this->karyawan())->post(route('karyawan.orders.store'), [
            'customer_name' => 'Kosong',
            'phone' => '081200000000',
            'pickup_datetime' => now()->addDay()->format('Y-m-d H:i'),
            'payment_method' => 'tunai',
            'payment_status' => 'belum_lunas',
            'items' => [
                $menu->id => ['quantity' => 0, 'option' => null],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
    }

    public function test_nomor_pesanan_tidak_bentrok_dengan_pesanan_bertanggal_lain(): void
    {
        // Simulasi data seeder: nomor memakai tanggal ambil beberapa hari ke
        // depan, padahal barisnya dibuat hari ini.
        Order::factory()->create([
            'order_number' => 'MAD-'.now()->addDays(4)->format('Ymd').'-0009',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $menu = Menu::factory()->create(['price' => 10000, 'status_ketersediaan' => true]);

        foreach (['Telepon Satu', 'Telepon Dua'] as $name) {
            $this->actingAs($this->karyawan())->post(route('karyawan.orders.store'), [
                'customer_name' => $name,
                'phone' => '081200000001',
                'pickup_datetime' => now()->addDay()->format('Y-m-d H:i'),
                'payment_method' => 'tunai',
                'payment_status' => 'belum_lunas',
                'items' => [
                    $menu->id => ['quantity' => 1, 'option' => null],
                ],
            ])->assertSessionHasNoErrors();
        }

        $numbers = Order::where('source', 'manual')->pluck('order_number');

        $this->assertCount(2, $numbers, 'Dua pesanan manual seharusnya tersimpan.');
        $this->assertSame(2, $numbers->unique()->count(), 'Nomor pesanan tidak boleh duplikat.');
        $this->assertTrue(
            $numbers->every(fn ($number) => str_starts_with($number, 'MAD-'.now()->format('Ymd').'-')),
            'Semua nomor baru harus memakai tanggal hari ini.'
        );
    }

    public function test_karyawan_tidak_bisa_membuka_menu_owner(): void
    {
        $this->actingAs($this->karyawan())
            ->get(route('owner.menus.index'))
            ->assertForbidden();
    }
}
