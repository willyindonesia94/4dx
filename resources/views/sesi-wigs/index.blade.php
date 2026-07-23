<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sesi WIG') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="sesiWigForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4 px-2 sm:px-0">
                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                <button @click="openModal = true;" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow">
                    + Generate Sesi WIG 1 Bulan
                </button>
                @endif
            </div>
            
            <div class="bg-transparent sm:bg-white sm:shadow-sm sm:rounded-lg">
                <div class="p-0 sm:p-6 bg-transparent sm:bg-white sm:border-b border-gray-200">
                    <div class="space-y-4">
                        @forelse($sesis as $sesi)
                        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-50 p-3 rounded-lg text-blue-600 hidden sm:block">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-500 mb-1">{{ \Carbon\Carbon::parse($sesi->tanggal_pelaksanaan)->translatedFormat('l, d M Y') }}</div>
                                    <div class="text-lg font-bold text-gray-900 mb-2">{{ $sesi->nama_sesi }}</div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($sesi->tipe_sesi == 'Mingguan')
                                            <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-200">Mingguan</span>
                                        @else
                                            <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-200">Bulanan</span>
                                        @endif
                                        <span class="text-xs text-gray-600 font-bold bg-gray-100 px-2.5 py-0.5 rounded-full border border-gray-200">{{ implode(', ', $sesi->level_terlibat ?? []) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end gap-3 mt-2 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-0 border-gray-100 w-full sm:w-auto">
                                <a href="{{ route('sesi-wigs.show', $sesi->id) }}" class="flex-1 sm:flex-none text-center inline-flex justify-center items-center px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg transition-colors shadow-sm font-bold text-sm">
                                    Masuk Sesi &rarr;
                                </a>
                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                <form action="{{ route('sesi-wigs.destroy', $sesi->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2.5 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-bold rounded-lg transition-colors text-sm border border-red-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span class="hidden sm:inline">Hapus</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="bg-white p-8 rounded-xl shadow-sm border border-dashed border-gray-300 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-gray-500 font-bold text-lg">Belum ada sesi WIG yang dibuat.</p>
                            <p class="text-gray-400 text-sm mt-1">Klik tombol "+ Generate Sesi" di atas untuk memulai.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate Modal using Alpine.js -->
        <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form action="{{ route('sesi-wigs.generate') }}" method="POST" class="flex flex-col max-h-[90vh]">
                        @csrf
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">Generate Sesi WIG 1 Bulan</h3>
                            <button type="button" @click="openModal = false" class="text-white/80 hover:text-white hover:bg-white/20 rounded-full p-1.5 transition-colors focus:outline-none focus:ring-2 focus:ring-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tahun</label>
                                        <select name="tahun" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm text-slate-700">
                                            @for($i = date('Y') - 1; $i <= date('Y') + 2; $i++)
                                                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Bulan</label>
                                        <select name="bulan" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm text-slate-700">
                                            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $b)
                                                <option value="{{ $index + 1 }}" {{ ($index + 1) == date('m') ? 'selected' : '' }}>{{ $b }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Hari Sesi Mingguan</label>
                                        <select name="hari_mingguan" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm text-slate-700">
                                            <option value="1" selected>Senin</option>
                                            <option value="2">Selasa</option>
                                            <option value="3">Rabu</option>
                                            <option value="4">Kamis</option>
                                            <option value="5">Jumat</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tanggal Sesi Bulanan</label>
                                        <input type="number" min="1" max="31" value="25" name="tanggal_bulanan" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm text-slate-700">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Level Unit Yang Diundang</label>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="level_terlibat[]" value="UID" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700 font-medium">UID</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="level_terlibat[]" value="UP3" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700 font-medium">UP3</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="level_terlibat[]" value="ULP" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700 font-medium">ULP</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="openModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                Generate Sesi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sesiWigForm() {
            return {
                openModal: false
            }
        }
    </script>
</x-app-layout>
