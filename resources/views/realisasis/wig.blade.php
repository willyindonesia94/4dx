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
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                    <button @click="openUploadModal = true" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Upload Massal WIG
                    </button>
                    @endif
                    <button @click="openModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Input Realisasi WIG
                    </button>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex gap-4 items-end">
                <form action="{{ route('realisasi-wig.index') }}" method="GET" class="flex flex-wrap gap-4 items-end w-full">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                        <select name="bulan" class="rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $bulanFilter == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
                        <select name="tahun" class="rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach(range(date('Y')-2, date('Y')+1) as $y)
                                <option value="{{ $y }}" {{ $tahunFilter == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">WIG</label>
                        <select name="wig_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">-- Semua WIG --</option>
                            @foreach($wigs as $wigOp)
                                <option value="{{ $wigOp->id }}" {{ (isset($wigFilter) && $wigFilter == $wigOp->id) ? 'selected' : '' }}>{{ $wigOp->judul }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">Filter</button>
                </form>
            </div>
            
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Bulan/Tahun</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Unit</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">WIG</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Target Bulan Ini</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Realisasi</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">% Capaian</th>
                                <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Bukti/Catatan</th>
                                <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($realisasis as $realisasi)
                                @php
                                    $monthMap = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                                    $targetCol = 'target_' . strtolower($monthMap[$realisasi->bulan]);
                                    // Cari target dari breakdown
                                    $breakdown = \App\Models\BreakdownWig::where('wig_id', $realisasi->wig_id)
                                        ->where('unit_id', $realisasi->unit_id)
                                        ->where('tahun', $realisasi->tahun)
                                        ->first();
                                    $target = $breakdown ? $breakdown->$targetCol : 0;
                                    $satuan = $realisasi->wig->satuan->name ?? '';
                                    $polaritas = strtolower(trim($realisasi->wig->polaritas ?? 'positif')); // positif / negatif
                                    $real = $realisasi->angka_realisasi;
                                    
                                    if ($polaritas == 'negatif' || $polaritas == '3') { // Negatif (makin kecil makin baik)
                                        if ($target == 0) {
                                            $persen = ($real == 0) ? 100 : 0;
                                        } else {
                                            if ($real == 0) {
                                                $persen = 100; // Atau maksimal PLN 120%
                                            } else {
                                                // PLN standard untuk Negatif: jika capai lebih kecil dari target, maka lebih dari 100%
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
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $monthMap[$realisasi->bulan] }} {{ $realisasi->tahun }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $realisasi->unit->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $realisasi->wig->judul ?? '-' }}">
                                    {{ $realisasi->wig->judul ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($target, 2) }} {{ $satuan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ number_format($realisasi->angka_realisasi, 2) }} {{ $satuan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $persen >= 100 ? 'text-green-600' : 'text-orange-500' }}">
                                    {{ number_format($persen, 1) }}%
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center text-sm font-medium space-x-2">
                                    @if($realisasi->bukti_file)
                                        <a href="{{ Storage::url($realisasi->bukti_file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 underline block mb-1">Lihat Bukti</a>
                                    @else
                                        <span class="text-gray-400 text-xs block mb-1">Tidak Ada Bukti</span>
                                    @endif
                                    
                                    @if($realisasi->keterangan_tambahan)
                                        <div class="text-[10px] text-gray-500 max-w-[150px] mx-auto truncate" title="{{ $realisasi->keterangan_tambahan }}">
                                            "{{ $realisasi->keterangan_tambahan }}"
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    @php
                                        $canEditDelete = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']);
                                    @endphp

                                    @if($canEditDelete)
                                    <button @click="openEditModal = true; editForm.id = {{ $realisasi->id }}; editForm.angka_realisasi = '{{ $realisasi->angka_realisasi }}'; editForm.keterangan_tambahan = '{{ $realisasi->keterangan_tambahan }}'; editForm.actionUrl = '{{ route('realisasi-wig.update', $realisasi->id) }}'" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</button>

                                    <form action="{{ route('realisasi-wig.destroy', $realisasi->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus realisasi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">Hapus</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada data realisasi WIG yang diinput.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
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
                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
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
                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
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
                    @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                        if (!this.form.unit_id) return;
                    @endif

                    this.isLoadingTarget = true;
                    
                    let url = `{{ route('realisasi-wig.target') }}?wig_id=${this.form.wig_id}&bulan=${this.form.bulan}&tahun=${this.form.tahun}`;
                    @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
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
                }
            }
        }
    </script>
</x-app-layout>
