<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sumber tunggal logika pesanan: pembuatan nomor, penyimpanan, transisi
 * status, dan sinkronisasi tabel payments.
 *
 * Sebelumnya status pembayaran pada orders dan payments diubah di tempat
 * berbeda sehingga sering tidak sinkron (mis. kasir menandai lunas tetapi
 * payments.status tetap pending). Semua perubahan sekarang lewat sini.
 */
class OrderService
{
    public function __construct(private readonly OrderItemBuilder $builder) {}

    /**
     * Simpan pesanan baru beserta item dan pembayarannya.
     *
     * @param  array<string, mixed>  $attributes  Data pelanggan, waktu ambil, metode bayar, catatan.
     * @param  array<int, array{menu: Menu, quantity: int, unitPrice: float, subtotal: float, options: array<string, mixed>|null}>  $items
     */
    public function create(
        array $attributes,
        array $items,
        float $totalPrice,
        ?User $actor = null,
        string $source = 'online',
        string $paymentStatus = 'belum_lunas',
    ): Order {
        $paymentStatus = in_array($paymentStatus, ['belum_lunas', 'lunas'], true)
            ? $paymentStatus
            : 'belum_lunas';

        $attempt = 0;

        while (true) {
            try {
                return DB::transaction(function () use ($attributes, $items, $totalPrice, $actor, $source, $paymentStatus) {
                    $order = Order::create([
                        'order_number' => $this->generateNumber(),
                        'user_id' => $actor?->id,
                        'customer_name' => $attributes['customer_name'],
                        'phone' => $attributes['phone'] ?? null,
                        'pickup_datetime' => $attributes['pickup_datetime'],
                        'payment_method' => $attributes['payment_method'],
                        'payment_status' => $paymentStatus,
                        'status' => 'menunggu_pembayaran',
                        'pickup_status' => 'belum_diambil',
                        'source' => $source,
                        'total_price' => $totalPrice,
                        'notes' => $attributes['notes'] ?? null,
                    ]);

                    foreach ($items as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_id' => $item['menu']->id,
                            'menu_name' => $item['menu']->name,
                            'quantity' => $item['quantity'],
                            'price' => $item['unitPrice'],
                            'subtotal' => $item['subtotal'],
                            'options' => $item['options'],
                        ]);
                    }

                    Payment::create([
                        'order_id' => $order->id,
                        'method' => $attributes['payment_method'],
                        'amount' => $totalPrice,
                        'status' => $paymentStatus === 'lunas' ? 'paid' : 'pending',
                        'paid_at' => $paymentStatus === 'lunas' ? now() : null,
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
     * Bangun item dari baris keranjang (id menu, jumlah, varian).
     *
     * Menu yang tidak dikenal/tidak tersedia ditolak dengan pesan yang sama
     * seperti alur checkout online.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{items: array<int, array<string, mixed>>, total_price: float}
     */
    public function buildItems(array $rows): array
    {
        $menuIds = collect($rows)
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
                : 'Ada item pesanan yang tidak valid. Silakan periksa kembali.';

            throw ValidationException::withMessages(['cart_data' => $message]);
        }

        return $this->builder->build($rows, $menus);
    }

    /**
     * Ubah status pesanan sekaligus menyinkronkan status pembayaran.
     *
     * Karyawan dibatasi transisi maju sesuai Order::STATUS_TRANSITIONS.
     * Owner bebas mengoreksi status (tetap divalidasi enum oleh controller).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateStatus(Order $order, array $data, User $actor): void
    {
        $isOwner = $actor->role === 'owner';

        $newStatus = $data['status'] ?? $order->status;
        $newPaymentStatus = $data['payment_status'] ?? $order->payment_status;

        if ($newStatus !== $order->status && ! $isOwner) {
            $allowed = Order::STATUS_TRANSITIONS[$order->status] ?? [];

            if (! in_array($newStatus, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Status pesanan tidak bisa diubah dari "'
                        .$this->label($order->status).'" ke "'.$this->label($newStatus).'".',
                ]);
            }
        }

        if ($newPaymentStatus !== $order->payment_status && ! $isOwner) {
            $allowed = Order::PAYMENT_STATUS_TRANSITIONS[$order->payment_status] ?? [];

            if (! in_array($newPaymentStatus, $allowed, true)) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Status pembayaran tidak bisa diubah dari "'
                        .$this->label($order->payment_status).'" ke "'.$this->label($newPaymentStatus).'".',
                ]);
            }
        }

        DB::transaction(function () use ($order, $data, $newPaymentStatus) {
            $order->update(array_intersect_key($data, array_flip([
                'payment_status', 'pickup_status', 'status', 'notes',
            ])));

            if (array_key_exists('payment_status', $data)) {
                $this->syncPayment($order->fresh('payment'), $newPaymentStatus);
            }
        });
    }

    /**
     * Samakan baris pembayaran dengan status pembayaran pesanan.
     */
    public function syncPayment(Order $order, string $paymentStatus): void
    {
        $payment = $order->payment;

        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'amount' => $order->total_price,
                'status' => 'pending',
            ]);
        }

        $attributes = match ($paymentStatus) {
            'lunas' => [
                'status' => 'paid',
                'paid_at' => $payment->paid_at ?? now(),
            ],
            'menunggu_verifikasi' => [
                'status' => 'menunggu_verifikasi',
                'paid_at' => null,
            ],
            default => [
                'status' => 'pending',
                'paid_at' => null,
            ],
        };

        $payment->update($attributes);
    }

    /**
     * Batalkan pesanan tanpa menghapus riwayat.
     */
    public function cancel(Order $order): void
    {
        if ($order->isCancelled()) {
            return;
        }

        $order->update(['status' => 'dibatalkan']);
    }

    /**
     * Nomor pesanan hari ini, dihitung dari nomor terakhir yang sudah dipakai.
     *
     * Hanya nomor dengan prefix tanggal hari ini yang dihitung. Sebelumnya
     * query mengambil nomor terakhir dari SEMUA pesanan yang dibuat hari ini,
     * termasuk nomor yang tanggalnya berbeda (mis. pesanan dengan jadwal
     * ambil beberapa hari ke depan). Karena diurutkan sebagai teks,
     * "MAD-20261128-0008" dianggap lebih besar dari "MAD-20261124-0019"
     * sehingga nomor berikutnya bisa bertabrakan dan memicu duplicate key.
     */
    public function generateNumber(): string
    {
        $today = now();
        $prefix = 'MAD-'.$today->format('Ymd').'-';

        $lastNumber = Order::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $next = $lastNumber ? ((int) substr($lastNumber, -4) + 1) : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function isDuplicateOrderNumber(QueryException $e): bool
    {
        return ($e->errorInfo[0] ?? null) === '23000'
            && str_contains(strtolower($e->getMessage()), 'order_number');
    }

    private function label(string $value): string
    {
        return ucfirst(str_replace('_', ' ', $value));
    }
}
