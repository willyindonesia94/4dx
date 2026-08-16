<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Unit') }}
        </h2>
    </x-slot>

    <!-- Leaflet JS and CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-12" x-data="unitForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="{ activeTab: 'UID' }">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-4 w-full">
                    <div class="flex space-x-1 bg-white border border-slate-200 p-1 rounded-lg shadow-sm w-full sm:w-auto overflow-x-auto">
                        <button @click="activeTab = 'UID'" :class="activeTab === 'UID' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-md transition-colors whitespace-nowrap">UID</button>
                        <button @click="activeTab = 'UP3'" :class="activeTab === 'UP3' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-md transition-colors whitespace-nowrap">UP3</button>
                        <button @click="activeTab = 'UP2D'" :class="activeTab === 'UP2D' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-md transition-colors whitespace-nowrap">UP2D</button>
                        <button @click="activeTab = 'UP2K'" :class="activeTab === 'UP2K' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-md transition-colors whitespace-nowrap">UP2K</button>
                        <button @click="activeTab = 'ULP'" :class="activeTab === 'ULP' ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-md transition-colors whitespace-nowrap">ULP</button>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <form action="{{ route('master-units.index') }}" method="GET" class="relative w-full sm:w-auto">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Unit..." class="w-full sm:w-64 px-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" oninput="performAjaxSearch(this, 'ajax-container')">
                        </form>
                        <button @click="openCreate()" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 sm:px-5 rounded-lg shadow flex items-center whitespace-nowrap">
                            <span class="hidden sm:inline">+ Tambah Unit</span>
                            <span class="sm:hidden">+ Tambah</span>
                        </button>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="ajax-container">
                    <div class="p-0 sm:p-6 bg-white border-b border-gray-200 overflow-x-auto">
                        @foreach(['UID', 'UP3', 'UP2D', 'UP2K', 'ULP'] as $tabType)
                        <div x-show="activeTab === '{{ $tabType }}'" style="display: none;">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Unit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Induk Unit</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($units->where('type', $tabType) as $unit)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $unit->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $unit->type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $unit->parent ? $unit->parent->name : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEdit({{ $unit->id }}, '{{ $unit->name }}', '{{ $unit->type }}', '{{ $unit->parent_id }}', '{{ $unit->latitude }}', '{{ $unit->longitude }}')" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <form action="{{ route('master-units.destroy', $unit->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus unit ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada unit {{ $tabType }}.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop with blur -->
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal Panel -->
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form :action="isEdit ? '/master-units/' + formId : '{{ route('master-units.store') }}'" method="POST" class="flex flex-col max-h-[90vh]">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" x-text="isEdit ? 'Edit Unit' : 'Tambah Unit'" id="modal-title"></h3>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Nama Unit</label>
                                        <input type="text" name="name" x-model="formName" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tipe Unit</label>
                                        <select name="type" x-model="formType" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                            <option value="UID">UID</option>
                                            <option value="UP3">UP3</option>
                                            <option value="UP2D">UP2D</option>
                                            <option value="UP2K">UP2K</option>
                                            <option value="ULP">ULP</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Induk Unit (Opsional)</label>
                                        <select name="parent_id" x-model="formParentId" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                            <option value="">-- Tidak Ada --</option>
                                            @foreach($parentUnits as $pu)
                                                <option value="{{ $pu->id }}">{{ $pu->name }} ({{ $pu->type }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Latitude</label>
                                            <input type="text" name="latitude" x-model="formLat" class="block w-full py-2 px-3 rounded-lg border-slate-200 bg-slate-100 text-slate-500 text-xs shadow-inner cursor-not-allowed" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Longitude</label>
                                            <input type="text" name="longitude" x-model="formLng" class="block w-full py-2 px-3 rounded-lg border-slate-200 bg-slate-100 text-slate-500 text-xs shadow-inner cursor-not-allowed" readonly>
                                        </div>
                                    </div>
                                    <p class="text-xs text-indigo-500 italic flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Klik pada peta untuk mengatur koordinat lokasi.</p>
                                </div>
                                <div class="border-2 border-slate-100 rounded-md overflow-hidden relative shadow-inner h-full min-h-[300px]">
                                    <div id="unitMap" class="absolute inset-0"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg">
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
    </div>

    <script>
        function unitForm() {
            return {
                openModal: false,
                isEdit: false,
                formId: null,
                formName: '',
                formType: 'ULP',
                formParentId: '',
                formLat: '',
                formLng: '',
                mapInstance: null,
                markerInstance: null,

                initMap() {
                    if (!this.mapInstance) {
                        this.mapInstance = L.map('unitMap').setView([-6.9147, 107.6098], 8); // Default Bandung
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.mapInstance);

                        this.mapInstance.on('click', (e) => {
                            this.formLat = e.latlng.lat.toFixed(6);
                            this.formLng = e.latlng.lng.toFixed(6);
                            this.updateMarker(e.latlng.lat, e.latlng.lng);
                        });
                    }

                    setTimeout(() => {
                        this.mapInstance.invalidateSize();
                        if (this.formLat && this.formLng) {
                            this.updateMarker(this.formLat, this.formLng);
                            this.mapInstance.setView([this.formLat, this.formLng], 12);
                        } else {
                            if (this.markerInstance) {
                                this.mapInstance.removeLayer(this.markerInstance);
                                this.markerInstance = null;
                            }
                            this.mapInstance.setView([-6.9147, 107.6098], 8);
                        }
                    }, 200);
                },

                updateMarker(lat, lng) {
                    if (this.markerInstance) {
                        this.mapInstance.removeLayer(this.markerInstance);
                    }
                    this.markerInstance = L.marker([lat, lng]).addTo(this.mapInstance);
                },

                openCreate() {
                    this.isEdit = false;
                    this.formId = null;
                    this.formName = '';
                    this.formType = 'ULP';
                    this.formParentId = '';
                    this.formLat = '';
                    this.formLng = '';
                    this.openModal = true;
                    this.initMap();
                },

                openEdit(id, name, type, parent, lat, lng) {
                    this.isEdit = true;
                    this.formId = id;
                    this.formName = name;
                    this.formType = type;
                    this.formParentId = parent;
                    this.formLat = lat;
                    this.formLng = lng;
                    this.openModal = true;
                    this.initMap();
                }
            }
        }
    </script>
</x-app-layout>
