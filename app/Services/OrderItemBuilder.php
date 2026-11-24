<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Menghitung item pesanan dari data keranjang.
 *
 * Harga, nama, dan delta varian SELALU diambil dari database. Nilai yang
 * dikirim browser sengaja diabaikan agar harga tidak dapat dimanipulasi.
 */
class OrderItemBuilder
{
    /**
     * @param  array<int, array{id?: mixed, quantity?: mixed, option?: mixed}>  $rows
     * @param  Collection<int, Menu>  $menus  Koleksi menu yang di-key dengan id.
     * @return array{items: array<int, array{menu: Menu, quantity: int, unitPrice: float, subtotal: float, options: array<string, mixed>|null}>, total_price: float}
     */
    public function build(array $rows, Collection $menus): array
    {
        $items = [];
        $totalPrice = 0.0;

        foreach ($rows as $row) {
            $menu = $menus[(int) ($row['id'] ?? 0)] ?? null;

            if (! $menu instanceof Menu) {
                throw ValidationException::withMessages([
                    'cart_data' => 'Ada item keranjang yang tidak valid. Silakan perbarui keranjangmu.',
                ]);
            }

            $quantity = min(max((int) ($row['quantity'] ?? 1), 1), 99);
            $option = $this->resolveOption($menu, $row['option'] ?? null);

            $unitPrice = (float) $menu->price + $option['delta'];
            $subtotal = $unitPrice * $quantity;

            $totalPrice += $subtotal;
            $items[] = [
                'menu' => $menu,
                'quantity' => $quantity,
                'unitPrice' => $unitPrice,
                'subtotal' => $subtotal,
                'options' => $option['key'] === null ? null : $option,
            ];
        }

        return ['items' => $items, 'total_price' => $totalPrice];
    }

    /**
     * Cocokkan key varian yang dikirim browser dengan definisi di database.
     *
     * @return array{key: string|null, label: string|null, delta: int}
     */
    public function resolveOption(Menu $menu, mixed $requestedKey): array
    {
        $choices = $menu->options['choices'] ?? null;

        if (! is_array($choices) || $choices === []) {
            return ['key' => null, 'label' => null, 'delta' => 0];
        }

        if (is_string($requestedKey) && $requestedKey !== '') {
            $choice = collect($choices)->firstWhere('key', $requestedKey);

            if (! is_array($choice)) {
                throw ValidationException::withMessages([
                    'cart_data' => "Varian untuk menu {$menu->name} tidak dikenali.",
                ]);
            }
        } else {
            $choice = $choices[0];
        }

        return [
            'key' => (string) $choice['key'],
            'label' => (string) $choice['label'],
            'delta' => max(0, (int) ($choice['delta'] ?? 0)),
        ];
    }
}
