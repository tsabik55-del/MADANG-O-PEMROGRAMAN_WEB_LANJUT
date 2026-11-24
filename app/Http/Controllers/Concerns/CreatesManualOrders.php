<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Aturan bersama untuk pencatatan pesanan manual oleh owner/karyawan.
 */
trait CreatesManualOrders
{
    /**
     * @return array<string, mixed>
     */
    protected function validateManualOrder(Request $request): array
    {
        return $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'pickup_datetime' => 'required|date|after:now',
            'payment_method' => 'required|in:tunai,transfer,qris',
            'payment_status' => 'required|in:belum_lunas,lunas',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.quantity' => 'nullable|integer|min:0|max:99',
            'items.*.option' => 'nullable|string|max:50',
        ]);
    }

    /**
     * Ambil baris menu dengan jumlah > 0 dari formulir.
     *
     * @return array<int, array{id: int, quantity: int, option: mixed}>
     */
    protected function manualOrderRows(Request $request): array
    {
        $rows = collect($request->input('items', []))
            ->map(fn ($row, $menuId) => [
                'id' => (int) $menuId,
                'quantity' => (int) ($row['quantity'] ?? 0),
                'option' => $row['option'] ?? null,
            ])
            ->filter(fn ($row) => $row['quantity'] > 0)
            ->values()
            ->all();

        if ($rows === []) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu menu dengan jumlah lebih dari 0.',
            ]);
        }

        return $rows;
    }
}
