<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Lengkap - {{ $wigs->count() > 1 ? 'Semua WIG' : ($wigs->first()->judul ?? 'Tidak Ada WIG') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: landscape; margin: 5mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; padding: 10px; color: #1e293b; }
        .text-xxs { font-size: 0.65rem; line-height: 1rem; }
        .bg-pln-blue { background-color: #0b2256; }
        .bg-pln-cyan { background-color: #0ea5e9; }
        .text-pln-blue { color: #0b2256; }
        
        .header-title { background: #0b2256; color: white; text-align: center; font-weight: bold; font-size: 1.5rem; padding: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .header-title img { height: 40px; }
        
        table.heatmap { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.heatmap th, table.heatmap td { border: 1px solid #94a3b8; padding: 4px; text-align: center; font-size: 0.75rem; }
        table.heatmap th { background: #3b82f6; color: white; font-weight: bold; }
        table.heatmap td.unit-name { text-align: left; font-weight: bold; }
        
        .box-title { background: #0b2256; color: white; padding: 4px 10px; font-weight: bold; border-radius: 4px 4px 0 0; font-size: 0.85rem; }
        .box-content { border: 2px solid #0b2256; border-top: none; border-radius: 0 0 4px 4px; padding: 10px; }
        
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.7rem; color: white; margin-top: 4px; }
        .bg-exceed { background-color: #22c55e; }
        .bg-watch { background-color: #ef4444; }
        
        .cell-green { background-color: #dcfce7; }
        .cell-red { background-color: #fee2e2; color: #b91c1c; }
        
        .page-break { page-break-after: always; }

        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="mx-auto max-w-7xl">

    <div class="no-print mb-4 flex justify-between items-center bg-gray-100 p-4 rounded-lg shadow-sm border border-gray-200">
        <p class="text-gray-600 font-semibold text-sm">Pratinjau HTML Ramah Cetak (A4 Landscape)</p>
        <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700 shadow flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print ke PDF
        </button>
    </div>

    @forelse($wigs as $wig)
    @php
        $wData = $reportData[$wig->id] ?? null;
        if (!$wData) continue;
        
        $wigTargetTot = $wData['target'];
        $wigRealTot = $wData['realisasi'];
        $pctUid = $wData['pct'];
    @endphp

    <div class="header-title mb-4">
        <div class="text-left w-1/4">
            <div class="text-sm font-bold leading-tight">{{ !empty($isUlpLevel) && $user && $user->unit ? strtoupper($user->unit->name) : (!empty($isUp3Level) && $user && $user->unit ? strtoupper($user->unit->name) : 'UID JAWA BARAT') }}</div>
        </div>
        <div class="w-1/2 text-center tracking-wider">
            {{ strtoupper($wig->judul) }}
            <div class="text-xs font-normal mt-1 text-gray-300">Periode: {{ $isAllBulan ? 'Semua Bulan (Tahunan)' : \Carbon\Carbon::create()->month($bulanT)->translatedFormat('F') }} {{ $tahun }}</div>
        </div>
        <div class="text-right w-1/4">
            <span class="text-yellow-400">⚡ PLN</span>
        </div>
    </div>

    <!-- WIG & LM CARDS -->
    <div class="grid grid-cols-12 gap-3 mb-4">
        <!-- WIG PERFORMANCE -->
        <div class="col-span-4">
            <div class="box-title uppercase tracking-wide">WIG PERFORMANCE | {{ $pctUid >= 100 ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH' }}</div>
            <div class="box-content flex justify-between items-center h-28 relative">
                <div class="text-center w-1/2">
                    <div class="text-4xl font-extrabold {{ $pctUid >= 100 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($pctUid, 2) }} %</div>
                    <div class="text-xs font-bold text-gray-700 mt-2">Capaian WIG {{ !empty($isUlpLevel) ? 'ULP' : (!empty($isUp3Level) ? 'UP3' : 'UID Jabar') }}</div>
                    <div class="text-xxs text-gray-500 mt-1">Target: {{ number_format($wigTargetTot, 2) }}</div>
                    <div class="text-xxs text-gray-500">Realisasi: {{ number_format($wigRealTot, 2) }}</div>
                </div>
                <div class="w-1/2 h-full flex flex-col justify-end items-center pb-2 pl-2 border-l border-gray-200">
                    <div class="text-xxs font-bold text-gray-600 mb-1">TREND CAPAIAN WIG (%)</div>
                    <div class="flex items-end space-x-1 h-12 w-full px-2">
                        @for($i=1; $i<=$bulanT; $i++)
                            <div class="w-1/6 bg-blue-500 rounded-t" style="height: {{ min(100, $pctUid) }}%;" title="Bulan {{ $i }}"></div>
                        @endfor
                    </div>
                    <div class="flex justify-between w-full text-[8px] text-gray-400 font-bold px-2 mt-1">
                        <span>JAN</span><span>..</span><span>{{ $isAllBulan ? 'DES' : strtoupper(substr(\Carbon\Carbon::create()->month($bulanT)->translatedFormat('F'),0,3)) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- LM CARDS -->
        <div class="col-span-8 grid grid-cols-3 gap-3">
            @foreach($wig->masterLms->take(3) as $idx => $lm)
            @php
                $lmData = $wData['lms'][$lm->id] ?? ['pct' => 0, 'menang' => 0, 'kalah' => 0];
                $lmPct = $lmData['pct'];
                $menang = $lmData['menang'];
                $kalah = $lmData['kalah'];
            @endphp
            <div>
                <div class="bg-gray-100 border border-gray-300 rounded shadow-sm h-full flex flex-col items-center justify-center p-2 relative overflow-hidden">
                    <div class="absolute top-0 w-full h-1 {{ $lmPct >= 100 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <div class="text-xs font-bold text-gray-700 text-center mb-1 w-full truncate px-1">LM {{ $idx+1 }} {{ $lm->judul_lm }}</div>
                    <div class="text-3xl font-extrabold {{ $lmPct >= 100 ? 'text-green-600' : 'text-red-600' }} my-1">{{ number_format($lmPct, 2) }} %</div>
                    <div class="status-badge {{ $lmPct >= 100 ? 'bg-exceed' : 'bg-watch' }}">{{ $lmPct >= 100 ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH' }}</div>
                    <div class="text-xxs font-bold text-gray-800 mt-2">{{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} Menang: {{ $menang }} | {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} Kalah: {{ $kalah }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-12 gap-3">
        <!-- HEATMAP KINERJA UP3 / ULP -->
        <div class="col-span-9">
            <div class="box-title">HEATMAP KINERJA {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} | WIG & LEAD MEASURE</div>
            <table class="heatmap">
                <thead>
                    <tr>
                        <th rowspan="2" class="w-1/6">UNIT</th>
                        <th colspan="3">WIG</th>
                        @foreach($wig->masterLms->take(3) as $idx => $lm)
                            <th colspan="5">LM-{{ $idx+1 }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th>T</th>
                        <th>R</th>
                        <th>%</th>
                        @foreach($wig->masterLms->take(3) as $lm)
                            <th>M1</th>
                            <th>M2</th>
                            <th>M3</th>
                            <th>M4</th>
                            <th>M5</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $unit)
                    @php
                        $uData = $wData['units'][$unit->id] ?? null;
                        if (!$uData) continue;

                        $uT = $uData['t'];
                        $uR = $uData['r'];
                        $uPct = $uData['pct'];
                        $uWigBg = $uPct >= 100 ? 'cell-green' : 'cell-red';
                    @endphp
                    <tr>
                        <td class="unit-name">{{ strtoupper($unit->name) }}</td>
                        <td>{{ number_format($uT, 2) }}</td>
                        <td>{{ number_format($uR, 2) }}</td>
                        <td class="{{ $uWigBg }} font-bold">{{ number_format($uPct, 2) }}%</td>
                        
                        @foreach($wig->masterLms->take(3) as $lm)
                        @php
                            $lmWeeks = $uData['lms'][$lm->id] ?? [];
                        @endphp
                            @for($w = 1; $w <= 5; $w++)
                            @php
                                $wPct = $lmWeeks[$w]['pct'] ?? 0;
                                $bg = $wPct >= 100 ? 'cell-green' : 'cell-red';
                            @endphp
                            <td class="{{ $bg }} font-bold">{{ number_format($wPct, 2) }}%</td>
                            @endfor
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- SIDEBAR WIDGETS -->
        <div class="col-span-3 flex flex-col gap-3">
            <div>
                <div class="box-title">STATUS {{ strtoupper($wig->judul) }}</div>
                <div class="border border-gray-300 p-4 bg-white flex justify-between items-center h-24">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold mr-2">{{ $wData['status_menang'] }}</div>
                        <div class="text-xs font-bold text-green-700">{{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} HIJAU<br><span class="text-xxs text-gray-500 font-normal">Menang</span></div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center font-bold mr-2">{{ $wData['status_kalah'] }}</div>
                        <div class="text-xs font-bold text-red-700">{{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} MERAH<br><span class="text-xxs text-gray-500 font-normal">Kalah</span></div>
                    </div>
                </div>
            </div>
            
            <div class="flex-1">
                <div class="box-title">FOCUS AREA NEXT WEEK</div>
                <div class="border border-gray-300 p-4 bg-white h-full text-xs text-gray-700">
                    <ul class="list-disc pl-4 space-y-2">
                        <li>Perkuatan Eksekusi LM di unit berkinerja merah.</li>
                        <li>Monitoring Penyelesaian Target Bulanan.</li>
                        <li>Evaluasi Kendala Eksekusi Lapangan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="page-break"></div>
    @empty
    <div class="text-center py-20 bg-gray-50 border border-gray-200 rounded-xl my-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Tidak Ada Data WIG</h3>
        <p class="text-gray-500 max-w-md mx-auto">Saat ini belum ada WIG yang terdaftar atau terhubung dengan lingkup kepemilikan Bidang/Divisi akun Anda untuk periode ini.</p>
    </div>
    @endforelse
</body>
</html>
