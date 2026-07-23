<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ __('Pilih Target Lead Measure') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Isi Realisasi Harian</h3>
                        <p class="text-sm text-gray-600">Silakan pilih Lead Measure mana yang ingin Anda isi realisasi pencapaian hariannya.</p>
                    </div>

                    <form method="GET" action="{{ route('realizations.create') }}" class="space-y-6">
                        <div>
                            <x-input-label for="target_id" :value="__('Target Lead Measure')" />
                            <select id="target_id" name="target_id" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" required autofocus>
                                <option value="" disabled selected>-- Pilih Target --</option>
                                @foreach($availableTargets as $target)
                                    <option value="{{ $target->id }}">
                                        {{ $target->name }} (Target: {{ $target->target_value }} {{ $target->metric->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-8 gap-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('realizations.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                                Lanjut
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
