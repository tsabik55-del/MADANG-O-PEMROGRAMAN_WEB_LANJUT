<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_orders' => Order::notCancelled()->count(),
            'orders_belum_lunas' => Order::notCancelled()
                ->whereIn('payment_status', ['belum_lunas', 'menunggu_verifikasi'])
                ->count(),
            'orders_belum_diambil' => Order::notCancelled()
                ->where('pickup_status', 'belum_diambil')
                ->count(),
            'orders_menunggu_verifikasi' => Order::notCancelled()
                ->where('payment_status', 'menunggu_verifikasi')
                ->count(),
        ];

        $recentOrders = Order::with('user')->latest()->take(10)->get();

        return view('karyawan.dashboard', compact('stats', 'recentOrders'));
    }
}
