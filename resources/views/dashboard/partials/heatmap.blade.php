@php
    $formatLmValue = function($value, $satuan) {
        if ($value === null || $value === '') return '-';
        if (trim($satuan) === '%') {
            $formatted = number_format((float)$value, 2, ",", ".");
            return $formatted . '%';
        }
        return number_format((float)$value, 2, ",", ".");
    };

    $bulanNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
@endphp

@foreach($wigs as $wig)
@php
    $wData = $reportData[$wig->id] ?? null;
    $targetBulan = (int)$bulan;
    $prevBulan = $targetBulan > 1 ? $targetBulan - 1 : 12;
    $prevBulanName = $bulanNames[$prevBulan] ?? '-';
    $curBulanName = $bulanNames[$targetBulan] ?? '-';
@endphp
@if($wData)
    <div class="mt-8 space-y-6">
        
        <!-- Top WIG Header (Trend & Overall) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row gap-6 items-center">
            <div class="flex-1 text-center md:text-left">
                <div class="text-4xl font-extrabold text-green-600 mb-2">
                    {{ number_format($wData['pct'], 2) }} %
                </div>
                <div class="text-sm font-bold text-gray-800 uppercase mb-3">
                    Capaian {{ $wig->judul }} {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UID Jabar' }}
                </div>
                <div class="text-xs text-gray-500">
                    Target: {{ number_format($wData['target'], 2) }}<br>
                    Realisasi: {{ number_format($wData['realisasi'], 2) }}
                </div>
            </div>
            <div class="flex-1 border-t md:border-t-0 md:border-l border-gray-200 pt-4 md:pt-0 md:pl-6 w-full">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest text-center mb-4">
                    Trend Capaian WIG (%)
                </div>
                <!-- Simple Bar Chart Visualization -->
                <div class="relative h-16 bg-gray-50 rounded-lg overflow-hidden border border-gray-100 flex items-end px-2">
                    <div class="flex-1 flex flex-col justify-end items-center group relative h-full">
                        <div class="w-4/5 bg-blue-300 rounded-t-sm transition-all" style="height: {{ min(100, max(5, $wData['prev_pct'] ?? 0)) }}%"></div>
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 text-white text-xs font-bold rounded">
                            {{ number_format($wData['prev_pct'] ?? 0, 2) }}%
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col justify-end items-center group relative h-full">
                        <div class="w-4/5 bg-blue-500 rounded-t-sm transition-all" style="height: {{ min(100, max(5, $wData['pct'])) }}%"></div>
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 text-white text-xs font-bold rounded">
                            {{ number_format($wData['pct'], 2) }}%
                        </div>
                    </div>
                </div>
                <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-1 uppercase px-4">
                    <span>{{ substr($prevBulanName, 0, 3) }}</span>
                    <span>{{ substr($curBulanName, 0, 3) }}</span>
                </div>
            </div>
        </div>

        <!-- WIG Matrix Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-gray-300 border-collapse text-xs">
                    <thead>
                        <tr>
                            <th rowspan="2" class="px-4 py-3 border border-gray-300 text-center font-bold text-gray-700 bg-gray-50 sticky left-0 z-10 w-48 uppercase">
                                UNIT
                            </th>
                            <th colspan="3" class="px-3 py-2 border border-gray-300 text-center font-bold text-gray-700 bg-gray-50 uppercase">
                                {{ $prevBulanName }}
                            </th>
                            <th colspan="3" class="px-3 py-2 border border-gray-300 text-center font-bold text-gray-700 bg-gray-50 uppercase">
                                {{ $curBulanName }}
                            </th>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-600 bg-gray-50">TARGET</th>
                            <th class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-600 bg-gray-50">REALISASI</th>
                            <th class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-600 bg-gray-50">CAPAIAN (%)</th>
                            <th class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-600 bg-gray-50">TARGET</th>
                            <th class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-600 bg-gray-50">REALISASI</th>
                            <th class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-600 bg-gray-50">CAPAIAN (%)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(!$isUlpLevel && !$isUp3Level)
                        <!-- UID Total Row -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border border-gray-300 font-bold text-gray-800 bg-white sticky left-0 z-10">UID Jawa Barat</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-700">-</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-700">-</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-700">-</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-800">{{ number_format($wData['target'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold text-gray-800">{{ number_format($wData['realisasi'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold {{ $wData['pct'] >= 100 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($wData['pct'], 2) }}%</td>
                        </tr>
                        @endif
                        
                        @foreach($units as $unit)
                        @php
                            $uData = $wData['units'][$unit->id] ?? null;
                            if (!$uData) continue;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border border-gray-300 font-medium text-gray-700 bg-white sticky left-0 z-10">{{ $unit->name }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center text-gray-600">{{ number_format($uData['pt'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center text-gray-600">{{ number_format($uData['pr'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-semibold {{ $uData['ppct'] >= 100 ? 'text-green-600' : 'text-gray-600' }}">{{ number_format($uData['ppct'], 2) }}%</td>
                            
                            <td class="px-2 py-2 border border-gray-300 text-center text-gray-600">{{ number_format($uData['t'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center text-gray-600">{{ number_format($uData['r'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 text-center font-bold {{ $uData['pct'] >= 100 ? 'text-green-600' : 'text-gray-600' }}">{{ number_format($uData['pct'], 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performa Lead Measure Cards -->
        <div>
            <div class="text-sm font-bold text-gray-800 uppercase mb-4 tracking-wider flex items-center gap-2 border-b pb-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Performa Lead Measure
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($wig->masterLms as $idx => $lm)
                @php
                    $lmData = $wData['lms'][$lm->id] ?? null;
                    if (!$lmData) continue;
                    $pct = $lmData['pct'];
                    $satuanName = $lm->satuan->name ?? '';
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-full hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-1 {{ $pct >= 100 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <div class="p-4 flex flex-col flex-grow text-center">
                        <div class="text-xs font-bold text-gray-500 mb-2">LM {{ $idx + 1 }}</div>
                        <div class="text-[11px] font-semibold text-gray-800 leading-snug flex-grow mb-4 flex items-center justify-center min-h-[40px]">
                            {{ $lm->judul_lm }}
                        </div>
                        
                        <div class="text-2xl font-black {{ $pct >= 100 ? 'text-green-600' : 'text-red-600' }} mb-2 tracking-tight">
                            {{ number_format($pct, 2) }} %
                        </div>
                        
                        <div class="text-[10px] text-gray-500 font-medium mb-3">
                            Target: {{ $formatLmValue($lmData['target'], $satuanName) }} | Real: {{ $formatLmValue($lmData['real'], $satuanName) }}
                        </div>
                        
                        <div class="mt-auto">
                            <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider mb-3 {{ $pct >= 100 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $pct >= 100 ? 'Exceeded Target' : 'Performance Watch' }}
                            </span>
                            
                            <div class="text-[10px] font-bold text-gray-600 pt-2 border-t border-gray-100">
                                {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} Menang: {{ $lmData['menang'] }} | {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} Kalah: {{ $lmData['kalah'] }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
    </div>
@endif
@endforeach
