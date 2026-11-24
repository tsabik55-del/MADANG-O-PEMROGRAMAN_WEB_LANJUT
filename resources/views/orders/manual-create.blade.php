<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Catat Pesanan Manual</h2>
            <a href="{{ $backRoute }}" class="text-gray-500 hover:text-gray-700">Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                Gunakan formulir ini untuk pesanan yang masuk lewat telepon atau WhatsApp.
                Harga dan total dihitung otomatis oleh sistem dari data menu.
            </div>

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $action }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemesan</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon/WA</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Ambil</label>
                        <input type="datetime-local" name="pickup_datetime" required
                               value="{{ old('pickup_datetime', now()->addHour()->format('Y-m-d\TH:i')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Bayar</label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                @foreach(['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method', 'tunai') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Bayar</label>
                            <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="belum_lunas" {{ old('payment_status', 'lunas') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="lunas" {{ old('payment_status', 'lunas') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 mb-3">Pilih Menu</h3>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="madang-table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Varian</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($menus as $menu)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $menu->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $menu->category?->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">Rp{{ number_format($menu->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        @php $choices = $menu->options['choices'] ?? []; @endphp
                                        @if(! empty($choices))
                                            <select name="items[{{ $menu->id }}][option]" class="text-sm px-2 py-1 border border-gray-300 rounded">
                                                @foreach($choices as $choice)
                                                    <option value="{{ $choice['key'] }}" {{ old('items.'.$menu->id.'.option') === $choice['key'] ? 'selected' : '' }}>
                                                        {{ $choice['label'] }}{{ ($choice['delta'] ?? 0) > 0 ? ' (+Rp'.number_format($choice['delta'], 0, ',', '.').')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="hidden" name="items[{{ $menu->id }}][option]" value="">
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="items[{{ $menu->id }}][quantity]" min="0" max="99"
                                               value="{{ old('items.'.$menu->id.'.quantity', 0) }}"
                                               class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-2 text-xs text-gray-500">Isi jumlah lebih dari 0 untuk menu yang dipesan. Total dihitung otomatis saat disimpan.</p>

                <div class="mt-6 flex gap-3">
                    <a href="{{ $backRoute }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                    <button type="submit" class="px-4 py-2 bg-brand-700 text-white rounded-lg hover:bg-brand-800">Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
