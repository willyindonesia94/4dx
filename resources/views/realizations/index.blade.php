<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Riwayat Realisasi Harian') }}
            </h2>
            @role('admin_ulp')
                <button x-data @click="$dispatch('open-modal-create')" class="bg-pln-cyan hover:bg-pln-primary text-white font-bold py-2 px-4 rounded-md transition text-sm shadow-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="hidden sm:inline">Isi Realisasi Harian</span>
                    <span class="sm:hidden">Realisasi</span>
                </button>
            @endrole
        </div>
    </x-slot>

    <div x-data='{
        showModal: false,
        showEditModal: false,
        selectedTargetId: "{{ old('target_id') }}",
        targetsData: {!! isset($availableTargets) ? $availableTargets->map(function($t) { return ["id" => $t->id, "unit" => $t->metric->unit, "target_value" => $t->target_value]; })->values()->toJson() : "[]" !!},
        editData: {},
        get selectedUnit() {
            let t = this.targetsData.find(x => x.id == this.selectedTargetId);
            return t ? t.unit : "";
        },
        openEditModal(data) {
            this.editData = data;
            this.showEditModal = true;
        }
    }' @open-modal-create.window="showModal = true">
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden border border-gray-100">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">Catatan Aktivitas Eksekusi</h3>
                            <p class="text-sm text-gray-500 mt-1">Laporan harian nilai pencapaian Lead Measure dari seluruh unit.</p>
                        </div>
                        <div class="flex w-full md:w-auto items-center">
                            <form method="GET" action="{{ route('realizations.index') }}" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                                <!-- Search -->
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Target/Lokasi/Pelapor..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm w-full md:w-64">
                                
                                <!-- Date Filter -->
                                <input type="date" name="date" value="{{ request('date') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm" title="Filter berdasarkan Tanggal Lapor">

                                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm transition">Cari</button>
                                @if(request('search') || request('date'))
                                    <a href="{{ route('realizations.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 text-sm transition text-center flex items-center justify-center">Reset</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Cards View (Tampil di HP, tanpa geser) -->
                <div class="md:hidden p-4 space-y-4">
                    @forelse($realizations as $realization)
                        <div class="bg-white rounded-md shadow-sm border border-gray-100 p-4 relative flex flex-col gap-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Unit / Lokasi</div>
                                    <div class="font-bold text-gray-900 text-sm">{{ $realization->target->location->name ?? 'N/A' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Nilai Input</div>
                                    <div class="font-black text-green-600 text-lg">+{{ number_format($realization->realization_value, 2) }}</div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">Target Lead Measure</div>
                                <div class="text-sm text-gray-800 font-medium leading-snug mb-1">{{ Str::limit($realization->target->name, 60) }}</div>
                                <div class="text-xs font-semibold text-blue-600">{{ $realization->target->metric->name }}</div>
                            </div>
                            
                            <div class="flex justify-between items-center bg-gray-50 p-2.5 rounded-lg text-[11px] border border-gray-100">
                                <div class="text-gray-600"><span class="font-medium text-gray-400">Tgl:</span> {{ \Carbon\Carbon::parse($realization->report_date)->format('d M Y') }}</div>
                                <div class="text-gray-600 flex items-center gap-1"><span class="font-medium text-gray-400">Oleh:</span> <span class="truncate max-w-[90px] inline-block">{{ $realization->creator->name ?? 'Staf ULP' }}</span></div>
                            </div>

                            @if(auth()->user()->hasRole('superadmin') || (auth()->id() == $realization->created_by && $realization->created_at->diffInHours(now()) <= 24))
                                <div class="flex justify-end gap-2 border-t border-gray-100 pt-3 mt-1">
                                    <button type="button" @click="openEditModal({{ json_encode([
                                        'id' => $realization->id,
                                        'report_date' => \Carbon\Carbon::parse($realization->report_date)->format('Y-m-d'),
                                        'realization_value' => $realization->realization_value,
                                        'notes' => $realization->notes ?? '',
                                        'has_evidence' => !empty($realization->evidence_path),
                                        'target_name' => $realization->target->name,
                                        'unit' => $realization->target->metric->unit,
                                        'target_value' => $realization->target->target_value
                                    ]) }})" class="flex-1 text-blue-700 bg-blue-50 hover:bg-blue-100 font-semibold px-3 py-2 rounded-lg transition text-xs text-center flex justify-center items-center gap-1.5 shadow-sm" title="Edit Realisasi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('realizations.destroy', $realization->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data realisasi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-red-700 bg-red-50 hover:bg-red-100 font-semibold px-3 py-2 rounded-lg transition text-xs text-center flex justify-center items-center gap-1.5 shadow-sm" title="Hapus Realisasi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-gray-500 bg-gray-50 rounded-md border border-gray-200 border-dashed">
                            <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2-2H5a2 2 0 01-2-2z"/></svg>
                            Belum ada aktivitas realisasi.
                        </div>
                    @endforelse
                </div>

                <!-- Desktop Table View (Tampil di layar besar) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Unit / Lokasi</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Target Lead Measure</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Lapor</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Nilai Input</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pelapor</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($realizations as $realization)
                                <tr class="hover:bg-blue-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $realization->target->location->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-[250px] truncate" title="{{ $realization->target->name }}">
                                        {{ Str::limit($realization->target->name, 40) }}
                                        <div class="text-xs font-medium text-blue-600 mt-0.5">{{ $realization->target->metric->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                        {{ \Carbon\Carbon::parse($realization->report_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-green-600 text-right">
                                        +{{ number_format($realization->realization_value, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $realization->creator->name ?? 'Staf ULP' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                        @if(auth()->user()->hasRole('superadmin') || (auth()->id() == $realization->created_by && $realization->created_at->diffInHours(now()) <= 24))
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" @click="openEditModal({{ json_encode([
                                                    'id' => $realization->id,
                                                    'report_date' => \Carbon\Carbon::parse($realization->report_date)->format('Y-m-d'),
                                                    'realization_value' => $realization->realization_value,
                                                    'notes' => $realization->notes ?? '',
                                                    'has_evidence' => !empty($realization->evidence_path),
                                                    'target_name' => $realization->target->name,
                                                    'unit' => $realization->target->metric->unit,
                                                    'target_value' => $realization->target->target_value
                                                ]) }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition" title="Edit Realisasi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <form action="{{ route('realizations.destroy', $realization->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data realisasi ini? Data yang dihapus tidak dapat dikembalikan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition" title="Hapus Realisasi">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs italic" title="Lewat dari 1x24 jam atau bukan pembuat data">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center bg-gray-50">
                                        Belum ada aktivitas realisasi harian yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $realizations->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay with inline style to guarantee opacity without Vite -->
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" style="background-color: rgba(17, 24, 39, 0.6);" aria-hidden="true" @click="showModal = false"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal panel -->
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-50 to-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2" id="modal-title">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Isi Realisasi Harian
                    </h3>
                    <button @click="showModal = false" type="button" class="text-gray-400 hover:text-gray-600 transition bg-white hover:bg-gray-100 rounded-full p-1">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div class="px-6 py-5">
                    @if($errors->any())
                        <div class="mb-5 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-md">
                            <ul class="list-disc list-inside text-xs text-red-700 font-medium">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.querySelector('[x-data]').__x.$data.showModal = true;
                            });
                        </script>
                    @endif
                    
                    <form method="POST" action="{{ route('realizations.store') }}" class="space-y-5" enctype="multipart/form-data">
                        @csrf
                        <div>
                            <x-input-label for="target_id" :value="__('Target Lead Measure')" />
                            <select id="target_id" name="target_id" x-model="selectedTargetId" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required autofocus>
                                <option value="" disabled>-- Pilih Target --</option>
                                @if(isset($availableTargets))
                                    @foreach($availableTargets as $target)
                                        <option value="{{ $target->id }}">
                                            {{ $target->name }} (Target: {{ $target->target_value }} {{ $target->metric->unit }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <x-input-label for="report_date" :value="__('Tanggal Laporan')" />
                            <x-text-input id="report_date" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" type="date" name="report_date" :value="old('report_date', date('Y-m-d'))" required />
                        </div>
                        <div>
                            <x-input-label for="realization_value">Nilai Capaian Harian <span x-show="selectedUnit" x-text="'(' + selectedUnit + ')'"></span></x-input-label>
                            <div class="relative mt-1">
                                <x-text-input id="realization_value" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm pl-4 pr-16 text-sm" type="number" step="0.01" min="0" name="realization_value" :value="old('realization_value')" required placeholder="Contoh: 1.5" />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-500 text-sm font-medium" x-text="selectedUnit"></div>
                            </div>
                        </div>
                        <div>
                            <x-input-label for="evidence" :value="__('Lampiran Bukti (Opsional)')" />
                            <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" class="block w-full mt-1 text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <p class="text-[10px] text-gray-500 mt-1">Format: JPG, PNG, atau PDF. Maks 5MB.</p>
                        </div>
                        <div>
                            <x-input-label for="notes" :value="__('Keterangan Tambahan (Opsional)')" />
                            <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" placeholder="Catatan pekerjaan hari ini...">{{ old('notes') }}</textarea>
                        </div>
                        <div class="flex items-center justify-end mt-4 gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="showModal = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Batal</button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-green-700">Simpan Realisasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" style="background-color: rgba(17, 24, 39, 0.6);" aria-hidden="true" @click="showEditModal = false"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal panel -->
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-50 to-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2" id="modal-title-edit">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Realisasi Harian
                    </h3>
                    <button @click="showEditModal = false" type="button" class="text-gray-400 hover:text-gray-600 transition bg-white hover:bg-gray-100 rounded-full p-1">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div class="px-6 py-5">
                    
                    <form method="POST" :action="`/realizations/${editData.id}`" class="space-y-5" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-5 p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1">Target Lead Measure</div>
                            <h4 class="text-sm font-bold text-gray-900 leading-tight mb-1" x-text="editData.target_name"></h4>
                            <div class="text-xs text-gray-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Target: <span x-text="editData.target_value + ' ' + editData.unit"></span>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="edit_report_date" :value="__('Tanggal Laporan')" />
                            <x-text-input id="edit_report_date" x-model="editData.report_date" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" type="date" name="report_date" required />
                        </div>
                        <div>
                            <x-input-label for="edit_realization_value">Nilai Capaian Harian <span x-text="'(' + editData.unit + ')'"></span></x-input-label>
                            <div class="relative mt-1">
                                <x-text-input id="edit_realization_value" x-model="editData.realization_value" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm pl-4 pr-16 text-sm" type="number" step="0.01" min="0" name="realization_value" required />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-500 text-sm font-medium" x-text="editData.unit"></div>
                            </div>
                        </div>
                        <div>
                            <x-input-label for="edit_evidence" :value="__('Lampiran Bukti Baru (Opsional)')" />
                            <input type="file" id="edit_evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" class="block w-full mt-1 text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <p class="text-[10px] text-gray-500 mt-1">Format: JPG, PNG, atau PDF. Maks 5MB.</p>
                            <div x-show="editData.has_evidence" style="display: none;" class="mt-2 text-[10px] text-green-600 font-medium">
                                ✓ Lampiran saat ini tersedia (akan dipertahankan jika kosong).
                            </div>
                        </div>
                        <div>
                            <x-input-label for="edit_notes" :value="__('Keterangan Tambahan (Opsional)')" />
                            <textarea id="edit_notes" x-model="editData.notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm"></textarea>
                        </div>
                        <div class="flex items-center justify-end mt-4 gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="showEditModal = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Batal</button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
