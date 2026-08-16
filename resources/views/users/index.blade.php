<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna & Matrix Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 w-full">
                <!-- Role Tabs Filter -->
                <div class="w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
                    <nav class="flex space-x-2" aria-label="Tabs">
                        <a href="{{ route('users.index') }}" class="{{ !$selectedLevel ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }} px-3 py-2 font-medium text-sm rounded-md whitespace-nowrap transition-colors">
                            Semua Level
                        </a>
                        @foreach($levels as $level)
                            <a href="{{ route('users.index', ['level' => $level]) }}" class="{{ $selectedLevel === $level ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }} px-3 py-2 font-medium text-sm rounded-md whitespace-nowrap transition-colors">
                                {{ $level }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <!-- Search Form -->
                    <form action="{{ route('users.index') }}" method="GET" class="relative w-full sm:w-auto">
                        @if(request('level'))
                            <input type="hidden" name="level" value="{{ request('level') }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau Email..." class="w-full sm:w-64 px-4 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" autofocus onfocus="var val = this.value; this.value = ''; this.value = val;" oninput="performAjaxSearch(this, 'ajax-container')">
                    </form>
                    <a x-data="" x-on:click.prevent="$dispatch('open-modal', 'bulk-upload')" href="#" class="w-full sm:w-auto justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 sm:px-5 rounded-lg shadow flex items-center whitespace-nowrap">
                        <span class="hidden sm:inline">Bulk Upload</span>
                        <span class="sm:hidden">Bulk</span>
                    </a>
                    <a href="{{ route('users.create') }}" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 sm:px-5 rounded-lg shadow flex items-center whitespace-nowrap">
                        <span class="hidden sm:inline">+ Tambah Pengguna</span>
                        <span class="sm:hidden">+ Tambah</span>
                    </a>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" id="ajax-container">
                <div class="p-0 sm:p-6 bg-white border-b border-gray-200 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama & Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Aplikasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Kerja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matrix Group (Bidang)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $user->role_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->unit->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ $user->matrix_group_id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                    <a href="{{ route('users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada pengguna yang ditemukan dengan level ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $users->links() }}
            </div>

            <!-- Bulk Upload Modal -->
            <x-modal name="bulk-upload" focusable maxWidth="4xl">
                <div x-data="bulkUploadPreview()" class="relative">
                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data" id="bulk-upload-form">
                        @csrf
                        
                        <!-- STEP 1: Upload File -->
                        <div x-show="step === 1" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Bulk Upload Pengguna</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 mb-4">Unggah file Excel (xlsx/xls) untuk menambahkan pengguna secara massal. Pastikan format kolom sesuai dengan template.</p>
                                            <a href="{{ route('users.template') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 underline block mb-4">Download Template Excel</a>
                                            
                                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition-colors">
                                                <div class="space-y-1 text-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <div class="flex text-sm text-gray-600 justify-center">
                                                        <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                                            <span>Pilih File Excel</span>
                                                            <input id="file-upload" name="file" type="file" class="sr-only" accept=".xlsx,.xls" required @change="handleFileChange">
                                                        </label>
                                                    </div>
                                                    <p class="text-xs font-bold text-gray-500 mt-2" id="file-name">XLSX atau XLS</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-100 rounded-b-lg">
                                <button type="button" class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm transition-colors" x-on:click="$dispatch('close')">Batal</button>
                                <button type="button" @click="previewData()" :disabled="loading" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:text-sm transition-colors disabled:opacity-50">
                                    <span x-text="loading ? 'Memproses...' : 'Preview Data'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2: Preview Data -->
                        <div x-show="step === 2" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">Preview Data Pengguna</h3>
                                <p class="text-sm text-gray-500 mb-4">Pastikan data berikut sudah benar sebelum disimpan ke dalam sistem.</p>
                                
                                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="(row, index) in paginatedRows" :key="index">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" x-text="row.nama"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="row.email"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="row.role_name"></span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="row.nama_unit"></td>
                                                </tr>
                                            </template>
                                            <tr x-show="rows.length === 0">
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data valid yang ditemukan dalam file ini.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4" x-show="rows.length > 0">
                                    <div class="flex-1 flex justify-between sm:hidden">
                                        <button type="button" @click="prevPage()" :disabled="currentPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Previous</button>
                                        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">Next</button>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Menampilkan <span class="font-medium" x-text="rows.length > 0 ? (currentPage - 1) * perPage + 1 : 0"></span> sampai <span class="font-medium" x-text="Math.min(currentPage * perPage, rows.length)"></span> dari <span class="font-medium" x-text="rows.length"></span> baris
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                <button type="button" @click="prevPage()" :disabled="currentPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 transition-colors">
                                                    <span class="sr-only">Previous</span>
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                </button>
                                                <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 transition-colors">
                                                    <span class="sr-only">Next</span>
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                                                </button>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3 border-t border-gray-100 rounded-b-lg">
                                <button type="button" @click="step = 1" class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm transition-colors">Kembali</button>
                                <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none sm:text-sm transition-colors">Simpan Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </x-modal>

            <script>
                function bulkUploadPreview() {
                    return {
                        step: 1,
                        loading: false,
                        rows: [],
                        currentPage: 1,
                        perPage: 5,
                        
                        get totalPages() {
                            return Math.ceil(this.rows.length / this.perPage) || 1;
                        },
                        
                        get paginatedRows() {
                            const start = (this.currentPage - 1) * this.perPage;
                            const end = start + this.perPage;
                            return this.rows.slice(start, end);
                        },
                        
                        nextPage() {
                            if (this.currentPage < this.totalPages) this.currentPage++;
                        },
                        
                        prevPage() {
                            if (this.currentPage > 1) this.currentPage--;
                        },
                        
                        handleFileChange(e) {
                            if(e.target.files.length > 0) {
                                document.getElementById('file-name').textContent = e.target.files[0].name;
                                document.getElementById('file-name').classList.add('text-green-600');
                            }
                        },
                        
                        async previewData() {
                            const fileInput = document.getElementById('file-upload');
                            if (!fileInput.files.length) {
                                alert('Pilih file Excel terlebih dahulu.');
                                return;
                            }
                            
                            this.loading = true;
                            const formData = new FormData();
                            formData.append('file', fileInput.files[0]);
                            formData.append('_token', '{{ csrf_token() }}');
                            
                            try {
                                const response = await fetch('{{ route("users.preview_import") }}', {
                                    method: 'POST',
                                    body: formData
                                });
                                
                                const result = await response.json();
                                if (result.success) {
                                    this.rows = result.data;
                                    this.step = 2;
                                    this.currentPage = 1;
                                } else {
                                    alert(result.message || 'Terjadi kesalahan saat memproses file. Periksa format Excel Anda.');
                                }
                            } catch (error) {
                                alert('Terjadi kesalahan koneksi saat memproses file.');
                            } finally {
                                this.loading = false;
                            }
                        }
                    }
                }
            </script>
        </div>
    </div>
</x-app-layout>
