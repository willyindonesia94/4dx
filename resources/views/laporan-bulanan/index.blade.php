<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Laporan Bulanan & Data Historis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success') || session('error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 class="fixed inset-0 z-[150] overflow-y-auto" 
                 aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                 
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Backdrop -->
                    <div x-show="show"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                         @click="show = false"></div>

                    <!-- This element is to trick the browser into centering the modal contents. -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal Panel -->
                    <div x-show="show"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                         
                        @if(session('success'))
                        <div class="bg-white px-6 py-8 border-t-8 border-green-500">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="text-center">
                                <h3 class="text-xl font-bold leading-6 text-slate-900 mb-2" id="modal-title">Berhasil!</h3>
                                <p class="text-sm font-medium text-slate-500">{{ session('success') }}</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex justify-center border-t border-slate-100">
                            <button type="button" @click="show = false" class="inline-flex w-full sm:w-1/2 justify-center rounded-lg bg-green-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-green-700 transition-colors focus:outline-none">Tutup</button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="bg-white px-6 py-8 border-t-8 border-red-500">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-4">
                                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="text-center">
                                <h3 class="text-xl font-bold leading-6 text-slate-900 mb-2" id="modal-title">Gagal!</h3>
                                <p class="text-sm font-medium text-slate-500">{{ session('error') }}</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex justify-center border-t border-slate-100">
                            <button type="button" @click="show = false" class="inline-flex w-full sm:w-1/2 justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition-colors focus:outline-none">Tutup</button>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            @endif

            <!-- FILTER SECTION -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Pilih Periode Laporan
                    </h3>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-col md:flex-row items-end gap-4" id="filterForm">
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bulan</label>
                            <select name="bulan" id="selectBulan" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 text-sm text-slate-700">
                                <option value="all" {{ request('bulan') == 'all' ? 'selected' : '' }}>Semua Bulan (Tahunan)</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tahun</label>
                            <select name="tahun" id="selectTahun" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 text-sm text-slate-700">
                                @foreach(range(date('Y')-2, date('Y')+2) as $y)
                                    <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-md font-semibold text-sm transition-colors">
                                Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="w-full mt-6">
                <!-- EXPORT SECTION -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 text-white/80 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export Laporan WIG & LM
                        </h3>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-center space-y-6">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Laporan Periode <span id="lblPeriodeExp">{{ \Carbon\Carbon::create()->month((int) request('bulan', date('n')))->translatedFormat('F') }} {{ request('tahun', date('Y')) }}</span></h4>
                            <p class="text-sm text-slate-500 mt-2">Anda dapat mengekspor rekap capaian LM (Excel), rekap capaian WIG (Excel), atau Laporan Lengkap per WIG Dashboard (Cetak/PDF).</p>
                        </div>
                        
                        <div class="max-w-md mx-auto w-full space-y-4 bg-slate-50 p-6 rounded-lg border border-slate-100">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Jenis Laporan</label>
                                <select id="selectJenisLaporan" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="lm">Laporan Capaian LM (Excel)</option>
                                    <option value="wig">Laporan Capaian WIG (Excel)</option>
                                    <option value="lengkap">Laporan Lengkap WIG Dashboard (Print/PDF)</option>
                                </select>
                            </div>
                            
                            <div id="wigSelectionWrapper" class="hidden">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih WIG Induk</label>
                                <select id="selectWigId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="all">-- Semua WIG --</option>
                                    @php
                                        $availableWigs = \App\Models\MasterWig::all();
                                    @endphp
                                    @foreach($availableWigs as $wig)
                                        <option value="{{ $wig->id }}">{{ $wig->judul }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-center mt-6">
                            <a href="javascript:void(0)" onclick="exportReport()" class="flex items-center justify-center px-6 py-3 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl transition-all shadow-md font-bold text-sm w-full sm:w-auto">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Buka Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('selectBulan').addEventListener('change', updateLabels);
        document.getElementById('selectTahun').addEventListener('change', updateLabels);
        
        document.getElementById('selectJenisLaporan').addEventListener('change', function() {
            const wigSelect = document.getElementById('wigSelectionWrapper');
            if (this.value === 'lengkap') {
                wigSelect.classList.remove('hidden');
            } else {
                wigSelect.classList.add('hidden');
            }
        });
        
        function updateLabels() {
            const b = document.getElementById('selectBulan');
            const bulanText = b.options[b.selectedIndex].text;
            const tahun = document.getElementById('selectTahun').value;
            document.getElementById('lblPeriodeExp').innerText = `${bulanText} ${tahun}`;
        }
        
        function exportReport() {
            const bulan = document.getElementById('selectBulan').value;
            const tahun = document.getElementById('selectTahun').value;
            const jenis = document.getElementById('selectJenisLaporan').value;
            
            if (jenis === 'lm') {
                window.location.href = `{{ route('laporan.export') }}?bulan=${bulan}&tahun=${tahun}&format=excel`;
            } else if (jenis === 'wig') {
                window.location.href = `{{ route('laporan.exportWig') ?? '#' }}?bulan=${bulan}&tahun=${tahun}`;
            } else if (jenis === 'lengkap') {
                const wigId = document.getElementById('selectWigId').value;
                window.open(`{{ route('laporan.exportLengkap') ?? '#' }}?bulan=${bulan}&tahun=${tahun}&wig_id=${wigId}`, '_blank');
            }
        }
    </script>
</x-app-layout>
