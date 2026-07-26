<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Lengkap - {{ $wigs->count() > 1 ? 'Semua WIG' : $wigs->first()->judul }}</title>
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

    @foreach($wigs as $wig)
    @php
        // Persiapan Data (Simulasi Kalkulasi UID)
        // Dalam implementasi nyata, kita query ke database berdasarkan $bulan dan $tahun
        
        // Ambil Data Target dan Realisasi WIG
        $tahunT = (int)$tahun;
        $isAllBulan = $bulan === 'all';
        $bulanT = $isAllBulan ? 12 : (int)$bulan; // Default iterasi WIG ke 12 jika semua bulan
        
        $wigTargetTot = 0;
        $wigRealTot = 0;
        if ($isAllBulan) {
            $wigTargetTot = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT)
                ->sum(\Illuminate\Support\Facades\DB::raw('target_jan + target_feb + target_mar + target_apr + target_mei + target_jun + target_jul + target_agu + target_sep + target_okt + target_nov + target_des'));
                
            $wigRealTot = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT)
                ->sum('angka_realisasi');
        } else {
            $colBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$bulanT];
            $wigTargetTot = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT)
                ->sum($colBln);
            $wigRealTot = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('bulan', $bulanT)
                ->sum('angka_realisasi');
        }
            
        $polaritas = strtolower(trim($wig->polaritas ?? 'positif'));
        $pctUid = 0;
        if ($wigTargetTot > 0) {
            if ($polaritas === 'negatif' || $polaritas === '3') {
                $pctUid = ($wigTargetTot / max(0.0001, $wigRealTot)) * 100;
            } else {
                $pctUid = ($wigRealTot / $wigTargetTot) * 100;
            }
        }
    @endphp

    <div class="header-title mb-4">
        <div class="text-left w-1/4">
            <!-- Placeholder Logo -->
            <div class="text-sm font-bold leading-tight">UID JAWA BARAT</div>
        </div>
        <div class="w-1/2 text-center tracking-wider">
            WIG {{ strtoupper($wig->judul) }}
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
                    <div class="text-xs font-bold text-gray-700 mt-2">Capaian WIG UID Jabar</div>
                    <div class="text-xxs text-gray-500 mt-1">Target: {{ number_format($wigTargetTot, 2) }}</div>
                    <div class="text-xxs text-gray-500">Realisasi: {{ number_format($wigRealTot, 2) }}</div>
                </div>
                <div class="w-1/2 h-full flex flex-col justify-end items-center pb-2 pl-2 border-l border-gray-200">
                    <div class="text-xxs font-bold text-gray-600 mb-1">TREND CAPAIAN WIG (%)</div>
                    <!-- Simple Sparkline Representation -->
                    <div class="flex items-end space-x-1 h-12 w-full px-2">
                        @for($i=1; $i<=$bulanT; $i++)
                            @php
                                $h = min(100, rand(70, 110)); // dummy sparkline
                            @endphp
                            <div class="w-1/6 bg-blue-500 rounded-t" style="height: {{ $h }}%;" title="Bulan {{ $i }}"></div>
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
                // Dummy LM calculations
                $lmPct = rand(8000, 14000) / 100;
                $menang = rand(10, 17);
                $kalah = 17 - $menang;
            @endphp
            <div>
                <div class="bg-gray-100 border border-gray-300 rounded shadow-sm h-full flex flex-col items-center justify-center p-2 relative overflow-hidden">
                    <div class="absolute top-0 w-full h-1 {{ $lmPct >= 100 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <div class="text-xs font-bold text-gray-700 text-center mb-1 w-full truncate px-1">LM {{ $idx+1 }} {{ $lm->judul_lm }}</div>
                    <div class="text-3xl font-extrabold {{ $lmPct >= 100 ? 'text-green-600' : 'text-red-600' }} my-1">{{ number_format($lmPct, 2) }} %</div>
                    <div class="status-badge {{ $lmPct >= 100 ? 'bg-exceed' : 'bg-watch' }}">{{ $lmPct >= 100 ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH' }}</div>
                    <div class="text-xxs font-bold text-gray-800 mt-2">UP3 Menang: {{ $menang }} | UP3 Kalah: {{ $kalah }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-12 gap-3">
        <!-- HEATMAP KINERJA UP3 -->
        <div class="col-span-9">
            <div class="box-title">HEATMAP KINERJA UP3 | WIG & LEAD MEASURE</div>
            <table class="heatmap">
                <thead>
                    <tr>
                        <th rowspan="2" class="w-1/6">UNIT</th>
                        <th colspan="3">WIG</th>
                        @foreach($wig->masterLms->take(3) as $idx => $lm)
                            <th colspan="3">LM-{{ $idx+1 }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th>T</th>
                        <th>R</th>
                        <th>%</th>
                        @foreach($wig->masterLms->take(3) as $lm)
                            <th>W-1</th>
                            <th>W</th>
                            <th>TREN</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $unit)
                    @php
                        // Mocking unit data for the heatmap
                        $uT = rand(5000, 20000)/100;
                        $uR = rand(5000, 25000)/100;
                        $uPct = ($uR/$uT)*100;
                        $uWigBg = $uPct >= 100 ? 'cell-green' : 'cell-red';
                    @endphp
                    <tr>
                        <td class="unit-name">{{ strtoupper($unit->name) }}</td>
                        <td>{{ number_format($uT, 2) }}</td>
                        <td>{{ number_format($uR, 2) }}</td>
                        <td class="{{ $uWigBg }} font-bold">{{ number_format($uPct, 2) }}%</td>
                        
                        @foreach($wig->masterLms->take(3) as $lm)
                        @php
                            $lmW1 = rand(70, 150);
                            $lmW = rand(70, 150);
                            $bg = $lmW >= 100 ? 'cell-green' : 'cell-red';
                            $tren = $lmW >= $lmW1 ? '&#8679;' : '&#8681;';
                            $trenColor = $lmW >= $lmW1 ? 'text-green-600' : 'text-red-600';
                        @endphp
                        <td>{{ number_format($lmW1, 2) }}%</td>
                        <td class="{{ $bg }} font-bold">{{ number_format($lmW, 2) }}%</td>
                        <td class="{{ $trenColor }} font-bold text-sm">{!! $tren !!}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- SIDEBAR WIDGETS -->
        <div class="col-span-3 flex flex-col gap-3">
            <div>
                <div class="box-title">STATUS WIG {{ strtoupper(substr($wig->judul, 0, 10)) }}</div>
                <div class="border border-gray-300 p-4 bg-white flex justify-between items-center h-24">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold mr-2">14</div>
                        <div class="text-xs font-bold text-green-700">UP3 HIJAU<br><span class="text-xxs text-gray-500 font-normal">Menang</span></div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center font-bold mr-2">3</div>
                        <div class="text-xs font-bold text-red-700">UP3 MERAH<br><span class="text-xxs text-gray-500 font-normal">Kalah</span></div>
                    </div>
                </div>
            </div>
            
            <div class="flex-1">
                <div class="box-title">FOCUS AREA NEXT WEEK</div>
                <div class="border border-gray-300 p-4 bg-white h-full text-xs text-gray-700">
                    <ul class="list-disc pl-4 space-y-2">
                        <li>Perkuatan Eksekusi LM-1 di unit berkinerja merah.</li>
                        <li>Monitoring Penyelesaian Target Bulanan.</li>
                        <li>Evaluasi Kendala Eksekusi Lapangan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="page-break"></div>
    @endforeach
</body>
</html>
