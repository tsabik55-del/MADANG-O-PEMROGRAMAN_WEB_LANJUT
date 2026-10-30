<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Katalog menu untuk pelanggan.
     *
     * Data dikirim dalam bentuk array siap pakai supaya Alpine dapat
     * menyaring dan mencari tanpa memuat ulang halaman. Harga dan nama
     * tetap berasal dari database.
     */
    public function index(): View
    {
        $categories = Category::orderBy('name')->get();

        $menus = Menu::with('category')
            ->where('status_ketersediaan', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $menuData = $menus->map(fn (Menu $menu) => [
            'id' => $menu->id,
            'nama' => $menu->name,
            'deskripsi' => $menu->description ?? '',
            'harga' => (float) $menu->price,
            'foto' => $menu->image_url,
            'kategori' => $menu->category_id,
            'kategoriNama' => $menu->category?->name,
            'opsi' => $menu->options,
        ])->values();

        $kategoriData = $categories->map(fn (Category $category) => [
            'id' => $category->id,
            'nama' => $category->name,
            'ikon' => $category->icon ?: '🍽️',
        ])->values();

        $user = auth()->user();

        return view('pelanggan.dashboard', [
            'menuData' => $menuData,
            'kategoriData' => $kategoriData,
            'userData' => [
                'name' => $user->name,
                'email' => $user->email,
                'wa' => $user->wa_number ?? '',
                'alamat' => $user->address ?? '',
            ],
        ]);
    }
}
