<x-app-layout>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 overflow-hidden shadow-xl sm:rounded-2xl relative border border-blue-800">
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white opacity-5 rounded-full transform scale-150 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-cyan-400 opacity-10 rounded-full transform scale-150 pointer-events-none"></div>
                
                <div class="p-8 text-white relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <h3 class="text-3xl font-extrabold tracking-tight mb-2 text-white drop-shadow-md">Pusat Komando 4DX</h3>
                        <p class="text-cyan-300 font-medium text-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            PLN UID Jawa Barat - Real-time Resume
                        </p>
                    </div>
                    
                    <!-- Filters -->
                    <div class="bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/20 shadow-inner w-full md:w-auto mt-4 md:mt-0">
                        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col md:flex-row md:items-center gap-4 md:gap-3 w-full">
                            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 w-full md:w-auto">
                                <label for="division_id" class="text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">Divisi</label>
                                <select name="division_id" id="division_id" onchange="this.form.submit()" class="bg-white/90 border-0 text-blue-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full md:w-40 p-2.5 md:p-2 font-bold shadow-sm">
                                    <option value="">-- Semua Divisi --</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}" {{ $selectedDivisionId == $div->id ? 'selected' : '' }}>
                                            {{ $div->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 w-full md:w-auto">
                                <label for="up3_id" class="text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">UP3</label>
                                <select name="up3_id" id="up3_id" onchange="document.getElementById('ulp_id').value=''; this.form.submit()" class="bg-white/90 border-0 text-blue-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full md:w-40 p-2.5 md:p-2 font-bold shadow-sm">
                                    <option value="">-- Semua UP3 --</option>
                                    @foreach($up3s as $up3)
                                        <option value="{{ $up3->id }}" {{ $selectedUp3Id == $up3->id ? 'selected' : '' }}>
                                            {{ $up3->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 w-full md:w-auto">
                                <label for="ulp_id" class="text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">ULP</label>
                                <select name="ulp_id" id="ulp_id" onchange="this.form.submit()" class="bg-white/90 border-0 text-blue-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full md:w-40 p-2.5 md:p-2 font-bold shadow-sm">
                                    <option value="">-- Semua ULP --</option>
                                    @foreach($ulps as $ulp)
                                        <option value="{{ $ulp->id }}" {{ $selectedUlpId == $ulp->id ? 'selected' : '' }}>
                                            {{ $ulp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: WIG Progress & Realizations -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Trend Chart (New) -->
                    <div class="bg-white shadow-md sm:rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <h4 class="text-xl font-black text-slate-800 drop-shadow-sm flex items-center gap-3">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                Tren Rata-rata Pencapaian
                            </h4>
                            
                            <form method="GET" action="{{ route('dashboard') }}" class="flex w-full md:w-auto">
                                <!-- Preserve existing filters -->
                                @if(request('division_id')) <input type="hidden" name="division_id" value="{{ request('division_id') }}"> @endif
                                @if(request('up3_id')) <input type="hidden" name="up3_id" value="{{ request('up3_id') }}"> @endif
                                @if(request('ulp_id')) <input type="hidden" name="ulp_id" value="{{ request('ulp_id') }}"> @endif
                                
                                <select name="chart_period" onchange="this.form.submit()" class="w-full md:w-48 border-gray-300 rounded-md text-sm py-2 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700 font-medium cursor-pointer bg-white">
                                    <option value="7_days" {{ $chartPeriod == '7_days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                                    <option value="1_month" {{ $chartPeriod == '1_month' ? 'selected' : '' }}>30 Hari Terakhir</option>
                                    <option value="1_year" {{ $chartPeriod == '1_year' ? 'selected' : '' }}>1 Tahun Terakhir</option>
                                    <optgroup label="Bulan Spesifik">
                                        @foreach(range(0, 5) as $m)
                                            @php 
                                                $dt = \Carbon\Carbon::now()->subMonths($m);
                                                $monthVal = $dt->format('Y-m'); 
                                                $monthLabel = $dt->translatedFormat('F Y'); 
                                            @endphp
                                            <option value="month_{{ $monthVal }}" {{ $chartPeriod == 'month_'.$monthVal ? 'selected' : '' }}>{{ $monthLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </form>
                        </div>
                        <div class="p-6 bg-white relative h-[300px]">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    <!-- WIG Utama Progress -->
                    <div class="bg-white shadow-md sm:rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                            <h4 class="text-xl font-black text-slate-800 drop-shadow-sm flex items-center gap-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                Kemajuan WIG Utama (UID)
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">Roll-up otomatis dari capaian harian Lead Measure ULP.</p>
                        </div>
                        <div class="p-4 space-y-4 bg-white max-h-[400px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
                            @forelse($wigProgresses as $wp)
                                <div class="relative group">
                                    <div class="flex justify-between items-center mb-2">
                                        <div class="flex-1 pr-4">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[9px] font-bold uppercase text-white bg-blue-600 px-1.5 py-0.5 rounded shadow-sm tracking-wider">{{ $wp['metric'] }}</span>
                                            </div>
                                            <h5 class="text-sm font-bold text-gray-800 leading-tight group-hover:text-blue-700 transition-colors">{{ $wp['name'] }}</h5>
                                        </div>
                                        <div class="text-right whitespace-nowrap">
                                            <span class="text-2xl font-black {{ $wp['progress_percentage'] >= 100 ? 'text-green-500' : ($wp['progress_percentage'] >= 85 ? 'text-yellow-500' : 'text-red-500') }} drop-shadow-sm">{{ $wp['progress_percentage'] }}%</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar Container -->
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1.5 overflow-hidden shadow-inner border border-gray-300">
                                        <div class="h-full rounded-full transition-all duration-1000 ease-out relative 
                                            {{ $wp['progress_percentage'] >= 100 ? 'bg-gradient-to-r from-green-400 to-green-600' : ($wp['progress_percentage'] >= 85 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 'bg-gradient-to-r from-red-400 to-red-600') }}" 
                                            style="width: {{ $wp['progress_percentage'] }}%">
                                            <!-- Shine effect -->
                                            <div class="absolute top-0 left-0 bottom-0 right-0 bg-white opacity-20" style="background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transform: translateX(-100%); animation: shimmer 2.5s infinite;"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] text-gray-500 font-medium">
                                        <span>Target: <strong class="text-gray-700">{{ $wp['target_value'] }} {{ $wp['unit'] }}</strong></span>
                                        <span>Dihitung dari <strong class="text-gray-700">{{ $wp['lead_count'] }}</strong> Lead</span>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <hr class="border-gray-100 border-dashed my-2">
                                @endif
                            @empty
                                <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                    <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <h3 class="text-xs font-bold text-gray-900">Belum ada WIG Utama</h3>
                                    <p class="mt-1 text-[10px] text-gray-500">Mulai dengan membuat target pada menu Cascading WIG.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Realizations -->
                    <div class="bg-white shadow-md sm:rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <div>
                                <h4 class="text-xl font-black text-slate-800 drop-shadow-sm flex items-center gap-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Aktivitas Realisasi Terbaru
                                </h4>
                            </div>
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500 shadow"></span>
                            </span>
                        </div>
                        <!-- Mobile Cards View (Minimalist) -->
                        <div class="md:hidden">
                            @forelse($recentRealizations as $realization)
                                <div class="px-4 py-3 border-b border-gray-100 last:border-0 flex flex-col gap-1 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-900 text-sm">{{ $realization->target->location->name ?? 'N/A' }}</span>
                                        <span class="font-black text-green-600 text-sm">+{{ number_format($realization->realization_value, 2) }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-700 font-medium leading-snug">
                                        {{ Str::limit($realization->target->name, 70) }}
                                        <span class="text-blue-600 ml-1 whitespace-nowrap">• {{ $realization->target->metric->name }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] text-gray-400 mt-1">
                                        <span>{{ $realization->report_date->format('d M Y') }}</span>
                                        <span class="truncate max-w-[150px]">{{ $realization->creator->name ?? 'Sistem' }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-5 text-center text-sm text-gray-500 bg-gray-50 border-b border-gray-100">
                                    Belum ada aktivitas realisasi hari ini.
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Unit (ULP)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Lead Measure</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Nilai Input</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pelapor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($recentRealizations as $realization)
                                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $realization->target->location->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600 max-w-[200px] truncate" title="{{ $realization->target->name }}">
                                                {{ Str::limit($realization->target->name, 40) }}
                                                <div class="text-xs font-medium text-blue-600 mt-0.5">{{ $realization->target->metric->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                                {{ $realization->report_date->format('d M Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-green-600 text-right">
                                                +{{ number_format($realization->realization_value, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $realization->creator->name ?? 'Sistem' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-10 whitespace-nowrap text-sm text-gray-500 text-center bg-gray-50">Belum ada aktivitas realisasi hari ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Map & Leaderboard -->
                <div class="lg:col-span-1 space-y-8">
                    
                    <!-- ULP Progress Map -->
                    <div class="bg-slate-900 shadow-xl sm:rounded-2xl overflow-hidden border border-slate-800 flex flex-col h-[400px]">
                        <div class="px-5 py-4 border-b border-slate-700 bg-slate-950 relative overflow-hidden flex-shrink-0">
                            <h4 class="text-lg font-extrabold flex items-center gap-2 text-white relative z-10 drop-shadow-md">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                Peta Performa ULP
                            </h4>
                        </div>
                        <div id="map" class="w-full flex-grow relative z-0"></div>
                    </div>

                    <!-- Fixed Leaderboard styling for High Contrast -->
                    <div class="bg-slate-900 shadow-xl sm:rounded-2xl overflow-hidden border border-slate-800">
                        <div class="px-6 py-6 border-b border-slate-700 bg-slate-950 relative overflow-hidden">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 16px 16px;"></div>
                            
                            <h4 class="text-xl font-extrabold flex items-center gap-3 text-white relative z-10 drop-shadow-md">
                                <svg class="w-6 h-6 text-yellow-400 drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
                                Papan Peringkat
                            </h4>
                            <p class="text-xs text-slate-400 mt-2 font-medium relative z-10">Rata-rata Penyelesaian Lead Measure Tertinggi antar Unit</p>
                        </div>
                        
                        <div class="p-0 bg-slate-900">
                            <ul class="divide-y divide-slate-800">
                                @forelse($leaderboard as $index => $board)
                                    <li class="px-6 py-5 flex items-center justify-between hover:bg-slate-800 transition-colors duration-200">
                                        <div class="flex items-center gap-4">
                                            <!-- Rank Badge -->
                                            <div class="flex items-center justify-center w-10 h-10 rounded-full text-md font-extrabold shadow-lg
                                                {{ $index == 0 ? 'bg-gradient-to-br from-yellow-300 to-yellow-600 text-yellow-950 ring-4 ring-yellow-500/30' : 
                                                  ($index == 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500 text-gray-900 ring-2 ring-gray-400/30' : 
                                                  ($index == 2 ? 'bg-gradient-to-br from-orange-400 to-orange-700 text-white ring-2 ring-orange-500/30' : 
                                                  'bg-slate-800 text-slate-300 border border-slate-700')) }}">
                                                {{ $index + 1 }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-white text-md">{{ $board['location_name'] }}</span>
                                                @if($index == 0)
                                                    <span class="text-[10px] font-bold text-yellow-400 uppercase tracking-widest mt-0.5">Top Performer 🏆</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="font-black text-xl text-cyan-400 drop-shadow">
                                            {{ $board['average_progress'] }}%
                                        </div>
                                    </li>
                                @empty
                                    <li class="px-6 py-12 text-center text-slate-400 text-sm flex flex-col items-center gap-3">
                                        <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        Belum ada data capaian realisasi yang masuk.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <style>
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        /* Fix leaflet map z-index */
        .leaflet-container {
            z-index: 10;
        }
    </style>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- TREND CHART ---
            var ctx = document.getElementById('trendChart').getContext('2d');
            var chartDates = @json($chartDates);
            var chartProgresses = @json($chartProgresses);
            
            // Create gradient for the line chart fill
            var gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); // Indigo-600 with opacity
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

            var trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartDates,
                    datasets: [{
                        label: 'Rata-rata Kemajuan (%)',
                        data: chartProgresses,
                        borderColor: '#4f46e5', // Indigo 600
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.4 // Smooth curves
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { size: 13 },
                            bodyFont: { size: 14, weight: 'bold' },
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '% Tercapai';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: { weight: '600' },
                                callback: function(value) { return value + '%' }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: { weight: '600' }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });

            // --- MAP INITIALIZATION ---
            // Initialize map centered at Bandung/Jawa Barat, enable dragging and zooming
            var map = L.map('map', {
                center: [-6.9147, 107.6098],
                zoom: 8,
                zoomControl: true,
                dragging: true,
                scrollWheelZoom: true,
                doubleClickZoom: true,
                boxZoom: true,
                keyboard: true,
                touchZoom: true
            });

            // NO TILE LAYER - So it looks like a pure graphic graphic
            
            // Load Jawa Barat GeoJSON for the graphic map
            fetch('https://cdn.jsdelivr.net/gh/superpikar/indonesia-geojson@master/indonesia-province-simple.json')
                .then(res => res.json())
                .then(data => {
                    var jabarGeoJson = {
                        type: "FeatureCollection",
                        features: data.features.filter(f => f.properties.Propinsi === "JAWA BARAT")
                    };
                    
                    var vectorMap = L.geoJSON(jabarGeoJson, {
                        style: {
                            color: '#22d3ee', // Cyan border
                            weight: 2,
                            opacity: 0.8,
                            fillColor: '#0f172a', // Dark slate fill
                            fillOpacity: 1
                        }
                    }).addTo(map);
                    
                    // Adjust map view to fit Jawa Barat exactly
                    map.fitBounds(vectorMap.getBounds(), {padding: [20, 20]});
                });

            var rawMapData = @json($mapData);

            rawMapData.forEach(function(item) {
                // Determine color based on 4DX 5-Second Rule
                var markerColor = '#ef4444'; // Red < 85%
                if (item.progress >= 100) markerColor = '#22c55e'; // Green >= 100%
                else if (item.progress >= 85) markerColor = '#eab308'; // Yellow >= 85%

                // Create a custom SVG Icon
                var svgIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `
                        <div style="background-color: ${markerColor}; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 15px ${markerColor}; cursor: pointer; transition: transform 0.2s;">
                            <span style="display:none;"></span>
                        </div>
                    `,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                var popupContent = `
                    <div style="text-align: center; font-family: sans-serif; padding: 5px;">
                        <h4 style="margin: 0; padding-bottom: 5px; font-weight: bold; border-bottom: 1px solid #eee; font-size: 14px;">${item.name}</h4>
                        <p style="margin: 5px 0 0; font-size: 18px; font-weight: 900; color: ${markerColor}">${item.progress}%</p>
                    </div>
                `;

                var marker = L.marker([item.lat, item.lng], {icon: svgIcon})
                    .addTo(map)
                    .bindTooltip(popupContent, {
                        direction: 'top',
                        offset: [0, -10],
                        opacity: 1,
                        className: 'custom-tooltip'
                    });
            });
            
            // Note: Map zooming is now handled by the GeoJSON bounds to ensure it always fits perfectly as a graphic.
        });
    </script>
    <style>
        .custom-tooltip {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .custom-tooltip::before {
            border-top-color: rgba(255, 255, 255, 0.95);
        }
        /* Hide map attribution since we aren't using tiles */
        .leaflet-control-attribution {
            display: none !important;
        }
        #map {
            background-color: #020617; /* Slate-950 to match card background */
        }
        /* Style custom dark-theme Leaflet control buttons */
        .leaflet-bar {
            border: 1px solid #334155 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }
        .leaflet-bar a {
            background-color: #0f172a !important;
            color: #22d3ee !important;
            border-bottom: 1px solid #1e293b !important;
            transition: all 0.2s;
        }
        .leaflet-bar a:hover {
            background-color: #1e293b !important;
            color: #67e8f9 !important;
        }
        .leaflet-bar a.leaflet-disabled {
            background-color: #1e293b !important;
            color: #475569 !important;
        }
    </style>
</x-app-layout>
