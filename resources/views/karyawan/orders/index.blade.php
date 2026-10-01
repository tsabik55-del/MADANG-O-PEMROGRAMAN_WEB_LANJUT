<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Semua Pesanan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Pesanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
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
                                <form action="{{ route('karyawan.orders.update-status', $order) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="pickup_status" value="{{ $order->pickup_status }}">
                                    <input type="hidden" name="status" value="{{ $order->status }}">
                                    <select name="payment_status" onchange="this.form.submit()" class="text-xs px-2 py-1 border border-gray-300 rounded
                                        @if($order->payment_status === 'lunas')
                                            bg-green-100 text-green-800
                                        @else
                                            bg-yellow-100 text-yellow-800
                                        @endif">
                                        <option value="belum_lunas" {{ $order->payment_status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                        <option value="lunas" {{ $order->payment_status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('karyawan.orders.update-status', $order) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="payment_status" value="{{ $order->payment_status }}">
                                    <input type="hidden" name="status" value="{{ $order->status }}">
                                    <select name="pickup_status" onchange="this.form.submit()" class="text-xs px-2 py-1 border border-gray-300 rounded
                                        @if($order->pickup_status === 'sudah_diambil')
                                            bg-green-100 text-green-800
                                        @else
                                            bg-blue-100 text-blue-800
                                        @endif">
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
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada pesanan</td>
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