<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nama Lengkap')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="$user->name" required autofocus />
                            </div>
                            
                            <div>
                                <x-input-label for="username" :value="__('Username')" />
                                <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="$user->username" required />
                            </div>



                            <div>
                                <x-input-label for="password" :value="__('Password (Kosongkan jika tidak diubah)')" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                            </div>
                            
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            </div>

                            <div>
                                <x-input-label for="role_name" :value="__('Role Aplikasi')" />
                                <select id="role_name" name="role_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ $user->role_name === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="unit_id" :value="__('Unit Kerja')" />
                                <select id="unit_id" name="unit_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ $user->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="matrix_group_id" :value="__('Matrix Group (Relasi Bidang)')" />
                                <select id="matrix_group_id" name="matrix_group_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-medium text-gray-800" required>
                                    <option value="" disabled>-- Pilih Bidang / Matrix Group --</option>
                                    <option value="ALL" {{ $user->matrix_group_id === 'ALL' ? 'selected' : '' }}>ALL (Semua Bidang / Tidak Dibatasi)</option>
                                    @foreach($bidangs as $bidang)
                                        <option value="{{ $bidang->name }}" {{ $user->matrix_group_id === $bidang->name ? 'selected' : '' }}>
                                            {{ $bidang->name }} ({{ $bidang->level == 'UID_BIDANG' ? 'UID' : ($bidang->level == 'UID_SUBBIDANG' ? 'Sub UID' : ($bidang->level == 'UP3_BIDANG' ? 'UP3' : 'ULP')) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Perbarui Pengguna') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
