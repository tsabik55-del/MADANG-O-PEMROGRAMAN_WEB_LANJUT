<x-pelanggan-layout title="Riwayat Pesanan">
    <div class="mx-auto w-full max-w-7xl">

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-black text-gray-800">Riwayat Pesanan</h1>
                <p class="text-[11px] text-gray-500">Semua pesanan yang pernah Anda buat.</p>
            </div>
            <a href="{{ route('pelanggan.dashboard') }}"
               class="flex items-center gap-1.5 rounded-xl bg-brand-800 px-3 py-2 text-xs font-bold text-white shadow transition hover:bg-brand-700">
                <span>🍴</span>
                <span>Pesan Lagi</span>
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="madang-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">No. Pesanan</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Waktu Ambil</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Total</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Bayar</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Ambil</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($orders as $order)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-xs font-bold text-gray-900">{{ $order->order_number }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-600">{{ $order->pickup_datetime->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs font-bold text-brand-800">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                                        @if($order->payment_status === 'lunas') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                                        @if($order->pickup_status === 'sudah_diambil') bg-emerald-100 text-emerald-800 @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->pickup_status)) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                                        @if($order->status === 'selesai') bg-emerald-100 text-emerald-800
                                        @elseif($order->status === 'dibatalkan') bg-red-100 text-red-800
                                        @else bg-indigo-100 text-indigo-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs">
                                    <a href="{{ route('pelanggan.orders.show', $order) }}" class="mr-3 font-bold text-brand-800 hover:underline">Detail</a>
                                    @can('delete', $order)
                                        <form action="{{ route('pelanggan.orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="font-bold text-red-600 hover:underline">Batal</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <p class="text-sm font-bold text-gray-500">Belum ada pesanan.</p>
                                    <p class="mt-1 text-[11px] text-gray-400">Yuk pesan menu favoritmu.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="border-t px-4 py-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-pelanggan-layout>
