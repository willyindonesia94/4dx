<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master WIG (Target Korporat)') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ 
            openModal: false, 
            editModal: false, 
            editData: { id: '', judul: '', deskripsi: '', divisi: '', unit_pemilik_id: '', angka_target: '', satuan_id: '', polaritas: 'positif' },
            openEdit(wig) {
                this.editData = { ...wig };
                this.editModal = true;
            }
        }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0 w-full">
                <!-- Filter Tabs -->
                <div class="flex space-x-1 bg-white border border-slate-200 p-1 rounded-lg shadow-sm w-full sm:w-auto overflow-x-auto">
                    <a href="{{ route('master-wigs.index', ['status' => 'all']) }}" class="whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-md transition-colors {{ ($status ?? 'all') === 'all' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Semua WIG</a>
                    <a href="{{ route('master-wigs.index', ['status' => 'draft']) }}" class="whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-md transition-colors flex items-center {{ ($status ?? 'all') === 'draft' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Menunggu Persetujuan
                        @php
                            $draftCount = \App\Models\MasterWig::where('is_approved', false)->count();
                        @endphp
                        @if($draftCount > 0)
                            <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 rounded-full text-xs font-bold">{{ $draftCount }}</span>
                        @endif
                    </a>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-3 w-full sm:w-auto">
                    <!-- Search Form -->
                    <form action="{{ route('master-wigs.index') }}" method="GET" class="relative flex-1 sm:flex-none">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari WIG..." class="w-full sm:w-64 px-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" oninput="performAjaxSearch(this, 'ajax-container')">
                    </form>

                    <button @click="openModal = true" class="whitespace-nowrap bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 sm:px-5 rounded-lg shadow-sm transition-colors flex items-center justify-center text-sm">
                        <svg class="w-5 h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span class="hidden sm:inline">Tambah WIG</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 mt-2" id="ajax-container">
                <div class="p-0 bg-white overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-50/80 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Judul WIG</th>
                                <th class="px-6 py-5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Sub Bidang</th>
                                <th class="px-6 py-5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Target / Polaritas</th>
                                <th class="px-6 py-5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($wigs as $wig)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ $wig->judul }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $wig->divisi }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 text-right font-bold">
                                    {{ number_format($wig->angka_target, 2) }} {{ $wig->satuan->name ?? '' }}
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $wig->polaritas == 'positif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($wig->polaritas) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($wig->is_approved)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center items-center space-x-2">
                                    @if(!$wig->is_approved && auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID']))
                                        <form action="{{ route('master-wigs.approve', $wig->id) }}" method="POST" class="inline m-0">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 hover:bg-green-100 font-bold bg-green-50 px-3 py-1.5 rounded-md border border-green-200 transition-colors text-xs">Setujui</button>
                                        </form>
                                    @endif
                                    <button @click="openEdit({{ $wig->toJson() }})" class="text-indigo-600 hover:text-white hover:bg-indigo-600 font-bold px-3 py-1.5 rounded-md border border-indigo-200 hover:border-transparent transition-all duration-200 text-xs">Edit</button>
                                    
                                    <form action="{{ route('master-wigs.destroy', $wig->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Master WIG ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 font-bold px-3 py-1.5 rounded-md border border-red-200 hover:border-transparent transition-all duration-200 text-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-sm text-slate-500 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <span class="font-medium text-slate-600">Belum ada data WIG yang ditemukan.</span>
                                    </div>
                                </td>
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
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="openModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form action="{{ route('master-wigs.store') }}" method="POST" class="flex flex-col max-h-[90vh]">
                        @csrf
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Tambah Master WIG</h3>
                            <button type="button" @click="openModal = false" class="text-white hover:text-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Judul WIG</label>
                                    <input type="text" name="judul" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Deskripsi WIG (Opsional)</label>
                                    <textarea name="deskripsi" rows="3" class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Sub Bidang</label>
                                    <select name="divisi" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Pilih Sub Bidang --</option>
                                        @foreach($bidangs as $bidang)
                                            <option value="{{ $bidang->name }}">{{ $bidang->name }} ({{ $bidang->level == 'UID_BIDANG' ? 'UID' : ($bidang->level == 'UID_SUBBIDANG' ? 'Sub UID' : ($bidang->level == 'UP3_BIDANG' ? 'UP3' : 'ULP')) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Unit Pemilik</label>
                                    <select name="unit_pemilik_id" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Pilih Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Target</label>
                                    <input type="number" step="0.01" name="angka_target" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Satuan</label>
                                    <select name="satuan_id" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Pilih Satuan --</option>
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Polaritas</label>
                                    <select name="polaritas" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="positif">Positif (Makin Besar Makin Baik)</option>
                                        <option value="negatif">Negatif (Makin Kecil Makin Baik)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="openModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none transition-all">Batal</button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2 bg-indigo-600 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none transition-all">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal using Alpine.js -->
        <div x-show="editModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="editModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="editModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form :action="`/master-wigs/${editData.id}`" method="POST" class="flex flex-col max-h-[90vh]">
                        @csrf
                        @method('PUT')
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Edit Master WIG</h3>
                            <button type="button" @click="editModal = false" class="text-white hover:text-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Judul WIG</label>
                                    <input type="text" name="judul" x-model="editData.judul" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Deskripsi WIG (Opsional)</label>
                                    <textarea name="deskripsi" x-model="editData.deskripsi" rows="3" class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Sub Bidang</label>
                                    <select name="divisi" x-model="editData.divisi" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Pilih Sub Bidang --</option>
                                        @foreach($bidangs as $bidang)
                                            <option value="{{ $bidang->name }}">{{ $bidang->name }} ({{ $bidang->level == 'UID_BIDANG' ? 'UID' : ($bidang->level == 'UID_SUBBIDANG' ? 'Sub UID' : ($bidang->level == 'UP3_BIDANG' ? 'UP3' : 'ULP')) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Unit Pemilik</label>
                                    <select name="unit_pemilik_id" x-model="editData.unit_pemilik_id" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Pilih Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Target</label>
                                    <input type="number" step="0.01" name="angka_target" x-model="editData.angka_target" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Satuan</label>
                                    <select name="satuan_id" x-model="editData.satuan_id" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">-- Pilih Satuan --</option>
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Polaritas</label>
                                    <select name="polaritas" x-model="editData.polaritas" required class="block w-full py-2 px-3 rounded-md border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="positif">Positif (Makin Besar Makin Baik)</option>
                                        <option value="negatif">Negatif (Makin Kecil Makin Baik)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="editModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none transition-all">Batal</button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2 bg-indigo-600 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none transition-all">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
