<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $cartData = json_decode($request->input('cart_data', '[]'), true);
        $cartData = is_array($cartData)
            ? array_values(array_filter($cartData, 'is_array'))
            : [];

        if ($cartData === []) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $validated = $request->validate([
            'customer_name'   => 'required|string|max:255',
            'phone'           => 'required|string|max:30',
            'pickup_datetime' => 'required|date|after:now',
            'payment_method'  => 'required|in:tunai,transfer,qris',
            'notes'           => 'nullable|string|max:1000',
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

            return back()
                ->withInput()
                ->with('error', $missingNames->isNotEmpty()
                    ? 'Menu tidak tersedia lagi: ' . $missingNames->implode(', ') . '.'
                    : 'Ada item keranjang yang tidak valid. Silakan perbarui keranjangmu.');
        }

        $items = [];
        $totalPrice = 0;

        foreach ($cartData as $row) {
            $menu = $menus[(int) $row['id']];
            $quantity = min(max((int) ($row['quantity'] ?? 1), 1), 99);
            $subtotal = $menu->price * $quantity;

            $totalPrice += $subtotal;
            $items[] = ['menu' => $menu, 'quantity' => $quantity, 'subtotal' => $subtotal];
        }

        $order = $this->createOrder($validated, $items, $totalPrice);

        return redirect()->route('pelanggan.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat! Silakan ambil pada waktu yang ditentukan.')
            ->with('cart_cleared', true);
    }

    /**
     * Buat pesanan beserta item dan pembayarannya dalam satu transaksi.
     *
     * Nomor pesanan dihitung ulang bila terjadi bentrok pada kolom unik
     * order_number (mis. dua pembeli memesan pada detik yang sama).
     */
    private function createOrder(array $validated, array $items, int $totalPrice): Order
    {
        $attempt = 0;

        while (true) {
            try {
                return DB::transaction(function () use ($validated, $items, $totalPrice) {
                    $order = Order::create([
                        'order_number'    => $this->generateOrderNumber(),
                        'user_id'         => auth()->id(),
                        'customer_name'   => $validated['customer_name'],
                        'phone'           => $validated['phone'],
                        'pickup_datetime' => $validated['pickup_datetime'],
                        'payment_method'  => $validated['payment_method'],
                        'payment_status'  => 'belum_lunas',
                        'status'          => 'menunggu_pembayaran',
                        'pickup_status'   => 'belum_diambil',
                        'source'          => 'online',
                        'total_price'     => $totalPrice,
                        'notes'           => $validated['notes'] ?? null,
                    ]);

                    foreach ($items as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_id'  => $item['menu']->id,
                            'quantity' => $item['quantity'],
                            'price'    => $item['menu']->price,
                            'subtotal' => $item['subtotal'],
                        ]);
                    }

                    Payment::create([
                        'order_id' => $order->id,
                        'method'   => $validated['payment_method'],
                        'amount'   => $totalPrice,
                        'status'   => 'pending',
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
     * Dipakai dari jumlah order hari ini, bukan Order::count()(), karena
     * jumlah total order bisa berkurang saat pelanggan membatalkan pesanan
     * sehingga nomor lama dipakai ulang dan bentrok dengan kolom unik.
     */
    private function generateOrderNumber(): string
    {
        $today = now();

        $lastNumber = Order::whereDate('created_at', $today->toDateString())
            ->orderByDesc('order_number')
            ->value('order_number');

        $next = $lastNumber ? ((int) substr($lastNumber, -4) + 1) : 1;

        return 'MAD-' . $today->format('Ymd') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Deteksi pelanggaran unique constraint pada kolom order_number.
     */
    private function isDuplicateOrderNumber(QueryException $e): bool
    {
        return ($e->errorInfo[0] ?? null) === '23000'
            && str_contains(strtolower($e->getMessage()), 'order_number');
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