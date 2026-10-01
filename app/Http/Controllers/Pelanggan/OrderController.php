<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\Payment;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.menu')
            ->latest()
            ->paginate(10);
        return view('pelanggan.orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $cartData = json_decode($request->cart_data, true);
        $totalPrice = (int) $request->total_price;

        if (empty($cartData)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $request->validate([
            'payment_method' => 'required|in:tunai,transfer,qris',
        ]);

        $order = Order::create([
            'order_number' => 'MAD-' . now()->format('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'phone' => $request->phone,
            'pickup_datetime' => $request->pickup_datetime,
            'payment_method' => $request->payment_method,
            'payment_status' => 'belum_lunas',
            'status' => 'menunggu_pembayaran',
            'pickup_status' => 'belum_diambil',
            'source' => 'online',
            'total_price' => $totalPrice,
            'notes' => $request->notes,
        ]);

        foreach ($cartData as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        Payment::create([
            'order_id' => $order->id,
            'method' => $request->payment_method,
            'amount' => $totalPrice,
            'status' => 'pending',
        ]);

        return redirect()->route('pelanggan.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat! Silakan ambil pada waktu yang ditentukan.');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items.menu');
        return view('pelanggan.orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        // Aturan bisnis: pesanan boleh dibatalkan selama status alur
        // belum masuk tahap proses (menunggu_pembayaran). Setelah diproses,
        // pelanggan tidak bisa membatalkan sendiri.
        if (! in_array($order->status, ['menunggu_pembayaran'])) {
            return back()->with('error', 'Pesanan tidak bisa dibatalkan karena sudah diproses.');
        }

        $order->delete();
        return redirect()->route('pelanggan.orders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }
}