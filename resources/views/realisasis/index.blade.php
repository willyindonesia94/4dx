<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Realisasi LM Harian') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="realisasiForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 px-2 sm:px-0">
                <p class="text-gray-600 text-sm sm:text-base">Berikut adalah daftar realisasi pencapaian Lead Measures yang telah diinput.</p>
                <button @click="openModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
                    + Input Realisasi LM
                </button>
            </div>
            
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal Input</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Penginput</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Target WIG</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Lead Measure</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Capaian</th>
                                <th class="px-4 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($realisasis as $realisasi)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($realisasi->tanggal_input)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $realisasi->user->name ?? '-' }}
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
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    @php
                                        $canEdit = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin']) || \Carbon\Carbon::parse($realisasi->tanggal_input)->isSameDay(now());
                                        $canDelete = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin']);
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada data realisasi yang diinput.</td>
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
                get filteredLms() {
                    if (!this.selectedWig) return [];
                    const wig = this.wigs.find(w => w.id == this.selectedWig);
                    return wig && wig.master_lms ? wig.master_lms : [];
                },
                get selectedSatuan() {
                    if (!this.selectedLm) return '';
                    const lms = this.filteredLms;
                    const lm = lms.find(l => l.id == this.selectedLm);
                    return (lm && lm.satuan) ? lm.satuan.name : '';
                }
            }
        }
    </script>
</x-app-layout>
