<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Detail Pesanan</h2>
            <a href="{{ route('karyawan.orders.index') }}" class="text-gray-500 hover:text-gray-700">Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $errors->first() }}</div>
            @endif

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
                        <p class="font-medium text-brand-700 font-term">Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <form action="{{ route('karyawan.orders.update-status', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                                @php
                                    $allowedPayment = array_unique(array_merge([$order->payment_status], $order->allowedNextPaymentStatuses()));
                                    $paymentLabels = ['belum_lunas' => 'Belum Lunas', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'lunas' => 'Lunas'];
                                @endphp
                                <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    @foreach($allowedPayment as $value)
                                        <option value="{{ $value }}" {{ $order->payment_status === $value ? 'selected' : '' }}>{{ $paymentLabels[$value] ?? $value }}</option>
                                    @endforeach
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
                                @php
                                    $statusLabels = ['menunggu_pembayaran' => 'Menunggu Pembayaran', 'diproses' => 'Diproses', 'siap' => 'Siap', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];
                                    $allowedStatus = array_unique(array_merge([$order->status], $order->allowedNextStatuses()));
                                @endphp
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    @foreach($allowedStatus as $value)
                                        <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $statusLabels[$value] ?? $value }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Status hanya bisa maju satu tahap. Hubungi owner untuk koreksi.</p>
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-brand-700 text-white rounded-lg hover:bg-brand-800">Simpan Status</button>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Item Pesanan</h3>
                <table class="madang-table min-w-full divide-y divide-gray-200">
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
                            <td class="px-6 py-4">
                                {{ $item->display_name }}
                                @if($item->options)
                                    <span class="block text-xs text-gray-500">{{ $item->options['label'] ?? '' }}</span>
                                @endif
                            </td>
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