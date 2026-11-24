<?php

use App\Http\Controllers\Karyawan\DashboardController as KaryawanDashboardController;
use App\Http\Controllers\Karyawan\OrderController as KaryawanOrderController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MenuController;
use App\Http\Controllers\Owner\OrderController as OwnerOrderController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Owner\UserController;
use App\Http\Controllers\Pelanggan\DashboardController as PelangganDashboardController;
use App\Http\Controllers\Pelanggan\OrderController as PelangganOrderController;
use App\Http\Controllers\Pelanggan\ProfileController as PelangganProfileController;
use Illuminate\Support\Facades\Route;

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
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::resource('menus', MenuController::class)->except(['show']);
        Route::resource('orders', OwnerOrderController::class);
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('role:karyawan')->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');

        // /orders/create harus didaftarkan sebelum /orders/{order}.
        Route::get('/orders/create', [KaryawanOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [KaryawanOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders', [KaryawanOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [KaryawanOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [KaryawanOrderController::class, 'updateStatus'])->name('orders.update-status');
    });

    Route::middleware('role:pelanggan')->prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::get('/dashboard', [PelangganDashboardController::class, 'index'])->name('dashboard');

        Route::patch('/profile', [PelangganProfileController::class, 'update'])->name('profile.update');

        // /orders/data harus didaftarkan sebelum /orders/{order}, kalau tidak
        // "data" akan dianggap sebagai ID pesanan dan berujung 404.
        Route::get('/orders/data', [PelangganOrderController::class, 'data'])->name('orders.data');
        Route::get('/orders', [PelangganOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [PelangganOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/qris', [PelangganOrderController::class, 'qris'])->name('orders.qris');
        Route::post('/orders/{order}/bayar', [PelangganOrderController::class, 'konfirmasiBayar'])->name('orders.bayar');
        Route::get('/orders/{order}', [PelangganOrderController::class, 'show'])->name('orders.show');
        Route::delete('/orders/{order}', [PelangganOrderController::class, 'destroy'])->name('orders.destroy');
    });
});
