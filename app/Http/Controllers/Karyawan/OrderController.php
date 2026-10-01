<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.menu'])->latest()->paginate(20);
        return view('karyawan.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.menu']);
        return view('karyawan.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:belum_lunas,lunas',
            'pickup_status' => 'required|in:belum_diambil,sudah_diambil',
            'status' => 'required|in:menunggu_pembayaran,diproses,siap,dikirim,selesai,dibatalkan',
        ]);

        $order->update($validated);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}