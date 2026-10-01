<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Menu Management</h2>
            <a href="{{ route('owner.menus.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Tambah Menu
            </a>
        </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($menus as $menu)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($menu->image)
                                        <img src="{{ asset($menu->image) }}" alt="{{ $menu->name }}" class="w-12 h-12 rounded object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs">foto</div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $menu->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $menu->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $menu->category->name }}</td>
                            <td class="px-6 py-4 text-gray-900">Rp{{ number_format($menu->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($menu->status_ketersediaan)
                                        bg-green-100 text-green-800
                                    @else
                                        bg-red-100 text-red-800
                                    @endif">
                                    {{ $menu->status_ketersediaan ? 'Tersedia' : 'Tidak Tersedia' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('owner.menus.edit', $menu) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <form action="{{ route('owner.menus.destroy', $menu) }}" method="POST" class="inline" onsubmit="return confirm('Hapus menu ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada menu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">
                    {{ $menus->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>