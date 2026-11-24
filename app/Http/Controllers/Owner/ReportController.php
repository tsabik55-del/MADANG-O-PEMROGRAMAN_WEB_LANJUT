<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Rekap penjualan harian & bulanan (FR-15).
 *
 * Pendapatan diambil dari baris payments berstatus paid, bukan dari
 * orders.payment_status, dan pesanan berstatus dibatalkan tidak dihitung.
 */
class ReportController extends Controller
{
    private const NAMA_BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public function index(Request $request): View
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $orders = Order::with(['payment', 'items.menu'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $validOrders = $orders->where('status', '!=', 'dibatalkan');

        $revenue = $validOrders
            ->filter(fn (Order $order) => $order->payment?->status === 'paid')
            ->sum(fn (Order $order) => (float) $order->payment->amount);

        $summary = [
            'total_orders' => $validOrders->count(),
            'total_cancelled' => $orders->where('status', 'dibatalkan')->count(),
            'total_items' => $validOrders->sum(fn (Order $order) => $order->items->sum('quantity')),
            'total_revenue' => (float) $revenue,
            'average_order' => $validOrders->count() > 0 ? $revenue / $validOrders->count() : 0,
        ];

        $daily = $validOrders
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'))
            ->map(function ($group, $date) {
                return [
                    'tanggal' => Carbon::parse($date),
                    'orders' => $group->count(),
                    'revenue' => (float) $group
                        ->filter(fn (Order $order) => $order->payment?->status === 'paid')
                        ->sum(fn (Order $order) => (float) $order->payment->amount),
                ];
            })
            ->sortKeys()
            ->values();

        $topMenus = $this->topMenus($validOrders);
        $monthly = $this->monthlyRecap($to->year);

        return view('owner.reports.index', compact(
            'from',
            'to',
            'summary',
            'daily',
            'topMenus',
            'monthly',
        ));
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return Collection<int, array{name: string, quantity: int, subtotal: float}>
     */
    private function topMenus($orders)
    {
        $rows = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $name = $item->display_name;

                $rows[$name] ??= ['name' => $name, 'quantity' => 0, 'subtotal' => 0.0];
                $rows[$name]['quantity'] += (int) $item->quantity;
                $rows[$name]['subtotal'] += (float) $item->subtotal;
            }
        }

        return collect($rows)->sortByDesc('quantity')->take(10)->values();
    }

    /**
     * @return array<int, array{bulan: string, orders: int, revenue: float}>
     */
    private function monthlyRecap(int $year): array
    {
        $orders = Order::with('payment')
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $result = [];

        for ($month = 1; $month <= 12; $month++) {
            $group = $orders->filter(fn (Order $order) => (int) $order->created_at->month === $month);

            $result[] = [
                'bulan' => self::NAMA_BULAN[$month].' '.$year,
                'orders' => $group->count(),
                'revenue' => (float) $group
                    ->filter(fn (Order $order) => $order->payment?->status === 'paid')
                    ->sum(fn (Order $order) => (float) $order->payment->amount),
            ];
        }

        return $result;
    }
}
