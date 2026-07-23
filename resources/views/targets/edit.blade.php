<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Target: ') }} {{ $target->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('targets.update', $target->id) }}" x-data="{
                        targetType: '{{ $target->type }}',
                        periodOptions: [],
                        periodValue: '{{ old('period', $target->period) }}',
                        
                        updatePeriodOptions() {
                            if (this.targetType === 'WIG Utama') {
                                this.periodOptions = {{ $activeYearsJson ?? '["2026"]' }};
                            } else if (this.targetType === 'Sub-WIG') {
                                this.periodOptions = ['2026', 'Semester 1', 'Semester 2', 'Triwulan 1', 'Triwulan 2', 'Triwulan 3', 'Triwulan 4'];
                            } else {
                                this.periodOptions = ['Bulanan', 'Mingguan', 'Harian', 'Insidental'];
                            }
                            
                            // Keep custom values if they don't exist in options
                            if (!this.periodOptions.includes(this.periodValue)) {
                                this.periodOptions.push(this.periodValue);
                            }
                        },
                        
                        init() {
                            this.updatePeriodOptions();
                        }
                    }">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Name -->
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="name" :value="__('Nama Target / WIG')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $target->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Target Value -->
                            <div>
                                <x-input-label for="target_value" :value="__('Angka Target (Besaran)')" />
                                <x-text-input id="target_value" class="block mt-1 w-full" type="number" step="0.01" name="target_value" :value="old('target_value', $target->target_value)" required />
                                <x-input-error :messages="$errors->get('target_value')" class="mt-2" />
                            </div>

                            <!-- Period -->
                            <div>
                                <x-input-label for="period" :value="__('Periode Target')" />
                                <select id="period" name="period" x-model="periodValue" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <template x-for="option in periodOptions" :key="option">
                                        <option :value="option" x-text="option"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('period')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status Pencapaian')" class="font-bold text-gray-700" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-pln-cyan focus:ring-pln-cyan rounded-md shadow-sm bg-gray-50 focus:bg-white transition-colors">
                                    <option value="Belum Mulai" {{ old('status', $target->status) == 'Belum Mulai' ? 'selected' : '' }}>Belum Mulai</option>
                                    <option value="On Track" {{ old('status', $target->status) == 'On Track' ? 'selected' : '' }}>On Track (Sesuai Rencana)</option>
                                    <option value="Delay" {{ old('status', $target->status) == 'Delay' ? 'selected' : '' }}>Delay (Terlambat)</option>
                                    <option value="Selesai" {{ old('status', $target->status) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-4 border-gray-200">
                            <a href="{{ route('targets.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 mr-3">
                                Batal
                            </a>
                            <x-primary-button class="bg-pln-primary hover:bg-blue-800">
                                {{ __('Perbarui Target') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
