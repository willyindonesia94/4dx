<x-app-layout>


    <!-- Leaflet JS and CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map { height: 400px; border-radius: 1rem; z-index: 1; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 shadow-xl rounded-xl sm:rounded-lg relative border border-blue-800 z-20">
                <div class="absolute inset-0 overflow-hidden rounded-lg pointer-events-none">
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white opacity-5 rounded-full transform scale-150 pointer-events-none"></div>
                </div>
                
                <div class="p-8 text-white relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="text-3xl font-extrabold tracking-tight mb-2 text-white drop-shadow-md">Pusat Komando 4DX</h3>
                        <p class="text-blue-200 font-medium text-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            PLN UID Jawa Barat - Real-time Resume
                        </p>
                    </div>
                    
                </div>
            </div>



            <div class="grid grid-cols-1 gap-6">
                <!-- Scoreboard WIGs -->
                <div class="w-full space-y-4">
                    <div class="bg-white rounded-xl sm:rounded-lg shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Progress Bar Pencapaian WIG dan LM
                            </h3>
                            
                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                                <div class="flex bg-gray-100 p-1 rounded-md">
                                    <button type="submit" name="periode_wig" value="bulanan" class="{{ ($periodeWig ?? 'bulanan') === 'bulanan' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700' }} px-4 py-1.5 rounded transition-all">Bulanan</button>
                                    <button type="submit" name="periode_wig" value="tahunan" class="{{ ($periodeWig ?? 'bulanan') === 'tahunan' ? 'bg-white shadow text-blue-700' : 'text-gray-500 hover:text-gray-700' }} px-4 py-1.5 rounded transition-all">Tahunan</button>
                                </div>
                                
                                <div class="flex gap-2">
                                    <select name="bulan" onchange="this.form.submit()" class="text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $idx => $namaBulan)
                                            <option value="{{ $idx + 1 }}" {{ $bulan == ($idx + 1) ? 'selected' : '' }}>{{ $namaBulan }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <select name="tahun" onchange="this.form.submit()" class="text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                        @for($y = 2024; $y <= 2030; $y++)
                                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>
                        
                        <div class="space-y-4">
                            @forelse($wigProgresses ?? [] as $wig)
                                <div x-data="{ expanded: false }" class="bg-gray-50/50 rounded-lg p-4 border border-gray-100 transition-all">
                                    <div @click="expanded = !expanded" class="cursor-pointer group flex justify-between items-center mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $wig['judul'] }}</h4>
                                                <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                            <span class="text-[11px] text-gray-500">
                                                {{ $wig['deskripsi'] ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="text-right ml-4">
                                            @php
                                                $progressColor = $wig['progress'] >= 80 ? 'text-green-600' : ($wig['progress'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                                $bgColor = $wig['progress'] >= 80 ? 'bg-green-500' : ($wig['progress'] >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                            @endphp
                                            <span class="text-xl font-black {{ $progressColor }}">{{ $wig['progress'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                                        <div class="{{ $bgColor }} h-2 rounded-full transition-all duration-700 ease-out" style="width: {{ min($wig['progress'], 100) }}%"></div>
                                    </div>

                                    <!-- LM Drill-down List -->
                                    <div x-show="expanded" x-collapse x-cloak class="mt-4 pt-3 border-t border-gray-200/60 space-y-3">
                                        @if(isset($wig['lms']) && count($wig['lms']) > 0)
                                            @foreach($wig['lms'] as $lm)
                                                <div class="pl-4 border-l-2 border-blue-200">
                                                    <div class="flex justify-between items-end mb-1">
                                                        <div class="flex-1 pr-4">
                                                            <span class="text-xs font-semibold text-gray-700">{{ $lm['judul'] }}</span>
                                                            <div class="text-[10px] text-gray-500 mt-0.5">
                                                                Target: {{ number_format($lm['target'], 2) }} | Realisasi: {{ number_format($lm['realisasi'], 2) }} {{ $lm['satuan'] }} ({{ ucfirst($lm['polaritas']) }})
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            @php
                                                                $lmColor = $lm['progress'] >= 80 ? 'text-green-600' : ($lm['progress'] >= 50 ? 'text-yellow-500' : 'text-red-500');
                                                                $lmBg = $lm['progress'] >= 80 ? 'bg-green-500' : ($lm['progress'] >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                                                            @endphp
                                                            <span class="text-sm font-bold {{ $lmColor }}">{{ $lm['progress'] }}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="{{ $lmBg }} h-1.5 rounded-full transition-all duration-700" style="width: {{ min($lm['progress'], 100) }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-xs text-gray-400 italic py-1">Tidak ada data LM yang aktif.</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-gray-100">Belum ada WIG yang didefinisikan.</div>
                            @endforelse
                        </div>
                    </div>


                </div>


            </div>

            <!-- Trend Chart Section -->
            <div x-data="{ chartType: 'wig' }" class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        Grafik Tren Capaian WIG
                    </h3>
                </div>
                <div class="relative h-[450px] md:h-[400px] w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Map Widget (Dynamic) -->
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden" 
                 x-data="mapController(@js($wigs), @js($dynamicMapData))">
                <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/30">
                    <div class="w-full sm:w-auto">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center uppercase">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Peta Performa Capaian <span class="ml-1" x-text="mapLevel"></span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Distribusi pencapaian berdasarkan lokasi per Lead Measure</p>
                    </div>
                    <div class="flex bg-gray-200/50 p-1 rounded-lg w-full sm:w-auto overflow-x-auto">
                        <button @click="setMapLevel('up3')" :class="mapLevel === 'up3' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 sm:flex-none px-4 py-1.5 text-sm rounded-md transition-all">UP3</button>
                        <button @click="setMapLevel('ulp')" :class="mapLevel === 'ulp' ? 'bg-white shadow text-blue-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 sm:flex-none px-4 py-1.5 text-sm rounded-md transition-all">ULP</button>
                    </div>
                </div>
                
                <!-- WIG Tabs -->
                <div class="border-b border-gray-200 px-6 pt-4 bg-white">
                    <nav class="flex overflow-x-auto gap-6 hide-scrollbar" aria-label="Tabs">
                        <template x-for="wig in wigs" :key="wig.id">
                            <button @click="selectWig(wig.id)" 
                                    :class="selectedWig === wig.id ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-3 px-1 border-b-2 font-bold text-sm transition-all flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-bold"
                                      :class="selectedWig === wig.id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="wig.id"></span>
                                <span x-text="wig.judul"></span>
                            </button>
                        </template>
                    </nav>
                </div>
                
                <!-- LM Pills -->
                <div class="px-6 py-4 bg-slate-50 border-b border-gray-100 flex flex-col gap-3" x-show="currentLms.length > 0" x-cloak>
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-xs font-bold text-slate-500 mr-2 uppercase tracking-wider">Pilih LM:</span>
                        <template x-for="(lm, index) in currentLms" :key="lm.id">
                            <button @click="selectLm(lm.id)"
                                    :class="selectedLm === lm.id ? 'bg-blue-600 text-white shadow-md border-blue-600 transform scale-105' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-blue-300'"
                                    class="px-4 py-1.5 text-xs font-bold rounded-full border transition-all duration-200">
                                <span x-text="(lm.judul_lm.match(/^LM-\d+/i) || [])[0] || ('LM ' + (index + 1))"></span>
                            </button>
                        </template>
                    </div>
                    
                    <!-- Selected LM Description -->
                    <div x-show="selectedLm !== null" class="bg-blue-50 border border-blue-100 text-blue-800 px-4 py-3 rounded-lg text-sm font-semibold flex items-start" x-transition>
                        <svg class="w-5 h-5 mr-2 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-text="currentLms.find(l => l.id === selectedLm)?.judul_lm || ''"></span>
                    </div>
                </div>
                
                <!-- Map Container -->
                <div class="p-4 relative bg-gray-100/50" x-show="selectedLm !== null" x-cloak>
                    <div id="map" class="shadow-inner rounded-xl border border-gray-200 z-0" style="height: 480px;"></div>
                </div>

                <!-- Capaian LM Realtime Section (Tabel) -->
                <div class="border-t border-gray-200 bg-white p-6">
                  <div class="mb-4">
                      @if($latestSesiWig)
                          <div>
                              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                  <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                  Matriks Capaian Lead Measure
                              </h3>
                              <p class="text-xs text-gray-500 mt-1">s.d Tanggal {{ \Carbon\Carbon::parse($latestSesiWig->tanggal_pelaksanaan)->format('d/m/Y') }} (Sesi WIG Minggu Ke-{{ $latestSesiWig->minggu_ke }})</p>
                          </div>
                      @else
                          <div>
                              <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                  <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                  Matriks Capaian Lead Measure
                              </h3>
                              <p class="text-xs text-gray-500 mt-1">Belum ada Sesi WIG di bulan ini</p>
                          </div>
                      @endif
                  </div>
                
                @if($latestSesiWig)
                <div class="mt-4">
                    @foreach($wigs as $wig)
                        @php $wigLms = $wigs->where('id', $wig->id)->first()->masterLms ?? collect(); @endphp
                        @if($wigLms->count() > 0)
                            @foreach($wigLms as $lm)
                                <!-- Container Tabel LM Dinamis -->
                                <div x-show="selectedLm === {{ $lm->id }}" x-cloak>
                                    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start mb-8">
                                        <!-- Kiri: Tabel LM -->
                                        <div class="xl:col-span-3 w-full">
                                            <div class="bg-white overflow-hidden shadow-sm rounded-xl sm:rounded-xl border border-gray-200">
                                                <div class="p-3 bg-gray-50 border-b border-gray-200">
                                                    <h5 class="text-xs font-bold text-gray-800">{{ $lm->judul_lm }}</h5>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-300 text-[10px]">
                                                        <thead class="bg-gray-100">
                                                            <tr>
                                                                <th rowspan="2" class="px-3 py-2 border border-gray-300 text-left font-bold text-gray-800 sticky left-0 bg-gray-100 z-10">UNIT</th>
                                                                @foreach($sesi_wigs_matrix as $sw)
                                                                    <th colspan="4" class="px-2 py-1 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">WEEK {{ $sw->minggu_ke }}</th>
                                                                @endforeach
                                                            </tr>
                                                            <tr>
                                                                @foreach($sesi_wigs_matrix as $sw)
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET</th>
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">REALISASI</th>
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">PENCAPAIAN (%)</th>
                                                                    <th class="px-1 py-1 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-8" title="Tren terhadap Realisasi Minggu Sebelumnya">TREN</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            <!-- Baris UID Jabar -->
                                                            <tr class="bg-indigo-50 border-b-2 border-indigo-200">
                                                                <td class="px-3 py-1 border border-gray-300 font-black text-indigo-900 whitespace-nowrap sticky left-0 bg-indigo-50 z-10 uppercase">
                                                                    UID JABAR
                                                                </td>
                                                                @foreach($sesi_wigs_matrix as $sw)
                                                                    @php
                                                                        $uidTarget = 0;
                                                                        if (isset($matrixTargets[$lm->id])) {
                                                                            foreach($matrixTargets[$lm->id] as $uid => $tgt) {
                                                                                $uidTarget += $tgt;
                                                                            }
                                                                        }
                                                                        $uidRealisasi = 0;
                                                                        if (isset($matrixRealisasi[$lm->id])) {
                                                                            foreach($matrixRealisasi[$lm->id] as $uid => $realSessions) {
                                                                                $uidRealisasi += $realSessions[$sw->id] ?? 0;
                                                                            }
                                                                        }
                                                                        $uidPencapaian = $uidTarget > 0 ? min(100, round(($uidRealisasi / $uidTarget) * 100, 2)) : 0;
                                                                        $uidBgColor = $uidPencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';
                                                                        $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                        $prevUidRealisasi = 0;
                                                                        $uidTrendIcon = '<span class="text-gray-400">-</span>';
                                                                        if ($prevSw) {
                                                                            if (isset($matrixRealisasi[$lm->id])) {
                                                                                foreach($matrixRealisasi[$lm->id] as $uid => $realSessions) {
                                                                                    $prevUidRealisasi += $realSessions[$prevSw->id] ?? 0;
                                                                                }
                                                                            }
                                                                            if ($uidRealisasi > $prevUidRealisasi) {
                                                                                $uidTrendIcon = '<svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                            } else if ($uidRealisasi < $prevUidRealisasi) {
                                                                                $uidTrendIcon = '<svg class="w-4 h-4 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                            } else {
                                                                                $uidTrendIcon = '<svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"></path></svg>';
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <td class="px-1 py-1 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidTarget, 2) }}</td>
                                                                    <td class="px-1 py-1 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidRealisasi, 2) }}</td>
                                                                    <td class="px-1 py-1 border border-gray-300 text-right font-black {{ $uidBgColor }}">{{ $uidPencapaian }}%</td>
                                                                    <td class="px-1 py-1 border border-gray-300 text-center bg-slate-50">{!! $uidTrendIcon !!}</td>
                                                                @endforeach
                                                            </tr>
                                                            @foreach($up3s as $up3)
                                                                @php
                                                                    $up3Ulps = $ulps->where('parent_id', $up3->id);
                                                                    $isExpanded = false;
                                                                @endphp
                                                                <tr class="hover:bg-slate-200 transition-colors bg-slate-100 cursor-pointer up3-row-rt" onclick="toggleUlpsRt('{{$lm->id}}-{{$up3->id}}')">
                                                                    <td class="px-3 py-1 border border-gray-300 font-bold text-indigo-900 whitespace-nowrap sticky left-0 bg-slate-100 z-10">
                                                                        <div class="flex items-center justify-between">
                                                                            <span>{{ $up3->name }}</span>
                                                                            <svg id="icon-rt-{{$lm->id}}-{{$up3->id}}" class="w-3 h-3 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                        </div>
                                                                    </td>
                                                                    @foreach($sesi_wigs_matrix as $sw)
                                                                        @php
                                                                            $up3Target = $matrixTargets[$lm->id][$up3->id] ?? 0;
                                                                            $up3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$sw->id] ?? 0;
                                                                            foreach($up3Ulps as $u) {
                                                                                $up3Realisasi += $matrixRealisasi[$lm->id][$u->id][$sw->id] ?? 0;
                                                                            }
                                                                            $up3Pencapaian = $up3Target > 0 ? min(100, round(($up3Realisasi / $up3Target) * 100, 2)) : 0;
                                                                            $up3BgColor = $up3Pencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';
                                                                            
                                                                            $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                            $prevUp3Realisasi = 0;
                                                                            $up3TrendIcon = '<span class="text-gray-400">-</span>';
                                                                            if ($prevSw) {
                                                                                $prevUp3Realisasi += $matrixRealisasi[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                                                foreach($up3Ulps as $u) {
                                                                                    $prevUp3Realisasi += $matrixRealisasi[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                                }
                                                                                if ($up3Realisasi > $prevUp3Realisasi) {
                                                                                    $up3TrendIcon = '<svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                                } else if ($up3Realisasi < $prevUp3Realisasi) {
                                                                                    $up3TrendIcon = '<svg class="w-4 h-4 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                                } else {
                                                                                    $up3TrendIcon = '<svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"></path></svg>';
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        <td class="px-1 py-1 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 2) }}</td>
                                                                        <td class="px-1 py-1 border border-gray-300 text-right font-semibold">{{ number_format($up3Realisasi, 2) }}</td>
                                                                        <td class="px-1 py-1 border border-gray-300 text-right font-bold {{ $up3BgColor }}">{{ $up3Pencapaian }}%</td>
                                                                        <td class="px-1 py-1 border border-gray-300 text-center bg-slate-50">{!! $up3TrendIcon !!}</td>
                                                                    @endforeach
                                                                </tr>
                                                                @foreach($up3Ulps as $u)
                                                                    <tr class="hover:bg-slate-50 transition-colors ulp-row-rt-{{$lm->id}}-{{$up3->id}} hidden">
                                                                        <td class="px-3 py-1 border border-gray-300 font-medium text-gray-800 whitespace-nowrap sticky left-0 bg-white z-10 pl-6">
                                                                            {{ $u->name }}
                                                                        </td>
                                                                        @foreach($sesi_wigs_matrix as $sw)
                                                                            @php
                                                                                $target = $matrixTargets[$lm->id][$u->id] ?? 0;
                                                                                $realisasi = $matrixRealisasi[$lm->id][$u->id][$sw->id] ?? 0;
                                                                                $pencapaian = $target > 0 ? min(100, round(($realisasi / $target) * 100, 2)) : 0;
                                                                                $bgColor = $pencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';
                                                                                
                                                                                $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                                $prevRealisasi = 0;
                                                                                $trendIcon = '<span class="text-gray-400">-</span>';
                                                                                if ($prevSw) {
                                                                                    $prevRealisasi = $matrixRealisasi[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                                    if ($realisasi > $prevRealisasi) {
                                                                                        $trendIcon = '<svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                                    } else if ($realisasi < $prevRealisasi) {
                                                                                        $trendIcon = '<svg class="w-4 h-4 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                                    } else {
                                                                                        $trendIcon = '<svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 12h14"></path></svg>';
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            <td class="px-1 py-1 border border-gray-300 text-right">{{ number_format($target, 2) }}</td>
                                                                            <td class="px-1 py-1 border border-gray-300 text-right">{{ number_format($realisasi, 2) }}</td>
                                                                            <td class="px-1 py-1 border border-gray-300 text-right font-bold {{ $bgColor }}">{{ $pencapaian }}%</td>
                                                                            <td class="px-1 py-1 border border-gray-300 text-center">{!! $trendIcon !!}</td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Menang Kalah Widget -->
                                        <div class="xl:col-span-1 w-full sticky top-6">
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4" x-data="{ tabRt: 'up3' }">
                                                <h3 class="text-xs font-bold text-gray-900 mb-3 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                    Menang Kalah LM
                                                </h3>
                                                <div class="flex space-x-1 bg-gray-100/50 p-1 rounded-md mb-3 text-[10px] font-semibold">
                                                    <button @click="tabRt = 'up3'" :class="tabRt === 'up3' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1 rounded-sm transition-all">UP3</button>
                                                    <button @click="tabRt = 'ulp'" :class="tabRt === 'ulp' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1 rounded-sm transition-all">ULP</button>
                                                </div>

                                                <div x-show="tabRt === 'up3'" class="space-y-3">
                                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                                        <div class="bg-green-50 border border-green-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-green-600">{{ count($rtMenangKalah[$lm->id]['up3']['menang'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-green-800">Menang</div>
                                                        </div>
                                                        <div class="bg-red-50 border border-red-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-red-600">{{ count($rtMenangKalah[$lm->id]['up3']['kalah'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-red-800">Kalah</div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar">
                                                        @foreach($rtMenangKalah[$lm->id]['up3']['menang'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-green-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($rtMenangKalah[$lm->id]['up3']['kalah'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-red-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div x-show="tabRt === 'ulp'" class="space-y-3" style="display: none;">
                                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                                        <div class="bg-green-50 border border-green-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-green-600">{{ count($rtMenangKalah[$lm->id]['ulp']['menang'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-green-800">Menang</div>
                                                        </div>
                                                        <div class="bg-red-50 border border-red-100 rounded p-2 text-center">
                                                            <div class="text-xl font-black text-red-600">{{ count($rtMenangKalah[$lm->id]['ulp']['kalah'] ?? []) }}</div>
                                                            <div class="text-[8px] uppercase font-bold text-red-800">Kalah</div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar">
                                                        @foreach($rtMenangKalah[$lm->id]['ulp']['menang'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-green-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($rtMenangKalah[$lm->id]['ulp']['kalah'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-2 py-1 rounded">
                                                                <span class="text-[10px] font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-[10px] font-black text-red-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- Close x-show div -->
                                @endforeach
                        @endif
                    @endforeach
                </div>
                @endif
            </div>

            <!-- WIG Heatmap Container -->
            <div x-show="selectedWig !== null" class="mt-6 border-t border-gray-200 bg-white p-6" x-cloak>
                <div x-html="heatmapHtml"></div>
                <div x-show="loadingHeatmap" class="py-12 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 mx-auto text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat Heatmap WIG...
                </div>
            </div>

            <!-- End of Combined Map & Matrix Widget -->
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup Trend Chart
            const trendData = @json($trendData);
            const ctx = document.getElementById('trendChart').getContext('2d');
            
            // Register datalabels plugin
            Chart.register(ChartDataLabels);
            
            // Generate datasets
            const wigDatasets = [];
            const colors = [
                '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e'
            ];
            let colorIndex = 0;

            // Add WIG Progresses (Thick lines)
            Object.values(trendData.wig_progress).forEach(wig => {
                wigDatasets.push({
                    label: wig.name,
                    data: wig.data,
                    borderColor: colors[colorIndex % colors.length],
                    backgroundColor: colors[colorIndex % colors.length] + '33',
                    borderWidth: 4,
                    tension: 0.3,
                    fill: false
                });
                colorIndex++;
            });

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: wigDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        },
                        datalabels: {
                            align: 'top',
                            anchor: 'end',
                            formatter: function(value) {
                                return value + '%';
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            color: function(context) {
                                return context.dataset.borderColor;
                            },
                            backgroundColor: 'rgba(255, 255, 255, 0.7)',
                            borderRadius: 4,
                            padding: 2
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

        });

        // Alpine Map Controller
        function mapController(wigs, dynamicMapData) {
            return {
                wigs: wigs,
                dynamicMapData: dynamicMapData,
                selectedWig: null,
                currentLms: [],
                selectedLm: null,
                mapLevel: 'up3', // 'ulp' or 'up3'
                map: null,
                markers: [],

                init() {
                    // Initialize Leaflet Map (Centered around Bandung, West Java)
                    this.map = L.map('map').setView([-6.9147, 107.6098], 8);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        subdomains: 'abcd',
                        maxZoom: 20
                    }).addTo(this.map);

                    if (this.wigs.length > 0) {
                        this.selectWig(this.wigs[0].id);
                    }
                },

                selectWig(wigId) {
                    this.selectedWig = wigId;
                    let wig = this.wigs.find(w => w.id === wigId);
                    let rawLms = wig ? (wig.master_lms || []) : [];
                    this.currentLms = rawLms.slice().sort((a, b) => {
                        const numA = parseInt((a.judul_lm.match(/LM-?(\d+)/i) || [0, 999])[1]);
                        const numB = parseInt((b.judul_lm.match(/LM-?(\d+)/i) || [0, 999])[1]);
                        return numA - numB;
                    });
                    
                    if (this.currentLms.length > 0) {
                        this.selectLm(this.currentLms[0].id);
                    } else {
                        this.selectedLm = null;
                        this.updateMap();
                    }

                    this.fetchHeatmap();
                },

                loadingHeatmap: false,
                heatmapHtml: '',
                fetchHeatmap() {
                    if (!this.selectedWig) return;
                    this.loadingHeatmap = true;
                    this.heatmapHtml = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div></div>';
                    fetch(`/laporan-bulanan/preview?jenis=dashboard_heatmap&tahun={{ $tahun }}&bulan={{ $bulan }}&wig_id=${this.selectedWig}&_t=${new Date().getTime()}`)
                        .then(res => res.json())
                        .then(data => {
                            this.heatmapHtml = data.html;
                            this.loadingHeatmap = false;
                        })
                        .catch(err => {
                            console.error('Error fetching heatmap:', err);
                            this.loadingHeatmap = false;
                        });
                },

                selectLm(lmId) {
                    this.selectedLm = lmId;
                    this.updateMap();
                },
                
                setMapLevel(level) {
                    this.mapLevel = level;
                    this.updateMap();
                },

                updateMap() {
                    // Clear existing markers
                    this.markers.forEach(marker => this.map.removeLayer(marker));
                    this.markers = [];

                    if (!this.selectedLm || !this.dynamicMapData[this.selectedLm] || !this.dynamicMapData[this.selectedLm][this.mapLevel]) return;

                    let lmData = this.dynamicMapData[this.selectedLm][this.mapLevel];

                    lmData.forEach(loc => {
                        var color = '#ef4444'; // Default Red (< 100)
                        if (loc.progress >= 100) {
                            if (loc.komitmen !== '' && loc.realisasi < parseFloat(loc.komitmen)) {
                                color = '#f97316'; // Orange (>= 100 but realisasi < komitmen)
                            } else {
                                color = '#22c55e'; // Green (>= 100 and realisasi >= komitmen)
                            }
                        }
                        
                        var markerHtmlStyles = `
                            background-color: ${color};
                            width: 2rem;
                            height: 2rem;
                            display: block;
                            left: -1rem;
                            top: -1rem;
                            position: relative;
                            border-radius: 3rem 3rem 0;
                            transform: rotate(45deg);
                            border: 1px solid #FFFFFF;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                        `;

                        var icon = L.divIcon({
                            className: "custom-pin",
                            iconAnchor: [0, 24],
                            labelAnchor: [-6, 0],
                            popupAnchor: [0, -36],
                            html: `<span style="${markerHtmlStyles}"></span>`
                        });

                        var marker = L.marker([loc.lat, loc.lng], {icon: icon})
                            .addTo(this.map)
                            .bindPopup(`<b>${loc.name}</b><br>Performa: ${loc.progress}%`);
                            
                        this.markers.push(marker);
                    });
                }
            }
        }
    </script>
</x-app-layout>

