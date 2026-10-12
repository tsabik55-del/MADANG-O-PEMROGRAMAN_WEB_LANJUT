<?php

namespace Tests\Feature\Pelanggan;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    private function payload(array $items, array $override = []): array
    {
        return array_merge([
            '_token' => 'test',
            'cart_data' => json_encode($items),
            'total_price' => 1,
            'customer_name' => 'Budi',
            'phone' => '08123456789',
            'pickup_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'payment_method' => 'tunai',
        ], $override);
    }

    public function test_harga_diambil_dari_database_bukan_dari_browser(): void
    {
        $menu = Menu::factory()->create(['price' => 20000, 'status_ketersediaan' => true]);
        $user = $this->pelanggan();

        $response = $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'name' => 'Menu Palsu', 'price' => 1, 'quantity' => 3],
        ]));

        $order = Order::first();
        $response->assertRedirect(route('pelanggan.orders.show', $order));

        $this->assertSame(60000.0, (float) $order->total_price, 'Total harus 20000 x 3, bukan harga kiriman browser.');
        $this->assertSame(20000.0, (float) $order->items->first()->price, 'Harga item harus dari database.');
        $this->assertSame(60000.0, (float) Payment::first()->amount, 'Nominal pembayaran harus sama dengan total.');
    }

    public function test_menu_yang_tidak_tersedia_ditolak(): void
    {
        $habis = Menu::factory()->create(['status_ketersediaan' => false]);
        $user = $this->pelanggan();

        $response = $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $habis->id, 'price' => 1000, 'quantity' => 1],
        ]));

        $response->assertRedirect();
        $this->assertSame(0, Order::count(), 'Pesanan tidak boleh dibuat.');
        $response->assertSessionHas('error');
    }

    public function test_field_wajib_kosong_ditolak(): void
    {
        $menu = Menu::factory()->create(['status_ketersediaan' => true]);

        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.orders.store'), $this->payload(
                [['id' => $menu->id, 'price' => 1000, 'quantity' => 1]],
                ['phone' => '', 'pickup_datetime' => '']
            ))
            ->assertSessionHasErrors(['phone', 'pickup_datetime']);

        $this->assertSame(0, Order::count());
    }

    public function test_waktu_ambil_di_masa_lampau_ditolak(): void
    {
        $menu = Menu::factory()->create(['status_ketersediaan' => true]);

        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.orders.store'), $this->payload(
                [['id' => $menu->id, 'price' => 1000, 'quantity' => 1]],
                ['pickup_datetime' => now()->subHour()->format('Y-m-d H:i:s')]
            ))
            ->assertSessionHasErrors(['pickup_datetime']);
    }

    public function test_nomor_pesanan_tidak_bentrok_setelah_pesanan_dibatalkan(): void
    {
        $menu = Menu::factory()->create(['status_ketersediaan' => true]);
        $user = $this->pelanggan();

        $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'price' => 1000, 'quantity' => 1],
        ]));
        $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'price' => 1000, 'quantity' => 1],
        ]));

        $this->assertSame(2, Order::count());

        // Pelanggan boleh membatalkan selama masih menunggu_pembayaran.
        $first = Order::orderBy('id')->first();
        $this->actingAs($user)->delete(route('pelanggan.orders.destroy', $first))->assertRedirect();
        $this->assertSame(1, Order::count(), 'Satu pesanan seharusnya terhapus.');

        // Pesanan berikutnya tidak boleh memakai nomor yang sudah dipakai.
        $this->actingAs($user)->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'price' => 1000, 'quantity' => 1],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, Order::count());
        $this->assertSame(
            2,
            Order::pluck('order_number')->unique()->count(),
            'Nomor pesanan harus unik meski ada pesanan yang dibatalkan.'
        );
    }

    public function test_berhasil_checkout_menandai_keranjang_kosong(): void
    {
        $menu = Menu::factory()->create(['status_ketersediaan' => true]);

        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.orders.store'), $this->payload([
                ['id' => $menu->id, 'price' => 1000, 'quantity' => 1],
            ]))
            ->assertSessionHas('cart_cleared', true);
    }

    public function test_halaman_katalog_memakai_class_css_keranjang(): void
    {
        // Elemen keranjang hanya bisa digeser oleh .cart-panel / .cart-overlay
        // di madang-theme.css. Kalau class itu hilang dari markup, panel diam
        // di luar layar dan tidak pernah terlihat meskipun JavaScript jalan.
        $menu = Menu::factory()->create(['status_ketersediaan' => true]);
        $category = $menu->category;
        $menu->update(['category_id' => $category->id]);

        $html = $this->actingAs($this->pelanggan())
            ->get(route('pelanggan.dashboard'))
            ->assertOk()
            ->getContent();

        // Hanya .cart-panel dan .cart-overlay yang punya aturan CSS di
        // madang-theme.css. #cart-sidebar sendiri diatur lewat utility
        // Tailwind, jadi tidak perlu class tambahan.
        foreach (['cart-panel', 'cart-overlay'] as $class) {
            $this->assertMatchesRegularExpression(
                '/class="[^"]*\b' . preg_quote($class, '/') . '\b[^"]*"/',
                $html,
                "Elemen keranjang harus punya class CSS {$class}."
            );
        }

        // translate-x-full milik Tailwind akan menimpa aturan .cart-panel,
        // sehingga class itu harus dihapus dari markup.
        $this->assertStringNotContainsString('translate-x-full', $html);
    }

    public function test_keranjang_kosong_ditolak(): void
    {
        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.orders.store'), $this->payload([]))
            ->assertRedirect();

        $this->assertSame(0, Order::count());
    }

    public function test_menu_id_palsu_ditolak(): void
    {
        $this->actingAs($this->pelanggan())
            ->post(route('pelanggan.orders.store'), $this->payload([
                ['id' => 999999, 'price' => 1, 'quantity' => 1],
            ]))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_jumlah_dibatasi_tidak_boleh_nol_atau_negatif(): void
    {
        $menu = Menu::factory()->create(['status_ketersediaan' => true, 'price' => 15000]);

        $this->actingAs($this->pelanggan())->post(route('pelanggan.orders.store'), $this->payload([
            ['id' => $menu->id, 'price' => 999, 'quantity' => -5],
        ]))->assertSessionHasNoErrors();

        $order = Order::first();
        $this->assertSame(1, $order->items->first()->quantity, 'Jumlah negatif harus dinormalkan jadi minimal 1.');
        $this->assertSame(15000.0, (float) $order->total_price);
    }
}