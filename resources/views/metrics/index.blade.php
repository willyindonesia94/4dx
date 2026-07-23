<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Metrik (Indikator)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        showModal: false,
        isEdit: false,
        formAction: '{{ route('metrics.store') }}',
        
        // Form Fields
        metricId: '',
        metricName: '',
        metricType: 'Leading',
        metricDivision: '',
        metricUnit: '',
        metricPolarity: 'Positive',
        
        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('metrics.store') }}';
            
            this.metricId = '';
            this.metricName = '';
            this.metricType = 'Leading';
            this.metricDivision = '';
            this.metricUnit = '';
            this.metricPolarity = 'Positive';
            
            this.showModal = true;
        },
        
        openEditModal(metric) {
            this.isEdit = true;
            this.formAction = '/metrics/' + metric.id;
            
            this.metricId = metric.id;
            this.metricName = metric.name;
            this.metricType = metric.type;
            this.metricDivision = metric.division_id;
            this.metricUnit = metric.unit;
            this.metricPolarity = metric.polarity;
            
            this.showModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alert Messages -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Indikator (Metrik)</h3>
                        <button @click="openCreateModal()" class="bg-pln-primary hover:bg-blue-800 text-white font-bold py-2 px-4 rounded shadow transition-colors">
                            + Tambah Metrik
                        </button>
                    </div>

                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Metrik</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sifat</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bidang</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($metrics as $metric)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $metric->type == 'Leading' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $metric->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $metric->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $metric->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($metric->polarity == 'Positive')
                                                <span class="text-blue-600 font-bold">↑ Maximize</span>
                                            @else
                                                <span class="text-red-600 font-bold">↓ Minimize</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $metric->division->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openEditModal({{ json_encode($metric) }})" class="text-pln-primary hover:text-blue-800 font-bold mr-3">
                                                Edit
                                            </button>
                                            <form action="{{ route('metrics.destroy', $metric->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus metrik ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        
        <!-- Modal Form (Tambah/Edit) -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-md text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <form method="POST" :action="formAction">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-gradient-to-r from-blue-600 to-pln-cyan px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white tracking-wide" id="modal-title">
                                <span x-text="isEdit ? 'Edit Metrik' : 'Tambah Metrik Baru'"></span>
                            </h3>
                            <button type="button" @click="showModal = false" class="text-white hover:text-gray-200 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="name" :value="__('Nama Metrik')" class="font-bold text-gray-700" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" x-model="metricName" required />
                            </div>
                            
                            <div>
                                <x-input-label for="type" :value="__('Tipe Metrik')" class="font-bold text-gray-700" />
                                <select id="type" name="type" x-model="metricType" class="block mt-1 w-full border-gray-300 focus:border-pln-cyan focus:ring-pln-cyan rounded-md shadow-sm" required>
                                    <option value="Leading">Leading Measure</option>
                                    <option value="Lagging">Lagging Measure</option>
                                </select>
                            </div>
                            
                            <div>
                                <x-input-label for="polarity" :value="__('Sifat Pencapaian')" class="font-bold text-gray-700" />
                                <select id="polarity" name="polarity" x-model="metricPolarity" class="block mt-1 w-full border-gray-300 focus:border-pln-cyan focus:ring-pln-cyan rounded-md shadow-sm" required>
                                    <option value="Positive">↑ Maximize (Makin Besar Makin Baik)</option>
                                    <option value="Negative">↓ Minimize (Makin Kecil Makin Baik)</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="unit" :value="__('Satuan')" class="font-bold text-gray-700" />
                                <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" x-model="metricUnit" required placeholder="Contoh: %, Menit, Pelanggan" />
                            </div>

                            <div>
                                <x-input-label for="division_id" :value="__('Bidang Terkait')" class="font-bold text-gray-700" />
                                <select id="division_id" name="division_id" x-model="metricDivision" class="block mt-1 w-full border-gray-300 focus:border-pln-cyan focus:ring-pln-cyan rounded-md shadow-sm" required>
                                    <option value="" disabled>-- Pilih Bidang --</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="bg-gray-100 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-xl border-t border-gray-200">
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-lg px-6 py-2.5 bg-pln-primary text-white font-bold hover:bg-blue-800 transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="showModal = false" class="w-full sm:w-auto inline-flex justify-center rounded-lg px-6 py-2.5 bg-white text-gray-700 font-bold border border-gray-300 hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
