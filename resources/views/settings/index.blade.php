<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="mb-8 border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Tahun Aktif</h3>
                        <p class="text-sm text-gray-500">Tentukan tahun berapa saja yang akan muncul di dropdown pilihan WIG Utama.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        
                        <div>
                            <x-input-label for="active_years" :value="__('Tahun Aktif (Pisahkan dengan koma)')" class="font-bold text-gray-700" />
                            <x-text-input id="active_years" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="text" name="active_years" :value="old('active_years', implode(', ', $activeYears))" required placeholder="Contoh: 2026, 2027, 2028" />
                            <p class="text-xs text-gray-500 mt-1">Masukkan tahun secara berurutan. Hindari penulisan spasi berlebih.</p>
                            <x-input-error :messages="$errors->get('active_years')" class="mt-2" />
                        </div>

                        <div class="flex items-center mt-6">
                            <x-primary-button class="bg-pln-primary hover:bg-blue-800">
                                {{ __('Simpan Pengaturan') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
