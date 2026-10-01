<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Tambah Pengguna</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ route('owner.users.store') }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Nama Lengkap')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="username" :value="__('Username')" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username')" required />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email (opsional)')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="wa_number" :value="__('Nomor WhatsApp')" />
                        <x-text-input id="wa_number" name="wa_number" type="text" class="mt-1 block w-full" :value="old('wa_number')" required />
                        <x-input-error :messages="$errors->get('wa_number')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="role" :value="__('Peran')" />
                        <select id="role" name="role" class="mt-1 block w-full border-gray-300 rounded-md" required>
                            <option value="pelanggan" @selected(old('role') === 'pelanggan')>Pelanggan</option>
                            <option value="karyawan" @selected(old('role') === 'karyawan')>Karyawan</option>
                            <option value="owner" @selected(old('role') === 'owner')>Owner</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('owner.users.index') }}" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg transition">Batal</a>
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
