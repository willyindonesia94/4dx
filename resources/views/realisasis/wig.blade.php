<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Realisasi WIG Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="realisasiWigForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <p class="text-gray-600 text-sm sm:text-base">Berikut adalah daftar realisasi pencapaian WIG secara bulanan.</p>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto mt-3 sm:mt-0">
                    @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']))
                        <div class="flex gap-2 w-full sm:w-auto">
                            <a href="{{ route('realisasi-wig.template') }}" class="w-1/2 sm:w-auto justify-center bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2.5 px-4 rounded-lg shadow-sm border border-indigo-200 transition-colors text-sm flex items-center whitespace-nowrap">
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
                        $roleNameLower = strtolower(trim((string)($userObj->role_name ?? '')));
                        $isManager = str_contains($roleNameLower, 'manager up3') || str_contains($roleNameLower, 'manajer up3') || str_contains($roleNameLower, 'manager ulp') || str_contains($roleNameLower, 'manajer ulp') || str_contains($roleNameLower, 'perencanaan up3');
                    @endphp
                    @if(!$isManager)
                    <button @click="openModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition-colors text-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Input Realisasi WIG
                    </button>
                    @endif
                </div>
            </div>

            @php
                $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            @endphp

            <!-- Filter Dropdown Bar -->
            <form method="GET" action="{{ route('realisasi-wig.index') }}" class="mb-5">
                <div class="flex flex-wrap items-end gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                    <!-- Periode -->
                    <div class="flex flex-col gap-1 min-w-[160px]">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Periode</label>
                        <select name="bulan" id="bulanSelect" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium">
                            @foreach([2026] as $yr)
                                @foreach(range(1,12) as $mn)
                                    <option value="{{ $mn }}" data-tahun="{{ $yr }}"
                                        {{ ($mn == $bulanFilter && $yr == $tahunFilter) ? 'selected' : '' }}>
                                        {{ $namaBulan[$mn] }} {{ $yr }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <input type="hidden" name="tahun" id="tahunHidden" value="{{ $tahunFilter }}">
                    </div>

                    <!-- WIG -->
                    <div class="flex flex-col gap-1 min-w-[200px] max-w-sm">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter WIG</label>
                        <select name="wig_id" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium truncate">
                            <option value="">— Semua WIG —</option>
                            @foreach($wigs as $wigOp)
                                <option value="{{ $wigOp->id }}" {{ (isset($wigFilter) && $wigFilter == $wigOp->id) ? 'selected' : '' }}>
                                    {{ $wigOp->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <!-- UP3 -->
                    <div class="flex flex-col gap-1 min-w-[180px] max-w-sm">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter Unit</label>
                        <select name="up3_id" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-gray-700 font-medium truncate">
                            <option value="">— Semua Unit —</option>
                            @foreach($availableUnits as $unit)
                                <option value="{{ $unit->id }}" {{ (isset($up3Filter) && $up3Filter == $unit->id) ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Submit Button Removed for Auto-submit -->

                    <!-- Info -->
                    <div class="ml-auto flex items-center h-full pb-2">
                        @php
                            $totalRealisasis = 0;
                            if(isset($displayWigs)) {
                                $totalRealisasis = $displayWigs->sum(function($w) {
                                    return $w->realisasis->count();
                                });
                            }
                        @endphp
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider bg-gray-100 px-3 py-1.5 rounded-md">
                            <span class="text-indigo-600 font-black">{{ $totalRealisasis }}</span> data ditemukan
                        </span>
                    </div>
                </div>
            </form>
            <script>
                document.getElementById('bulanSelect').addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const tahun = selectedOption.getAttribute('data-tahun');
                    if (tahun) document.getElementById('tahunHidden').value = tahun;
                    this.form.submit();
                });
                
                // Auto submit for other selects
                document.querySelectorAll('select[name="wig_id"], select[name="up3_id"]').forEach(function(select) {
                    select.addEventListener('change', function() {
                        this.form.submit();
                    });
                });
            </script>
            
            <div class="space-y-4">
                @forelse($displayWigs as $wig)
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                <button type="button" @click="activeWig = activeWig === {{ $wig->id }} ? null : {{ $wig->id }}" class="w-full flex justify-between items-start px-6 py-4 bg-gray-50 hover:bg-gray-100 transition-colors focus:outline-none text-left">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-500 mr-3 mt-0.5 transform transition-transform flex-shrink-0" :class="{'rotate-90': activeWig === {{ $wig->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $wig->judul }}</h3>
                            @if($wig->deskripsi)
                                <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ $wig->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded-full flex-shrink-0 mt-0.5">{{ $wig->realisasis->count() }} Realisasi</span>
                </button>
                <div x-show="activeWig === {{ $wig->id }}" x-collapse style="display: none;" class="border-t border-gray-200 overflow-x-auto">
                        @if($wig->realisasis->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                                    <th class="px-4 py-3 w-10 text-center border-b border-gray-200">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer"
                                               @change="const checkboxes = $event.target.closest('table').querySelectorAll('.row-checkbox');
                                                        checkboxes.forEach(cb => {
                                                            cb.checked = $event.target.checked;
                                                            if(cb.checked && !selectedRealisasis.includes(cb.value)) selectedRealisasis.push(cb.value);
                                                            if(!cb.checked && selectedRealisasis.includes(cb.value)) selectedRealisasis = selectedRealisasis.filter(id => id !== cb.value);
                                                        });">
                                    </th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Bulan/Tahun</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Unit</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Target Bulan Ini</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Realisasi</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">% Capaian</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Bukti/Catatan</th>
                                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($wig->realisasis as $realisasi)
                                    @php
                                        $monthMap = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                                        $targetCol = 'target_' . strtolower($monthMap[$realisasi->bulan]);
                                        // Cari target dari breakdown
                                        $breakdown = \App\Models\BreakdownWig::where('wig_id', $realisasi->wig_id)
                                            ->where('unit_id', $realisasi->unit_id)
                                            ->where('tahun', $realisasi->tahun)
                                            ->first();
                                        $target = $breakdown ? $breakdown->$targetCol : 0;
                                        $satuan = $wig->satuan->name ?? '';
                                        $polaritas = strtolower(trim($wig->polaritas ?? 'positif')); // positif / negatif
                                        $real = $realisasi->angka_realisasi;
                                        
                                        if ($polaritas == 'negatif' || $polaritas == '3') { // Negatif (makin kecil makin baik)
                                            if ($target == 0) {
                                                $persen = ($real == 0) ? 100 : 0;
                                            } else {
                                                if ($real == 0) {
                                                    $persen = 100;
                                                } else {
                                                    $persen = ($target / $real) * 100;
                                                }
                                            }
                                        } else { // Positif
                                            if ($target == 0) {
                                                $persen = ($real > 0) ? 100 : (($real == 0) ? 100 : 0);
                                            } else {
                                                $persen = ($real / $target) * 100;
                                            }
                                        }
                                    @endphp
                                    <tr class="hover:bg-indigo-50/30 transition-colors">
                                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" value="{{ $realisasi->id }}" class="row-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer" x-model="selectedRealisasis">
                                        </td>
                                        @endif
                                        <td class="px-4 py-3 whitespace-nowrap text-[11px] font-bold text-gray-700">
                                            {{ $monthMap[$realisasi->bulan] }} {{ $realisasi->tahun }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                                            {{ $realisasi->unit->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 font-medium">
                                            {{ number_format($target, 2) }} <span class="text-[10px]">{{ $satuan }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs font-bold text-gray-900">
                                            {{ number_format($realisasi->angka_realisasi, 2) }} <span class="text-[10px]">{{ $satuan }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs font-bold {{ $persen >= 100 ? 'text-emerald-600' : 'text-orange-600' }}">
                                            {{ number_format($persen, 1) }}%
                                        </td>
                                        <td class="px-4 py-3 text-center text-xs">
                                            @if($realisasi->bukti_file)
                                                <a href="{{ Storage::url($realisasi->bukti_file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium underline">Bukti</a>
                                            @else
                                                <span class="text-gray-400 italic">Tidak ada</span>
                                            @endif
                                            @if($realisasi->keterangan_tambahan)
                                                <div class="text-[10px] text-gray-500 mt-1 max-w-[120px] mx-auto truncate" title="{{ $realisasi->keterangan_tambahan }}">
                                                    "{{ $realisasi->keterangan_tambahan }}"
                                                </div>
                                            @endif
                                        </td>
                                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                                        <td class="px-4 py-3 text-center whitespace-nowrap text-xs font-medium space-x-3">
                                            <button @click="openEditModal = true; editForm.id = {{ $realisasi->id }}; editForm.angka_realisasi = '{{ $realisasi->angka_realisasi }}'; editForm.keterangan_tambahan = '{{ $realisasi->keterangan_tambahan }}'; editForm.actionUrl = '{{ route('realisasi-wig.update', $realisasi->id) }}'" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</button>
                                            <button @click="confirmDelete('{{ route('realisasi-wig.destroy', $realisasi->id) }}')" class="text-red-600 hover:text-red-900 transition-colors">Hapus</button>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="p-6 text-center text-gray-500 text-sm italic">
                            Belum ada data realisasi WIG pada bulan/tahun dan WIG ini.
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="bg-white p-8 rounded-lg shadow-sm text-center border border-gray-100">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-gray-500 text-sm font-medium">Tidak ada data WIG yang ditemukan untuk filter ini.</p>
                </div>
                @endforelse
            </div>

            <!-- Floating Action Button for Bulk Delete -->
            <div x-show="selectedRealisasis.length > 0" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-10"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-10"
                 class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-red-600 shadow-2xl rounded-full px-6 py-3 flex items-center gap-4 z-50 border border-red-500" style="display: none;">
                <span class="font-bold text-white text-sm"><span x-text="selectedRealisasis.length"></span> Terpilih</span>
                <div class="h-5 w-px bg-red-400"></div>
                <button @click="bulkDelete()" class="text-white hover:text-red-100 font-bold text-sm flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus Sekaligus
                </button>
            </div>

            <!-- Hidden Form for Bulk Action -->
            <form id="bulkDeleteForm" action="{{ route('realisasi-wig.bulk-destroy') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="ids" id="bulkDeleteInput">
            </form>
            
            <!-- Hidden Single Delete Form -->
            <form id="singleDeleteForm" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <!-- Custom Confirm Modal -->
            <div x-show="showConfirmModal"
                 x-cloak
                 class="fixed inset-0 z-[9999] flex items-center justify-center"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showConfirmModal = false"></div>
                <!-- Modal Box -->
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                     x-transition:enter="transition ease-out duration-200 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <!-- Header -->
                    <div class="bg-red-50 border-b border-red-100 px-6 py-4 flex items-center gap-3">
                        <div class="bg-red-100 text-red-600 flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-base">Konfirmasi Hapus</h3>
                            <p class="text-xs text-slate-500">Perhatian — tindakan tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    <!-- Body -->
                    <div class="px-6 py-5">
                        <p class="text-sm text-slate-600 leading-relaxed" x-html="confirmMessage"></p>
                    </div>
                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                        <button @click="showConfirmModal = false"
                                class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button @click="doConfirmedAction()"
                                class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-white text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal using Alpine.js -->
        <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div x-show="openModal" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form action="{{ route('realisasi-wig.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col max-h-[90vh]">
                        @csrf
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Input Realisasi WIG</h3>
                            <button @click="openModal = false" type="button" class="text-indigo-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto flex-1">
                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']))
                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Pilih Unit</label>
                                <select name="unit_id" x-model="form.unit_id" @change="fetchTarget()" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-colors">
                                    <option value="">-- Pilih Unit (UP3) --</option>
                                    @foreach($availableUnits ?? [] as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Pilih WIG Target</label>
                                    <select name="wig_id" x-model="form.wig_id" @change="fetchTarget()" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                        <option value="">-- Pilih WIG --</option>
                                        @foreach($wigs as $wig)
                                            <option value="{{ $wig->id }}">{{ $wig->judul }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bulan</label>
                                        <select name="bulan" x-model="form.bulan" @change="fetchTarget()" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm">
                                            @php $curMonth = (int)date('n'); @endphp
                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']))
                                                @foreach(range(1, 12) as $m)
                                                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                                                @endforeach
                                            @else
                                                <option value="{{ $curMonth }}">{{ date('F', mktime(0, 0, 0, $curMonth, 10)) }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tahun</label>
                                        <select name="tahun" x-model="form.tahun" @change="fetchTarget()" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm">
                                            @php $curYear = (int)date('Y'); @endphp
                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']))
                                                @foreach(range($curYear-1, $curYear+1) as $y)
                                                    <option value="{{ $y }}">{{ $y }}</option>
                                                @endforeach
                                            @else
                                                <option value="{{ $curYear }}">{{ $curYear }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <!-- Target Display Box -->
                                <div x-show="form.target !== null" class="bg-indigo-50 border border-indigo-100 rounded-md p-4 text-center" style="display: none;">
                                    <p class="text-xs text-indigo-600 font-semibold uppercase tracking-wider">Target Bulanan</p>
                                    <h4 class="text-2xl font-bold text-indigo-700 mt-1">
                                        <span x-text="form.targetFormatted"></span>
                                        <span class="text-sm font-normal text-indigo-600" x-text="form.satuan"></span>
                                    </h4>
                                </div>
                                <div x-show="form.error" class="bg-red-50 text-red-600 p-3 rounded-md text-xs font-medium" x-text="form.error" style="display: none;"></div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Angka Realisasi</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" name="angka_realisasi" x-model="form.realisasi" :disabled="form.target === null" required class="block w-full py-2.5 px-4 pr-16 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed" placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 text-sm font-medium" x-text="form.satuan"></div>
                                    </div>
                                </div>

                                <!-- Persentase Realisasi Otomatis -->
                                <div x-show="form.target !== null && form.realisasi !== ''" class="flex items-center justify-between mt-2" style="display: none;">
                                    <span class="text-xs font-medium text-slate-500">Persentase Capaian:</span>
                                    <span class="text-sm font-bold" :class="persentase >= 100 ? 'text-green-600' : 'text-orange-500'" x-text="persentaseFormatted"></span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bukti Fisik (Opsional)</label>
                                    <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full py-2 px-3 text-sm text-slate-700 border border-slate-200 rounded-md cursor-pointer bg-slate-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="text-[10px] text-slate-500 mt-1">Format: JPG, PNG, PDF max 2MB</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Keterangan Tambahan (Opsional)</label>
                                    <textarea name="keterangan_tambahan" rows="2" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" placeholder="Catatan tambahan..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="openModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.target === null" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300">
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
                <div x-show="openEditModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="openEditModal" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form :action="editForm.actionUrl" method="POST" enctype="multipart/form-data" class="flex flex-col max-h-[90vh]">
                        @csrf
                        @method('PUT')
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide">Edit Realisasi WIG</h3>
                            <button @click="openEditModal = false" type="button" class="text-indigo-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Angka Realisasi Baru</label>
                                    <input type="number" step="0.01" name="angka_realisasi" x-model="editForm.angka_realisasi" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bukti Fisik Baru (Opsional)</label>
                                    <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" class="block w-full py-2 px-3 text-sm text-slate-700 border border-slate-200 rounded-md bg-slate-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Keterangan Tambahan Baru</label>
                                    <textarea name="keterangan_tambahan" x-model="editForm.keterangan_tambahan" rows="2" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg">
                            <button @click="openEditModal = false" type="button" class="w-full sm:w-auto px-6 py-2.5 border border-slate-200 rounded-md bg-white text-slate-700 font-semibold shadow-sm hover:bg-slate-50 text-sm">Batal</button>
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-md shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 text-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Upload Massal Modal -->
        <div x-show="openUploadModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openUploadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="openUploadModal" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white tracking-wide">Upload Massal Realisasi WIG</h3>
                        <button @click="openUploadModal = false" type="button" class="text-emerald-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <form action="{{ route('realisasi-wig.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-6 pt-5 pb-6">
                            <div class="space-y-6">
                                <!-- Pilih Tahun -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tahun</label>
                                    <select name="tahun" x-model="uploadForm.tahun" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                                        @php $curYear = (int)date('Y'); @endphp
                                        @foreach(range($curYear-1, $curYear+1) as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Download Template Button -->
                                <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-md flex justify-between items-center">
                                    <div class="text-sm text-emerald-800">
                                        <p class="font-semibold">Langkah 1: Unduh Template</p>
                                        <p class="text-xs mt-1">Gunakan template ini untuk mengisi data.</p>
                                    </div>
                                    <button type="button" @click="downloadTemplate()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download Excel
                                    </button>
                                </div>

                                <!-- File Upload -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Langkah 2: Upload File Template Terisi</label>
                                    <input type="file" name="file_import" accept=".xlsx,.xls,.csv" required class="block w-full py-2 px-3 text-sm text-slate-700 border border-slate-200 rounded-md cursor-pointer bg-slate-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg">
                            <button @click="openUploadModal = false" type="button" class="w-full sm:w-auto px-6 py-2.5 border border-slate-200 rounded-md bg-white text-slate-700 font-semibold shadow-sm hover:bg-slate-50 text-sm">Batal</button>
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-md shadow-lg shadow-emerald-200 hover:from-emerald-700 hover:to-teal-700 text-sm transition-colors">Import Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>

    <script>
        function realisasiWigForm() {
            return {
                selectedRealisasis: [],
                showConfirmModal: false,
                confirmMessage: '',
                confirmActionUrl: '',
                isBulkAction: false,
                openModal: false,
                openEditModal: false,
                openUploadModal: false,
                isLoadingTarget: false,
                uploadForm: {
                    tahun: '{{ (int)date("Y") }}'
                },
                editForm: {
                    id: null,
                    angka_realisasi: '',
                    keterangan_tambahan: '',
                    actionUrl: ''
                },
                activeWig: {{ session('active_wig', 'null') }},
                form: {
                    wig_id: '',
                    unit_id: '',
                    bulan: '{{ (int)date("n") }}',
                    tahun: '{{ (int)date("Y") }}',
                    target: null,
                    satuan: '',
                    polaritas: '1',
                    realisasi: '',
                    error: null,
                    get targetFormatted() {
                        if (this.target === null) return '';
                        return Number(this.target).toLocaleString('id-ID', {minimumFractionDigits: 2});
                    }
                },
                get persentase() {
                    let target = parseFloat(this.form.target) || 0;
                    let realisasi = parseFloat(this.form.realisasi) || 0;
                    let polar = (this.form.polaritas || '').toString().toLowerCase().trim();
                    
                    if (polar === 'negatif' || polar === '3') { // Negatif
                        if (target === 0) {
                            return realisasi === 0 ? 100 : 0;
                        } else {
                            if (realisasi === 0) return 100;
                            return (target / realisasi) * 100;
                        }
                    } else { // Positif
                        if (target === 0) {
                            return realisasi > 0 ? 100 : (realisasi === 0 ? 100 : 0);
                        } else {
                            return (realisasi / target) * 100;
                        }
                    }
                },
                get persentaseFormatted() {
                    return this.persentase.toFixed(1) + '%';
                },
                fetchTarget() {
                    if (!this.form.wig_id || !this.form.bulan || !this.form.tahun) return;
                    @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']))
                        if (!this.form.unit_id) return;
                    @endif

                    this.isLoadingTarget = true;
                    
                    let url = `{{ route('realisasi-wig.target') }}?wig_id=${this.form.wig_id}&bulan=${this.form.bulan}&tahun=${this.form.tahun}`;
                    @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']))
                        url += `&unit_id=${this.form.unit_id}`;
                    @endif
                    
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.isLoadingTarget = false;
                            if (data.target !== undefined && data.target !== null) {
                                this.form.target = data.target;
                                this.form.satuan = data.satuan;
                                this.form.polaritas = data.polaritas || '1';
                                this.form.error = null;
                            } else {
                                this.form.target = null;
                                this.form.satuan = '';
                                this.form.polaritas = '1';
                                this.form.error = data.message || 'Data target tidak ditemukan';
                            }
                        })
                        .catch(err => {
                            this.form.target = null;
                            this.form.error = 'Terjadi kesalahan saat mengambil target';
                        });
                },
                downloadTemplate() {
                    window.location.href = `{{ route('realisasi-wig.template') }}?tahun=${this.uploadForm.tahun}`;
                },
                bulkDelete() {
                    if (this.selectedRealisasis.length === 0) return;
                    this.isBulkAction = true;
                    this.confirmMessage = `Anda yakin ingin menghapus <strong>${this.selectedRealisasis.length}</strong> data realisasi terpilih secara permanen?`;
                    this.showConfirmModal = true;
                },
                confirmDelete(url) {
                    this.isBulkAction = false;
                    this.confirmActionUrl = url;
                    this.confirmMessage = `Anda yakin ingin menghapus data realisasi ini secara permanen?`;
                    this.showConfirmModal = true;
                },
                doConfirmedAction() {
                    if (this.isBulkAction) {
                        document.getElementById('bulkDeleteInput').value = JSON.stringify(this.selectedRealisasis);
                        document.getElementById('bulkDeleteForm').submit();
                    } else {
                        const form = document.getElementById('singleDeleteForm');
                        form.action = this.confirmActionUrl;
                        form.submit();
                    }
                }
            }
        }
    </script>
</x-app-layout>
