<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cascading LM') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data='{ 
        activeWig: null, openBreakdownModal: false, editMode: false, editBreakdownId: null,
        formLmId: null, formLmTitle: "", formType: "uid", availableUnitsData: @json($availableUnits),
        formUnitId: "", formBidang: "", formAngkaTarget: null, formSatuanId: "", formPeriodeStart: "", formPeriodeEnd: "",
        targetM1: null, targetM2: null, targetM3: null, targetM4: null, targetM5: null, isAutoFill: true,
        openEditModal(bw, title, type) {
            this.editMode = true; this.editBreakdownId = bw.id; this.formLmId = bw.lm_id; this.formLmTitle = title;
            this.formType = type; this.formUnitId = bw.unit_id; this.formBidang = bw.bidang || ""; 
            this.formAngkaTarget = bw.angka_target; this.formSatuanId = bw.satuan_id; 
            this.formPeriodeStart = bw.periode_start ? bw.periode_start.split("T")[0] : ""; 
            this.formPeriodeEnd = bw.periode_end ? bw.periode_end.split("T")[0] : ""; 
            this.openBreakdownModal = true;
        },
        openAddModal(id, title, type) {
            this.editMode = false; this.editBreakdownId = null; this.formLmId = id; this.formLmTitle = title;
            this.formType = type; this.formUnitId = ""; this.formBidang = ""; this.formAngkaTarget = null;
            this.formSatuanId = ""; this.formPeriodeStart = ""; this.formPeriodeEnd = "";
            this.targetM1 = null; this.targetM2 = null; this.targetM3 = null; this.targetM4 = null; this.targetM5 = null; this.isAutoFill = true;
            this.openBreakdownModal = true;
        },
        autoCalcWeekly() {
            if (this.isAutoFill && this.formAngkaTarget > 0 && !this.editMode) {
                let val = (parseFloat(this.formAngkaTarget) / 5).toFixed(2);
                this.targetM1 = val; this.targetM2 = val; this.targetM3 = val; this.targetM4 = val; this.targetM5 = val;
            }
        },
        manualWeeklyEdit() {
            this.isAutoFill = false;
        }
    }'>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <p class="text-gray-600">Berikut adalah daftar Lead Measures (LM) yang dikelompokkan berdasarkan Master WIG induknya beserta penjabarannya ke unit operasional.</p>
                        
                        <!-- Mass Upload Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('cascading.lm.template') }}" class="whitespace-nowrap justify-center inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-md text-sm font-semibold transition-colors">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Template LM
                            </a>
                            <button onclick="document.getElementById('uploadMassalForm').classList.toggle('hidden'); document.getElementById('uploadTargetForm').classList.add('hidden')" class="whitespace-nowrap justify-center inline-flex items-center px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md text-sm font-bold transition-colors">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Upload Master LM
                            </button>
                            <button onclick="document.getElementById('uploadTargetForm').classList.toggle('hidden'); document.getElementById('uploadMassalForm').classList.add('hidden')" class="whitespace-nowrap justify-center inline-flex items-center px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-md text-sm font-bold transition-colors">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Upload Target Unit
                            </button>
                        </div>
                    </div>

                    <!-- Upload Form (Hidden by default) -->
                    <div id="uploadMassalForm" class="hidden mb-8 bg-slate-50 border border-slate-200 rounded-xl p-6">
                        <h3 class="font-bold text-slate-800 text-lg mb-2">Upload Excel Massal LM</h3>
                        <p class="text-sm text-slate-600 mb-4">Pastikan format kolom sesuai dengan template. Sistem akan memasukkan LM ke dalam WIG yang namanya sama persis. Jika WIG tidak ditemukan, baris akan dilewati.</p>
                        <form action="{{ route('cascading.lm.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            @csrf
                            <div class="flex-1 w-full">
                                <input type="file" name="file_excel" accept=".xlsx, .xls" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md bg-white">
                            </div>
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md transition-colors">
                                Proses Upload
                            </button>
                        </form>
                    </div>

                    <!-- Upload Target Form (Hidden by default) -->
                    <div id="uploadTargetForm" class="hidden mb-8 bg-green-50 border border-green-200 rounded-xl p-6">
                        <h3 class="font-bold text-green-800 text-lg mb-2">Upload Excel Target Unit (Breakdown LM)</h3>
                        <p class="text-sm text-green-700 mb-4">Pastikan format kolom sesuai dengan template (ada Target Bulanan & Target Minggu 1-5). Pilih bulan dan tahun target tersebut akan diterapkan.</p>
                        <form action="{{ route('cascading.breakdown.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-end">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Bulan</label>
                                <select name="bulan" required class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun</label>
                                <select name="tahun" required class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                    @foreach(range(date('Y')-1, date('Y')+2) as $y)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 w-full">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">File Excel</label>
                                <input type="file" name="file_excel" accept=".xlsx, .xls" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-100 file:text-green-700 hover:file:bg-green-200 border border-slate-300 rounded-md bg-white">
                            </div>
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md transition-colors">
                                Proses Upload
                            </button>
                        </form>
                    </div>

                    @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md shadow-sm">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-md shadow-sm">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    @endif
                    @if(session('warning_skipped'))
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded-r-md shadow-sm">
                        <p class="text-sm font-medium text-yellow-800">{{ session('warning_skipped') }}</p>
                    </div>
                    @endif

                    <div class="space-y-4">
                        @foreach($wigs as $wig)
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <button @click="activeWig = activeWig === {{ $wig->id }} ? null : {{ $wig->id }}" class="w-full flex justify-between items-start px-6 py-4 bg-gray-50 hover:bg-gray-100 transition-colors focus:outline-none text-left">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-500 mr-3 mt-0.5 transform transition-transform flex-shrink-0" :class="{'rotate-90': activeWig === {{ $wig->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $wig->judul }}</h3>
                                        @if($wig->deskripsi)
                                            <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ $wig->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full flex-shrink-0 mt-0.5">{{ $wig->masterLms->count() }} LMs</span>
                            </button>
                            
                            <div x-show="activeWig === {{ $wig->id }}" x-collapse class="border-t border-gray-200 bg-white">


                                @if($wig->masterLms->count() > 0)
                                    @php 
                                        $groupedLms = $wig->masterLms->groupBy('tujuan_unit_role');
                                    @endphp
                                    
                                    @foreach($groupedLms as $role => $lmsInRole)
                                        <div class="px-6 py-2 bg-blue-50 border-b border-blue-100 font-semibold text-blue-800 text-sm flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            Level: {{ $role ?: 'Tidak ada Tujuan Unit' }}
                                        </div>
                                        <ul class="divide-y divide-gray-100">
                                            @foreach($lmsInRole as $lm)
                                            <li class="px-6 py-4 border-l-4 border-blue-400" x-data="{ openUid: false, openUp3: false, openUlp: false }">
                                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center pl-4">
                                                    <div>
                                                        <h4 class="text-md font-semibold text-gray-800">{{ $lm->judul_lm }}</h4>
                                                    </div>
                                                    <div class="mt-2 sm:mt-0 flex items-center space-x-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ ($wig->polaritas ?? 'positif') == 'positif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            Polaritas {{ ucfirst($wig->polaritas ?? 'positif') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                @php
                                                    $uidLmBreakdowns = $lm->breakdowns ? $lm->breakdowns->filter(function($b) { return $b->unit && $b->unit->type === 'UID'; }) : collect();
                                                    $up3LmBreakdowns = $lm->breakdowns ? $lm->breakdowns->filter(function($b) { return $b->unit && $b->unit->type === 'UP3'; }) : collect();
                                                    $ulpLmBreakdowns = $lm->breakdowns ? $lm->breakdowns->filter(function($b) { return $b->unit && $b->unit->type === 'ULP'; }) : collect();
                                                @endphp

                                                <div class="mt-4 ml-4 space-y-3">
                                                    <!-- UID Section -->
                                                    <div class="bg-indigo-50 rounded-lg border border-indigo-100 overflow-hidden">
                                                        <div @click="openUid = !openUid" class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-center px-4 py-3 hover:bg-indigo-100 transition-colors cursor-pointer focus:outline-none gap-2">
                                                            <div class="flex items-center">
                                                                <span class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Breakdown UID</span>
                                                                <span class="ml-3 bg-white text-indigo-700 border border-indigo-200 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $uidLmBreakdowns->count() }} Target Unit</span>
                                                            </div>
                                                            <div class="flex items-center space-x-3 w-full sm:w-auto justify-between sm:justify-end">
                                                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                                                <button @click.stop="openAddModal({{ $lm->id }}, '{{ $lm->judul_lm }}', 'uid')" class="text-[10px] bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1 px-2 rounded transition-colors shadow-sm">+ Breakdown UID</button>
                                                                @endif
                                                                <svg class="w-4 h-4 text-indigo-500 transform transition-transform" :class="{'rotate-180': openUid}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                            </div>
                                                        </div>
                                                        <div x-show="openUid" x-collapse>
                                                            @if($uidLmBreakdowns->count() > 0)
                                                            <div class="overflow-x-auto border-t border-indigo-100">
                                                                <table class="min-w-full text-xs text-left">
                                                                    <thead class="text-indigo-900 border-b border-indigo-100 bg-indigo-50/50">
                                                                        <tr>
                                                                            <th class="px-4 py-2 font-medium">Unit</th>
                                                                            <th class="px-4 py-2 font-medium">Bidang</th>
                                                                            <th class="px-4 py-2 font-medium text-right">Target</th>
                                                                            <th class="px-4 py-2 font-medium">Periode</th>
                                                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                                                            <th class="px-4 py-2 font-medium text-center">Aksi</th>
                                                                            @endif
                                                                        </tr>
                                                                    </thead>
                                                                        @php
                                                                            $groupedUid = $uidLmBreakdowns->sortBy('periode_start')->groupBy(function($item) {
                                                                                return \Carbon\Carbon::parse($item->periode_start)->format('M Y');
                                                                            });
                                                                        @endphp
                                                                        @foreach($groupedUid as $month => $items)
                                                                        <tbody x-data="{ openMonth: true }" class="divide-y divide-indigo-50 bg-white border-b border-indigo-100/50">
                                                                            <tr class="bg-indigo-100/50 cursor-pointer hover:bg-indigo-200/50 transition-colors" @click="openMonth = !openMonth">
                                                                                <td colspan="{{ in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']) ? '5' : '4' }}" class="px-4 py-2 font-bold text-indigo-800 text-xs uppercase tracking-wider">
                                                                                    <div class="flex justify-between items-center">
                                                                                        <span>Target {{ $month }}</span>
                                                                                        <svg class="w-4 h-4 transform transition-transform duration-200" :class="{'rotate-180': openMonth}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            @foreach($items->sortBy(function($b) { return $b->unit->name ?? ''; }) as $breakdown)
                                                                            <tr x-show="openMonth">
                                                                                <td class="px-4 py-2 font-semibold text-gray-700">{{ $breakdown->unit->name ?? '-' }}</td>
                                                                                <td class="px-4 py-2 text-gray-600">{{ $breakdown->bidang ?? '-' }}</td>
                                                                                <td class="px-4 py-2 text-right font-bold text-gray-800">{{ number_format($breakdown->angka_target, 2) }} {{ $breakdown->satuan->name ?? '' }}</td>
                                                                                <td class="px-4 py-2 text-gray-500">{{ \Carbon\Carbon::parse($breakdown->periode_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($breakdown->periode_end)->format('d M Y') }}</td>
                                                                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                                                                <td class="px-4 py-2 text-center space-x-2 whitespace-nowrap">
                                                                                    <button type="button" @click='openEditModal({{ $breakdown->toJson() }}, "{{ $lm->judul_lm }}", "uid")' class="text-blue-500 hover:text-blue-700 font-bold transition-colors text-xs">Edit</button>
                                                                                    <form action="{{ route('cascading.breakdown.destroy', $breakdown->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus breakdown LM ini?');">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">Hapus</button>
                                                                                    </form>
                                                                                </td>
                                                                                @endif
                                                                            </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                        @endforeach
                                                                </table>
                                                            </div>
                                                            @else
                                                            <div class="px-4 py-3 text-xs text-indigo-500 italic bg-white border-t border-indigo-100">Belum ada target UID</div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- UP3 Section -->
                                                    <div class="bg-emerald-50 rounded-lg border border-emerald-100 overflow-hidden">
                                                        <div @click="openUp3 = !openUp3" class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-center px-4 py-3 hover:bg-emerald-100 transition-colors cursor-pointer focus:outline-none gap-2">
                                                            <div class="flex items-center">
                                                                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Breakdown UP3</span>
                                                                <span class="ml-3 bg-white text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $up3LmBreakdowns->count() }} Target Unit</span>
                                                            </div>
                                                            <div class="flex items-center space-x-3 w-full sm:w-auto justify-between sm:justify-end">
                                                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                                                <button @click.stop="openAddModal({{ $lm->id }}, '{{ $lm->judul_lm }}', 'up3')" class="text-[10px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-2 rounded transition-colors shadow-sm">+ Breakdown UP3</button>
                                                                @endif
                                                                <svg class="w-4 h-4 text-emerald-500 transform transition-transform" :class="{'rotate-180': openUp3}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                            </div>
                                                        </div>
                                                        <div x-show="openUp3" x-collapse>
                                                            @if($up3LmBreakdowns->count() > 0)
                                                            <div class="overflow-x-auto border-t border-emerald-100">
                                                                <table class="min-w-full text-xs text-left">
                                                                    <thead class="text-emerald-900 border-b border-emerald-100 bg-emerald-50/50">
                                                                        <tr>
                                                                            <th class="px-4 py-2 font-medium">Unit</th>
                                                                            <th class="px-4 py-2 font-medium">Bidang</th>
                                                                            <th class="px-4 py-2 font-medium text-right">Target</th>
                                                                            <th class="px-4 py-2 font-medium">Periode</th>
                                                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                                                            <th class="px-4 py-2 font-medium text-center">Aksi</th>
                                                                            @endif
                                                                        </tr>
                                                                    </thead>
                                                                        @php
                                                                            $groupedUp3 = $up3LmBreakdowns->sortBy('periode_start')->groupBy(function($item) {
                                                                                return \Carbon\Carbon::parse($item->periode_start)->format('M Y');
                                                                            });
                                                                        @endphp
                                                                        @foreach($groupedUp3 as $month => $items)
                                                                        <tbody x-data="{ openMonth: true }" class="divide-y divide-emerald-50 bg-white border-b border-emerald-100/50">
                                                                            <tr class="bg-emerald-100/50 cursor-pointer hover:bg-emerald-200/50 transition-colors" @click="openMonth = !openMonth">
                                                                                <td colspan="{{ in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']) ? '5' : '4' }}" class="px-4 py-2 font-bold text-emerald-800 text-xs uppercase tracking-wider">
                                                                                    <div class="flex justify-between items-center">
                                                                                        <span>Target {{ $month }}</span>
                                                                                        <svg class="w-4 h-4 transform transition-transform duration-200" :class="{'rotate-180': openMonth}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            @foreach($items->sortBy(function($b) { return $b->unit->name ?? ''; }) as $breakdown)
                                                                            <tr x-show="openMonth">
                                                                                <td class="px-4 py-2 font-semibold text-gray-700">{{ $breakdown->unit->name ?? '-' }}</td>
                                                                                <td class="px-4 py-2 text-gray-600">{{ $breakdown->bidang ?? '-' }}</td>
                                                                                <td class="px-4 py-2 text-right font-bold text-gray-800">{{ number_format($breakdown->angka_target, 2) }} {{ $breakdown->satuan->name ?? '' }}</td>
                                                                                <td class="px-4 py-2 text-gray-500">{{ \Carbon\Carbon::parse($breakdown->periode_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($breakdown->periode_end)->format('d M Y') }}</td>
                                                                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']))
                                                                                <td class="px-4 py-2 text-center space-x-2 whitespace-nowrap">
                                                                                    <button type="button" @click='openEditModal({{ $breakdown->toJson() }}, "{{ $lm->judul_lm }}", "up3")' class="text-blue-500 hover:text-blue-700 font-bold transition-colors text-xs">Edit</button>
                                                                                    <form action="{{ route('cascading.breakdown.destroy', $breakdown->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus breakdown LM ini?');">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">Hapus</button>
                                                                                    </form>
                                                                                </td>
                                                                                @endif
                                                                            </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                        @endforeach
                                                                </table>
                                                            </div>
                                                            @else
                                                            <div class="px-4 py-3 text-xs text-emerald-500 italic bg-white border-t border-emerald-100">Belum ada target UP3</div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- ULP Section -->
                                                    <div class="bg-amber-50 rounded-lg border border-amber-100 overflow-hidden">
                                                        <div @click="openUlp = !openUlp" class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-center px-4 py-3 hover:bg-amber-100 transition-colors cursor-pointer focus:outline-none gap-2">
                                                            <div class="flex items-center">
                                                                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Breakdown ULP</span>
                                                                <span class="ml-3 bg-white text-amber-700 border border-amber-200 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $ulpLmBreakdowns->count() }} Target Unit</span>
                                                            </div>
                                                            <div class="flex items-center space-x-3 w-full sm:w-auto justify-between sm:justify-end">
                                                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID', 'Admin Unit', 'UP3']))
                                                                <button @click.stop="openAddModal({{ $lm->id }}, '{{ $lm->judul_lm }}', 'ulp')" class="text-[10px] bg-amber-600 hover:bg-amber-700 text-white font-bold py-1 px-2 rounded transition-colors shadow-sm">+ Breakdown ULP</button>
                                                                @endif
                                                                <svg class="w-4 h-4 text-amber-500 transform transition-transform" :class="{'rotate-180': openUlp}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                            </div>
                                                        </div>
                                                        <div x-show="openUlp" x-collapse>
                                                            @if($ulpLmBreakdowns->count() > 0)
                                                            <div class="overflow-x-auto border-t border-amber-100">
                                                                <table class="min-w-full text-xs text-left">
                                                                    <thead class="text-amber-900 border-b border-amber-100 bg-amber-50/50">
                                                                        <tr>
                                                                            <th class="px-4 py-2 font-medium">Unit</th>
                                                                            <th class="px-4 py-2 font-medium">Bidang</th>
                                                                            <th class="px-4 py-2 font-medium text-right">Target</th>
                                                                            <th class="px-4 py-2 font-medium">Periode</th>
                                                                            @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID', 'Admin Unit', 'UP3']))
                                                                            <th class="px-4 py-2 font-medium text-center">Aksi</th>
                                                                            @endif
                                                                        </tr>
                                                                    </thead>
                                                                        @php
                                                                            $groupedUlp = $ulpLmBreakdowns->sortBy('periode_start')->groupBy(function($item) {
                                                                                return \Carbon\Carbon::parse($item->periode_start)->format('M Y');
                                                                            });
                                                                        @endphp
                                                                        @foreach($groupedUlp as $month => $items)
                                                                        <tbody x-data="{ openMonth: true }" class="divide-y divide-amber-50 bg-white border-b border-amber-100/50">
                                                                            <tr class="bg-amber-100/50 cursor-pointer hover:bg-amber-200/50 transition-colors" @click="openMonth = !openMonth">
                                                                                <td colspan="{{ in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID', 'Admin Unit', 'UP3']) ? '5' : '4' }}" class="px-4 py-2 font-bold text-amber-800 text-xs uppercase tracking-wider">
                                                                                    <div class="flex justify-between items-center">
                                                                                        <span>Target {{ $month }}</span>
                                                                                        <svg class="w-4 h-4 transform transition-transform duration-200" :class="{'rotate-180': openMonth}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            @foreach($items->sortBy(function($b) { return $b->unit->name ?? ''; }) as $breakdown)
                                                                            <tr x-show="openMonth">
                                                                                <td class="px-4 py-2 font-semibold text-gray-700">{{ $breakdown->unit->name ?? '-' }}</td>
                                                                                <td class="px-4 py-2 text-gray-600">{{ $breakdown->bidang ?? '-' }}</td>
                                                                                <td class="px-4 py-2 text-right font-bold text-gray-800">{{ number_format($breakdown->angka_target, 2) }} {{ $breakdown->satuan->name ?? '' }}</td>
                                                                                <td class="px-4 py-2 text-gray-500">{{ \Carbon\Carbon::parse($breakdown->periode_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($breakdown->periode_end)->format('d M Y') }}</td>
                                                                                @if(in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID', 'Admin Unit', 'UP3']))
                                                                                <td class="px-4 py-2 text-center space-x-2 whitespace-nowrap">
                                                                                    <button type="button" @click='openEditModal({{ $breakdown->toJson() }}, "{{ $lm->judul_lm }}", "ulp")' class="text-blue-500 hover:text-blue-700 font-bold transition-colors text-xs">Edit</button>
                                                                                    <form action="{{ route('cascading.breakdown.destroy', $breakdown->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus breakdown LM ini?');">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">Hapus</button>
                                                                                    </form>
                                                                                </td>
                                                                                @endif
                                                                            </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                        @endforeach
                                                                </table>
                                                            </div>
                                                            @else
                                                            <div class="px-4 py-3 text-xs text-amber-500 italic bg-white border-t border-amber-100">Belum ada target ULP</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    @endforeach

                                @else
                                <div class="px-6 py-4 text-sm text-gray-500 pl-14 italic">Belum ada Lead Measure untuk WIG ini.</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Modal -->
        <div x-show="openBreakdownModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop with blur -->
                <div x-show="openBreakdownModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal Panel -->
                <div x-show="openBreakdownModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form :action="editMode ? '{{ url('cascading/breakdown') }}/' + editBreakdownId : '{{ route('cascading.breakdown.store') }}'" method="POST" class="flex flex-col max-h-[90vh]">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <input type="hidden" name="lm_id" x-model="formLmId">
                        
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title" x-text="editMode ? 'Edit Breakdown Target ke Unit' : 'Breakdown Target ke Unit'"></h3>
                            <button @click="openBreakdownModal = false" type="button" class="text-indigo-100 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto">
                            <div class="mb-5 bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                                <p class="text-sm text-indigo-900">LM: <span class="font-bold" x-text="formLmTitle"></span></p>
                            </div>
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Pilih Unit</label>
                                    <select name="unit_id" x-model="formUnitId" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                        <option value="">-- Pilih Unit --</option>
                                        <template x-for="au in availableUnitsData.filter(u => u.type.toLowerCase() === formType)" :key="au.id">
                                            <option :value="au.id" x-text="`${au.name} (${au.type})`"></option>
                                        </template>
                                    </select>
                                    <template x-if="availableUnitsData.filter(u => u.type.toLowerCase() === formType).length === 0">
                                        <p class="text-xs text-red-500 mt-2 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Tidak ada unit di level ini yang bisa dibreakdown.</p>
                                    </template>
                                </div>
                                

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1" x-text="editMode ? 'Angka Target' : 'Target Bulanan'"></label>
                                        <input type="number" step="0.01" name="angka_target" x-model="formAngkaTarget" @input="autoCalcWeekly" required placeholder="0.00" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Satuan</label>
                                        <select name="satuan_id" x-model="formSatuanId" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                            @foreach($satuans as $satuan)
                                                <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Periode Mulai</label>
                                        <input type="text" name="periode_start" x-model="formPeriodeStart" x-init="flatpickr($el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'l, j F Y', locale: 'id' })" required placeholder="Pilih Tanggal Mulai" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Periode Akhir</label>
                                        <input type="text" name="periode_end" x-model="formPeriodeEnd" x-init="flatpickr($el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'l, j F Y', locale: 'id' })" required placeholder="Pilih Tanggal Akhir" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                    </div>
                                    </div>

                                <template x-if="!editMode">
                                    <div class="mt-2 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Distribusi Target Mingguan</h4>
                                            <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded font-semibold" x-show="isAutoFill">Auto Dibagi 5</span>
                                            <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-semibold" x-show="!isAutoFill">Manual Edit</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 mb-3 leading-tight">Target mingguan ini akan disimpan terpisah mengikuti tanggal rentang mingguan dari bulan yang dipilih di atas.</p>
                                        <div class="grid grid-cols-5 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Minggu 1</label>
                                                <input type="number" step="0.01" name="target_m1" x-model="targetM1" @input="manualWeeklyEdit" class="block w-full px-2 py-1.5 text-xs text-center rounded border-slate-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Minggu 2</label>
                                                <input type="number" step="0.01" name="target_m2" x-model="targetM2" @input="manualWeeklyEdit" class="block w-full px-2 py-1.5 text-xs text-center rounded border-slate-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Minggu 3</label>
                                                <input type="number" step="0.01" name="target_m3" x-model="targetM3" @input="manualWeeklyEdit" class="block w-full px-2 py-1.5 text-xs text-center rounded border-slate-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Minggu 4</label>
                                                <input type="number" step="0.01" name="target_m4" x-model="targetM4" @input="manualWeeklyEdit" class="block w-full px-2 py-1.5 text-xs text-center rounded border-slate-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1 text-center">Minggu 5</label>
                                                <input type="number" step="0.01" name="target_m5" x-model="targetM5" @input="manualWeeklyEdit" class="block w-full px-2 py-1.5 text-xs text-center rounded border-slate-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-lg flex-shrink-0">
                            <button @click="openBreakdownModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-sm font-semibold text-white shadow-lg shadow-indigo-200 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                Simpan Breakdown
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
</x-app-layout>
