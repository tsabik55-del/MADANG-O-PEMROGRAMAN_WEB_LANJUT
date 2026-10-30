<?php

namespace Tests\Feature\Pelanggan;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganFiturTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    private function varianNasi(): array
    {
        return [
            'label' => 'Varian Nasi',
            'choices' => [
                ['key' => 'putih', 'label' => 'Nasi Putih', 'delta' => 0],
                ['key' => 'kuning', 'label' => 'Nasi Kuning', 'delta' => 4000],
            ],
        ];
    }

    private function payload(array $items, array $override = []): array
    {
        return array_merge([
            '_token' => 'test',
            'cart_data' => json_encode($items),
            'customer_name' => 'Budi',
            'phone' => '08123456789',
            'pickup_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'payment_method' => 'tunai',
        ], $override);
    }

    public function test_harga_varian_dihitung_di_server(): void
    {
        $menu = Menu::factory()->create([
            'price' => 10000,
            'status_ketersediaan' => true,
            'options' => $this->varianNasi(),
        ]);

        $this->actingAs($this->pelanggan())->post(route('pelanggan.orders.store'), $this->payload([
            // Harga & delta dari browser sengaja dipalsukan; server harus mengabaikannya.
            ['id' => $menu->id, 'quantity' => 2, 'option' => 'kuning', 'price' => 1, 'delta' => 1],
        ]))->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $item = $order->items->first();

        $this->assertSame(28000.0, (float) $order->total_price, 'Harga harus (10000 + 4000) x 2.');
        $this->assertSame(14000.0, (float) $item->price);
        $this->assertSame('kuning', $item->options['key']);
        $this->assertSame('Nasi Kuning', $item->options['label']);
    }

    public function test_varian_tidak_dikenal_ditolak(): void
    {
        $menu = Menu::factory()->create([
            'price' => 10000,
            'status_ketersediaan' => true,
            'options' => $this->varianNasi(),
        ]);

        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.orders.store'), $this->payload([
                ['id' => $menu->id, 'quantity' => 1, 'option' => 'ngawur'],
            ]))
            ->assertSessionHasErrors('cart_data');

        $this->assertSame(0, Order::count());
    }

    public function test_varian_kosong_memakai_pilihan_standar(): void
    {
        $menu = Menu::factory()->create([
            'price' => 10000,
            'status_ketersediaan' => true,
            'options' => $this->varianNasi(),
        ]);

        $this->actingAs($this->pelanggan())->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'quantity' => 1],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(10000.0, (float) Order::firstOrFail()->total_price);
    }

    public function test_biodata_pelanggan_tersimpan(): void
    {
        $user = $this->pelanggan();

        $this->actingAs($user)
            ->patchJson(route('pelanggan.profile.update'), [
                'name' => 'Budi Santoso',
                'wa_number' => '081298765432',
                'address' => 'Jl. Melati 12, Sidoarjo',
            ])
            ->assertOk()
            ->assertJsonPath('user.alamat', 'Jl. Melati 12, Sidoarjo');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Budi Santoso',
            'wa_number' => '081298765432',
            'address' => 'Jl. Melati 12, Sidoarjo',
        ]);
    }

    public function test_biodata_tidak_lengkap_ditolak(): void
    {
        $this->actingAs($this->pelanggan())
            ->patchJson(route('pelanggan.profile.update'), [
                'name' => '',
                'wa_number' => 'abc',
                'address' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'wa_number', 'address']);
    }

    public function test_konfirmasi_bayar_tidak_langsung_lunas(): void
    {
        $menu = Menu::factory()->create(['price' => 10000, 'status_ketersediaan' => true]);
        $user = $this->pelanggan();

        $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'quantity' => 1],
        ], ['payment_method' => 'qris']));

        $order = Order::firstOrFail();

        $this->actingAs($user)
            ->postJson(route('pelanggan.orders.bayar', $order))
            ->assertOk()
            ->assertJsonPath('payment_status', 'menunggu_verifikasi');

        $this->assertSame('menunggu_verifikasi', Payment::firstOrFail()->status);
        $this->assertSame('belum_lunas', $order->fresh()->payment_status, 'Pelanggan tidak boleh melunasi sendiri.');
    }

    public function test_checkout_json_mengembalikan_pesanan(): void
    {
        $menu = Menu::factory()->create(['price' => 12000, 'status_ketersediaan' => true]);

        $response = $this->actingAs($this->pelanggan())
            ->postJson(route('pelanggan.orders.store'), $this->payload([
                ['id' => $menu->id, 'quantity' => 3],
            ]));

        $response->assertStatus(201)
            ->assertJsonPath('order.total', 36000)
            ->assertJsonStructure(['order' => ['id', 'nomor', 'total', 'detail_url']]);
    }

    public function test_qris_menghasilkan_svg(): void
    {
        $menu = Menu::factory()->create(['price' => 10000, 'status_ketersediaan' => true]);
        $user = $this->pelanggan();

        $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'quantity' => 1],
        ], ['payment_method' => 'qris']));

        $order = Order::firstOrFail();

        $response = $this->actingAs($user)
            ->getJson(route('pelanggan.orders.qris', $order))
            ->assertOk();

        $this->assertStringContainsString('<svg', $response->json('svg'));
        $this->assertStringContainsString($order->order_number, $response->json('isi'));
    }

    public function test_qris_ditolak_untuk_pesanan_tunai(): void
    {
        $menu = Menu::factory()->create(['price' => 10000, 'status_ketersediaan' => true]);
        $user = $this->pelanggan();

        $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'quantity' => 1],
        ], ['payment_method' => 'tunai']));

        $this->actingAs($user)
            ->getJson(route('pelanggan.orders.qris', Order::firstOrFail()))
            ->assertNotFound();
    }

    public function test_riwayat_json_hanya_milik_sendiri(): void
    {
        $menu = Menu::factory()->create(['price' => 10000, 'status_ketersediaan' => true]);
        $saya = $this->pelanggan();
        $lain = $this->pelanggan();

        $this->actingAs($saya)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'quantity' => 1],
        ]));

        $milikSaya = $this->actingAs($saya)
            ->getJson(route('pelanggan.orders.data'))
            ->assertOk()
            ->json('orders');

        $milikLain = $this->actingAs($lain)
            ->getJson(route('pelanggan.orders.data'))
            ->assertOk()
            ->json('orders');

        $this->assertCount(1, $milikSaya);
        $this->assertCount(0, $milikLain);
    }
}
