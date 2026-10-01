<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Manajemen Pengguna</h2>
            <a href="{{ route('owner.users.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex gap-2">
                        <a href="{{ route('owner.users.index') }}" class="px-3 py-1.5 rounded-full text-sm {{ !request('role') ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700' }}">Semua</a>
                        <a href="{{ route('owner.users.index', ['role' => 'pelanggan']) }}" class="px-3 py-1.5 rounded-full text-sm {{ request('role') === 'pelanggan' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700' }}">Pelanggan ({{ $counts['pelanggan'] }})</a>
                        <a href="{{ route('owner.users.index', ['role' => 'karyawan']) }}" class="px-3 py-1.5 rounded-full text-sm {{ request('role') === 'karyawan' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">Karyawan ({{ $counts['karyawan'] }})</a>
                        <a href="{{ route('owner.users.index', ['role' => 'owner']) }}" class="px-3 py-1.5 rounded-full text-sm {{ request('role') === 'owner' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700' }}">Owner ({{ $counts['owner'] }})</a>
                    </div>
                    <form method="GET" action="{{ route('owner.users.index') }}" class="ml-auto flex gap-2">
                        <input type="hidden" name="role" value="{{ request('role') }}">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / username / email..." class="border-gray-300 rounded-md text-sm w-64">
                        <button type="submit" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md text-sm">Cari</button>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">@{{ $user->username }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                <div>{{ $user->email ?? '-' }}</div>
                                <div>{{ $user->wa_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($user->role === 'owner') bg-purple-100 text-purple-800
                                    @elseif($user->role === 'karyawan') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('owner.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('owner.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengguna {{ $user->name }}? Pesanannya tetap tersimpan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                                @else
                                <span class="text-gray-400">(akun Anda)</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Belum ada pengguna</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
