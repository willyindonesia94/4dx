<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if($parentTarget)
                {{ __('Breakdown Target: ') }} {{ $parentTarget->name }}
            @else
                {{ __('Buat WIG Utama (Top-Level)') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('targets.store') }}">
                        @csrf
                        
                        @if($parentTarget)
                            <input type="hidden" name="parent_id" value="{{ $parentTarget->id }}">
                            
                            <!-- Display Parent Info -->
                            <div class="mb-6 p-4 bg-gray-50 border-l-4 border-pln-cyan rounded-r-lg">
                                <h4 class="font-bold text-gray-700 text-sm uppercase">Referensi Target Atasan:</h4>
                                <p class="text-gray-900 font-medium">{{ $parentTarget->name }}</p>
                                <p class="text-sm text-gray-600">
                                    Metrik: {{ $parentTarget->metric->name }} | 
                                    Target: {{ $parentTarget->target_value }} {{ $parentTarget->metric->unit }}
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Name -->
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="name" :value="__('Nama Target / WIG')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="Contoh: Menurunkan SAIDI UP3 Bandung 15%" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Target Type -->
                            <div>
                                <x-input-label for="type" :value="__('Jenis Target (Tingkatan)')" />
                                <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @if(!$parentTarget)
                                        <option value="WIG Utama" selected>WIG Utama (Top-Level)</option>
                                    @elseif($parentTarget->type == 'WIG Utama')
                                        <option value="Sub-WIG" selected>Sub-WIG (Breakdown UP3)</option>
                                    @elseif($parentTarget->type == 'Sub-WIG')
                                        <option value="Lead Measure" selected>Lead Measure (Tindakan ULP)</option>
                                    @endif
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- Metric -->
                            <div>
                                <x-input-label for="metric_id" :value="__('Indikator (Metrik)')" />
                                <select id="metric_id" name="metric_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Pilih Metrik --</option>
                                    @foreach($metrics as $metric)
                                        <option value="{{ $metric->id }}" {{ (old('metric_id') ?? ($parentTarget->metric_id ?? '')) == $metric->id ? 'selected' : '' }}>
                                            [{{ strtoupper($metric->type) }} MEASURE] {{ $metric->name }} ({{ $metric->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('metric_id')" class="mt-2" />
                            </div>

                            <!-- Location -->
                            <div>
                                <x-input-label for="location_id" :value="__('Unit Pemilik Target')" />
                                @if(count($locations) == 1 && !auth()->user()->hasRole('superadmin'))
                                    <!-- For Unit Roles (UP3/ULP), lock the location -->
                                    <input type="hidden" name="location_id" value="{{ $locations->first()->id }}">
                                    <select disabled class="block mt-1 w-full border-gray-300 bg-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="{{ $locations->first()->id }}" selected>{{ $locations->first()->name }}</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Dikunci secara otomatis ke unit Anda.</p>
                                @else
                                    <select id="location_id" name="location_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="" disabled selected>-- Pilih Unit (UID/UP3/ULP) --</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                                {{ $loc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
                                @endif
                            </div>

                            <!-- Period -->
                            <div>
                                <x-input-label for="period" :value="__('Periode Target')" />
                                <x-text-input id="period" class="block mt-1 w-full" type="text" name="period" :value="old('period') ?? ($parentTarget->period ?? '2026')" required placeholder="Misal: 2026, Triwulan 1, atau Minggu 1" />
                                <x-input-error :messages="$errors->get('period')" class="mt-2" />
                            </div>

                            <!-- Target Value -->
                            <div class="col-span-1">
                                <x-input-label for="target_value" :value="__('Angka Target (Besaran)')" />
                                <x-text-input id="target_value" class="block mt-1 w-full" type="number" step="0.01" name="target_value" :value="old('target_value')" required />
                                <p class="text-xs text-gray-500 mt-1">Gunakan titik (.) untuk desimal. Contoh: 15.5</p>
                                <x-input-error :messages="$errors->get('target_value')" class="mt-2" />
                            </div>

                            <!-- Polarity / Target Scale -->
                            <div class="col-span-1">
                                <x-input-label for="polarity" :value="__('Skala Target (Sifat Metrik)')" />
                                <select id="polarity" name="polarity" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="Maximize" {{ old('polarity') == 'Maximize' ? 'selected' : '' }}>
                                        Memaksimalkan Pencapaian (Semakin tinggi semakin baik)
                                    </option>
                                    <option value="Minimize" {{ old('polarity') == 'Minimize' ? 'selected' : '' }}>
                                        Meminimalkan Pencapaian (Semakin rendah semakin baik)
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Menentukan rumus persentase di Dashboard.</p>
                                <x-input-error :messages="$errors->get('polarity')" class="mt-2" />
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-8 border-t pt-4 border-gray-200">
                            <a href="{{ route('targets.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 mr-3">
                                Batal
                            </a>
                            <x-primary-button class="bg-pln-primary hover:bg-blue-800">
                                {{ __('Simpan Target') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
