<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600 shadow-sm" style="width: 40px; height: 40px; min-width: 40px;">
                    <svg style="width: 20px; height: 20px; max-width: 20px; max-height: 20px;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-800 leading-tight">
                        {{ __('Backup & Restore Database') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Kelola pencadangan (export) dan pemulihan (import) seluruh database aplikasi tanpa perlu mengakses phpMyAdmin.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Super Admin Only
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showImportModal: false,
        showRestoreLocalModal: false,
        selectedLocalFile: '',
        confirmAgreement: false,
        isExporting: false,
        isImporting: false,
        selectedFileName: ''
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm animate-fade-in">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm leading-relaxed">
                        <strong class="font-bold">Berhasil!</strong> {{ session('success') }}
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm animate-fade-in">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm leading-relaxed">
                        <strong class="font-bold">Terjadi Kesalahan:</strong> {{ session('error') }}
                    </div>
                </div>
            @endif

            <!-- Database Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px;">
                        <svg style="width: 24px; height: 24px;" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Database</div>
                        <div class="text-lg font-bold text-slate-800 truncate" title="{{ $stats['database'] }}">{{ $stats['database'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px;">
                        <svg style="width: 24px; height: 24px;" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jumlah Tabel</div>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['tables_count'] }} <span class="text-xs font-normal text-slate-500">tabel</span></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px;">
                        <svg style="width: 24px; height: 24px;" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ukuran Database</div>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['size_formatted'] }}</div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px;">
                        <svg style="width: 24px; height: 24px;" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Metode Engine</div>
                        <div class="text-sm font-bold {{ $stats['mysqldump_available'] ? 'text-emerald-700' : 'text-purple-700' }} flex items-center gap-1.5 mt-0.5">
                            @if($stats['mysqldump_available'])
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                CLI Fast Native
                            @else
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                PHP Streaming PDO
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Main Actions: Export & Import -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- CARD 1: EXPORT DATABASE -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px; min-width: 40px;">
                                <svg style="width: 20px; height: 20px;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900">Export / Download Database</h3>
                                <p class="text-xs text-slate-500">Cadangkan seluruh struktur tabel dan isi data ke file .sql</p>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Proses ini akan mengekspor seluruh database <span class="font-bold text-slate-800 font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $stats['database'] }}</span> ({{ $stats['tables_count'] }} tabel) ke dalam file format SQL standar yang kompatibel dengan phpMyAdmin, MySQL Workbench, dan command line.
                            </p>

                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 text-xs text-slate-600 space-y-2">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Cakupan Backup Termasuk:
                                </div>
                                <ul class="list-disc list-inside space-y-1 pl-1 text-slate-500">
                                    <li>Seluruh Master Data (WIG, LM, Unit, Periode, Bidang, Satuan)</li>
                                    <li>Data Cascading Target WIG & LM seluruh UP3 dan ULP</li>
                                    <li>Data Realisasi Harian & Mingguan beserta log bukti</li>
                                    <li>Akun Pengguna, Level Role, dan Catatan Audit Log</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('database-backup.export') }}" method="POST" class="flex-1 m-0" @submit="isExporting = true">
                            @csrf
                            <input type="hidden" name="action" value="download">
                            <button type="submit" :disabled="isExporting" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold rounded-xl text-sm shadow-sm transition-all">
                                <template x-if="!isExporting">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Download File Backup (.sql)</span>
                                    </div>
                                </template>
                                <template x-if="isExporting">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Mengekspor Database...</span>
                                    </div>
                                </template>
                            </button>
                        </form>

                        <form action="{{ route('database-backup.export') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="action" value="save">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold rounded-xl text-sm shadow-sm transition-all" title="Simpan file backup ke folder server tanpa mendownload">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                <span>Simpan di Server</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- CARD 2: IMPORT DATABASE -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px; min-width: 40px;">
                                <svg style="width: 20px; height: 20px;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900">Import / Restore Database</h3>
                                <p class="text-xs text-slate-500">Pulihkan struktur dan data database dari file backup .sql</p>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <!-- Danger Warning Box -->
                            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs text-rose-800 flex items-start gap-3">
                                <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <div class="space-y-1">
                                    <strong class="font-bold">Peringatan Penting:</strong>
                                    <p class="leading-relaxed">Proses import akan <strong>menimpa data database yang ada</strong> dengan data dari file yang diunggah. Pastikan file SQL yang Anda gunakan berasal dari sumber yang valid.</p>
                                    <p class="text-emerald-700 font-semibold pt-1">Sistem akan secara otomatis membuat 1 file pencadangan darurat (Emergency Backup) sesaat sebelum proses restore dijalankan.</p>
                                </div>
                            </div>

                            <!-- File Input Form -->
                            <form id="importForm" action="{{ route('database-backup.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                        Pilih File SQL (.sql)
                                    </label>
                                    <div class="flex items-center justify-center w-full">
                                        <label for="file_sql" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                <p class="mb-1 text-xs text-slate-600 font-semibold" x-text="selectedFileName ? selectedFileName : 'Klik untuk memilih file .sql'"></p>
                                                <p class="text-[11px] text-slate-400">File format .sql (Maks. 500 MB)</p>
                                            </div>
                                            <input id="file_sql" name="file_sql" type="file" accept=".sql" class="hidden" @change="selectedFileName = $event.target.files[0] ? $event.target.files[0].name : ''" required />
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2 pt-1">
                                    <input id="confirm_agreement" type="checkbox" x-model="confirmAgreement" class="mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <label for="confirm_agreement" class="text-xs text-slate-600 leading-normal select-none cursor-pointer">
                                        Saya mengerti bahwa seluruh data yang ada saat ini akan ditimpa oleh data dari file backup yang saya upload.
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-100">
                        <button type="button" 
                                @click="showImportModal = true"
                                :disabled="!confirmAgreement || !selectedFileName || isImporting"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm shadow-sm transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span>Lanjutkan Restore Database</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- CARD 3: LOCAL BACKUPS HISTORY ON SERVER -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px; min-width: 40px;">
                            <svg style="width: 20px; height: 20px;" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Riwayat File Backup di Server</h3>
                            <p class="text-xs text-slate-500">Daftar file cadangan yang tersimpan di direktori server lokal</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                        Total: {{ count($backups) }} File Backup
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-6">Nama File</th>
                                <th class="py-3 px-4">Tipe Cadangan</th>
                                <th class="py-3 px-4">Ukuran</th>
                                <th class="py-3 px-4">Waktu Pembuatan</th>
                                <th class="py-3 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @forelse($backups as $b)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-6 font-mono font-medium text-slate-800 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span class="truncate max-w-xs sm:max-w-md" title="{{ $b['filename'] }}">{{ $b['filename'] }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($b['is_emergency'])
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Darurat Otomatis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                Manual Backup
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-600">
                                        {{ $b['size_formatted'] }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-slate-800">{{ $b['created_at'] }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $b['created_ago'] }}</div>
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <!-- Download -->
                                            <a href="{{ route('database-backup.download', $b['filename']) }}" class="p-1.5 bg-slate-100 hover:bg-blue-100 hover:text-blue-700 text-slate-600 rounded-lg transition-colors border border-slate-200" title="Download File">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>

                                            <!-- Restore from this file -->
                                            <button type="button" @click="selectedLocalFile = '{{ $b['filename'] }}'; showRestoreLocalModal = true" class="p-1.5 bg-slate-100 hover:bg-amber-100 hover:text-amber-700 text-slate-600 rounded-lg transition-colors border border-slate-200" title="Pulihkan (Restore) database dari file ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            </button>

                                            <!-- Delete -->
                                            <form action="{{ route('database-backup.delete', $b['filename']) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup {{ $b['filename'] }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 rounded-lg transition-colors border border-slate-200" title="Hapus File Backup">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            <p class="text-sm font-medium">Belum ada file backup yang tersimpan di server.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- MODAL KONFIRMASI RESTORE (DARI UPLOAD FILE) -->
        <div x-show="showImportModal" class="fixed inset-0 z-[110] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showImportModal = false" x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100"
                    x-show="showImportModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <!-- Header -->
                    <div class="bg-rose-50 p-6 border-b border-rose-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0 text-rose-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-rose-950">Konfirmasi Restore Database</h3>
                                <p class="text-xs text-rose-700 mt-0.5">Tindakan ini akan menimpa seluruh database aktif</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Apakah Anda yakin ingin memulihkan database dari file:
                        </p>
                        
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono font-bold text-slate-800 break-all" x-text="selectedFileName"></div>

                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 space-y-1">
                            <strong class="font-bold flex items-center gap-1.5 text-amber-900">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Keamanan Terjamin
                            </strong>
                            <p class="leading-relaxed">Sistem akan otomatis membuat file snapshot backup database saat ini sebelum proses penimpaan dilakukan.</p>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:justify-end gap-2.5">
                        <button @click="showImportModal = false" type="button" :disabled="isImporting" class="w-full sm:w-auto px-4 py-2 border border-slate-200 rounded-lg text-slate-700 font-semibold hover:bg-slate-100 text-xs transition-colors">
                            Batal
                        </button>
                        <button type="button" 
                                @click="isImporting = true; document.getElementById('importForm').submit();"
                                :disabled="isImporting"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-sm transition-colors">
                            <template x-if="!isImporting">
                                <span>Ya, Jalankan Restore Sekarang</span>
                            </template>
                            <template x-if="isImporting">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Memproses Restore Database...</span>
                                </div>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL KONFIRMASI RESTORE DARI FILE SERVER LOKAL -->
        <div x-show="showRestoreLocalModal" class="fixed inset-0 z-[110] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showRestoreLocalModal = false" x-show="showRestoreLocalModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100"
                    x-show="showRestoreLocalModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="bg-amber-50 p-6 border-b border-amber-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-amber-950">Restore dari Backup Lokal</h3>
                                <p class="text-xs text-amber-700 mt-0.5">Pulihkan database ke kondisi file backup ini</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Apakah Anda yakin ingin memulihkan seluruh database menggunakan file cadangan ini?
                        </p>
                        
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono font-bold text-slate-800 break-all" x-text="selectedLocalFile"></div>

                        <p class="text-xs text-slate-500 leading-relaxed">
                            Data yang dibuat setelah waktu backup tersebut akan digantikan oleh isi file ini. Backup darurat saat ini akan tetap dibuat terlebih dahulu.
                        </p>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:justify-end gap-2.5">
                        <button @click="showRestoreLocalModal = false" type="button" class="w-full sm:w-auto px-4 py-2 border border-slate-200 rounded-lg text-slate-700 font-semibold hover:bg-slate-100 text-xs transition-colors">
                            Batal
                        </button>
                        <form :action="'{{ url('database-backup/restore-local') }}/' + selectedLocalFile" method="POST" class="inline m-0">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span>Ya, Pulihkan Sekarang</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
