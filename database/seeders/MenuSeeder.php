<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Varian nasi yang ditawarkan untuk menu berat.
     *
     * `delta` adalah tambahan harga (rupiah) yang dihitung di server.
     * Browser hanya mengirim `key`, sehingga harga tidak bisa diubah
     * dari sisi klien.
     */
    private const VARIAN_NASI = [
        'label' => 'Varian Nasi',
        'choices' => [
            ['key' => 'putih', 'label' => 'Nasi Putih (Standar)', 'delta' => 0],
            ['key' => 'jeruk', 'label' => 'Nasi Daun Jeruk', 'delta' => 2000],
            ['key' => 'kuning', 'label' => 'Nasi Kuning', 'delta' => 4000],
        ],
    ];

    private static array $menus = [
        'Menu Harian' => [
            ['name' => 'Nasi Sambel Ayam', 'description' => 'Nasi putih dengan sambel ayam khas', 'price' => 15000, 'image' => 'images/menus/nasi-sambel-ayam.jpg'],
            ['name' => 'Nasi Sambel Lele', 'description' => 'Nasi putih dengan sambel lele goreng', 'price' => 12000, 'image' => 'images/menus/nasi-sambel-lele.jpg'],
            ['name' => 'Nasi Sambel Tahu', 'description' => 'Nasi putih dengan sambel tahu goreng', 'price' => 10000, 'image' => 'images/menus/nasi-sambel-tahu.jpg'],
            ['name' => 'Nasi Sambel Tempe', 'description' => 'Nasi putih dengan sambel tempe goreng', 'price' => 10000, 'image' => 'images/menus/nasi-sambel-tempe.jpg'],
            ['name' => 'Nasi Sambel Telor', 'description' => 'Nasi putih dengan sambel telur goreng', 'price' => 11000, 'image' => 'images/menus/nasi-sambel-telor.jpg'],
        ],
        'Paket Katering' => [
            ['name' => 'Nasi Kotak', 'description' => 'Nasi kotak untuk pesanan katering', 'price' => 18000, 'image' => 'images/menus/nasi-kotak.jpg'],
            ['name' => 'Tumpeng', 'description' => 'Tumpeng nasi kuning untuk acara', 'price' => 250000, 'image' => 'images/menus/tumpeng.jpg'],
        ],
    ];

    public function run(): void
    {
        foreach (self::$menus as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                Menu::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $item['name'],
                    ],
                    [
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'image' => $item['image'] ?? null,
                        'status_ketersediaan' => true,
                        'options' => self::VARIAN_NASI,
                    ]
                );
            }
        }
    }
}
