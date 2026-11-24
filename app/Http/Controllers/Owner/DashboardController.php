<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_menus' => Menu::count(),
            'total_orders' => Order::notCancelled()->count(),
            'total_pelanggan' => User::where('role', 'pelanggan')->count(),

            // Pendapatan diambil dari payments yang benar-benar lunas dan
            // bukan pesanan batal. Sebelumnya dihitung dari orders.payment_status
            // sehingga pesanan batal ikut terhitung.
            'total_pendapatan' => (float) Payment::where('status', 'paid')
                ->whereHas('order', fn ($query) => $query->where('status', '!=', 'dibatalkan'))
                ->sum('amount'),
            'pesanan_hari_ini' => Order::notCancelled()
                ->whereDate('created_at', today())
                ->count(),
        ];

        $recentOrders = Order::with('user')
            ->notCancelled()
            ->latest()
            ->take(8)
            ->get();

        return view('owner.dashboard', compact('stats', 'recentOrders'));
    }
}
