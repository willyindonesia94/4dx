<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            {{ __('Edit Realisasi Harian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    
                    <div class="mb-8 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Target Lead Measure</div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $target->name }}</h3>
                        <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                {{ $target->location->name }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                {{ $target->target_value }} {{ $target->metric->unit }}
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('realizations.update', $realization->id) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="report_date" :value="__('Tanggal Laporan')" />
                            <x-text-input id="report_date" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" type="date" name="report_date" :value="old('report_date', \Carbon\Carbon::parse($realization->report_date)->format('Y-m-d'))" required autofocus />
                            <x-input-error :messages="$errors->get('report_date')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Pilih tanggal realisasi pekerjaan dilakukan.</p>
                        </div>

                        <div>
                            <x-input-label for="realization_value" value="Nilai Capaian Harian ({{ $target->metric->unit }})" />
                            <div class="relative mt-1">
                                <x-text-input id="realization_value" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm pl-4 pr-16" type="number" step="0.01" min="0" name="realization_value" :value="old('realization_value', $realization->realization_value)" required />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-500 text-sm font-medium">
                                    {{ $target->metric->unit }}
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('realization_value')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="evidence" :value="__('Lampiran Bukti Baru (Opsional)')" />
                            <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" class="block w-full mt-1 text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah bukti yang sudah ada. Format: JPG, PNG, atau PDF. Maksimal 5MB.</p>
                            @if($realization->evidence_path)
                                <div class="mt-2 text-sm text-green-600 font-medium">
                                    ✓ Lampiran saat ini sudah tersedia (akan dipertahankan jika Anda tidak mengunggah file baru).
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('evidence')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" :value="__('Keterangan Tambahan (Opsional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" placeholder="Catatan pekerjaan hari ini...">{{ old('notes', $realization->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8 gap-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('realizations.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
