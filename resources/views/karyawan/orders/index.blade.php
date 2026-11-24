<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800">Semua Pesanan</h2>
            <a href="{{ route('karyawan.orders.create') }}" class="bg-brand-700 text-white px-4 py-2 rounded-lg hover:bg-brand-800 transition">
                + Pesanan Manual
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="GET" action="{{ route('karyawan.orders.index') }}" class="mb-4 bg-white shadow rounded-lg p-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="No. pesanan, nama, telepon"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status Alur</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Semua</option>
                        @foreach(['menunggu_pembayaran' => 'Menunggu Pembayaran', 'diproses' => 'Diproses', 'siap' => 'Siap', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pembayaran</label>
                    <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Semua</option>
                        @foreach(['belum_lunas' => 'Belum Lunas', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'lunas' => 'Lunas'] as $value => $label)
                            <option value="{{ $value }}" {{ request('payment_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Ambil</label>
                    <div class="flex gap-2">
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button type="submit" class="px-3 py-2 bg-brand-700 text-white rounded-lg text-sm hover:bg-brand-800">Filter</button>
                    </div>
                </div>
            </form>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="madang-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ambil</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->customer_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($order->status === 'selesai') bg-green-100 text-green-800
                                    @elseif($order->status === 'dibatalkan') bg-red-100 text-red-800
                                    @else bg-indigo-100 text-indigo-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('karyawan.orders.update-status', $order) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="pickup_status" value="{{ $order->pickup_status }}">
                                    <input type="hidden" name="status" value="{{ $order->status }}">
                                    @php
                                        $allowedPayment = array_unique(array_merge([$order->payment_status], $order->allowedNextPaymentStatuses()));
                                        $paymentLabels = ['belum_lunas' => 'Belum Lunas', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'lunas' => 'Lunas'];
                                    @endphp
                                    <select name="payment_status" onchange="this.form.submit()" class="text-xs px-2 py-1 border border-gray-300 rounded
                                        @if($order->payment_status === 'lunas') bg-green-100 text-green-800
                                        @elseif($order->payment_status === 'menunggu_verifikasi') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        @foreach($allowedPayment as $value)
                                            <option value="{{ $value }}" {{ $order->payment_status === $value ? 'selected' : '' }}>{{ $paymentLabels[$value] ?? $value }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('karyawan.orders.update-status', $order) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="payment_status" value="{{ $order->payment_status }}">
                                    <input type="hidden" name="status" value="{{ $order->status }}">
                                    <select name="pickup_status" onchange="this.form.submit()" class="text-xs px-2 py-1 border border-gray-300 rounded
                                        @if($order->pickup_status === 'sudah_diambil') bg-green-100 text-green-800 @else bg-blue-100 text-blue-800 @endif">
                                        <option value="belum_diambil" {{ $order->pickup_status === 'belum_diambil' ? 'selected' : '' }}>Belum Diambil</option>
                                        <option value="sudah_diambil" {{ $order->pickup_status === 'sudah_diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('karyawan.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">Belum ada pesanan yang cocok.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
