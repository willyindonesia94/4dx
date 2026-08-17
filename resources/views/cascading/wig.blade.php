<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cascading WIG') }}
        </h2>
    </x-slot>

    @php
        $role = auth()->user()->role_name ?? '';
        $isUid = auth()->user()->unit && auth()->user()->unit->type === 'UID';
        $canCreateUp3Breakdown = in_array($role, ['Super Admin', 'superadmin', 'Perencanaan UID', 'perencanaan_uid']) || $isUid;
        $canEditDelete = !in_array(strtoupper($role), ['BIDANG UID', 'SUB BIDANG UID', 'MANAGER UP3', 'UP2K', 'UP2D', 'MANAGER ULP', 'GENERAL MANAGER UID']);
        
        $highlightWigId = 'null';
        if(request('highlight_unit')) {
            foreach($wigs as $w) {
                if($w->breakdowns && $w->breakdowns->contains(function($b) { return request('highlight_unit') == $b->unit_id && !$b->is_approved; })) {
                    $highlightWigId = $w->id;
                    break;
                }
            }
        }
    @endphp

    <div class="py-12" x-data="{ 
        activeWig: {{ $highlightWigId }}, 
        openBreakdownWigModal: false, 
        formWigId: null, 
        formWigTitle: '', 
        formWigDeskripsi: '',
        formType: 'uid',
        formWigSatuanId: '',
        formTahun: new Date().getFullYear(),
        uidTargets: [],
        expandedBreakdown: null,
        editMode: false,
        editBreakdownId: null,
        formUnitId: '',
        formTargetTahunan: null,
        formTargets: { jan:0, feb:0, mar:0, apr:0, mei:0, jun:0, jul:0, agu:0, sep:0, okt:0, nov:0, des:0 },
        get currentUidTarget() { return this.uidTargets.find(t => t.tahun == this.formTahun) || null; },
        formatNumber(num) {
            if(!num) return '0';
            return Number(num).toLocaleString('id-ID');
        },
        openCreateModal(w, type, uidTargetsData = []) {
            this.editMode = false;
            this.editBreakdownId = null;
            this.formWigId = w.id;
            this.formWigTitle = w.judul;
            this.formWigDeskripsi = w.deskripsi;
            this.formType = type;
            this.formWigSatuanId = w.satuan_id;
            this.uidTargets = uidTargetsData;
            
            this.formUnitId = '';
            this.formTahun = new Date().getFullYear();
            this.formTargetTahunan = null;
            
            this.formTargets = { jan:0, feb:0, mar:0, apr:0, mei:0, jun:0, jul:0, agu:0, sep:0, okt:0, nov:0, des:0 };
            
            this.openBreakdownWigModal = true;
        },
        openEditModal(bw, w, type, uidTargetsData = []) {
            this.editMode = true;
            this.editBreakdownId = bw.id;
            this.formWigId = w.id;
            this.formWigTitle = w.judul;
            this.formWigDeskripsi = w.deskripsi;
            this.formType = type;
            this.formWigSatuanId = w.satuan_id;
            this.uidTargets = uidTargetsData;
            
            this.formUnitId = bw.unit_id;
            this.formTahun = bw.tahun;
            this.formTargetTahunan = bw.target_tahunan;
            this.formTargets = {
                jan: bw.target_jan || 0, feb: bw.target_feb || 0, mar: bw.target_mar || 0,
                apr: bw.target_apr || 0, mei: bw.target_mei || 0, jun: bw.target_jun || 0,
                jul: bw.target_jul || 0, agu: bw.target_agu || 0, sep: bw.target_sep || 0,
                okt: bw.target_okt || 0, nov: bw.target_nov || 0, des: bw.target_des || 0
            };
            
            this.openBreakdownWigModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <p class="text-gray-600">Berikut adalah daftar Master WIG (Wildly Important Goal) dan penjabarannya (Breakdown) ke unit level UID.</p>
                        
                        <!-- Mass Upload Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('cascading.wig.template') }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-md text-sm font-semibold transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Template WIG
                            </a>
                            <button onclick="document.getElementById('uploadMassalForm').classList.toggle('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md text-sm font-bold transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                Upload Massal
                            </button>
                        </div>
                    </div>

                    <!-- Upload Form (Hidden by default) -->
                    <div id="uploadMassalForm" class="hidden mb-8 bg-slate-50 border border-slate-200 rounded-xl p-6">
                        <h3 class="font-bold text-slate-800 text-lg mb-2">Upload Excel Massal WIG</h3>
                        <p class="text-sm text-slate-600 mb-4">Pastikan format kolom sesuai dengan template.</p>
                        <form action="{{ route('cascading.wig.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            @csrf
                            <div class="flex-1 w-full">
                                <input type="file" name="file_excel" accept=".xlsx, .xls" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-md bg-white">
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
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md shadow-sm">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
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
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full flex-shrink-0 mt-0.5">Target: {{ $wig->polaritas ?? 'Positif' }}</span>
                            </button>
                            
                            <div x-show="activeWig === {{ $wig->id }}" x-collapse class="border-t border-gray-200 bg-white">
                                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Breakdown WIG ke Level UID</h4>
                                    <button @click='openCreateModal({ id: {{ $wig->id }}, judul: @json($wig->judul), deskripsi: @json($wig->deskripsi), satuan_id: "{{ $wig->satuan_id }}" }, "uid", [])' class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-800 font-semibold py-1.5 px-3 rounded-md transition-colors shadow-sm border border-indigo-200 flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Breakdown UID
                                    </button>
                                </div>
                                
                                @php
                                    $uidBreakdowns = $wig->breakdowns->filter(function($bw) {
                                        return $bw->unit && $bw->unit->type === 'UID';
                                    });
                                    
                                    $up3Breakdowns = $wig->breakdowns->filter(function($bw) {
                                        return $bw->unit && in_array(strtoupper($bw->unit->type), ['UP3', 'UP2D', 'UP2K']);
                                    });
                                @endphp

                                @if($uidBreakdowns->count() > 0)
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs text-left whitespace-nowrap">
                                                <thead class="text-gray-500 border-b border-gray-200 bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 font-medium w-10"></th>
                                                        <th class="px-3 py-2 font-medium">Unit UID</th>
                                                        <th class="px-3 py-2 font-medium text-center">Tahun</th>
                                                        <th class="px-3 py-2 font-medium text-right">Target Tahunan</th>
                                                        <th class="px-3 py-2 font-medium text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($uidBreakdowns as $bw)
                                                    @php $isHighlighted = request('highlight_unit') == $bw->unit_id && !$bw->is_approved; @endphp
                                                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer {{ $isHighlighted ? 'bg-yellow-50 outline outline-2 outline-yellow-400 z-10 relative' : '' }}" 
                                                        @click="expandedBreakdown = expandedBreakdown === {{ $bw->id }} ? null : {{ $bw->id }}"
                                                        @if($isHighlighted) x-init="setTimeout(() => { expandedBreakdown = {{ $bw->id }}; $el.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 500);" @endif
                                                    >
                                                        <td class="px-3 py-2 text-center text-gray-400">
                                                            <svg class="w-4 h-4 transform transition-transform" :class="{'rotate-90': expandedBreakdown === {{ $bw->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                        </td>
                                                        <td class="px-3 py-2 font-semibold text-gray-700">
                                                            {{ $bw->unit->name ?? '-' }}
                                                            @if(!$bw->is_approved)
                                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Draft</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-center">{{ $bw->tahun }}</td>
                                                        <td class="px-3 py-2 text-right font-bold text-gray-800">{{ number_format($bw->target_tahunan, 2) }} {{ $wig->satuan->name ?? '' }}</td>
                                                        <td class="px-3 py-2 text-center" @click.stop>
                                                            <div class="flex justify-center items-center gap-3">
                                                                @if(!$bw->is_approved && isset($canApproveWig) && $canApproveWig)
                                                                <form action="{{ route('cascading.wig-breakdown.approve', $bw->id) }}" method="POST" class="inline m-0">
                                                                    @csrf
                                                                    <button type="submit" class="text-emerald-500 hover:text-emerald-700 font-bold transition-colors text-xs">Setujui</button>
                                                                </form>
                                                                @endif
                                                                <button type="button" @click='openEditModal({{ $bw->toJson() }}, { id: {{ $wig->id }}, judul: @json($wig->judul), deskripsi: @json($wig->deskripsi), satuan_id: "{{ $wig->satuan_id }}" }, "uid", [])' class="text-blue-500 hover:text-blue-700 font-bold transition-colors text-xs">Edit</button>
                                                                @if($canEditDelete)
                                                                <form action="{{ route('cascading.wig-breakdown.destroy', $bw->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus breakdown WIG ini?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">Hapus</button>
                                                                </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Expandable Row for Months -->
                                                    <tr x-show="expandedBreakdown === {{ $bw->id }}" class="bg-indigo-50/30 border-b border-indigo-100">
                                                        <td colspan="5" class="px-6 py-4">
                                                            <h5 class="text-xs font-bold text-indigo-800 uppercase mb-3 flex items-center">
                                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                Target Bulanan (Tahun {{ $bw->tahun }})
                                                            </h5>
                                                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs">
                                                                @php
                                                                    $monthsData = [
                                                                        'Jan' => $bw->target_jan, 'Feb' => $bw->target_feb, 'Mar' => $bw->target_mar,
                                                                        'Apr' => $bw->target_apr, 'Mei' => $bw->target_mei, 'Jun' => $bw->target_jun,
                                                                        'Jul' => $bw->target_jul, 'Agu' => $bw->target_agu, 'Sep' => $bw->target_sep,
                                                                        'Okt' => $bw->target_okt, 'Nov' => $bw->target_nov, 'Des' => $bw->target_des,
                                                                    ];
                                                                @endphp
                                                                @foreach($monthsData as $mName => $mValue)
                                                                    <div class="bg-white p-2 rounded border border-indigo-100 shadow-sm flex flex-col items-center justify-center">
                                                                        <span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-1">{{ $mName }}</span>
                                                                        <span class="font-bold text-slate-700">{{ number_format((float)$mValue, 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- Breakdown UP3 Section -->
                                <div class="px-6 py-4 bg-gray-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-gray-200">
                                    <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Breakdown WIG ke Level UP3</h4>
                                    @if($canCreateUp3Breakdown)
                                    <button @click='openCreateModal({ id: {{ $wig->id }}, judul: @json($wig->judul), deskripsi: @json($wig->deskripsi), satuan_id: "{{ $wig->satuan_id }}" }, "up3", @json($uidBreakdowns->values()))' class="text-xs bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-semibold py-1.5 px-3 rounded-md transition-colors shadow-sm border border-emerald-200 flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Breakdown UP3
                                    </button>
                                    @endif
                                </div>
                                
                                @if($up3Breakdowns->count() > 0)
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs text-left whitespace-nowrap">
                                                <thead class="text-gray-500 border-b border-gray-200 bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 font-medium w-10"></th>
                                                        <th class="px-3 py-2 font-medium">Unit UP3</th>
                                                        <th class="px-3 py-2 font-medium text-center">Tahun</th>
                                                        <th class="px-3 py-2 font-medium text-right">Target Tahunan</th>
                                                        <th class="px-3 py-2 font-medium text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($up3Breakdowns as $bw)
                                                    @php $isHighlighted = request('highlight_unit') == $bw->unit_id && !$bw->is_approved; @endphp
                                                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer {{ $isHighlighted ? 'bg-yellow-50 outline outline-2 outline-yellow-400 z-10 relative' : '' }}" 
                                                        @click="expandedBreakdown = expandedBreakdown === {{ $bw->id }} ? null : {{ $bw->id }}"
                                                        @if($isHighlighted) x-init="setTimeout(() => { expandedBreakdown = {{ $bw->id }}; $el.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 500);" @endif
                                                    >
                                                        <td class="px-3 py-2 text-center text-gray-400">
                                                            <svg class="w-4 h-4 transform transition-transform" :class="{'rotate-90': expandedBreakdown === {{ $bw->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                        </td>
                                                        <td class="px-3 py-2 font-semibold text-gray-700">
                                                            {{ $bw->unit->name ?? '-' }}
                                                            @if(!$bw->is_approved)
                                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Draft</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-center">{{ $bw->tahun }}</td>
                                                        <td class="px-3 py-2 text-right font-bold text-gray-800">{{ number_format($bw->target_tahunan, 2) }} {{ $wig->satuan->name ?? '' }}</td>
                                                        <td class="px-3 py-2 text-center" @click.stop>
                                                            <div class="flex justify-center items-center space-x-2">
                                                                @if(!$bw->is_approved && ((isset($canApproveWig) && $canApproveWig) || (in_array(strtoupper(auth()->user()->role_name ?? ''), ['MANAGER UP3', 'UP2K', 'UP2D']) && auth()->user()->unit_id == $bw->unit_id)))
                                                                <form action="{{ route('cascading.wig-breakdown.approve', $bw->id) }}" method="POST" class="inline m-0">
                                                                    @csrf
                                                                    <button type="submit" class="text-emerald-500 hover:text-emerald-700 font-bold transition-colors text-xs">Setujui</button>
                                                                </form>
                                                                @endif
                                                                @if($canCreateUp3Breakdown)
                                                                <button type="button" @click='openEditModal({{ $bw->toJson() }}, { id: {{ $wig->id }}, judul: @json($wig->judul), deskripsi: @json($wig->deskripsi), satuan_id: "{{ $wig->satuan_id }}" }, "up3", @json($uidBreakdowns->values()))' class="text-blue-500 hover:text-blue-700 font-bold transition-colors text-xs">Edit</button>
                                                                @endif
                                                                @if($canEditDelete)
                                                                <form action="{{ route('cascading.wig-breakdown.destroy', $bw->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus breakdown WIG ini?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">Hapus</button>
                                                                </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Expandable Row for Months -->
                                                    <tr x-show="expandedBreakdown === {{ $bw->id }}" class="bg-emerald-50/30 border-b border-emerald-100">
                                                        <td colspan="5" class="px-6 py-4">
                                                            <h5 class="text-xs font-bold text-emerald-800 uppercase mb-3 flex items-center">
                                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                Target Bulanan (Tahun {{ $bw->tahun }})
                                                            </h5>
                                                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs">
                                                                @php
                                                                    $monthsData = [
                                                                        'Jan' => $bw->target_jan, 'Feb' => $bw->target_feb, 'Mar' => $bw->target_mar,
                                                                        'Apr' => $bw->target_apr, 'Mei' => $bw->target_mei, 'Jun' => $bw->target_jun,
                                                                        'Jul' => $bw->target_jul, 'Agu' => $bw->target_agu, 'Sep' => $bw->target_sep,
                                                                        'Okt' => $bw->target_okt, 'Nov' => $bw->target_nov, 'Des' => $bw->target_des,
                                                                    ];
                                                                @endphp
                                                                @foreach($monthsData as $mName => $mValue)
                                                                    <div class="bg-white p-2 rounded border border-emerald-100 shadow-sm flex flex-col items-center justify-center">
                                                                        <span class="block text-[10px] text-emerald-500 uppercase font-semibold mb-1">{{ $mName }}</span>
                                                                        <span class="font-bold text-slate-700">{{ number_format((float)$mValue, 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif


                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown WIG Modal -->
        <div x-show="openBreakdownWigModal" class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openBreakdownWigModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openBreakdownWigModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-100 max-h-[90vh] flex flex-col">
                    <form :action="editMode ? '{{ url('cascading/wig-breakdown') }}/' + editBreakdownId : '{{ route('cascading.wig-breakdown.store') }}'" method="POST" class="flex flex-col max-h-[90vh] overflow-hidden">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <input type="hidden" name="wig_id" x-model="formWigId">
                        
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white tracking-wide" id="modal-title">
                                <span x-show="!editMode && formType === 'uid'">Breakdown WIG ke Level UID</span>
                                <span x-show="!editMode && formType === 'up3'">Breakdown WIG ke Level UP3</span>
                                <span x-show="editMode && formType === 'uid'">Edit Breakdown UID</span>
                                <span x-show="editMode && formType === 'up3'">Edit Breakdown UP3</span>
                            </h3>
                            <button @click="openBreakdownWigModal = false" type="button" class="text-white/80 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-1.5 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="bg-white px-6 pt-5 pb-6 overflow-y-auto flex-1">
                            <div class="mb-5 bg-indigo-50 border border-indigo-100 rounded-lg p-3">
                                <p class="text-sm text-indigo-900 mb-1">WIG: <span class="font-bold" x-text="formWigTitle"></span></p>
                                <p class="text-xs text-indigo-700 italic" x-show="formWigDeskripsi" x-text="formWigDeskripsi"></p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">
                                        <span x-show="formType === 'uid'">Pilih Unit (UID)</span>
                                        <span x-show="formType === 'up3'">Pilih Unit (UP3)</span>
                                    </label>
                                    <select name="unit_id" x-model="formUnitId" x-show="formType === 'uid'" :required="formType === 'uid'" :disabled="formType !== 'uid'" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                        <option value="">-- Pilih UID --</option>
                                        @foreach($uidUnits ?? [] as $uid)
                                            <option value="{{ $uid->id }}">{{ $uid->name }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <select name="unit_id" x-model="formUnitId" x-show="formType === 'up3'" :required="formType === 'up3'" :disabled="formType !== 'up3'" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                        <option value="">-- Pilih UP3 --</option>
                                        @foreach($up3Units ?? [] as $up3)
                                            <option value="{{ $up3->id }}">{{ $up3->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Satuan</label>
                                    <input type="hidden" name="satuan_id" x-model="formWigSatuanId">
                                    <select disabled x-model="formWigSatuanId" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-100 text-slate-500 shadow-sm text-sm cursor-not-allowed">
                                        @foreach($satuans as $satuan)
                                            <option value="{{ $satuan->id }}">{{ $satuan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tahun</label>
                                    <input type="number" name="tahun" x-model="formTahun" required class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Total Target Tahunan</label>
                                    <input type="number" step="0.01" name="target_tahunan" x-model="formTargetTahunan" required placeholder="0.00" class="block w-full py-2.5 px-4 rounded-md border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 shadow-sm text-sm text-slate-700">
                                </div>
                            </div>

                            <hr class="mb-5 border-slate-200">

                            <!-- UID Target Reference Banner -->
                            <div x-show="formType === 'up3' && currentUidTarget" class="mb-5 p-4 bg-indigo-50 border border-indigo-100 rounded-md shadow-sm">
                                <h5 class="text-xs font-bold text-indigo-800 uppercase mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Acuan Target UID (Tahun <span x-text="formTahun" class="ml-1"></span>)
                                </h5>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 text-xs">
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Tahunan</span><span class="font-bold text-indigo-900 break-all" x-text="formatNumber(currentUidTarget.target_tahunan)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Jan</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_jan)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Feb</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_feb)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Mar</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_mar)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Apr</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_apr)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Mei</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_mei)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Jun</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_jun)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Jul</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_jul)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Agu</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_agu)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Sep</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_sep)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Okt</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_okt)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Nov</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_nov)"></span></div>
                                    <div class="bg-white p-2 rounded border border-indigo-100"><span class="block text-[10px] text-indigo-500 uppercase font-semibold mb-0.5">Des</span><span class="font-bold text-slate-700 break-all" x-text="formatNumber(currentUidTarget.target_des)"></span></div>
                                </div>
                            </div>
                            <div x-show="formType === 'up3' && !currentUidTarget" class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-md shadow-sm">
                                <h5 class="text-xs font-bold text-amber-800 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Target UID untuk tahun <span x-text="formTahun" class="mx-1"></span> belum ditentukan. Silakan tentukan target UID terlebih dahulu.
                                </h5>
                            </div>

                            <h4 class="text-sm font-bold text-gray-700 mb-4">Input Target Per Bulan</h4>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @php
                                    $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                                    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                @endphp
                                @foreach($months as $idx => $m)
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">{{ $monthNames[$idx] }}</label>
                                    <input type="number" step="0.01" name="target_{{ $m }}" x-model="formTargets.{{ $m }}" placeholder="0" class="block w-full py-2 px-3 rounded-md border-slate-200 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-sm text-sm text-slate-700 transition-colors">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 rounded-b-lg gap-2 sm:gap-0">
                            <button @click="openBreakdownWigModal = false" type="button" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-slate-200 px-6 py-2.5 bg-white text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all duration-300">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center rounded-md border border-transparent px-6 py-2.5 bg-blue-600 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                                <span x-text="editMode ? 'Simpan Perubahan' : 'Simpan WIG Breakdown'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
