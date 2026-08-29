<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Bidang & Organisasi (4 Level Hierarki)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="bidangForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between">
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside text-xs font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-4">
                    <!-- 4 Tabs -->
                    <div class="flex flex-wrap gap-1 bg-white border border-slate-200 p-1 rounded-xl shadow-sm w-full sm:w-auto">
                        <button @click="activeTab = 'UID_BIDANG'" :class="activeTab === 'UID_BIDANG' ? 'bg-indigo-50 text-indigo-700 font-bold shadow-sm border-indigo-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium border-transparent'" class="flex-1 sm:flex-none px-4 py-2 text-xs sm:text-sm rounded-lg border transition-all duration-200 flex items-center justify-center sm:justify-start gap-2 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            Bidang UID
                        </button>
                        <button @click="activeTab = 'UID_SUBBIDANG'" :class="activeTab === 'UID_SUBBIDANG' ? 'bg-blue-50 text-blue-700 font-bold shadow-sm border-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium border-transparent'" class="flex-1 sm:flex-none px-4 py-2 text-xs sm:text-sm rounded-lg border transition-all duration-200 flex items-center justify-center sm:justify-start gap-2 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Sub-Bidang UID
                        </button>
                        <button @click="activeTab = 'UP3_BIDANG'" :class="activeTab === 'UP3_BIDANG' ? 'bg-purple-50 text-purple-700 font-bold shadow-sm border-purple-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium border-transparent'" class="flex-1 sm:flex-none px-4 py-2 text-xs sm:text-sm rounded-lg border transition-all duration-200 flex items-center justify-center sm:justify-start gap-2 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            Bidang UP3
                        </button>
                        <button @click="activeTab = 'ULP_BIDANG'" :class="activeTab === 'ULP_BIDANG' ? 'bg-emerald-50 text-emerald-700 font-bold shadow-sm border-emerald-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium border-transparent'" class="flex-1 sm:flex-none px-4 py-2 text-xs sm:text-sm rounded-lg border transition-all duration-200 flex items-center justify-center sm:justify-start gap-2 whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Bidang ULP
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <form action="{{ route('master-bidangs.index') }}" method="GET" class="relative w-full sm:w-auto">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Bidang..." class="w-full sm:w-64 px-4 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" oninput="performAjaxSearch(this, 'ajax-container')">
                        </form>
                        <button @click="openCreate()" class="w-full sm:w-auto justify-center inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all duration-150 shrink-0 whitespace-nowrap">
                            <svg class="w-4 h-4 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            <span class="hidden sm:inline" x-text="activeTab === 'UID_BIDANG' ? 'Tambah Bidang UID' : (activeTab === 'UID_SUBBIDANG' ? 'Tambah Sub-Bidang UID' : (activeTab === 'UP3_BIDANG' ? 'Tambah Bidang UP3' : 'Tambah Bidang ULP'))"></span>
                            <span class="sm:hidden">Tambah</span>
                        </button>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200" id="ajax-container">
                    <div class="p-0 bg-white overflow-x-auto">
                        
                        <!-- TAB 1: BIDANG UID -->
                        <div x-show="activeTab === 'UID_BIDANG'">
                            <div class="bg-indigo-50/50 px-6 py-3 border-b border-slate-200 text-xs text-indigo-800 font-semibold flex items-center justify-between">
                                <span>🏢 Bidang UID (Puncak Hierarki Organisasi)</span>
                                <span class="bg-indigo-100 px-2 py-0.5 rounded text-[11px] font-bold">{{ $bidangs->where('level', 'UID_BIDANG')->count() }} Item</span>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-slate-50/80 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Nama Bidang (UID)</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Sub-Bidang Terkait</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($bidangs->where('level', 'UID_BIDANG') as $item)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                                            {{ $item->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $item->description ?: '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-indigo-600">
                                            {{ $item->children()->count() }} Sub-Bidang
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->level }}', '{{ $item->parent_id }}', '{{ addslashes($item->description ?? '') }}')" class="text-indigo-600 hover:text-white hover:bg-indigo-600 font-bold px-3 py-1 rounded-md border border-indigo-200 hover:border-transparent transition-all text-xs">Edit</button>
                                            
                                            <form action="{{ route('master-bidangs.destroy', $item->id) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus bidang ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 font-bold px-3 py-1 rounded-md border border-red-200 hover:border-transparent transition-all text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-sm text-slate-500 text-center">Belum ada Bidang UID terdaftar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB 2: SUB-BIDANG UID -->
                        <div x-show="activeTab === 'UID_SUBBIDANG'" style="display: none;">
                            <div class="bg-blue-50/50 px-6 py-3 border-b border-slate-200 text-xs text-blue-800 font-semibold flex items-center justify-between">
                                <span>📂 Sub-Bidang UID (Turunan langsung dari Bidang UID & Induk bagi Bidang UP3)</span>
                                <span class="bg-blue-100 px-2 py-0.5 rounded text-[11px] font-bold">{{ $bidangs->where('level', 'UID_SUBBIDANG')->count() }} Item</span>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-slate-50/80 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Nama Sub-Bidang</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Matrix Group (Bidang)</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Induk (Bidang UID)</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Bidang UP3 Terhubung</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($bidangs->where('level', 'UID_SUBBIDANG') as $item)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                            {{ $item->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-700">
                                            @if($item->name == 'Strategi Pemasaran (MSB)')
                                                NIAGA
                                            @elseif($item->name == 'Pengendalian Operasi dan Pemeliharaan (MSB)')
                                                JARINGAN
                                            @elseif($item->name == 'EPM (MSB)')
                                                TE
                                            @elseif($item->name == 'K3L')
                                                K3L
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-700">
                                            {{ $item->parent ? $item->parent->name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-purple-600">
                                            {{ $item->children()->count() }} Bidang UP3
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->level }}', '{{ $item->parent_id }}', '{{ addslashes($item->description ?? '') }}')" class="text-indigo-600 hover:text-white hover:bg-indigo-600 font-bold px-3 py-1 rounded-md border border-indigo-200 hover:border-transparent transition-all text-xs">Edit</button>
                                            
                                            <form action="{{ route('master-bidangs.destroy', $item->id) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Hapus Sub-Bidang ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 font-bold px-3 py-1 rounded-md border border-red-200 hover:border-transparent transition-all text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-sm text-slate-500 text-center">Belum ada Sub-Bidang UID terdaftar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB 3: BIDANG UP3 -->
                        <div x-show="activeTab === 'UP3_BIDANG'" style="display: none;">
                            <div class="bg-purple-50/50 px-6 py-3 border-b border-slate-200 text-xs text-purple-800 font-semibold flex items-center justify-between">
                                <span>⚡ Bidang UP3 (Turunan dari Sub-Bidang UID & Induk bagi Bidang ULP)</span>
                                <span class="bg-purple-100 px-2 py-0.5 rounded text-[11px] font-bold">{{ $bidangs->where('level', 'UP3_BIDANG')->count() }} Item</span>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-slate-50/80 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Nama Bidang UP3</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Terhubung ke Induk (Sub-Bidang UID)</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Bidang ULP Terhubung</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($bidangs->where('level', 'UP3_BIDANG') as $item)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-purple-500"></div>
                                            {{ $item->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-700">
                                            {{ $item->parent ? $item->parent->name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-emerald-600">
                                            {{ $item->children()->count() }} Bidang ULP
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->level }}', '{{ $item->parent_id }}', '{{ addslashes($item->description ?? '') }}')" class="text-indigo-600 hover:text-white hover:bg-indigo-600 font-bold px-3 py-1 rounded-md border border-indigo-200 hover:border-transparent transition-all text-xs">Edit</button>
                                            
                                            <form action="{{ route('master-bidangs.destroy', $item->id) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Hapus Bidang UP3 ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 font-bold px-3 py-1 rounded-md border border-red-200 hover:border-transparent transition-all text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-sm text-slate-500 text-center">Belum ada Bidang UP3 terdaftar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB 4: BIDANG ULP -->
                        <div x-show="activeTab === 'ULP_BIDANG'" style="display: none;">
                            <div class="bg-emerald-50/50 px-6 py-3 border-b border-slate-200 text-xs text-emerald-800 font-semibold flex items-center justify-between">
                                <span>🔌 Bidang / Seksi ULP (Turunan langsung dari Bidang UP3)</span>
                                <span class="bg-emerald-100 px-2 py-0.5 rounded text-[11px] font-bold">{{ $bidangs->where('level', 'ULP_BIDANG')->count() }} Item</span>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-slate-50/80 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Nama Bidang / Seksi ULP</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Terhubung ke Induk (Bidang UP3)</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($bidangs->where('level', 'ULP_BIDANG') as $item)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                            {{ $item->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-700">
                                            {{ $item->parent ? $item->parent->name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $item->description ?: '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->level }}', '{{ $item->parent_id }}', '{{ addslashes($item->description ?? '') }}')" class="text-indigo-600 hover:text-white hover:bg-indigo-600 font-bold px-3 py-1 rounded-md border border-indigo-200 hover:border-transparent transition-all text-xs">Edit</button>
                                            
                                            <form action="{{ route('master-bidangs.destroy', $item->id) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Hapus Bidang ULP ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-white hover:bg-red-600 font-bold px-3 py-1 rounded-md border border-red-200 hover:border-transparent transition-all text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-sm text-slate-500 text-center">Belum ada Bidang ULP terdaftar.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="openModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 flex flex-col">
                    <form :action="isEdit ? '/master-bidangs/' + formId : '{{ route('master-bidangs.store') }}'" method="POST" class="flex flex-col">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide flex items-center gap-2" id="modal-title">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span x-text="isEdit ? 'Edit Bidang Organisasi' : 'Tambah Bidang Baru'"></span>
                            </h3>
                            <button type="button" @click="openModal = false" class="text-white hover:text-slate-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="bg-white px-6 pt-5 pb-6 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tipe / Tingkat Organisasi</label>
                                <select name="level" x-model="formLevel" @change="onLevelChange()" required class="block w-full py-2.5 px-3 rounded-xl border border-slate-300 font-bold text-indigo-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm shadow-sm bg-slate-50/70">
                                    <option value="UID_BIDANG">1. Bidang Utama (UID)</option>
                                    <option value="UID_SUBBIDANG">2. Sub-Bidang (UID)</option>
                                    <option value="UP3_BIDANG">3. Bidang (UP3)</option>
                                    <option value="ULP_BIDANG">4. Bidang / Seksi (ULP)</option>
                                </select>
                                <div class="mt-1.5 p-2 bg-slate-50 rounded-lg border border-slate-200/60 text-[11px] font-medium text-slate-600">
                                    <span x-show="formLevel === 'UID_BIDANG'">ℹ️ <strong>Bidang UID:</strong> Puncak hierarki (contoh: Niaga dan Manajemen Pelanggan).</span>
                                    <span x-show="formLevel === 'UID_SUBBIDANG'">ℹ️ <strong>Sub-Bidang UID:</strong> Bagian teknis di UID yang terhubung ke <em>Bidang UID</em> (contoh: MSB Pemasaran).</span>
                                    <span x-show="formLevel === 'UP3_BIDANG'">ℹ️ <strong>Bidang UP3:</strong> Bidang di tingkat UP3 yang diturunkan dari <em>Sub-Bidang UID</em> (contoh: Niaga dan Pemasaran Asman).</span>
                                    <span x-show="formLevel === 'ULP_BIDANG'">ℹ️ <strong>Bidang ULP:</strong> Seksi/bagian di ULP yang diturunkan langsung dari <em>Bidang UP3</em>.</span>
                                </div>
                            </div>

                            <div x-show="formLevel !== 'UID_BIDANG'" x-transition>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                                    <span>🔗</span>
                                    <span x-text="parentSelectLabel"></span>
                                </label>
                                <select name="parent_id" x-model="formParentId" :required="formLevel !== 'UID_BIDANG'" class="block w-full py-2.5 px-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm font-semibold text-slate-800 shadow-sm bg-white">
                                    <option value="">-- Pilih Bidang Induk --</option>
                                    <template x-for="p in filteredParents" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="p.id == formParentId"></option>
                                    </template>
                                </select>
                                <p x-show="filteredParents.length === 0 && formLevel !== 'UID_BIDANG'" class="text-[11px] text-rose-500 mt-1.5 font-bold flex items-center gap-1">
                                    <span>⚠️</span> Belum ada data bidang untuk level induk di atasnya.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Nama Bidang / Organisasi</label>
                                <input type="text" name="name" x-model="formName" :placeholder="namePlaceholder" required class="block w-full py-2.5 px-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm font-bold text-slate-800 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Deskripsi & Catatan (Opsional)</label>
                                <textarea name="description" x-model="formDesc" rows="3" placeholder="Tuliskan keterangan singkat atau lingkup tugas..." class="block w-full py-2 px-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-2xl">
                            <button @click="openModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-bold text-white shadow-md hover:from-indigo-700 hover:to-blue-700 transition-all">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bidangForm() {
            return {
                activeTab: '{{ session("active_tab", "UID_BIDANG") }}',
                openModal: false,
                isEdit: false,
                formId: null,
                formName: '',
                formLevel: 'UID_BIDANG',
                formParentId: '',
                formDesc: '',
                allParents: @json($parentBidangs),

                get filteredParents() {
                    if (this.formLevel === 'UID_SUBBIDANG') {
                        return this.allParents.filter(p => p.level === 'UID_BIDANG');
                    }
                    if (this.formLevel === 'UP3_BIDANG') {
                        return this.allParents.filter(p => p.level === 'UID_SUBBIDANG');
                    }
                    if (this.formLevel === 'ULP_BIDANG') {
                        return this.allParents.filter(p => p.level === 'UP3_BIDANG');
                    }
                    return [];
                },

                get parentSelectLabel() {
                    if (this.formLevel === 'UID_SUBBIDANG') return 'Terhubung ke Induk: Bidang Utama (UID)';
                    if (this.formLevel === 'UP3_BIDANG') return 'Terhubung ke Induk: Sub-Bidang (UID)';
                    if (this.formLevel === 'ULP_BIDANG') return 'Terhubung ke Induk: Bidang (UP3)';
                    return 'Pilih Bidang Induk';
                },

                get namePlaceholder() {
                    if (this.formLevel === 'UID_BIDANG') return 'Contoh: Niaga dan Manajemen Pelanggan (SRM)';
                    if (this.formLevel === 'UID_SUBBIDANG') return 'Contoh: Pengendalian Operasi dan Pemeliharaan (MSB)';
                    if (this.formLevel === 'UP3_BIDANG') return 'Contoh: Jaringan (Asman) / Niaga dan Pemasaran (Asman)';
                    if (this.formLevel === 'ULP_BIDANG') return 'Contoh: Seksi Pemasaran ULP / Pelayanan Pelanggan';
                    return 'Nama bidang...';
                },

                onLevelChange() {
                    this.formParentId = '';
                },

                openCreate() {
                    this.isEdit = false;
                    this.formId = null;
                    this.formName = '';
                    this.formLevel = this.activeTab;
                    this.formParentId = '';
                    this.formDesc = '';
                    this.openModal = true;
                },

                openEdit(id, name, level, parent, desc) {
                    this.isEdit = true;
                    this.formId = id;
                    this.formName = name;
                    this.formLevel = level;
                    this.formParentId = parent ? Number(parent) : '';
                    this.formDesc = desc || '';
                    this.openModal = true;
                }
            }
        }
    </script>
</x-app-layout>
