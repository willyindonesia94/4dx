<x-app-layout>


    <!-- Leaflet JS and CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map { height: 400px; border-radius: 1rem; z-index: 1; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 shadow-xl sm:rounded-lg relative border border-blue-800 z-20">
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
                    
                    <!-- Filters (Simplified Dropdown) -->
                    <div x-data="{ open: false }" @click.away="open = false" class="relative mt-4 md:mt-0">
                        <!-- Trigger Button -->
                        <button @click="open = !open" type="button" class="bg-white text-blue-900 font-bold px-4 py-2 rounded-md shadow-md flex items-center gap-2 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter Data
                        </button>

                        <!-- Mobile backdrop -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak class="fixed inset-0 bg-black/40 z-40 md:hidden" @click="open = false"></div>

                        <!-- Dropdown Panel -->
                        <div x-show="open" x-transition x-cloak
                             class="fixed inset-x-4 bottom-0 z-50 bg-white rounded-t-2xl shadow-2xl border border-gray-200 p-5 max-h-[85vh] overflow-y-auto
                                    md:absolute md:inset-auto md:right-0 md:bottom-auto md:top-full md:mt-2 md:rounded-lg md:max-h-none md:overflow-visible md:w-[800px]">
                            <!-- Mobile drag handle -->
                            <div class="flex justify-center mb-3 md:hidden">
                                <div class="w-10 h-1.5 bg-gray-300 rounded-full"></div>
                            </div>

                            <h4 class="font-bold text-gray-900 text-lg border-b pb-3 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                Penyesuaian Filter Data
                            </h4>

                            <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                                <!-- Mobile: 2 column grid; Desktop: 5 column -->
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                    <div>
                                        <label for="bulan" class="block text-sm font-bold text-gray-700 mb-1">Bulan</label>
                                        <select name="bulan" id="bulan" class="block w-full text-sm font-medium text-gray-900 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ (isset($bulan) && $bulan == $m) ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="tahun" class="block text-sm font-bold text-gray-700 mb-1">Tahun</label>
                                        <select name="tahun" id="tahun" class="block w-full text-sm font-medium text-gray-900 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            @php $currentYear = date('Y'); @endphp
                                            @foreach(range($currentYear - 2, $currentYear + 1) as $y)
                                                <option value="{{ $y }}" {{ (isset($tahun) && $tahun == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2 md:col-span-1">
                                        <label for="divisi" class="block text-sm font-bold text-gray-700 mb-1">Divisi (Bidang)</label>
                                        <select name="divisi" id="divisi" class="block w-full text-sm font-medium text-gray-900 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            <option value="">-- Semua Divisi --</option>
                                            @foreach($divisions as $div)
                                                <option value="{{ $div }}" {{ $selectedDivisi == $div ? 'selected' : '' }}>{{ $div }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2 md:col-span-1">
                                        <label for="up3_id" class="block text-sm font-bold text-gray-700 mb-1">UP3</label>
                                        <select name="up3_id" id="up3_id" class="block w-full text-sm font-medium text-gray-900 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            <option value="">-- Semua UP3 --</option>
                                            @foreach($up3s as $up3)
                                                <option value="{{ $up3->id }}" {{ $selectedUp3 == $up3->id ? 'selected' : '' }}>{{ $up3->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2 md:col-span-1">
                                        <label for="ulp_id" class="block text-sm font-bold text-gray-700 mb-1">ULP</label>
                                        <select name="ulp_id" id="ulp_id" class="block w-full text-sm font-medium text-gray-900 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
                                            <option value="">-- Semua ULP --</option>
                                            @foreach($ulps as $ulp)
                                                <option value="{{ $ulp->id }}" {{ $selectedUlp == $ulp->id ? 'selected' : '' }}>{{ $ulp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-t border-gray-100">
                                    <button type="submit" class="bg-blue-600 text-white font-bold text-base px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition-all w-full flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Terapkan Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Scoreboard WIGs (2 Columns wide) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Scoreboard WIG UID (Matrix)
                        </h3>
                        
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
                                                Target Gabungan: {{ number_format($wig['angka_target'], 2) }} {{ $wig['satuan'] }} • Berdasarkan {{ $wig['lm_count'] }} Lead Measure
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
                                        <div class="{{ $bgColor }} h-2 rounded-full transition-all duration-700 ease-out" style="width: {{ $wig['progress'] }}%"></div>
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
                                                        <div class="{{ $lmBg }} h-1.5 rounded-full transition-all duration-700" style="width: {{ $lm['progress'] }}%"></div>
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

                    <!-- Map Widget (Landscape) -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Peta Performa ULP
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">Distribusi pencapaian berdasar lokasi</p>
                            </div>
                        </div>
                        <div class="p-4 relative">
                            <div id="map" class="shadow-inner" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar / Leaderboard Column -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Ranking Widget -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            Top Performa ULP
                        </h3>
                        <div class="space-y-4">
                            @foreach($leaderboard as $index => $rank)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border {{ $index < 3 ? 'border-yellow-400 text-yellow-600 font-bold' : 'border-gray-200 text-gray-500 text-sm' }}">
                                            {{ $index + 1 }}
                                        </div>
                                        <p class="text-sm font-semibold text-gray-800">{{ $rank['unit'] }}</p>
                                    </div>
                                    <div class="text-sm font-bold {{ $rank['score'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                        {{ number_format($rank['score'], 1) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Menang / Kalah Widget -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" x-data="{ tab: 'up3' }">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Status Menang / Kalah
                        </h3>
                        
                        <!-- Tabs -->
                        <div class="flex space-x-1 bg-gray-100/50 p-1 rounded-lg mb-4 text-xs font-semibold">
                            <button @click="tab = 'divisi'" :class="tab === 'divisi' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">Divisi</button>
                            <button @click="tab = 'up3'" :class="tab === 'up3' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">UP3</button>
                            <button @click="tab = 'ulp'" :class="tab === 'ulp' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">ULP</button>
                        </div>
                        
                        <!-- Divisi Tab -->
                        <div x-show="tab === 'divisi'" class="space-y-4">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                    <div class="text-2xl font-black text-green-600">{{ count($menangKalah['divisi']['menang']) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                    <div class="text-2xl font-black text-red-600">{{ count($menangKalah['divisi']['kalah']) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                                </div>
                            </div>
                            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                                @foreach($menangKalah['divisi']['menang'] as $item)
                                    <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                        <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                        <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                    </div>
                                @endforeach
                                @foreach($menangKalah['divisi']['kalah'] as $item)
                                    <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                        <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                        <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                    </div>
                                @endforeach
                                @if(empty($menangKalah['divisi']['menang']) && empty($menangKalah['divisi']['kalah']))
                                    <div class="text-center text-xs text-gray-400 italic">Belum ada data.</div>
                                @endif
                            </div>
                        </div>

                        <!-- UP3 Tab -->
                        <div x-show="tab === 'up3'" class="space-y-4" style="display: none;">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                    <div class="text-2xl font-black text-green-600">{{ count($menangKalah['up3']['menang']) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                    <div class="text-2xl font-black text-red-600">{{ count($menangKalah['up3']['kalah']) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                                </div>
                            </div>
                            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                                @foreach($menangKalah['up3']['menang'] as $item)
                                    <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                        <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                        <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                    </div>
                                @endforeach
                                @foreach($menangKalah['up3']['kalah'] as $item)
                                    <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                        <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                        <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- ULP Tab -->
                        <div x-show="tab === 'ulp'" class="space-y-4" style="display: none;">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                    <div class="text-2xl font-black text-green-600">{{ count($menangKalah['ulp']['menang']) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                                </div>
                                <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                    <div class="text-2xl font-black text-red-600">{{ count($menangKalah['ulp']['kalah']) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                                </div>
                            </div>
                            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                                @foreach($menangKalah['ulp']['menang'] as $item)
                                    <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                        <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                        <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                    </div>
                                @endforeach
                                @foreach($menangKalah['ulp']['kalah'] as $item)
                                    <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                        <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                        <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Leaflet Map (Centered around Bandung, West Java)
            var map = L.map('map').setView([-6.9147, 107.6098], 8);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            var mapData = @json($mapData ?? []);

            mapData.forEach(function(loc) {
                var color = loc.progress >= 80 ? '#22c55e' : (loc.progress >= 50 ? '#eab308' : '#ef4444');
                
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

                L.marker([loc.lat, loc.lng], {icon: icon})
                    .addTo(map)
                    .bindPopup(`<b>${loc.name}</b><br>Performa: ${loc.progress}%`);
            });
        });
    </script>
</x-app-layout>
