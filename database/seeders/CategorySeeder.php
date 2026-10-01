<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Menu Harian', 'icon' => '🍚'],
            ['name' => 'Paket Katering', 'icon' => '📦'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
