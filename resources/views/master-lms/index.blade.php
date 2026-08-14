<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Lead Measure (LM)') }}
        </h2>
    </x-slot>

        <!-- Modals using Alpine.js -->
        <div x-data="{                  
                  openModal: false, 
                  openUpload: false,
                  editModal: false, 
                  editData: { id: '', wig_id: '', judul_lm: '', satuan_id: '', polaritas: 'positif' },
                  openEdit(lm) {
                    this.editData = { ...lm };
                    this.editModal = true;
                }
            }"
            @open-edit.window="openEdit($event.detail)">
               <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0 gap-4 w-full">
                <!-- Filter Tabs -->
                <div class="flex space-x-1 bg-white border border-slate-200 p-1 rounded-lg shadow-sm w-full sm:w-auto overflow-x-auto">
                    <a href="{{ route('master-lms.index', ['status' => 'all']) }}" class="whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-md transition-colors {{ ($status ?? 'all') === 'all' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">Semua LM</a>
                    <a href="{{ route('master-lms.index', ['status' => 'draft']) }}" class="whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-md transition-colors flex items-center {{ ($status ?? 'all') === 'draft' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Menunggu Persetujuan
                        @php
                            $draftCountLm = \App\Models\MasterLm::where('is_approved', false)->count();
                        @endphp
                        @if($draftCountLm > 0)
                            <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 rounded-full text-xs font-bold">{{ $draftCountLm }}</span>
                        @endif
                    </a>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <!-- Search Form -->
                    <form action="{{ route('master-lms.index') }}" method="GET" class="relative w-full sm:w-auto">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari LM..." class="w-full sm:w-80 px-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" oninput="performAjaxSearch(this, 'ajax-container')">
                    </form>

                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <div class="flex gap-2 w-full sm:w-auto">
                            <a href="{{ route('cascading.lm.template') }}" class="w-1/2 sm:w-auto whitespace-nowrap justify-center inline-flex items-center px-4 py-2.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-lg shadow-sm text-sm font-semibold transition-colors">
                                <svg class="w-4 h-4 mr-1.5 sm:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span class="hidden sm:inline">Template LM</span>
                                <span class="sm:hidden">Template</span>
                            </a>
                            <button @click="openUpload = !openUpload" class="w-1/2 sm:w-auto whitespace-nowrap justify-center inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm transition-colors text-sm">
                                <svg class="w-4 h-4 mr-1.5 sm:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                <span class="hidden sm:inline">Upload Master LM</span>
                                <span class="sm:hidden">Upload</span>
                            </button>
                        </div>
                        <button @click="openModal = true" class="w-full sm:w-auto whitespace-nowrap justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 sm:px-5 rounded-lg shadow-sm transition-colors flex items-center text-sm">
                            <svg class="w-5 h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span class="hidden sm:inline">Tambah Master LM</span>
                            <span class="sm:hidden">Tambah LM</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Upload Form (Hidden by default) -->
            <div x-show="openUpload" x-collapse x-cloak class="mb-6 bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 text-lg mb-2">Upload Excel Massal LM</h3>
                <p class="text-sm text-slate-600 mb-4">Pastikan format kolom sesuai dengan template. Sistem akan memasukkan LM ke dalam WIG yang namanya sama persis. Jika WIG tidak ditemukan, baris akan dilewati.</p>
                <form action="{{ route('cascading.lm.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    @csrf
                    <div class="flex-1 w-full">
                        <input type="file" name="file_excel" accept=".xlsx, .xls" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md bg-white">
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md transition-colors shadow-sm">
                        Proses Upload
                    </button>
                </form>
            </div>
            
            <div class="mt-4 space-y-4" id="ajax-container">
                @php
                    $groupedLms = $lms->groupBy('wig_id');
                @endphp

                @forelse($wigs as $wig)
                    @php
                        $wigLms = $groupedLms->get($wig->id, collect());
                    @endphp
                    @if($wigLms->count() > 0)
                    <div x-data="{ expanded: false }" class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-hidden">
                        <div @click="expanded = !expanded" class="cursor-pointer bg-slate-50 hover:bg-slate-100 px-6 py-4 flex justify-between items-center transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="bg-indigo-100 text-indigo-700 font-bold p-2 rounded-lg text-xs w-8 h-8 flex items-center justify-center">
                                    {{ $loop->iteration }}
                                </div>
                                <h3 class="font-bold text-slate-800 text-sm">{{ $wig->judul }}</h3>
                                <span class="bg-slate-200 text-slate-600 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $wigLms->count() }} LM</span>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        
                        <div x-show="expanded" x-collapse x-cloak>
                            <div class="p-0 bg-white overflow-x-auto border-t border-slate-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-slate-50/50">
                                        <tr>
                                              <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/2">Judul LM</th>
                                              <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Satuan</th>
                                              <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Polaritas</th>
                                              <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($wigLms as $lm)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 text-sm font-semibold text-slate-800 break-words max-w-lg">
                                                <div class="flex items-start">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2.5 mt-2 hidden sm:block flex-shrink-0"></span>
                                                    <span>{{ $lm->judul_lm }}</span>
                                                </div>
                                            </td>
                                              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                      {{ $lm->satuan->name ?? '-' }}
                                                  </span>
                                              </td>
                                              <td class="px-6 py-4 whitespace-nowrap text-center">
                                                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($lm->polaritas ?? 'positif') == 'positif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                      {{ ucfirst($lm->polaritas ?? 'positif') }}
                                                  </span>
                                              </td>
                                              <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($lm->is_approved)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center items-center space-x-2">
                                                @if(!$lm->is_approved && auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID']))
                                                    <form action="{{ route('master-lms.approve', $lm->id) }}" method="POST" class="inline m-0">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 hover:bg-green-100 font-bold bg-green-50 px-3 py-1.5 rounded-md border border-green-200 transition-colors text-xs">Setujui</button>
                                                    </form>
                                                @endif
                                                <button @click="$dispatch('open-edit', {{ $lm }})" class="text-indigo-600 hover:text-white hover:bg-indigo-600 font-bold px-3 py-1.5 rounded-md border border-indigo-200 hover:border-transparent transition-all duration-200 text-xs">Edit</button>
                                                
                                                <form action="{{ route('master-lms.destroy', $lm->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Master LM ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 font-bold px-3 py-1.5 rounded-md border border-red-200 hover:border-transparent transition-all duration-200 text-xs">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <!-- No LMs at all -->
                @endforelse

                @if(count($lms ?? []) == 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                        <div class="px-6 py-12 text-sm text-slate-500 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <span class="font-medium text-slate-600">Belum ada Lead Measure yang ditemukan.</span>
                            </div>
                        </div>
                    </div>
                @endif
                
            </div>

            <!-- Create Modal -->
            <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="openModal = false"></div>
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                        <form action="{{ route('master-lms.store') }}" method="POST" class="flex flex-col max-h-[90vh]">
                            @csrf
                            <input type="hidden" name="periode_start" value="{{ date('Y-01-01') }}">
                            <input type="hidden" name="periode_end" value="{{ date('Y-12-31') }}">
                            <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Tambah Master LM</h3>
                                <button type="button" @click="openModal = false" class="text-white hover:text-slate-200 focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">WIG Induk</label>
                                        <select name="wig_id" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                            <option value="">Pilih WIG Induk...</option>
                                            @foreach($wigs as $w)
                                                <option value="{{ $w->id }}">{{ $w->judul }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Judul LM</label>
                                        <input type="text" name="judul_lm" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                    </div>

                                      <div>
                                          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Satuan</label>
                                          <select name="satuan_id" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                              @foreach($satuans as $s)
                                                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                      <div>
                                          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Polaritas</label>
                                          <select name="polaritas" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                              <option value="positif">Positif (Makin Besar Makin Baik)</option>
                                              <option value="negatif">Negatif (Makin Kecil Makin Baik)</option>
                                          </select>
                                      </div>
                                </div>
                            </div>
                            <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                                <button @click="openModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
                                    Batal
                                </button>
                                <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div x-show="editModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="editModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="editModal = false"></div>
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <div x-show="editModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                        <form :action="`/master-lms/${editData.id}`" method="POST" class="flex flex-col max-h-[90vh]">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="periode_start" value="{{ date('Y-01-01') }}">
                            <input type="hidden" name="periode_end" value="{{ date('Y-12-31') }}">
                            <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Edit Master LM</h3>
                                <button type="button" @click="editModal = false" class="text-white hover:text-slate-200 focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">WIG Induk</label>
                                        <select name="wig_id" x-model="editData.wig_id" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                            <option value="">Pilih WIG Induk...</option>
                                            @foreach($wigs as $w)
                                                <option value="{{ $w->id }}">{{ $w->judul }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Judul LM</label>
                                        <input type="text" name="judul_lm" x-model="editData.judul_lm" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                    </div>

                                      <div>
                                          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Satuan</label>
                                          <select name="satuan_id" x-model="editData.satuan_id" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                              @foreach($satuans as $s)
                                                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                      <div>
                                          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Polaritas</label>
                                          <select name="polaritas" x-model="editData.polaritas" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700" required>
                                              <option value="positif">Positif (Makin Besar Makin Baik)</option>
                                              <option value="negatif">Negatif (Makin Kecil Makin Baik)</option>
                                          </select>
                                      </div>
                                </div>
                            </div>
                            <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                                <button @click="editModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
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
    </div>
</x-app-layout>
