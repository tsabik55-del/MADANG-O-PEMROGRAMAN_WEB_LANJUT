<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
                    'nama' => $item->menu?->name ?? 'Menu dihapus',
                    'jumlah' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'opsi' => $item->options['label'] ?? null,
                ]),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $cartData = json_decode($request->input('cart_data', '[]'), true);
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

        $items = [];
        $totalPrice = 0.0;

        foreach ($cartData as $row) {
            $menu = $menus[(int) $row['id']];
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

        $order = $this->createOrder($validated, $items, $totalPrice);

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
     * Cocokkan key varian yang dikirim browser dengan definisi di database.
     *
     * Bila menu tidak punya varian, kembalikan delta 0. Bila key tidak
     * dikenal, permintaan ditolak. Bila key kosong, pakai pilihan pertama
     * (biasanya varian standar dengan delta 0).
     *
     * @return array{key: string|null, label: string|null, delta: int}
     */
    private function resolveOption(Menu $menu, mixed $requestedKey): array
    {
        $choices = $menu->options['choices'] ?? null;

        if (! is_array($choices) || $choices === []) {
            return ['key' => null, 'label' => null, 'delta' => 0];
        }

        $choice = null;

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

    /**
     * Buat pesanan beserta item dan pembayarannya dalam satu transaksi.
     *
     * Nomor pesanan dihitung ulang bila terjadi bentrok pada kolom unik
     * order_number (mis. dua pembeli memesan pada detik yang sama).
     */
    private function createOrder(array $validated, array $items, float $totalPrice): Order
    {
        $attempt = 0;

        while (true) {
            try {
                return DB::transaction(function () use ($validated, $items, $totalPrice) {
                    $order = Order::create([
                        'order_number' => $this->generateOrderNumber(),
                        'user_id' => auth()->id(),
                        'customer_name' => $validated['customer_name'],
                        'phone' => $validated['phone'],
                        'pickup_datetime' => $validated['pickup_datetime'],
                        'payment_method' => $validated['payment_method'],
                        'payment_status' => 'belum_lunas',
                        'status' => 'menunggu_pembayaran',
                        'pickup_status' => 'belum_diambil',
                        'source' => 'online',
                        'total_price' => $totalPrice,
                        'notes' => $validated['notes'] ?? null,
                    ]);

                    foreach ($items as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_id' => $item['menu']->id,
                            'quantity' => $item['quantity'],
                            'price' => $item['unitPrice'],
                            'subtotal' => $item['subtotal'],
                            'options' => $item['options'],
                        ]);
                    }

                    Payment::create([
                        'order_id' => $order->id,
                        'method' => $validated['payment_method'],
                        'amount' => $totalPrice,
                        'status' => 'pending',
                    ]);

                    return $order;
                });
            } catch (QueryException $e) {
                if (! $this->isDuplicateOrderNumber($e) || $attempt >= 4) {
                    throw $e;
                }

                $attempt++;
                usleep(50_000);
            }
        }
    }

    /**
     * Nomor pesanan hari ini, dihitung dari nomor terakhir yang sudah dipakai.
     *
     * Dipakai dari nomor terakhir, bukan Order::count(), karena jumlah total
     * order bisa berkurang saat pelanggan membatalkan pesanan sehingga nomor
     * lama dipakai ulang dan bentrok dengan kolom unik.
     */
    private function generateOrderNumber(): string
    {
        $today = now();

        $lastNumber = Order::whereDate('created_at', $today->toDateString())
            ->orderByDesc('order_number')
            ->value('order_number');

        $next = $lastNumber ? ((int) substr($lastNumber, -4) + 1) : 1;

        return 'MAD-'.$today->format('Ymd').'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Deteksi pelanggaran unique constraint pada kolom order_number.
     */
    private function isDuplicateOrderNumber(QueryException $e): bool
    {
        return ($e->errorInfo[0] ?? null) === '23000'
            && str_contains(strtolower($e->getMessage()), 'order_number');
    }

    /**
     * QR pembayaran untuk pesanan QRIS.
     *
     * Isi QR dibuat di server (nomor pesanan + nominal) sehingga tidak
     * bergantung pada layanan pihak ketiga. Hasilnya disimpan di
     * payments.qr_code agar tidak dibuat ulang setiap kali modal dibuka.
     */
    public function qris(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        if ($order->payment_method !== 'qris') {
            abort(404);
        }

        $isi = 'MADANG-O|'.$order->order_number.'|'.number_format((float) $order->total_price, 0, '', '');

        $payment = $order->payment;

        if ($payment && blank($payment->qr_code)) {
            $payment->update(['qr_code' => $isi]);
        }

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

        $payment = $order->payment;

        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $order->total_price,
                'status' => 'pending',
            ]);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'message' => 'Pembayaran pesanan ini sudah lunas.',
                'payment_status' => 'paid',
            ]);
        }

        $payment->update(['status' => 'menunggu_verifikasi']);

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
        // belum masuk tahap proses (menunggu_pembayaran). Setelah diproses,
        // pelanggan tidak bisa membatalkan sendiri.
        if (! in_array($order->status, ['menunggu_pembayaran'])) {
            return back()->with('error', 'Pesanan tidak bisa dibatalkan karena sudah diproses.');
        }

        $order->delete();

        return redirect()->route('pelanggan.orders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
