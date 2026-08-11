<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Lengkap - {{ $wigs->count() > 1 ? 'Semua WIG' : ($wigs->first()->judul ?? 'Tidak Ada WIG') }}</title>
    <style>
        @page { size: landscape; margin: 10mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #fff; padding: 0; color: #1e293b; font-size: 12px; }
        .text-xxs { font-size: 0.65rem; line-height: 1rem; }
        
        table.layout-table { width: 100%; border-collapse: collapse; }
        table.layout-table td { vertical-align: top; }
        
        table.heatmap { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        table.heatmap th, table.heatmap td { border: 1px solid #94a3b8; padding: 3px; text-align: center; font-size: 0.65rem; word-wrap: break-word; }
        table.heatmap th { background: #3b82f6; color: white; font-weight: bold; }
        table.heatmap td.unit-name { text-align: left; font-weight: bold; }
        
        .box-title { background: #0b2256; color: white; padding: 5px 10px; font-weight: bold; border-radius: 4px 4px 0 0; font-size: 0.85rem; }
        .box-content { border: 1px solid #0b2256; border-top: none; border-radius: 0 0 4px 4px; padding: 10px; }
        
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-weight: bold; font-size: 0.65rem; color: white; margin-top: 4px; text-align: center; }
        .bg-exceed { background-color: #22c55e; }
        .bg-watch { background-color: #ef4444; }
        
        .cell-green { background-color: #dcfce7; }
        .cell-red { background-color: #fee2e2; color: #b91c1c; }
        
        .page-break { page-break-after: always; }

        .no-print { display: none; }
    </style>
</head>
<body>

    @forelse($wigs as $wig)
    @php
        $wData = $reportData[$wig->id] ?? null;
        if (!$wData) continue;
        
        $wigTargetTot = $wData['target'];
        $wigRealTot = $wData['realisasi'];
        $pctUid = $wData['pct'];
    @endphp

    <!-- HEADER -->
    <table style="width: 100%; background: #0b2256; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
        <tr>
            <td style="width: 25%; text-align: left; font-weight: bold; font-size: 14px; vertical-align: middle;">
                {{ !empty($isUlpLevel) && $user && $user->unit ? strtoupper($user->unit->name) : (!empty($isUp3Level) && $user && $user->unit ? strtoupper($user->unit->name) : 'UID JAWA BARAT') }}
            </td>
            <td style="width: 50%; text-align: center; font-weight: bold; font-size: 20px; letter-spacing: 1px; vertical-align: middle;">
                {{ strtoupper($wig->judul) }}<br>
                <span style="font-size: 12px; font-weight: normal; color: #d1d5db;">Periode: {{ $isAllBulan ? 'Semua Bulan (Tahunan)' : \Carbon\Carbon::create()->month($bulanT)->translatedFormat('F') }} {{ $tahun }}</span>
            </td>
            <td style="width: 25%; text-align: right; font-weight: bold; color: #facc15; font-size: 18px; vertical-align: middle;">
                PLN
            </td>
        </tr>
    </table>

    <!-- WIG & LM CARDS -->
    <table class="layout-table" style="margin-bottom: 15px;">
        <tr>
            <!-- WIG PERFORMANCE -->
            <td style="width: 30%; padding-right: 10px;">
                <div class="box-title" style="text-transform: uppercase;">WIG PERFORMANCE | {{ $pctUid >= 100 ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH' }}</div>
                <div class="box-content" style="height: 90px;">
                    <table style="width: 100%; height: 100%;">
                        <tr>
                            <td style="width: 50%; text-align: center; vertical-align: middle;">
                                <div style="font-size: 24px; font-weight: bold; color: {{ $pctUid >= 100 ? '#16a34a' : '#dc2626' }};">{{ number_format($pctUid, 2) }} %</div>
                                <div style="font-size: 11px; font-weight: bold; color: #374151; margin-top: 5px;">Capaian WIG {{ !empty($isUlpLevel) ? 'ULP' : (!empty($isUp3Level) ? 'UP3' : 'UID Jabar') }}</div>
                                <div style="font-size: 10px; color: #6b7280; margin-top: 3px;">Target: {{ number_format($wigTargetTot, 2) }}</div>
                                <div style="font-size: 10px; color: #6b7280;">Realisasi: {{ number_format($wigRealTot, 2) }}</div>
                            </td>
                            <td style="width: 50%; border-left: 1px solid #e5e7eb; vertical-align: bottom; padding-left: 5px; text-align: center;">
                                <div style="font-size: 10px; font-weight: bold; color: #4b5563; margin-bottom: 5px;">TREND CAPAIAN WIG (%)</div>
                                <table style="width: 100%; height: 40px; border-collapse: separate; border-spacing: 2px;">
                                    <tr style="vertical-align: bottom;">
                                        @for($i=1; $i<=$bulanT; $i++)
                                            <td style="background: #3b82f6; width: 16%; padding: 0;">
                                                <div style="height: {{ max(1, min(100, $pctUid)) / 2.5 }}px;"></div>
                                            </td>
                                        @endfor
                                    </tr>
                                </table>
                                <div style="font-size: 8px; color: #9ca3af; font-weight: bold; margin-top: 2px;">
                                    <span style="float: left;">JAN</span>
                                    <span>..</span>
                                    <span style="float: right;">{{ $isAllBulan ? 'DES' : strtoupper(substr(\Carbon\Carbon::create()->month($bulanT)->translatedFormat('F'),0,3)) }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            
            <!-- LM CARDS -->
            <td style="width: 70%;">
                <table style="width: 100%; table-layout: fixed;">
                    <tr>
                        @foreach($wig->masterLms->take(3) as $idx => $lm)
                        @php
                            $lmData = $wData['lms'][$lm->id] ?? ['pct' => 0, 'menang' => 0, 'kalah' => 0];
                            $lmPct = $lmData['pct'];
                            $menang = $lmData['menang'];
                            $kalah = $lmData['kalah'];
                        @endphp
                        <td style="padding-left: {{ $idx == 0 ? '0' : '10px' }}; vertical-align: top;">
                            <div style="background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px; text-align: center; position: relative; height: 90px;">
                                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background-color: {{ $lmPct >= 100 ? '#22c55e' : '#ef4444' }}; border-radius: 4px 4px 0 0;"></div>
                                <div style="font-size: 11px; font-weight: bold; color: #374151; margin-bottom: 2px; margin-top: 5px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">LM {{ $idx+1 }} {{ $lm->judul_lm }}</div>
                                <div style="font-size: 22px; font-weight: bold; color: {{ $lmPct >= 100 ? '#16a34a' : '#dc2626' }}; margin: 2px 0;">{{ number_format($lmPct, 2) }} %</div>
                                <div><span class="status-badge {{ $lmPct >= 100 ? 'bg-exceed' : 'bg-watch' }}">{{ $lmPct >= 100 ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH' }}</span></div>
                                <div style="font-size: 9px; font-weight: bold; color: #1f2937; margin-top: 5px;">{{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} Menang: {{ $menang }} | {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} Kalah: {{ $kalah }}</div>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="layout-table">
        <tr>
            <!-- HEATMAP KINERJA UP3 / ULP -->
            <td style="width: 75%; padding-right: 15px;">
                <div class="box-title">HEATMAP KINERJA {{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} | WIG & LEAD MEASURE</div>
                <table class="heatmap">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 15%;">UNIT</th>
                            <th colspan="3">WIG</th>
                            @foreach($wig->masterLms->take(3) as $idx => $lm)
                                <th colspan="5">LM-{{ $idx+1 }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            <th style="width: 4%;">T</th>
                            <th style="width: 4%;">R</th>
                            <th style="width: 5%;">%</th>
                            @foreach($wig->masterLms->take(3) as $lm)
                                <th style="width: 3.5%;">M1</th>
                                <th style="width: 3.5%;">M2</th>
                                <th style="width: 3.5%;">M3</th>
                                <th style="width: 3.5%;">M4</th>
                                <th style="width: 3.5%;">M5</th>
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
                            <td class="{{ $uWigBg }}" style="font-weight: bold;">{{ number_format($uPct, 2) }}%</td>
                            
                            @foreach($wig->masterLms->take(3) as $lm)
                            @php
                                $lmWeeks = $uData['lms'][$lm->id] ?? [];
                            @endphp
                                @for($w = 1; $w <= 5; $w++)
                                @php
                                    $wPct = $lmWeeks[$w]['pct'] ?? 0;
                                    $bg = $wPct >= 100 ? 'cell-green' : 'cell-red';
                                @endphp
                                <td class="{{ $bg }}" style="font-weight: bold;">{{ number_format($wPct, 2) }}%</td>
                                @endfor
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            
            <!-- SIDEBAR WIDGETS -->
            <td style="width: 25%;">
                <div style="margin-bottom: 15px;">
                    <div class="box-title">STATUS {{ strtoupper($wig->judul) }}</div>
                    <div style="border: 1px solid #d1d5db; border-top: none; padding: 10px; background: white;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 50%; vertical-align: middle;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 35px;">
                                                <div style="width: 28px; height: 28px; background: #22c55e; color: white; text-align: center; border-radius: 14px; font-weight: bold; line-height: 28px;">{{ $wData['status_menang'] }}</div>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <div style="font-size: 11px; font-weight: bold; color: #15803d;">{{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} HIJAU</div>
                                                <div style="font-size: 9px; color: #6b7280;">Menang</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; vertical-align: middle;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 35px;">
                                                <div style="width: 28px; height: 28px; background: #ef4444; color: white; text-align: center; border-radius: 14px; font-weight: bold; line-height: 28px;">{{ $wData['status_kalah'] }}</div>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <div style="font-size: 11px; font-weight: bold; color: #b91c1c;">{{ !empty($isUlpLevel) || !empty($isUp3Level) ? 'ULP' : 'UP3' }} MERAH</div>
                                                <div style="font-size: 9px; color: #6b7280;">Kalah</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div>
                    <div class="box-title">FOCUS AREA NEXT WEEK</div>
                    <div style="border: 1px solid #d1d5db; border-top: none; padding: 10px; background: white; font-size: 11px; color: #374151;">
                        <ul style="margin: 0; padding-left: 15px;">
                            <li style="margin-bottom: 5px;">Perkuatan Eksekusi LM di unit berkinerja merah.</li>
                            <li style="margin-bottom: 5px;">Monitoring Penyelesaian Target Bulanan.</li>
                            <li>Evaluasi Kendala Eksekusi Lapangan.</li>
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="page-break"></div>
    @empty
    <div style="text-align: center; padding: 50px; background: #f9fafb; border: 1px solid #e5e7eb; margin: 40px; border-radius: 8px;">
        <h3 style="font-size: 20px; font-weight: bold; color: #1f2937; margin-bottom: 10px;">Tidak Ada Data WIG</h3>
        <p style="color: #6b7280;">Saat ini belum ada WIG yang terdaftar atau terhubung dengan lingkup kepemilikan Bidang/Divisi akun Anda untuk periode ini.</p>
    </div>
    @endforelse
</body>
</html>
