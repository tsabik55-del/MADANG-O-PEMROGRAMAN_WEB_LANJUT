<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Edit Menu: {{ $menu->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $errors->first() }}</div>
                @endif
                <form action="{{ route('owner.menus.update', $menu) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <input type="hidden" name="image" value="{{ $menu->image }}">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $menu->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu</label>
                        <input type="text" name="name" value="{{ $menu->name }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ $menu->description }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Menu (opsional)</label>
                        @if($menu->image_url)
                            <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="mb-2 h-24 w-24 rounded object-cover border">
                        @endif
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="mt-1 text-xs text-gray-500">Format JPG, PNG, atau WEBP. Maksimal 2 MB. Biarkan kosong bila tidak mengganti foto.</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                        <input type="number" name="price" value="{{ $menu->price }}" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="status_ketersediaan" value="1" {{ $menu->status_ketersediaan ? 'checked' : '' }} id="status_ketersediaan" class="h-4 w-4 text-brand-700 focus:ring-brand-600 border-gray-300 rounded">
                        <label for="status_ketersediaan" class="ml-2 text-sm text-gray-700">Tersedia</label>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('owner.menus.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-brand-700 text-white rounded-lg hover:bg-brand-800">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>