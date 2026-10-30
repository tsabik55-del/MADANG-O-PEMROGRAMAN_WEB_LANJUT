<x-pelanggan-layout title="Detail Pesanan">
    <div class="mx-auto w-full max-w-3xl">

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-black text-gray-800">Detail Pesanan</h1>
                <p class="text-[11px] text-gray-500">Nomor <span class="font-bold text-brand-800">{{ $order->order_number }}</span></p>
            </div>
            <a href="{{ route('pelanggan.orders.index') }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                ← Kembali
            </a>
        </div>

        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pelanggan</p>
                    <p class="text-xs font-bold text-gray-800">{{ $order->customer_name }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Telepon</p>
                    <p class="text-xs font-bold text-gray-800">{{ $order->phone }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Waktu Ambil</p>
                    <p class="text-xs font-bold text-gray-800">{{ $order->pickup_datetime->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Metode Bayar</p>
                    <p class="text-xs font-bold capitalize text-gray-800">{{ $order->payment_method }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total</p>
                    <p class="text-sm font-black text-brand-800">Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Pesanan</p>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                        @if($order->status === 'selesai') bg-emerald-100 text-emerald-800
                        @elseif($order->status === 'dibatalkan') bg-red-100 text-red-800
                        @else bg-indigo-100 text-indigo-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 border-t pt-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Pembayaran</p>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                        @if($order->payment_status === 'lunas') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Ambil</p>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                        @if($order->pickup_status === 'sudah_diambil') bg-emerald-100 text-emerald-800 @else bg-blue-100 text-blue-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->pickup_status)) }}
                    </span>
                </div>
            </div>

            @if($order->payment && $order->payment->status === 'menunggu_verifikasi')
                <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-900">
                    Konfirmasi pembayaran Anda sudah diterima dan sedang menunggu verifikasi kasir.
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-black text-gray-800">Item Pesanan</h3>
            <div class="overflow-x-auto">
                <table class="madang-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Menu</th>
                            <th class="px-4 py-2 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Qty</th>
                            <th class="px-4 py-2 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Harga</th>
                            <th class="px-4 py-2 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-xs">
                                    <span class="font-bold text-gray-800">{{ $item->menu->name ?? 'Menu dihapus' }}</span>
                                    @if($item->options)
                                        <span class="block text-[10px] text-gray-500">{{ $item->options['label'] ?? '' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-xs">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-xs font-bold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-xs font-bold text-gray-700">Total</td>
                            <td class="px-4 py-3 text-xs font-black text-brand-800">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($order->notes)
                <div class="mt-4 rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] text-gray-600"><span class="font-bold">Catatan:</span> {{ $order->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Pesanan baru dibuat lewat form biasa: keranjang browser harus
         dikosongkan. Alur AJAX di dashboard sudah mengosongkan sendiri. --}}
    @if(session('cart_cleared'))
        @push('scripts')
            <script>
                try {
                    localStorage.removeItem('madango_cart_{{ Auth::id() }}');
                } catch (e) {
                    // localStorage tidak tersedia (mode privat) — abaikan saja.
                }
            </script>
        @endpush
    @endif
</x-pelanggan-layout>
