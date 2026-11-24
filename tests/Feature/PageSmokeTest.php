<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test seluruh halaman utama tiap peran.
 *
 * Tujuannya menangkap error Blade (variabel hilang, route salah) yang tidak
 * tercakup test fungsional.
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_owner_dapat_dibuka(): void
    {
        $owner = User::factory()->owner()->create();
        $category = Category::factory()->create();
        $menu = Menu::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['user_id' => null]);

        $pages = [
            route('owner.dashboard'),
            route('owner.menus.index'),
            route('owner.menus.create'),
            route('owner.menus.edit', $menu),
            route('owner.orders.index'),
            route('owner.orders.create'),
            route('owner.orders.show', $order),
            route('owner.orders.edit', $order),
            route('owner.users.index'),
            route('owner.users.create'),
            route('owner.users.edit', $owner),
            route('owner.reports.index'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }

    public function test_halaman_karyawan_dapat_dibuka(): void
    {
        $karyawan = User::factory()->karyawan()->create();
        $order = Order::factory()->create(['user_id' => null]);

        foreach ([
            route('karyawan.dashboard'),
            route('karyawan.orders.index'),
            route('karyawan.orders.create'),
            route('karyawan.orders.show', $order),
        ] as $url) {
            $this->actingAs($karyawan)->get($url)->assertOk();
        }
    }

    public function test_halaman_pelanggan_dapat_dibuka(): void
    {
        $pelanggan = User::factory()->pelanggan()->create();
        $order = Order::factory()->create(['user_id' => $pelanggan->id]);

        foreach ([
            route('pelanggan.dashboard'),
            route('pelanggan.orders.index'),
            route('pelanggan.orders.show', $order),
        ] as $url) {
            $this->actingAs($pelanggan)->get($url)->assertOk();
        }
    }
}
