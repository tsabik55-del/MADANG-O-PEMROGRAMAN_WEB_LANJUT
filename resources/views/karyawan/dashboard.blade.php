<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="madang-hero madang-stripes madang-stripes-animated rounded-2xl p-6 sm:p-8 mb-8 text-cream shadow-lg hero-animate">
                <p class="font-term text-xs tracking-[0.25em] text-cream/70">MADANG-O // KARYAWAN</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold mt-1">Meja Operasional</h2>
                <p class="text-cream/80 text-sm mt-1">Terima pesanan, verifikasi bayar, tandai pengambilan.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">TOTAL_PESANAN</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">{{ $stats['total_orders'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">BELUM_LUNAS</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">{{ $stats['orders_belum_lunas'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg p-6 border-t-4 border-brand-700">
                    <div class="flex items-center">
                        <div class="bg-brand-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="font-term text-xs tracking-widest text-gray-500">BELUM_DIAMBIL</p>
                            <p class="text-2xl font-bold text-gray-800 font-term">{{ $stats['orders_belum_diambil'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <a href="{{ route('karyawan.orders.index') }}" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="text-center font-medium">Lihat Semua Pesanan</p>
                        </a>
                        <a href="#" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-center font-medium">Pesanan Manual</p>
                        </a>
                        <a href="#" class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <p class="text-center font-medium">Cetak Pesanan</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Pesanan Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="madang-table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Pesanan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembayaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ambil</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(\App\Models\Order::with('user')->latest()->take(10)->get() as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->customer_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($order->payment_status === 'lunas')
                                            bg-green-100 text-green-800
                                        @else
                                            bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($order->pickup_status === 'sudah_diambil')
                                            bg-green-100 text-green-800
                                        @else
                                            bg-blue-100 text-blue-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->pickup_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('karyawan.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>