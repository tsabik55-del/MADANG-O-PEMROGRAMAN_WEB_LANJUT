<?php

namespace Tests\Feature\Owner;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->owner()->create();
    }

    private function paidOrder(float $total, array $overrides = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'user_id' => null,
            'customer_name' => 'Laporan Uji',
            'payment_method' => 'tunai',
            'payment_status' => 'lunas',
            'status' => 'selesai',
            'pickup_status' => 'sudah_diambil',
            'source' => 'online',
            'total_price' => $total,
        ], $overrides));

        Payment::create([
            'order_id' => $order->id,
            'method' => 'tunai',
            'amount' => $total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $order;
    }

    public function test_rekap_harian_menghitung_pesanan_dan_pendapatan(): void
    {
        $this->paidOrder(25000);

        $this->actingAs($this->owner())
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertSee('Rekap Harian')
            ->assertSee('Rp25.000');
    }

    public function test_pesanan_batal_tidak_dihitung_dalam_laporan(): void
    {
        $this->paidOrder(25000);
        $this->paidOrder(88000, ['status' => 'dibatalkan']);

        $this->actingAs($this->owner())
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertSee('Rp25.000')
            ->assertDontSee('Rp88.000');
    }

    public function test_filter_periode_membatasi_data(): void
    {
        $kemarin = $this->paidOrder(15000);
        $kemarin->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();

        $hariIni = $this->paidOrder(35000);

        $this->actingAs($this->owner())
            ->get(route('owner.reports.index', [
                'from' => now()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Rp35.000')
            ->assertDontSee('Rp15.000');

        $this->assertNotSame($kemarin->id, $hariIni->id);
    }
}
