<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::with('category')->latest()->paginate(15);

        return view('owner.menus.index', compact('menus'));
    }

    public function create(): View
    {
        $categories = Category::all();

        return view('owner.menus.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['status_ketersediaan'] = $request->boolean('status_ketersediaan');

        if ($request->hasFile('image_file')) {
            $validated['image'] = $request->file('image_file')->store('images/menus', 'public');
        }

        unset($validated['image_file']);

        Menu::create($validated);

        return redirect()->route('owner.menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        $categories = Category::all();

        return view('owner.menus.edit', compact('menu', 'categories'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['status_ketersediaan'] = $request->boolean('status_ketersediaan');

        if ($request->hasFile('image_file')) {
            $oldImage = $menu->image;
            $validated['image'] = $request->file('image_file')->store('images/menus', 'public');

            $menu->update($validated);
            $this->deleteImageIfUnused($oldImage);
        } else {
            // Menghapus isi kolom path berarti foto lama dilepas.
            $validated['image'] = filled($request->input('image')) ? (string) $request->input('image') : null;

            $menu->update($validated);
        }

        return redirect()->route('owner.menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        try {
            $image = $menu->image;
            $menu->delete();
            $this->deleteImageIfUnused($image);
        } catch (QueryException $e) {
            // Kunci foreign key restrictOnDelete: menu yang sudah pernah
            // dipesan tidak boleh dihapus agar riwayat penjualan tetap utuh.
            return back()->with(
                'error',
                'Menu tidak bisa dihapus karena sudah dipakai pada pesanan. Nonaktifkan saja status ketersediaannya.'
            );
        }

        return redirect()->route('owner.menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price' => 'required|numeric|min:0',
        ]);
    }

    /**
     * Hapus berkas foto hanya bila tidak dipakai menu lain.
     */
    private function deleteImageIfUnused(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'images/menus/')) {
            return;
        }

        if (Menu::where('image', $path)->exists()) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
