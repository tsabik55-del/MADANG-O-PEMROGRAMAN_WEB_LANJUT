<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.menu'])->latest()->paginate(20);
        return view('owner.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.menu']);
        return view('owner.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('owner.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:belum_lunas,lunas',
            'pickup_status' => 'required|in:belum_diambil,sudah_diambil',
            'status' => 'required|in:menunggu_pembayaran,diproses,siap,dikirim,selesai,dibatalkan',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('owner.orders.index')->with('success', 'Pesanan berhasil diperbarui.');
    }
}