<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Realisasi LM Harian') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="realisasiForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <p class="text-gray-600 text-sm sm:text-base">Berikut adalah daftar realisasi pencapaian Lead Measures yang telah diinput.</p>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto mt-3 sm:mt-0">
                    @if(auth()->user()->role_name === 'Super Admin' || auth()->user()->hasRole('Super Admin') || auth()->user()->role_name === 'Perencanaan UID' || auth()->user()->hasRole('Perencanaan UID'))
                        <div class="flex gap-2 w-full sm:w-auto">
                            <a href="{{ route('realisasis.template') }}" class="w-1/2 sm:w-auto justify-center bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2.5 px-4 rounded-lg shadow-sm border border-indigo-200 transition-colors text-sm flex items-center whitespace-nowrap">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Template
                            </a>
                            <button @click="openUploadModal = true" class="w-1/2 sm:w-auto justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition-colors text-sm flex items-center whitespace-nowrap">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Upload Massal
                            </button>
                        </div>
                    @endif
                    @php
                        $userObj = auth()->user();
                        $isUp3User = $userObj && $userObj->unit && strtoupper(trim((string)$userObj->unit->type)) === 'UP3';
                    @endphp
                    @if(!$isUp3User)
                        <button @click="openModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition-colors text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Input Realisasi LM
                        </button>
                    @endif
                </div>
            </div>
            
        @php
            $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        @endphp

        <!-- Filter Dropdown Bar -->
        <form method="GET" action="{{ route('realisasis.index') }}" class="mb-5">
            <div class="flex flex-wrap items-end gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                <!-- Periode -->
                <div class="flex flex-col gap-1 min-w-[160px]">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Periode</label>
                    <select name="bulan" id="bulanSelect" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium">
                        @foreach([2026] as $yr)
                            @foreach(range(1,12) as $mn)
                                <option value="{{ $mn }}" data-tahun="{{ $yr }}"
                                    {{ ($mn == $bulan && $yr == $tahun) ? 'selected' : '' }}>
                                    {{ $namaBulan[$mn] }} {{ $yr }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    <input type="hidden" name="tahun" id="tahunHidden" value="{{ $tahun }}">
                </div>

                @if(!empty($isUlpLevel) || $wigs->count() <= 1)
                <!-- Filter LM (khusus ULP atau jika WIG hanya 1) -->
                <div class="flex flex-col gap-1 min-w-[260px] max-w-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter Lead Measure</label>
                    <select name="lm_id_filter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium truncate">
                        <option value="">— Semua LM —</option>
                        @foreach(($availableLms ?? []) as $lmItem)
                            <option value="{{ $lmItem->id }}" {{ ($lmIdFilter ?? '') == $lmItem->id ? 'selected' : '' }}>
                                {{ $lmItem->judul_lm }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <!-- WIG -->
                <div class="flex flex-col gap-1 min-w-[200px] max-w-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter WIG</label>
                    <select name="wig_id" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium truncate">
                        <option value="">— Semua WIG —</option>
                        @foreach($wigs as $wig)
                            <option value="{{ $wig->id }}" {{ $wigId == $wig->id ? 'selected' : '' }}>
                                {{ $wig->judul }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Submit -->
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Terapkan
                </button>

                <!-- Info -->
                <p class="text-xs text-gray-400 ml-auto self-center">
                    <strong class="text-gray-700">{{ $realisasis->total() }}</strong> data ditemukan
                </p>
            </div>
        </form>

        <script>
            // Sync tahun hidden field when bulan changes
            document.querySelector('select[name="bulan"]')?.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const tahun = selected.getAttribute('data-tahun');
                if (tahun) document.getElementById('tahunHidden').value = tahun;
            });
        </script>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-4 sm:p-6 bg-white border-b border-gray-200 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal Input</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Unit</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Target WIG</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Lead Measure</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Capaian</th>
                            @if(!$isUp3User)
                            <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($realisasis as $realisasi)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($realisasi->tanggal_input)->locale('id')->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $realisasi->unit->name ?? ($realisasi->user->name ?? '-') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $realisasi->lm->wig->judul ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $realisasi->lm->judul_lm ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ number_format($realisasi->angka_realisasi, 2) }} 
                                <span class="text-xs text-gray-500 font-normal">{{ $realisasi->lm->satuan->name ?? '' }}</span>
                            </td>
                            @if(!$isUp3User)
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                @php
                                    $canEdit = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']) || \Carbon\Carbon::parse($realisasi->tanggal_input)->isSameDay(now());
                                    $canDelete = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
                                @endphp

                                @if($canEdit)
                                <button @click="openEditModal = true; editForm.id = {{ $realisasi->id }}; editForm.angka_realisasi = '{{ $realisasi->angka_realisasi }}'; editForm.keterangan_tambahan = '{{ $realisasi->keterangan_tambahan }}'; editForm.actionUrl = '{{ route('realisasis.update', $realisasi->id) }}'" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</button>
                                @endif

                                @if($canDelete)
                                <form action="{{ route('realisasis.destroy', $realisasi->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus realisasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">Hapus</button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isUp3User ? 5 : 6 }}" class="px-6 py-10 text-sm text-gray-400 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <span>Belum ada data realisasi untuk periode <strong>{{ $namaBulan[$bulan] }} {{ $tahun }}</strong>.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($realisasis->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $realisasis->links() }}
            </div>
            @endif
        </div>

        </div>

        <!-- Upload Modal -->
        <div x-show="openUploadModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form action="{{ route('realisasis.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col">
                        @csrf
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Upload Massal Realisasi Harian</h3>
                            <button @click="openUploadModal = false" type="button" class="text-indigo-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="p-6 bg-slate-50 flex-1 overflow-y-auto">
                            <div class="space-y-4">
                                <!-- Info Box (collapsible) -->
                                <div x-data="{ openPanduan: false }" class="bg-indigo-50/70 border border-indigo-200 rounded-xl shadow-sm">
                                    <button type="button" @click="openPanduan = !openPanduan" class="w-full flex items-center justify-between p-4 text-left">
                                        <h4 class="text-xs font-bold text-indigo-950 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Panduan &amp; Struktur Kolom Excel
                                        </h4>
                                        <svg :class="openPanduan ? 'rotate-180' : ''" class="w-4 h-4 text-indigo-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="openPanduan" x-collapse class="px-4 pb-4">
                                        <p class="text-xs text-indigo-900 mb-2.5 leading-relaxed">
                                            Gunakan file Excel dengan urutan kolom yang <strong>telah disesuaikan persis dengan Form Input Realisasi Harian</strong>:
                                        </p>
                                        <ol class="list-decimal list-inside text-[11px] text-indigo-800 space-y-1.5 bg-white p-3 rounded-lg border border-indigo-100 font-medium shadow-2xl/10">
                                            <li><span class="font-bold text-slate-700">judul_wig</span> : WIG Target yang dicapai</li>
                                            <li><span class="font-bold text-slate-700">judul_lm</span> : Nama Lead Measure Harian Anda</li>
                                            <li><span class="font-bold text-slate-700">angka_realisasi</span> : Angka capaian (contoh: 15.50)</li>
                                            <li><span class="font-bold text-slate-700">tanggal_input</span> : Tanggal pelaksanaan (format: YYYY-MM-DD)</li>
                                            <li><span class="font-bold text-slate-700">bukti_keterangan</span> : Link bukti dokumen atau catatan tambahan</li>
                                            <li><span class="font-bold text-slate-700">nip_pengguna</span> : <em>Opsional</em> (Kosongkan jika untuk akun sendiri)</li>
                                        </ol>
                                        <div class="mt-3 pt-3 border-t border-indigo-200/60 flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-[11px] text-indigo-800">Belum memiliki format file teratas?</span>
                                            <a href="{{ route('realisasis.template') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 px-3 py-1.5 rounded-lg transition-all shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                Unduh Template Sekarang
                                            </a>
                                        </div>
                                    </div>
                                </div>


                                <!-- Pilih Bulan & Tahun -->
                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Periode (Bulan / Tahun)</label>
                                    <div class="flex gap-2">
                                        <select name="bulan_import" required class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium">
                                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                                                <option value="{{ $i+1 }}" {{ ($i+1) == date('n') ? 'selected' : '' }}>{{ $nm }}</option>
                                            @endforeach
                                        </select>
                                        <select name="tahun_import" required class="w-28 text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium">
                                            @foreach([2025, 2026, 2027] as $yr)
                                                <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>{{ $yr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-2">⚠️ Pastikan periode sesuai dengan data di file Excel Anda.</p>
                                </div>

                                <!-- Format Import -->
                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Format File Excel</label>
                                    <div class="space-y-2">
                                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-indigo-50 transition">
                                            <input type="radio" name="format_import" value="standar" checked class="mt-0.5 text-indigo-600">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-700">Format Standar (Template Sistem)</div>
                                                <div class="text-[11px] text-slate-400">Kolom: judul_wig, judul_lm, angka_realisasi, tanggal_input, email_penginput</div>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-indigo-50 transition">
                                            <input type="radio" name="format_import" value="bidang" class="mt-0.5 text-indigo-600">
                                            <div>
                                                <div class="text-sm font-semibold text-slate-700">Format Scoreboard Bidang</div>
                                                <div class="text-[11px] text-slate-400">Kolom: PRIMARY, KM, UNIT, INDIKATOR KINERJA, REALISASI MINGGU-1 s/d REALISASI MINGGU-5 (seperti spreadsheet monitoring bidang)</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- File Input -->
                                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih File Excel (.xlsx / .xls)</label>
                                    <input type="file" name="file_excel" accept=".xlsx,.xls" required class="w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-lg p-2 bg-slate-50/50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                                    <p class="text-[11px] text-slate-400 mt-2">Sistem akan mengalokasikan data realisasi ke periode bulan yang dipilih di atas.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-100 px-6 py-4 border-t border-slate-200 flex flex-col-reverse sm:flex-row justify-end gap-2">
                            <button @click="openUploadModal = false" type="button" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500/20 transition-all text-sm">Batal</button>
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold rounded-lg shadow-md shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all duration-200 text-sm flex items-center justify-center transform hover:-translate-y-0.5">
                                Upload & Proses Realisasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Modal using Alpine.js -->
        <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop with blur -->
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal Panel -->
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form action="{{ route('realisasis.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col max-h-[90vh]">
                        @csrf
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Input Realisasi Harian</h3>
                            <button @click="openModal = false" type="button" class="text-indigo-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Pilih WIG Target</label>
                                    <select x-model="selectedWig" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                        <option value="">-- Pilih WIG --</option>
                                        <template x-for="wig in wigs" :key="wig.id">
                                            <option :value="wig.id" x-text="wig.judul"></option>
                                        </template>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Pilih Lead Measure</label>
                                    <p class="text-[11px] text-slate-500 mb-2">Hanya menampilkan LM yang sesuai dengan WIG terpilih.</p>
                                    <select name="lm_id" x-model="selectedLm" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                        <option value="">-- Pilih LM --</option>
                                        <template x-for="lm in filteredLms" :key="lm.id">
                                            <option :value="lm.id" x-text="lm.judul_lm"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Angka Realisasi</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" name="angka_realisasi" required class="block w-full py-2.5 px-4 pr-16 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 text-sm font-medium" x-text="selectedSatuan"></div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tanggal Input</label>
                                    <input type="text" value="{{ now()->translatedFormat('l, d F Y - H:i') }} (Waktu Saat Ini)" readonly class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-100 focus:outline-none shadow-sm text-sm text-slate-500 cursor-not-allowed italic">
                                    <p class="text-[10px] text-slate-500 mt-1">Tanggal dan jam tercatat otomatis secara realtime pada saat form dikirim.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bukti Fisik (Foto/Dokumen)</label>
                                    <p class="text-[10px] text-red-500 mb-2 font-medium">*Wajib dilampirkan untuk verifikasi</p>
                                    <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png" required class="block w-full py-2 px-3 text-sm text-slate-700 border border-slate-200 rounded-md cursor-pointer bg-slate-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="text-[10px] text-slate-500 mt-1">Format: JPG, PNG up to 2MB</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Keterangan Tambahan (Opsional)</label>
                                    <textarea name="keterangan_tambahan" rows="2" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" placeholder="Catatan tambahan..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="openModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                Kirim Realisasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="openEditModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form :action="editForm.actionUrl" method="POST" enctype="multipart/form-data" class="flex flex-col max-h-[90vh]">
                        @csrf
                        @method('PUT')
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide">Edit Realisasi LM</h3>
                            <button @click="openEditModal = false" type="button" class="text-indigo-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Angka Realisasi Baru</label>
                                    <input type="number" step="0.01" name="angka_realisasi" x-model="editForm.angka_realisasi" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bukti Fisik Baru (Opsional)</label>
                                    <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full py-2 px-3 text-sm text-slate-700 border border-slate-200 rounded-md cursor-pointer bg-slate-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah bukti.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Keterangan Tambahan Baru</label>
                                    <textarea name="keterangan_tambahan" x-model="editForm.keterangan_tambahan" rows="2" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="openEditModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function realisasiForm() {
            return {
                openModal: false,
                openUploadModal: false,
                openEditModal: false,
                editForm: {
                    id: null,
                    angka_realisasi: '',
                    keterangan_tambahan: '',
                    actionUrl: ''
                },
                selectedWig: '',
                selectedLm: '',
                wigs: @json($wigs ?? []),
                init() {
                    if (this.wigs && this.wigs.length === 1) {
                        this.selectedWig = String(this.wigs[0].id);
                    }
                },
                get filteredLms() {
                    if (!this.selectedWig) return [];
                    const wig = this.wigs.find(w => String(w.id) === String(this.selectedWig));
                    return wig && (wig.master_lms || wig.masterLms) ? (wig.master_lms || wig.masterLms) : [];
                },
                get selectedSatuan() {
                    if (!this.selectedLm) return '';
                    const lms = this.filteredLms;
                    const lm = lms.find(l => String(l.id) === String(this.selectedLm));
                    return (lm && lm.satuan) ? lm.satuan.name : '';
                }
            }
        }
    </script>
</x-app-layout>
