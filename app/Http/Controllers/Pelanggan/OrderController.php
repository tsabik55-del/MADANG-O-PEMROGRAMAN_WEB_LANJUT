<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderItemBuilder;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly OrderItemBuilder $builder,
    ) {}

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.menu')
            ->latest()
            ->paginate(10);

        return view('pelanggan.orders.index', compact('orders'));
    }

    /**
     * Riwayat ringkas dalam bentuk JSON untuk modal di dashboard.
     */
    public function data(): JsonResponse
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.menu')
            ->latest()
            ->limit(15)
            ->get();

        return response()->json([
            'orders' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'nomor' => $order->order_number,
                'tanggal' => $order->pickup_datetime?->format('d/m/Y'),
                'jam' => $order->pickup_datetime?->format('H:i'),
                'total' => (float) $order->total_price,
                'metode' => $order->payment_method,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'pickup_status' => $order->pickup_status,
                'detail_url' => route('pelanggan.orders.show', $order),
                'items' => $order->items->map(fn (OrderItem $item) => [
                    'nama' => $item->display_name,
                    'jumlah' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'opsi' => $item->options['label'] ?? null,
                ]),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $cartData = json_decode((string) $request->input('cart_data', '[]'), true);
        $cartData = is_array($cartData)
            ? array_values(array_filter($cartData, 'is_array'))
            : [];

        if ($cartData === []) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Keranjang masih kosong.'], 422);
            }

            return back()->with('error', 'Keranjang kosong.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'pickup_datetime' => 'required|date|after:now',
            'payment_method' => 'required|in:tunai,transfer,qris',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Harga dan nama menu SELALU diambil dari database. Nilai price/name
        // yang dikirim browser sengaja diabaikan supaya harga tidak bisa
        // dimanipulasi dari sisi klien.
        $menuIds = collect($cartData)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $menus = Menu::whereIn('id', $menuIds)
            ->where('status_ketersediaan', true)
            ->get()
            ->keyBy('id');

        if ($menus->count() !== $menuIds->count()) {
            $missingNames = Menu::whereIn('id', $menuIds->diff($menus->keys())->all())
                ->pluck('name');

            $message = $missingNames->isNotEmpty()
                ? 'Menu tidak tersedia lagi: '.$missingNames->implode(', ').'.'
                : 'Ada item keranjang yang tidak valid. Silakan perbarui keranjangmu.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withInput()->with('error', $message);
        }

        $built = $this->builder->build($cartData, $menus);

        $order = $this->orders->create(
            $validated,
            $built['items'],
            $built['total_price'],
            $request->user(),
            'online',
            'belum_lunas',
        );

        if ($request->expectsJson()) {
            $order->load('items.menu');

            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'order' => [
                    'id' => $order->id,
                    'nomor' => $order->order_number,
                    'total' => (float) $order->total_price,
                    'payment_method' => $order->payment_method,
                    'pickup_datetime' => $order->pickup_datetime?->toIso8601String(),
                    'status' => $order->status,
                    'detail_url' => route('pelanggan.orders.show', $order),
                ],
            ], 201);
        }

        return redirect()->route('pelanggan.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat! Silakan ambil pada waktu yang ditentukan.')
            ->with('cart_cleared', true);
    }

    /**
     * QR pembayaran untuk pesanan QRIS.
     *
     * Isi QR ditentukan oleh nomor pesanan + nominal, sehingga bisa dibuat
     * ulang kapan saja tanpa menyimpan apa pun ke database.
     */
    public function qris(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        if ($order->payment_method !== 'qris') {
            abort(404);
        }

        $isi = 'MADANG-O|'.$order->order_number.'|'.number_format((float) $order->total_price, 0, '', '');

        return response()->json([
            'isi' => $isi,
            'svg' => (string) QrCode::format('svg')
                ->size(220)
                ->margin(1)
                ->errorCorrection('M')
                ->generate($isi),
        ]);
    }

    /**
     * Pelanggan mengaku sudah membayar.
     *
     * Status pembayaran HANYA berubah menjadi menunggu_verifikasi. Kasir
     * yang berhak menandai lunas. Dengan begitu pelanggan tidak bisa
     * melunasi pesanannya sendiri.
     */
    public function konfirmasiBayar(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        if (! in_array($order->payment_method, ['qris', 'transfer'], true)) {
            return response()->json([
                'message' => 'Pesanan tunai dibayar langsung di kasir saat pengambilan.',
            ], 422);
        }

        if ($order->payment_status === 'lunas' || $order->payment?->status === 'paid') {
            return response()->json([
                'message' => 'Pembayaran pesanan ini sudah lunas.',
                'payment_status' => 'lunas',
            ]);
        }

        $order->update(['payment_status' => 'menunggu_verifikasi']);
        $this->orders->syncPayment($order->fresh('payment'), 'menunggu_verifikasi');

        return response()->json([
            'message' => 'Konfirmasi diterima. Pembayaran akan diverifikasi kasir.',
            'payment_status' => 'menunggu_verifikasi',
        ]);
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
        // belum masuk tahap proses. Setelah diproses, pelanggan tidak bisa
        // membatalkan sendiri. Riwayat tetap disimpan (soft cancel) agar
        // laporan dan nomor pesanan tidak hilang.
        if (! in_array($order->status, ['menunggu_pembayaran'], true)) {
            return back()->with('error', 'Pesanan tidak bisa dibatalkan karena sudah diproses.');
        }

        $this->orders->cancel($order);

        return redirect()->route('pelanggan.orders.index')
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
