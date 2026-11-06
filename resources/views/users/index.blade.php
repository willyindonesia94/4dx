<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna & Matrix Role') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        showImpersonateModal: false,
        impersonateUserId: null,
        impersonateUserName: '',
        impersonateUserRole: '',
        impersonateUserUnit: '',
        openImpersonateModal(id, name, role, unit) {
            this.impersonateUserId = id;
            this.impersonateUserName = name;
            this.impersonateUserRole = role;
            this.impersonateUserUnit = unit;
            this.showImpersonateModal = true;
            this.$nextTick(() => {
                const form = document.getElementById('form-impersonate');
                if (form) {
                    form.action = '/users/' + id + '/impersonate';
                }
            });
        }
    }">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
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
                    <a href="{{ route('users.create', $selectedLevel ? ['level' => $selectedLevel] : []) }}" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 sm:px-5 rounded-lg shadow flex items-center whitespace-nowrap">
                        <span class="hidden sm:inline">+ Tambah Pengguna</span>
                        <span class="sm:hidden">+ Tambah</span>
                    </a>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200" id="ajax-container">
                <div class="p-0 overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama & Username</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Role Aplikasi</th>
                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Unit Kerja</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Matrix Group</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($users as $user)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-4 py-2.5">
                                    <div class="text-sm font-semibold text-gray-900 leading-snug">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $user->username }}</div>
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $user->role_name }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-gray-600 font-medium whitespace-nowrap">{{ $user->unit->name ?? '-' }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-center text-xs">
                                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                        {{ $user->matrix_group_id }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-center text-xs">
                                    @if($user->hasRole('Super Admin'))
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Permanent</span>
                                    @else
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" {{ $user->is_active ? 'checked' : '' }} onchange="toggleUserStatus(this, '{{ route('users.toggle_active', $user->id) }}')" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <div class="w-10 h-5.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-green-600 {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                                        </label>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-center text-xs">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        @if(auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID']) && !session()->has('impersonator_id') && $user->id !== auth()->id() && $user->is_active)
                                            <button type="button"
                                                @click="openImpersonateModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->role_name ?? '') }}', '{{ addslashes($user->unit->name ?? 'UID') }}')"
                                                class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200 hover:border-transparent px-2 py-1 rounded-md text-xs font-semibold transition-all duration-200 shadow-sm"
                                                title="Masuk sebagai {{ $user->name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                                Masuk Sebagai
                                            </button>
                                        @endif
                                        <a href="{{ route('users.edit', ['user' => $user->id] + ($selectedLevel ? ['level' => $selectedLevel] : [])) }}"
                                           class="inline-flex items-center gap-1 text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-transparent px-2 py-1 rounded-md text-xs font-semibold transition-all duration-200 shadow-sm"
                                           title="Edit pengguna">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('users.destroy', ['user' => $user->id] + ($selectedLevel ? ['level' => $selectedLevel] : [])) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Hapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 text-red-700 bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 hover:border-transparent px-2 py-1 rounded-md text-xs font-semibold transition-all duration-200 shadow-sm"
                                                title="Hapus pengguna">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada pengguna yang ditemukan dengan level ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Links & Filter -->
            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('users.index') }}" method="GET" class="flex items-center gap-2 text-sm text-gray-600 bg-white px-3 py-2 rounded-lg shadow-sm border border-gray-200">
                    @if(request('level'))
                        <input type="hidden" name="level" value="{{ request('level') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <label for="per_page_bottom" class="font-medium whitespace-nowrap">Tampilkan:</label>
                    <select name="per_page" id="per_page_bottom" onchange="this.form.submit()" class="pl-2 pr-8 py-1 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>
                </form>

                <div class="w-full sm:flex-1">
                    {{ $users->links() }}
                </div>
            </div>

            <!-- Bulk Upload Modal -->
            <x-modal name="bulk-upload" focusable maxWidth="4xl">
                <div x-data="bulkUploadPreview()" class="relative">
                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data" id="bulk-upload-form" @submit="isSubmitting = true">
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
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Username</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="(row, index) in paginatedRows" :key="index">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" x-text="row.nama"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="row.username"></td>
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
                                <button type="submit" :disabled="isSubmitting" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none sm:text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Simpan Data'"></span>
                                </button>
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
                        isSubmitting: false,
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

                function toggleUserStatus(checkbox, url) {
                    const label = checkbox.parentElement.querySelector('.status-label');
                    const isChecked = checkbox.checked;
                    const originalText = label.innerText;
                    label.innerText = 'Menyimpan...';
                    checkbox.disabled = true;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network error');
                        }
                        return response.json();
                    })
                    .then(data => {
                        checkbox.disabled = false;
                        if (data.success) {
                            label.innerText = data.is_active ? 'Aktif' : 'Nonaktif';
                        } else {
                            checkbox.checked = !isChecked;
                            label.innerText = originalText;
                            alert(data.message || 'Gagal mengubah status');
                        }
                    })
                    .catch(error => {
                        checkbox.disabled = false;
                        checkbox.checked = !isChecked;
                        label.innerText = originalText;
                        alert('Terjadi kesalahan jaringan atau koneksi terputus. Pastikan file kode sudah diperbarui di server.');
                    });
                }
            </script>

            <!-- Impersonate Confirmation Modal (Standard Style) -->
            <div x-show="showImpersonateModal" class="fixed inset-0 z-[110] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showImpersonateModal = false" x-show="showImpersonateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    
                    <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100"
                        x-show="showImpersonateModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        
                        <!-- Header -->
                        <div class="bg-emerald-50 p-6 border-b border-emerald-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-emerald-950">Konfirmasi Akses Pengguna</h3>
                                    <p class="text-xs text-emerald-700 mt-0.5">Masuk sebagai pengguna tanpa kata sandi (Impersonation)</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Body -->
                        <div class="p-6 space-y-4" style="padding: 1.5rem;">
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Apakah Anda yakin ingin masuk ke dalam sistem menggunakan akun ini?
                            </p>
                            
                            <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-sm" style="padding: 16px; margin-top: 12px; margin-bottom: 16px;">
                                <div class="font-bold text-slate-900 text-base tracking-wide" x-text="impersonateUserName" style="margin-bottom: 8px;"></div>
                                <div class="flex flex-wrap items-center gap-2" style="gap: 8px;">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200" x-text="impersonateUserRole"></span>
                                    <span class="inline-flex items-center text-xs text-slate-600 font-medium" x-text="'Unit: ' + impersonateUserUnit"></span>
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-start gap-2.5" style="padding: 12px;">
                                <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="leading-relaxed">Seluruh tampilan, data, dan hak akses aplikasi akan berubah sesuai pengguna ini. Anda dapat kembali ke akun utama kapan saja melalui tombol di bagian atas layar.</span>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:justify-end gap-2.5">
                            <button @click="showImpersonateModal = false" type="button" class="w-full sm:w-auto px-4 py-2 border border-slate-200 rounded-md text-slate-700 font-semibold hover:bg-slate-100 text-xs transition-colors">
                                Batal
                            </button>
                            <form id="form-impersonate" :action="'/users/' + impersonateUserId + '/impersonate'" method="POST" class="inline m-0">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md text-xs shadow-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                    Masuk Sebagai Pengguna
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
