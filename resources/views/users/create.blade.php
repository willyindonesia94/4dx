<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nama Lengkap')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="username" :value="__('Username')" />
                                <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required />
                                <x-input-error :messages="$errors->get('username')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Password')" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="role_name" :value="__('Role Aplikasi')" />
                                <select id="role_name" name="role_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled {{ old('role_name') ? '' : 'selected' }}>-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role_name') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('role_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="unit_id" :value="__('Unit Kerja')" />
                                <select id="unit_id" name="unit_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled {{ old('unit_id') ? '' : 'selected' }}>-- Pilih Unit --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->type }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="matrix_group_id" :value="__('Matrix Group (Relasi Bidang)')" />
                                <p class="text-xs text-gray-500 mb-1">Pilih bidang fungsional untuk mengaitkan target (Misal: Divisi Jaringan UID & TL Teknik ULP pilih JARINGAN).</p>
                                <select id="matrix_group_id" name="matrix_group_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-medium text-gray-800" required>
                                    <option value="" disabled {{ old('matrix_group_id') ? '' : 'selected' }}>-- Pilih Bidang / Matrix Group --</option>
                                    <option value="ALL" {{ old('matrix_group_id') == 'ALL' ? 'selected' : '' }}>ALL (Semua Bidang / Tidak Dibatasi)</option>
                                    <option value="NIAGA" {{ old('matrix_group_id') == 'NIAGA' ? 'selected' : '' }}>NIAGA</option>
                                    <option value="JARINGAN" {{ old('matrix_group_id') == 'JARINGAN' ? 'selected' : '' }}>JARINGAN</option>
                                    <option value="TE" {{ old('matrix_group_id') == 'TE' ? 'selected' : '' }}>TE (Transaksi Energi)</option>
                                    <option value="K3L" {{ old('matrix_group_id') == 'K3L' ? 'selected' : '' }}>K3L</option>
                                </select>
                                <x-input-error :messages="$errors->get('matrix_group_id')" class="mt-2" />
                            </div>
                        </div>

                        <input type="hidden" name="return_level" value="{{ request('level') }}">

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index', request('level') ? ['level' => request('level')] : []) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Pengguna') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
