<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Detail Pesanan</h2>
            <a href="{{ route('karyawan.orders.index') }}" class="text-gray-500 hover:text-gray-700">Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500">No. Pesanan</p>
                        <p class="font-medium">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pelanggan</p>
                        <p class="font-medium">{{ $order->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Telepon</p>
                        <p class="font-medium">{{ $order->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Waktu Ambil</p>
                        <p class="font-medium">{{ $order->pickup_datetime->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Sumber</p>
                        <p class="font-medium capitalize">{{ $order->source }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="font-medium text-green-600">Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <form action="{{ route('karyawan.orders.update-status', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                                <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="belum_lunas" {{ $order->payment_status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                    <option value="lunas" {{ $order->payment_status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Ambil</label>
                                <select name="pickup_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="belum_diambil" {{ $order->pickup_status === 'belum_diambil' ? 'selected' : '' }}>Belum Diambil</option>
                                    <option value="sudah_diambil" {{ $order->pickup_status === 'sudah_diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Alur Pesanan</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    @foreach(['menunggu_pembayaran' => 'Menunggu Pembayaran', 'diproses' => 'Diproses', 'siap' => 'Siap', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $value => $label)
                                        <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Simpan Status</button>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Item Pesanan</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4">{{ $item->menu->name }}</td>
                            <td class="px-6 py-4">{{ $item->quantity }}</td>
                            <td class="px-6 py-4">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-medium">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>