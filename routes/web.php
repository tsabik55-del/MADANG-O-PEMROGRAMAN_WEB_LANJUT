<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use App\Models\Order;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->get('/dashboard', function () {
    $redirect = match (auth()->user()->role) {
        'owner' => route('owner.dashboard'),
        'karyawan' => route('karyawan.dashboard'),
        default => route('pelanggan.dashboard'),
    };
    return redirect($redirect);
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', function () {
            $stats = [
                'total_menus' => Menu::count(),
                'total_orders' => Order::count(),
                'total_pelanggan' => \App\Models\User::where('role', 'pelanggan')->count(),
                'total_pendapatan' => Order::where('payment_status', 'lunas')->sum('total_price'),
            ];
            return view('owner.dashboard', compact('stats'));
        })->name('dashboard');

        Route::resource('menus', \App\Http\Controllers\Owner\MenuController::class)->except(['show']);
        Route::resource('orders', \App\Http\Controllers\Owner\OrderController::class)->except(['create', 'store']);
        Route::resource('users', \App\Http\Controllers\Owner\UserController::class)->except(['show']);
    });

    Route::middleware('role:karyawan')->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', function () {
            $stats = [
                'total_orders' => Order::count(),
                'orders_belum_lunas' => Order::where('payment_status', 'belum_lunas')->count(),
                'orders_belum_diambil' => Order::where('pickup_status', 'belum_diambil')->count(),
            ];
            return view('karyawan.dashboard', compact('stats'));
        })->name('dashboard');

        Route::get('/orders', [\App\Http\Controllers\Karyawan\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Karyawan\OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [\App\Http\Controllers\Karyawan\OrderController::class, 'updateStatus'])->name('orders.update-status');
    });

    Route::middleware('role:pelanggan')->prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Pelanggan\DashboardController::class, 'index'])->name('dashboard');

        Route::patch('/profile', [\App\Http\Controllers\Pelanggan\ProfileController::class, 'update'])->name('profile.update');

        // /orders/data harus didaftarkan sebelum /orders/{order}, kalau tidak
        // "data" akan dianggap sebagai ID pesanan dan berujung 404.
        Route::get('/orders/data', [\App\Http\Controllers\Pelanggan\OrderController::class, 'data'])->name('orders.data');
        Route::get('/orders', [\App\Http\Controllers\Pelanggan\OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [\App\Http\Controllers\Pelanggan\OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/qris', [\App\Http\Controllers\Pelanggan\OrderController::class, 'qris'])->name('orders.qris');
        Route::post('/orders/{order}/bayar', [\App\Http\Controllers\Pelanggan\OrderController::class, 'konfirmasiBayar'])->name('orders.bayar');
        Route::get('/orders/{order}', [\App\Http\Controllers\Pelanggan\OrderController::class, 'show'])->name('orders.show');
        Route::delete('/orders/{order}', [\App\Http\Controllers\Pelanggan\OrderController::class, 'destroy'])->name('orders.destroy');
    });
});