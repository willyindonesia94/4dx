<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Satuan') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ openModal: false, isEdit: false, formId: null, formName: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between sm:justify-end mb-4">
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <form action="{{ route('master-satuans.index') }}" method="GET" class="relative w-full sm:w-auto">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Satuan..." class="w-full sm:w-64 px-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" oninput="performAjaxSearch(this, 'ajax-container')">
                    </form>
                    <button @click="openModal = true; isEdit = false; formName = ''; formId = null;" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow whitespace-nowrap">
                        + Tambah Satuan
                    </button>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="ajax-container">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Satuan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($satuans as $satuan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $satuan->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <button @click="openModal = true; isEdit = true; formName = '{{ $satuan->name }}'; formId = {{ $satuan->id }};" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                    <form action="{{ route('master-satuans.destroy', $satuan->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus satuan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada satuan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal using Alpine.js -->
        <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop with blur -->
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal Panel -->
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                    <form :action="isEdit ? '/master-satuans/' + formId : '{{ route('master-satuans.store') }}'" method="POST">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" x-text="isEdit ? 'Edit Satuan' : 'Tambah Satuan'" id="modal-title"></h3>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6">
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Nama Satuan</label>
                                    <input type="text" name="name" x-model="formName" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
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
</x-app-layout>
