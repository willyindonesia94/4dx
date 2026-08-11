<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Kalender 4DX (Periode Mingguan)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <form method="GET" action="{{ route('master-periodes.index') }}" class="flex items-center gap-3">
                            <label for="tahun" class="font-semibold text-sm text-gray-700">Filter Tahun:</label>
                            <select name="tahun" id="tahun" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-medium" onchange="this.form.submit()">
                                @for($y = date('Y') - 1; $y <= date('Y') + 3; $y++)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                        
                        <form method="POST" action="{{ route('master-periodes.generate') }}" onsubmit="return confirm('Apakah Anda yakin ingin men-generate jadwal mingguan M1-M5 untuk seluruh bulan di tahun {{ $tahun }} secara otomatis (Rumus Senin Pertama)? \n\nCatatan: Ini akan me-replace konfigurasi tahun {{ $tahun }} jika sudah ada.')">
                            @csrf
                            <input type="hidden" name="tahun" value="{{ $tahun }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Generate Otomatis Kalender {{ $tahun }}
                            </button>
                        </form>
                    </div>

                    @if($periodes->isEmpty())
                        <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Belum Ada Kalender</h3>
                            <p class="mt-1 text-sm text-gray-500">Jadwal mingguan 4DX untuk tahun {{ $tahun }} belum dibuat.</p>
                            <div class="mt-6">
                                <button onclick="document.forms[1].submit()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Generate Sekarang
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Alpine Data for Modal -->
                        <div x-data="{ 
                            editModal: false, 
                            editData: { id: '', bulan: '', tahun: '', start_m1: '', end_m1: '', start_m2: '', end_m2: '', start_m3: '', end_m3: '', start_m4: '', end_m4: '', start_m5: '', end_m5: '' },
                            openEdit(periode) {
                                this.editData = { ...periode };
                                this.editModal = true;
                            }
                        }">
                        
                            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bulan</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Minggu 1</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Minggu 2</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Minggu 3</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Minggu 4</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Minggu 5</th>
                                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Opsi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @php
                                                $monthsIndo = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                                            @endphp
                                            @foreach($periodes as $p)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-indigo-900 bg-gray-50/50">
                                                    {{ $monthsIndo[$p->bulan] }}
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs text-gray-700">
                                                    @if($p->start_m1) <span class="font-medium">{{ \Carbon\Carbon::parse($p->start_m1)->locale('id')->translatedFormat('d M') }}</span> <br> <span class="text-gray-400 text-[10px]">s/d</span> <br> <span class="font-medium">{{ \Carbon\Carbon::parse($p->end_m1)->locale('id')->translatedFormat('d M') }}</span> @else - @endif
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs text-gray-700">
                                                    @if($p->start_m2) <span class="font-medium">{{ \Carbon\Carbon::parse($p->start_m2)->locale('id')->translatedFormat('d M') }}</span> <br> <span class="text-gray-400 text-[10px]">s/d</span> <br> <span class="font-medium">{{ \Carbon\Carbon::parse($p->end_m2)->locale('id')->translatedFormat('d M') }}</span> @else - @endif
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs text-gray-700">
                                                    @if($p->start_m3) <span class="font-medium">{{ \Carbon\Carbon::parse($p->start_m3)->locale('id')->translatedFormat('d M') }}</span> <br> <span class="text-gray-400 text-[10px]">s/d</span> <br> <span class="font-medium">{{ \Carbon\Carbon::parse($p->end_m3)->locale('id')->translatedFormat('d M') }}</span> @else - @endif
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs text-gray-700">
                                                    @if($p->start_m4) <span class="font-medium">{{ \Carbon\Carbon::parse($p->start_m4)->locale('id')->translatedFormat('d M') }}</span> <br> <span class="text-gray-400 text-[10px]">s/d</span> <br> <span class="font-medium">{{ \Carbon\Carbon::parse($p->end_m4)->locale('id')->translatedFormat('d M') }}</span> @else - @endif
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap text-center text-xs text-gray-700">
                                                    @if($p->start_m5) <span class="font-medium">{{ \Carbon\Carbon::parse($p->start_m5)->locale('id')->translatedFormat('d M') }}</span> <br> <span class="text-gray-400 text-[10px]">s/d</span> <br> <span class="font-medium">{{ \Carbon\Carbon::parse($p->end_m5)->locale('id')->translatedFormat('d M') }}</span> @else - @endif
                                                </td>
                                                <td class="px-5 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <button @click="openEdit({{ json_encode($p) }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Edit Modal -->
                            <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                                    <div x-show="editModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm" @click="editModal = false"></div>

                                    <div x-show="editModal" x-transition class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-2xl sm:my-8 sm:w-full sm:max-w-3xl sm:align-middle">
                                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-800 flex justify-between items-center">
                                            <h3 class="text-lg font-bold text-white tracking-wide">Sesuaikan Kalender M1-M5</h3>
                                            <button @click="editModal = false" class="text-white hover:text-indigo-200 transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        
                                        <form :action="`/master-periodes/${editData.id}`" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="px-6 py-5 bg-white">
                                                <div class="mb-5 bg-blue-50/80 p-4 rounded-lg border border-blue-100 text-sm text-blue-800 flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <div>
                                                        Anda mengedit periode <strong>Bulan ke-<span x-text="editData.bulan"></span> Tahun <span x-text="editData.tahun"></span></strong>. Sistem secara bawaan membuat siklus Senin-Minggu. Anda dapat menggesernya secara manual jika terdapat kebijakan libur atau geser minggu kerja. <em>(Kosongkan tanggal jika M5 tidak tersedia)</em>.
                                                    </div>
                                                </div>
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                    <!-- M1 -->
                                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                                                        <h4 class="font-bold text-gray-700 text-sm mb-3 border-b border-gray-200 pb-2">Minggu 1</h4>
                                                        <div class="flex gap-3">
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Mulai</label>
                                                                <input type="text" name="start_m1" x-model="editData.start_m1" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.start_m1', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Akhir</label>
                                                                <input type="text" name="end_m1" x-model="editData.end_m1" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.end_m1', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- M2 -->
                                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                                                        <h4 class="font-bold text-gray-700 text-sm mb-3 border-b border-gray-200 pb-2">Minggu 2</h4>
                                                        <div class="flex gap-3">
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Mulai</label>
                                                                <input type="text" name="start_m2" x-model="editData.start_m2" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.start_m2', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Akhir</label>
                                                                <input type="text" name="end_m2" x-model="editData.end_m2" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.end_m2', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- M3 -->
                                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                                                        <h4 class="font-bold text-gray-700 text-sm mb-3 border-b border-gray-200 pb-2">Minggu 3</h4>
                                                        <div class="flex gap-3">
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Mulai</label>
                                                                <input type="text" name="start_m3" x-model="editData.start_m3" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.start_m3', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Akhir</label>
                                                                <input type="text" name="end_m3" x-model="editData.end_m3" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.end_m3', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- M4 -->
                                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                                                        <h4 class="font-bold text-gray-700 text-sm mb-3 border-b border-gray-200 pb-2">Minggu 4</h4>
                                                        <div class="flex gap-3">
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Mulai</label>
                                                                <input type="text" name="start_m4" x-model="editData.start_m4" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.start_m4', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Akhir</label>
                                                                <input type="text" name="end_m4" x-model="editData.end_m4" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.end_m4', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- M5 -->
                                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm md:col-span-2 md:w-1/2 md:mx-auto">
                                                        <h4 class="font-bold text-gray-700 text-sm mb-3 border-b border-gray-200 pb-2 text-center">Minggu 5 (Opsional)</h4>
                                                        <div class="flex gap-3">
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1 text-center">Mulai</label>
                                                                <input type="text" name="start_m5" x-model="editData.start_m5" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.start_m5', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                                            </div>
                                                            <div class="w-1/2">
                                                                <label class="block text-xs font-semibold text-gray-500 mb-1 text-center">Akhir</label>
                                                                <input type="text" name="end_m5" x-model="editData.end_m5" x-init="let fp = flatpickr($el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', onOpen: function(selectedDates, dateStr, instance) { if (selectedDates.length === 0 && editData.tahun && editData.bulan) { instance.jumpToDate(new Date(editData.tahun, editData.bulan - 1, 1)); } } }); $watch('editData.end_m5', val => fp.setDate(val))" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-6 py-4 bg-gray-50 sm:flex sm:flex-row-reverse border-t border-gray-200">
                                                <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                                    Simpan Perubahan
                                                </button>
                                                <button type="button" @click="editModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Pustaka Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
</x-app-layout>
