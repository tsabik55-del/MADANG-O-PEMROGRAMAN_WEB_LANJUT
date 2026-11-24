<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Rekap &amp; Laporan Penjualan</h2>
                <p class="text-sm text-gray-500">Rekap harian dan bulanan dari pesanan yang tidak dibatalkan.</p>
            </div>
            <button type="button" onclick="window.print()" class="no-print bg-brand-700 text-white px-4 py-2 rounded-lg hover:bg-brand-800 transition">
                Cetak Laporan
            </button>
        </div>
    </x-slot>

    <style>
        @media print {
            nav, .no-print { display: none !important; }
            header { border: 0 !important; }
            body { background: #fff !important; }
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('owner.reports.index') }}" class="no-print mb-6 bg-white shadow rounded-lg p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-brand-700 text-white rounded-lg text-sm hover:bg-brand-800 w-full">Tampilkan</button>
                </div>
                <div class="flex items-end text-xs text-gray-500">
                    Periode: {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white shadow rounded-lg p-5 border-t-4 border-brand-700">
                    <p class="font-term text-xs tracking-widest text-gray-500">TOTAL_PESANAN</p>
                    <p class="text-2xl font-bold text-gray-800 font-term">{{ $summary['total_orders'] }}</p>
                    <p class="text-xs text-gray-400">{{ $summary['total_cancelled'] }} dibatalkan</p>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-t-4 border-brand-700">
                    <p class="font-term text-xs tracking-widest text-gray-500">TOTAL_PENDAPATAN</p>
                    <p class="text-xl font-bold text-gray-800 font-term">Rp{{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">Hanya pembayaran lunas</p>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-t-4 border-brand-700">
                    <p class="font-term text-xs tracking-widest text-gray-500">MENU_TERJUAL</p>
                    <p class="text-2xl font-bold text-gray-800 font-term">{{ $summary['total_items'] }}</p>
                    <p class="text-xs text-gray-400">Total porsi</p>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-t-4 border-brand-700">
                    <p class="font-term text-xs tracking-widest text-gray-500">RATA_RATA_ORDER</p>
                    <p class="text-xl font-bold text-gray-800 font-term">Rp{{ number_format($summary['average_order'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">Pendapatan / pesanan</p>
                </div>
                <div class="bg-white shadow rounded-lg p-5 border-t-4 border-brand-700">
                    <p class="font-term text-xs tracking-widest text-gray-500">PERIODE</p>
                    <p class="text-lg font-bold text-gray-800 font-term">{{ $from->format('d/m') }}–{{ $to->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-400">Rekap bulanan: {{ $to->year }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Rekap Harian</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="madang-table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pesanan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($daily as $row)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ $row['tanggal']->format('d/m/Y') }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-right">{{ $row['orders'] }}</td>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900 text-right">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada data pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Menu Terlaris</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="madang-table min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Porsi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topMenus as $menu)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ $menu['name'] }}</td>
                                        <td class="px-6 py-3 text-sm text-gray-700 text-right">{{ $menu['quantity'] }}</td>
                                        <td class="px-6 py-3 text-sm font-medium text-gray-900 text-right">Rp{{ number_format($menu['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada menu terjual.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Rekap Bulanan {{ $to->year }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="madang-table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pesanan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($monthly as $row)
                                <tr class="{{ $row['orders'] === 0 ? 'text-gray-400' : '' }}">
                                    <td class="px-6 py-3 text-sm">{{ $row['bulan'] }}</td>
                                    <td class="px-6 py-3 text-sm text-right">{{ $row['orders'] }}</td>
                                    <td class="px-6 py-3 text-sm font-medium text-right">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-400">
                Catatan: pesanan berstatus dibatalkan tidak dihitung. Pendapatan hanya dari pembayaran berstatus lunas (paid).
            </p>
        </div>
    </div>
</x-app-layout>
