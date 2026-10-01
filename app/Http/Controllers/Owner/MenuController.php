<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->latest()->paginate(15);
        return view('owner.menus.index', compact('menus'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('owner.menus.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'status_ketersediaan' => 'boolean',
        ]);

        Menu::create($validated);

        return redirect()->route('owner.menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $categories = Category::all();
        return view('owner.menus.edit', compact('menu', 'categories'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'status_ketersediaan' => 'boolean',
        ]);

        $menu->update($validated);

        return redirect()->route('owner.menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('owner.menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}