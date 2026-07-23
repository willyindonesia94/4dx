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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- IMPORT SECTION -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 text-white/80 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import Data Historis (Excel)
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <p class="text-sm text-slate-600">
                            Gunakan fitur ini untuk memindahkan data laporan LM bulan-bulan sebelumnya secara cepat.
                        </p>
                        
                        <!-- Step 1 -->
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-100">
                            <h4 class="font-bold text-slate-800 text-sm mb-2">Langkah 1: Unduh Template</h4>
                            <p class="text-xs text-slate-500 mb-3">Template akan digenerate otomatis berisi daftar WIG, LM, dan Unit yang terdaftar di sistem untuk periode <strong id="lblPeriodeTpl">{{ \Carbon\Carbon::create()->month((int) request('bulan', date('n')))->translatedFormat('F') }} {{ request('tahun', date('Y')) }}</strong>.</p>
                            <a href="javascript:void(0)" onclick="downloadTemplate()" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-md text-sm font-semibold transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download Template
                            </a>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-100">
                            <h4 class="font-bold text-slate-800 text-sm mb-2">Langkah 2: Upload Excel</h4>
                            <form action="{{ route('laporan.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="tahun" id="importTahun" value="{{ request('tahun', date('Y')) }}">
                                <input type="hidden" name="bulan" id="importBulan" value="{{ request('bulan', date('n')) }}">
                                <div>
                                    <input type="file" name="file_excel" accept=".xlsx, .xls, .csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md">
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md text-sm font-bold transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    Upload & Proses Data
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- EXPORT SECTION -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 text-white/80 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export Laporan Bulanan (DATA LM)
                        </h3>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-center space-y-6">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Laporan LM Periode <span id="lblPeriodeExp">{{ \Carbon\Carbon::create()->month((int) request('bulan', date('n')))->translatedFormat('F') }} {{ request('tahun', date('Y')) }}</span></h4>
                            <p class="text-sm text-slate-500 mt-2">Mengekspor laporan capaian LM yang merangkum target dan realisasi bulanan serta mingguan untuk semua Unit.</p>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 mt-auto">
                            <a href="javascript:void(0)" onclick="openPreviewModal()" class="flex flex-col items-center justify-center px-4 py-4 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 hover:border-indigo-300 rounded-xl transition-all shadow-sm">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span class="font-bold text-sm">Preview</span>
                            </a>
                            <a href="javascript:void(0)" onclick="exportReport('excel')" class="flex flex-col items-center justify-center px-4 py-4 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-300 rounded-xl transition-all shadow-sm">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="font-bold text-sm">Excel</span>
                            </a>
                            <a href="javascript:void(0)" onclick="exportReport('pdf')" class="flex flex-col items-center justify-center px-4 py-4 bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 hover:border-red-300 rounded-xl transition-all shadow-sm">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span class="font-bold text-sm">PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PREVIEW MODAL -->
    <div id="previewModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closePreviewModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-7xl flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="bg-slate-800 px-4 sm:px-6 py-4 flex justify-between items-center shrink-0 border-b border-slate-700">
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center" id="modal-title">
                        <svg class="w-5 h-5 text-white/80 mr-2 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Preview Laporan ({{ \Carbon\Carbon::create()->month((int) request('bulan', date('n')))->translatedFormat('F') }} {{ request('tahun', date('Y')) }})
                    </h3>
                    
                    <div class="flex items-center gap-2 sm:gap-4">
                        <!-- Zoom Controls -->
                        <div class="flex items-center bg-slate-700 rounded-md p-1 border border-slate-600">
                            <button type="button" onclick="zoomPreview('out')" class="px-2 py-1 text-slate-300 hover:text-white hover:bg-slate-600 rounded transition-colors" title="Perkecil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path></svg>
                            </button>
                            <span id="zoomLevel" class="px-2 sm:px-3 text-xs font-bold text-white min-w-[2.5rem] sm:min-w-[3rem] text-center">100%</span>
                            <button type="button" onclick="zoomPreview('in')" class="px-2 py-1 text-slate-300 hover:text-white hover:bg-slate-600 rounded transition-colors" title="Perbesar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                            </button>
                        </div>
                        
                        <!-- Close Button Top -->
                        <button type="button" onclick="closePreviewModal()" class="flex items-center px-3 py-1.5 rounded-md bg-red-500/10 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors border border-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500/50">
                            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-sm font-bold hidden sm:block">Tutup</span>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="bg-slate-200 relative flex-1 flex flex-col overflow-hidden">
                    <div class="overflow-auto flex-1 p-4 sm:p-8 w-full" id="previewWrapperContainer">
                        @if(isset($previewData) && count($previewData['data']) > 0)
                            <div class="w-full min-w-max flex justify-center pb-16">
                                <div id="pdfPreviewPaper" class="bg-white shadow-xl border border-slate-300 transition-all duration-300 inline-block" style="padding: 2rem; min-width: 800px;">
                                    @include('exports.lm_report_table', $previewData)
                                </div>
                            </div>
                        @else
                            <div class="p-12 text-center bg-white rounded-xl border border-slate-300 shadow-sm max-w-lg mx-auto my-auto mt-10">
                                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-slate-600 font-bold text-lg mb-1">Belum ada data laporan</p>
                                <p class="text-sm text-slate-500">Silakan lakukan import data historis terlebih dahulu untuk periode ini.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="absolute bottom-4 left-0 w-full flex justify-center pointer-events-none">
                        <button type="button" onclick="closePreviewModal()" class="pointer-events-auto px-6 py-2.5 bg-slate-800/90 text-white shadow-lg shadow-slate-900/20 hover:bg-slate-700 hover:-translate-y-0.5 rounded-full font-bold transition-all flex items-center border border-slate-600 backdrop-blur-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Tutup Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openPreviewModal() {
            document.getElementById('previewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // prevent background scrolling
        }
        
        function closePreviewModal() {
            document.getElementById('previewModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function downloadTemplate() {
            const bulan = document.getElementById('selectBulan').value;
            const tahun = document.getElementById('selectTahun').value;
            window.location.href = `{{ route('laporan.template') }}?bulan=${bulan}&tahun=${tahun}`;
        }

        function exportReport(format) {
            const bulan = document.getElementById('selectBulan').value;
            const tahun = document.getElementById('selectTahun').value;
            window.location.href = `{{ route('laporan.export') }}?bulan=${bulan}&tahun=${tahun}&format=${format}`;
        }
        
        // Update labels dynamically when select changes
        document.getElementById('selectBulan').addEventListener('change', updateLabels);
        document.getElementById('selectTahun').addEventListener('change', updateLabels);
        
        function updateLabels() {
            const b = document.getElementById('selectBulan');
            const bulanText = b.options[b.selectedIndex].text;
            const bulanValue = b.value;
            const tahun = document.getElementById('selectTahun').value;
            
            // Update labels
            document.getElementById('lblPeriodeTpl').innerText = `${bulanText} ${tahun}`;
            document.getElementById('lblPeriodeExp').innerText = `${bulanText} ${tahun}`;
            
            // Update hidden inputs for import form
            const importBulan = document.getElementById('importBulan');
            const importTahun = document.getElementById('importTahun');
            if (importBulan) importBulan.value = bulanValue;
            if (importTahun) importTahun.value = tahun;
        }
        
        // Zoom functionality
        let currentZoom = 1;
        const zoomStep = 0.1;
        
        function zoomPreview(action) {
            const paper = document.getElementById('pdfPreviewPaper');
            if (!paper) return;
            
            if (action === 'in' && currentZoom < 2) {
                currentZoom += zoomStep;
            } else if (action === 'out' && currentZoom > 0.4) {
                currentZoom -= zoomStep;
            }
            
            // Fix floating point issues
            currentZoom = Math.round(currentZoom * 10) / 10;
            
            // Use CSS zoom instead of transform for proper layout scaling and reflow
            paper.style.zoom = currentZoom;
            
            document.getElementById('zoomLevel').innerText = Math.round(currentZoom * 100) + '%';
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePreviewModal();
            }
        });
    </script>
</x-app-layout>
