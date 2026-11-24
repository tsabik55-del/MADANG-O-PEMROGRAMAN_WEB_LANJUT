<?php

namespace Tests\Feature\Owner;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
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
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }

    public function test_owner_menandai_lunas_menyinkronkan_tabel_payments(): void
    {
        $order = $this->order();
        $payment = $this->payment($order);

        $this->actingAs($this->owner())
            ->put(route('owner.orders.update', $order), [
                'payment_status' => 'lunas',
                'pickup_status' => 'belum_diambil',
                'status' => 'diproses',
                'notes' => null,
            ])
            ->assertRedirect(route('owner.orders.index'));

        $this->assertSame('lunas', $order->fresh()->payment_status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at, 'paid_at harus terisi saat lunas.');
    }

    public function test_owner_boleh_mengoreksi_status_tanpa_batas_transisi(): void
    {
        $order = $this->order(['status' => 'menunggu_pembayaran']);
        $this->payment($order);

        $this->actingAs($this->owner())
            ->put(route('owner.orders.update', $order), [
                'payment_status' => 'belum_lunas',
                'pickup_status' => 'belum_diambil',
                'status' => 'selesai',
                'notes' => 'koreksi owner',
            ])->assertRedirect();

        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_owner_membatalkan_pesanan_tanpa_menghapus_riwayat(): void
    {
        $order = $this->order();

        $this->actingAs($this->owner())
            ->delete(route('owner.orders.destroy', $order))
            ->assertRedirect(route('owner.orders.index'));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'dibatalkan']);
    }

    public function test_filter_pesanan_berdasarkan_status_dan_pencarian(): void
    {
        $a = $this->order(['customer_name' => 'Ani', 'status' => 'diproses']);
        $b = $this->order(['customer_name' => 'Budi', 'status' => 'selesai']);

        $this->actingAs($this->owner())
            ->get(route('owner.orders.index', ['status' => 'diproses']))
            ->assertOk()
            ->assertSee($a->order_number)
            ->assertDontSee($b->order_number);

        $this->actingAs($this->owner())
            ->get(route('owner.orders.index', ['q' => 'Budi']))
            ->assertOk()
            ->assertSee($b->order_number)
            ->assertDontSee($a->order_number);
    }

    public function test_menu_yang_sudah_dipakai_pesanan_tidak_bisa_dihapus(): void
    {
        $menu = Menu::factory()->create();
        $order = $this->order();

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => 1,
            'price' => $menu->price,
            'subtotal' => $menu->price,
        ]);

        $this->actingAs($this->owner())
            ->delete(route('owner.menus.destroy', $menu))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('menus', ['id' => $menu->id]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'menu_id' => $menu->id]);
    }

    public function test_pendapatan_dashboard_mengabaikan_pesanan_batal(): void
    {
        $lunas = $this->order(['payment_status' => 'lunas', 'total_price' => 12000]);
        $this->payment($lunas, 'paid');

        $batal = $this->order([
            'payment_status' => 'lunas',
            'status' => 'dibatalkan',
            'total_price' => 99000,
        ]);
        $this->payment($batal, 'paid');

        $this->actingAs($this->owner())
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Rp12.000')
            ->assertDontSee('Rp99.000');
    }
}
