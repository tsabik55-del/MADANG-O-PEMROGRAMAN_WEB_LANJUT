<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Detail Pesanan</h2>
            <a href="{{ route('pelanggan.orders.index') }}" class="text-gray-500 hover:text-gray-700">Kembali</a>
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
                        <p class="text-sm text-gray-500">Metode Bayar</p>
                        <p class="font-medium capitalize">{{ $order->payment_method }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="font-medium text-green-600">Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Status Pembayaran</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->payment_status === 'lunas')
                                    bg-green-100 text-green-800
                                @else
                                    bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Ambil</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->pickup_status === 'sudah_diambil')
                                    bg-green-100 text-green-800
                                @else
                                    bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->pickup_status)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Pesanan</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->status === 'selesai')
                                    bg-green-100 text-green-800
                                @elseif($order->status === 'dibatalkan')
                                    bg-red-100 text-red-800
                                @else
                                    bg-indigo-100 text-indigo-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </div>
                    </div>
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
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right font-semibold">Total</td>
                            <td class="px-6 py-3 font-bold text-green-600">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                @if($order->notes)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Catatan: {{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>