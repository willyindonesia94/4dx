<style>
    .sesi-wig-table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; }
    .sesi-wig-table th, .sesi-wig-table td { border: 1px solid #cbd5e1; padding: 5px 6px; }
    .sesi-wig-table thead th { font-weight: bold; }
    .bg-header-main { background-color: #1e3a8a; color: #ffffff; }
    .bg-header-sub { background-color: #2563eb; color: #ffffff; }
    .bg-header-col { background-color: #3b82f6; color: #ffffff; }
    .bg-header-co { background-color: #4f46e5; color: #ffffff; }
    .bg-uid-row { background-color: #e0e7ff; font-weight: bold; }
    .bg-up3-row { background-color: #f1f5f9; font-weight: bold; }
    .bg-ulp-row { background-color: #ffffff; }
    .text-co { color: #4338ca; font-weight: bold; }
    .text-success-bold { color: #15803d; font-weight: bold; }
    .text-danger-bold { color: #b91c1c; font-weight: bold; }
</style>

<!-- HEADER LAPORAN -->
<table border="1" cellpadding="6" class="sesi-wig-table" style="margin-bottom: 20px;">
    <tr>
        <th colspan="{{ 5 + (count($sesiWigs) * 6) }}" bgcolor="#1e3a8a" align="center" style="font-size: 14px; font-weight: bold; color: #ffffff;">
            LAPORAN SESI WIG 4DX PLN UID JAWA BARAT
        </th>
    </tr>
    <tr>
        <th colspan="{{ 5 + (count($sesiWigs) * 6) }}" bgcolor="#f1f5f9" align="center" style="font-size: 11px; font-weight: bold; color: #334155;">
            Periode: {{ $namaBulan }} {{ $tahun }} | Tanggal Unduh: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB
        </th>
    </tr>
</table>

<!-- BAGIAN 1: DAFTAR PELAKSANAAN SESI WIG -->
<table border="0" cellpadding="2" style="margin-top: 15px; margin-bottom: 5px;">
    <tr>
        <td style="font-size: 13px; font-weight: bold; color: #1e3a8a;">
            I. DAFTAR PELAKSANAAN SESI WIG ({{ strtoupper($namaBulan) }} {{ $tahun }})
        </td>
    </tr>
</table>

<table border="1" cellpadding="5" class="sesi-wig-table" style="margin-bottom: 25px;">
    <thead>
        <tr bgcolor="#f8fafc">
            <th align="center" width="40">No</th>
            <th align="left">Nama Sesi</th>
            <th align="center" width="90">Minggu Ke</th>
            <th align="center" width="120">Tgl Pelaksanaan</th>
            <th align="left">Presenter UP3</th>
            <th align="left">Presenter ULP</th>
            <th align="left">Catatan / Evaluasi Sesi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sesiWigs as $idx => $sw)
            @php
                $presUp3 = $sw->presenters->where('type', 'UP3')->pluck('name')->implode(', ') ?: '-';
                $presUlp = $sw->presenters->where('type', 'ULP')->pluck('name')->implode(', ') ?: '-';
                $evaluasiRaw = $sw->evaluasi ?: ($sw->komitmen ?: '-');
                $evaluasiClean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $evaluasiRaw);
                $namaSesiClean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $sw->nama_sesi);
            @endphp
            <tr bgcolor="{{ $idx % 2 == 1 ? '#f8fafc' : '#ffffff' }}">
                <td align="center">{{ $idx + 1 }}</td>
                <td align="left"><b>{{ $namaSesiClean }}</b></td>
                <td align="center">Minggu {{ $sw->minggu_ke ?? '-' }}</td>
                <td align="center">{{ $sw->tanggal_pelaksanaan ? \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format('d/m/Y') : '-' }}</td>
                <td align="left" style="color: #0f766e;">{{ $presUp3 }}</td>
                <td align="left" style="color: #0284c7;">{{ $presUlp }}</td>
                <td align="left" style="color: #475569;">{{ $evaluasiClean }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" align="center" style="color: #94a3b8; padding: 12px;">
                    Belum ada Sesi WIG yang dibuat untuk periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- BAGIAN 2: MATRIKS CAPAIAN LEAD MEASURE & CARRY OVER -->
<table border="0" cellpadding="2" style="margin-top: 15px; margin-bottom: 5px;">
    <tr>
        <td style="font-size: 13px; font-weight: bold; color: #1e3a8a;">
            II. MATRIKS CAPAIAN LEAD MEASURE &amp; CARRY OVER MINGGUAN
        </td>
    </tr>
    <tr>
        <td style="font-size: 10px; color: #64748b;">
            *Target+CO menghitung batas minimal 10% target breakdown jika terjadi carry over negatif (realisasi melebihi target).
        </td>
    </tr>
</table>

<table border="1" cellpadding="4" class="sesi-wig-table" style="margin-bottom: 25px;">
    <thead>
        <tr bgcolor="#1e3a8a" style="color: #ffffff;">
            <th rowspan="2" align="center" valign="middle">WIG Induk</th>
            <th rowspan="2" align="left" valign="middle">Lead Measure (LM)</th>
            <th rowspan="2" align="center" valign="middle">Satuan</th>
            <th rowspan="2" align="center" valign="middle">Polaritas</th>
            <th rowspan="2" align="left" valign="middle">Unit</th>
            <th rowspan="2" bgcolor="#1e3a8a" align="right" valign="middle" style="color: #ffffff;">Target Bulanan</th>
            <th rowspan="2" bgcolor="#1e3a8a" align="right" valign="middle" style="color: #ffffff;">Carry Over Bulan Lalu</th>
            @foreach($sesiWigs as $sw)
                <th colspan="6" bgcolor="#2563eb" align="center" valign="middle" style="color: #ffffff;">
                    {{ $sw->nama_sesi }}
                </th>
            @endforeach
            <th rowspan="2" bgcolor="#1e3a8a" align="right" valign="middle" style="color: #ffffff;">Realisasi Bulanan</th>
            <th rowspan="2" bgcolor="#1e3a8a" align="center" valign="middle" style="color: #ffffff;">% Capaian Bulan</th>
        </tr>
        <tr bgcolor="#3b82f6" style="color: #ffffff;">
            @foreach($sesiWigs as $sw)
                <th bgcolor="#3b82f6" align="right" style="color: #ffffff;">Target Awal</th>
                <th bgcolor="#4338ca" align="right" style="color: #ffffff;">Target+CO</th>
                <th bgcolor="#3b82f6" align="right" style="color: #ffffff;">Komitmen</th>
                <th bgcolor="#3b82f6" align="right" style="color: #ffffff;">Realisasi</th>
                <th bgcolor="#3b82f6" align="center" style="color: #ffffff;">Capaian (%)</th>
                <th bgcolor="#3b82f6" align="center" style="color: #ffffff;">Carry Over</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($lms as $lm)
            @php
                $isDecimalLm = in_array((int)($lm->satuan_id ?? 0), [7, 8, 13]) 
                    || str_contains(strtolower($lm->satuan->name ?? ''), 'mva') 
                    || str_contains(strtolower($lm->satuan->name ?? ''), 'kwh') 
                    || str_contains(strtolower($lm->satuan->name ?? ''), 'gwh')
                    || str_contains(strtolower($lm->judul_lm ?? ''), 'daya tersambung')
                    || str_contains(strtolower($lm->judul_lm ?? ''), 'smart p2tl')
                    || str_contains(strtolower($lm->judul_lm ?? ''), 'mva')
                    || str_contains(strtolower($lm->judul_lm ?? ''), 'kwh');
                $isPercent = (($lm->satuan_id ?? null) == 1) || (trim($lm->satuan->name ?? '') === '%');
                $isPolaritasNegatif = strtolower(trim($lm->polaritas ?? 'positif')) === 'negatif' || ($lm->polaritas === '3');
                $isEligibleCarryOver = !$isPercent && !$isPolaritasNegatif;
                $isNonSummable = in_array($lm->satuan_id, [1, 2, 6, 14]);

                $lmUnitData = [];

                // 1. ULPs
                foreach ($allUlps as $u) {
                    $runningCO = 0;
                    $kumReal = 0;
                    $prevT = 0;
                    $ulpTargetBulanan = $matrixTargetBulanan[$lm->id][$u->id] ?? 0;
                    $ulpDefisitBulanLalu = $matrixDefisitBulanLalu[$lm->id][$u->id] ?? 0;

                    foreach ($sesiWigs as $swIdx => $sw) {
                        $t = (float)($matrixTargets[$lm->id][$u->id][$sw->id] ?? 0);
                        $r = (float)($matrixRealisasi[$lm->id][$u->id][$sw->id] ?? 0);

                        if (!$isEligibleCarryOver) {
                            $tPlusCO = $t;
                            $co = 0;
                        } else {
                            if ($swIdx === 0) {
                                $tPlusCO = round($t + $ulpDefisitBulanLalu, 2);
                            } else {
                                if ($ulpTargetBulanan > 0 && $kumReal >= $ulpTargetBulanan) {
                                    $baseTarget = $t > 0 ? $t : $prevT;
                                    $floorT = ($isDecimalLm || ($baseTarget != round($baseTarget))) ? ($baseTarget > 0 ? max(0.01, round($baseTarget * 0.10, 2)) : 0) : ($baseTarget > 0 ? max(1, (int)ceil($baseTarget * 0.10)) : 0);
                                    $tPlusCO = $floorT;
                                } else {
                                    $tPlusCO = max($t, round($t + $runningCO, 2));
                                }
                            }
                            $co = round($tPlusCO - $r, 2);
                        }
                        if ($t > 0) $prevT = $t;
                        $runningCO = $co;
                        $kumReal += $r;

                        $lmUnitData[$u->id][$sw->id] = [
                            'target' => $t,
                            'targetPlusCO' => $tPlusCO,
                            'carryOver' => $co,
                            'realisasi' => $r,
                            'kumulatifReal' => $kumReal,
                        ];
                    }
                }

                // 2. UP3
                foreach ($up3s as $up3) {
                    $ulpsOfThisUp3 = $allUlps->where('parent_id', $up3->id);
                    if ($ulpsOfThisUp3->isNotEmpty()) {
                        $kumUp3Real = 0;
                        foreach ($sesiWigs as $swIdx => $sw) {
                            $tSum = 0;
                            $tPlusCOSum = 0;
                            $coSum = 0;
                            $rSum = 0;
                            $cnt = count($ulpsOfThisUp3);

                            foreach ($ulpsOfThisUp3 as $u) {
                                $d = $lmUnitData[$u->id][$sw->id] ?? ['target' => 0, 'targetPlusCO' => 0, 'carryOver' => 0, 'realisasi' => 0];
                                $tSum += $d['target'];
                                $tPlusCOSum += $d['targetPlusCO'];
                                $coSum += $d['carryOver'];
                                $rSum += $d['realisasi'];
                            }

                            if ($isNonSummable) {
                                $tSum = $cnt > 0 ? $tSum / $cnt : 0;
                                $tPlusCOSum = $cnt > 0 ? $tPlusCOSum / $cnt : 0;
                                $coSum = $cnt > 0 ? $coSum / $cnt : 0;
                                $rSum = $cnt > 0 ? $rSum / $cnt : 0;
                            }

                            $kumUp3Real += $rSum;
                            $lmUnitData[$up3->id][$sw->id] = [
                                'target' => round($tSum, 2),
                                'targetPlusCO' => round($tPlusCOSum, 2),
                                'carryOver' => round($coSum, 2),
                                'realisasi' => round($rSum, 2),
                                'kumulatifReal' => round($kumUp3Real, 2),
                            ];
                        }
                    } else {
                        $runningCO = 0;
                        $kumReal = 0;
                        $prevT = 0;
                        $up3TargetBulanan = $matrixTargetBulanan[$lm->id][$up3->id] ?? 0;
                        $up3DefisitBulanLalu = $matrixDefisitBulanLalu[$lm->id][$up3->id] ?? 0;

                        foreach ($sesiWigs as $swIdx => $sw) {
                            $t = (float)($matrixTargets[$lm->id][$up3->id][$sw->id] ?? 0);
                            $r = (float)($matrixRealisasi[$lm->id][$up3->id][$sw->id] ?? 0);

                            if (!$isEligibleCarryOver) {
                                $tPlusCO = $t;
                                $co = 0;
                            } else {
                                if ($swIdx === 0) {
                                    $tPlusCO = round($t + $up3DefisitBulanLalu, 2);
                                } else {
                                    if ($up3TargetBulanan > 0 && $kumReal >= $up3TargetBulanan) {
                                        $baseTarget = $t > 0 ? $t : $prevT;
                                        $floorT = ($isDecimalLm || ($baseTarget != round($baseTarget))) ? ($baseTarget > 0 ? max(0.01, round($baseTarget * 0.10, 2)) : 0) : ($baseTarget > 0 ? max(1, (int)ceil($baseTarget * 0.10)) : 0);
                                        $tPlusCO = $floorT;
                                    } else {
                                        $tPlusCO = max($t, round($t + $runningCO, 2));
                                    }
                                }
                                $co = round($tPlusCO - $r, 2);
                            }
                            if ($t > 0) $prevT = $t;
                            $runningCO = $co;
                            $kumReal += $r;

                            $lmUnitData[$up3->id][$sw->id] = [
                                'target' => $t,
                                'targetPlusCO' => $tPlusCO,
                                'carryOver' => $co,
                                'realisasi' => $r,
                                'kumulatifReal' => $kumReal,
                            ];
                        }
                    }
                }

                // 3. UID
                $kumUidReal = 0;
                foreach ($sesiWigs as $swIdx => $sw) {
                    $tSum = 0;
                    $tPlusCOSum = 0;
                    $coSum = 0;
                    $rSum = 0;
                    $cnt = count($up3s);

                    foreach ($up3s as $up3) {
                        $d = $lmUnitData[$up3->id][$sw->id] ?? ['target' => 0, 'targetPlusCO' => 0, 'carryOver' => 0, 'realisasi' => 0];
                        $tSum += $d['target'];
                        $tPlusCOSum += $d['targetPlusCO'];
                        $coSum += $d['carryOver'];
                        $rSum += $d['realisasi'];
                    }

                    if ($isNonSummable) {
                        $tSum = $cnt > 0 ? $tSum / $cnt : 0;
                        $tPlusCOSum = $cnt > 0 ? $tPlusCOSum / $cnt : 0;
                        $coSum = $cnt > 0 ? $coSum / $cnt : 0;
                        $rSum = $cnt > 0 ? $rSum / $cnt : 0;
                    }

                    $kumUidReal += $rSum;
                    $lmUnitData[1][$sw->id] = [
                        'target' => round($tSum, 2),
                        'targetPlusCO' => round($tPlusCOSum, 2),
                        'carryOver' => round($coSum, 2),
                        'realisasi' => round($rSum, 2),
                        'kumulatifReal' => round($kumUidReal, 2),
                    ];
                }
            @endphp

            <!-- LEVEL UID JAWA BARAT -->
            @if(!$isUlpLevel && !$isUp3Level)
            @php
                $uidTargetBulan = $matrixTargetBulanan[$lm->id][1] ?? 0;
                $uidDefisitLalu = $matrixDefisitBulanLalu[$lm->id][1] ?? 0;
            @endphp
            <tr bgcolor="#e0e7ff" style="font-weight: bold;">
                <td align="left">{{ $lm->wig->judul ?? '-' }}</td>
                <td align="left">{{ $lm->judul_lm }}</td>
                <td align="center">{{ $lm->satuan->name ?? '' }}</td>
                <td align="center">{{ ucfirst($lm->polaritas ?? 'positif') }}</td>
                <td align="left" style="color: #1e3a8a;">UID JAWA BARAT</td>
                <td align="right">{{ number_format($uidTargetBulan, 2) }}</td>
                <td align="right">{{ number_format($uidDefisitLalu, 2) }}</td>
                @foreach($sesiWigs as $swIdx => $sw)
                    @php
                        $t = $lmUnitData[1][$sw->id]['target'] ?? 0;
                        $tPlusCO = $lmUnitData[1][$sw->id]['targetPlusCO'] ?? 0;
                        $coThisWeek = $lmUnitData[1][$sw->id]['carryOver'] ?? 0;
                        $r = $lmUnitData[1][$sw->id]['realisasi'] ?? 0;
                        $k = $matrixKomitmen[$lm->id][1][$sw->id]['komitmen'] ?? null;
                        $pct = $calcCapaian($tPlusCO, $r, $lm->polaritas ?? 'positif');
                    @endphp
                    <td align="right">{{ number_format($t, 2) }}</td>
                    <td align="right" bgcolor="#ede9fe" style="color: #4338ca; font-weight: bold;">{{ number_format($tPlusCO, 2) }}</td>
                    <td align="right">{{ $k !== null ? number_format((float)$k, 2) : '-' }}</td>
                    <td align="right">{{ number_format($r, 2) }}</td>
                    <td align="center" style="color: {{ $pct !== null ? ($pct >= 100 ? '#15803d' : '#b91c1c') : '#64748b' }}; font-weight: bold;">{{ $pct !== null ? number_format($pct, 2) . '%' : '-' }}</td>
                    <td align="center" style="color: {{ $coThisWeek < 0 ? '#15803d' : ($coThisWeek > 0 ? '#4338ca' : '#64748b') }}; font-weight: bold;">{{ number_format($coThisWeek, 2) }}</td>
                @endforeach
                @php
                    $totalUidReal = $sesiWigs->isNotEmpty() ? ($lmUnitData[1][$sesiWigs->last()->id]['kumulatifReal'] ?? 0) : 0;
                    $finalUidReal = $isPercent ? (count($sesiWigs) > 0 ? $totalUidReal / count($sesiWigs) : 0) : $totalUidReal;
                    $uidCapaianBulan = $calcCapaian($uidTargetBulan, $finalUidReal, $lm->polaritas ?? 'positif');
                @endphp
                <td align="right" style="font-weight: bold;">{{ number_format($finalUidReal, 2) }}</td>
                <td align="center" style="color: {{ $uidCapaianBulan !== null ? ($uidCapaianBulan >= 100 ? '#15803d' : '#b91c1c') : '#64748b' }}; font-weight: bold;">{{ $uidCapaianBulan !== null ? number_format($uidCapaianBulan, 2) . '%' : '-' }}</td>
            </tr>
            @endif

            <!-- LEVEL UP3 -->
            @foreach($up3s as $up3)
                @php
                    $up3TargetBulan = $matrixTargetBulanan[$lm->id][$up3->id] ?? 0;
                    $up3DefisitLalu = $matrixDefisitBulanLalu[$lm->id][$up3->id] ?? 0;
                @endphp
                <tr bgcolor="#f8fafc">
                    <td align="left">{{ $lm->wig->judul ?? '-' }}</td>
                    <td align="left">{{ $lm->judul_lm }}</td>
                    <td align="center">{{ $lm->satuan->name ?? '' }}</td>
                    <td align="center">{{ ucfirst($lm->polaritas ?? 'positif') }}</td>
                    <td align="left" style="font-weight: bold; color: #0369a1;">{{ $up3->name }}</td>
                    <td align="right" style="font-weight: bold;">{{ number_format($up3TargetBulan, 2) }}</td>
                    <td align="right" style="font-weight: bold;">{{ number_format($up3DefisitLalu, 2) }}</td>
                    @foreach($sesiWigs as $swIdx => $sw)
                        @php
                            $t = $lmUnitData[$up3->id][$sw->id]['target'] ?? 0;
                            $tPlusCO = $lmUnitData[$up3->id][$sw->id]['targetPlusCO'] ?? 0;
                            $coThisWeek = $lmUnitData[$up3->id][$sw->id]['carryOver'] ?? 0;
                            $r = $lmUnitData[$up3->id][$sw->id]['realisasi'] ?? 0;
                            $k = $matrixKomitmen[$lm->id][$up3->id][$sw->id]['komitmen'] ?? null;
                            $pct = $calcCapaian($tPlusCO, $r, $lm->polaritas ?? 'positif');
                        @endphp
                        <td align="right">{{ number_format($t, 2) }}</td>
                        <td align="right" bgcolor="#f5f3ff" style="color: #4338ca; font-weight: bold;">{{ number_format($tPlusCO, 2) }}</td>
                        <td align="right">{{ $k !== null ? number_format((float)$k, 2) : '-' }}</td>
                        <td align="right">{{ number_format($r, 2) }}</td>
                        <td align="center" style="color: {{ $pct !== null ? ($pct >= 100 ? '#15803d' : '#b91c1c') : '#64748b' }}; font-weight: bold;">{{ $pct !== null ? number_format($pct, 2) . '%' : '-' }}</td>
                        <td align="center" style="color: {{ $coThisWeek < 0 ? '#15803d' : ($coThisWeek > 0 ? '#4338ca' : '#64748b') }}; font-weight: bold;">{{ number_format($coThisWeek, 2) }}</td>
                    @endforeach
                    @php
                        $totalUp3Real = $sesiWigs->isNotEmpty() ? ($lmUnitData[$up3->id][$sesiWigs->last()->id]['kumulatifReal'] ?? 0) : 0;
                        $finalUp3Real = $isPercent ? (count($sesiWigs) > 0 ? $totalUp3Real / count($sesiWigs) : 0) : $totalUp3Real;
                        $up3CapaianBulan = $calcCapaian($up3TargetBulan, $finalUp3Real, $lm->polaritas ?? 'positif');
                    @endphp
                    <td align="right" style="font-weight: bold;">{{ number_format($finalUp3Real, 2) }}</td>
                    <td align="center" style="color: {{ $up3CapaianBulan !== null ? ($up3CapaianBulan >= 100 ? '#15803d' : '#b91c1c') : '#64748b' }}; font-weight: bold;">{{ $up3CapaianBulan !== null ? number_format($up3CapaianBulan, 2) . '%' : '-' }}</td>
                </tr>

                <!-- LEVEL ULP (Di bawah UP3 terkait) -->
                @php
                    $ulpsOfUp3 = $allUlps->where('parent_id', $up3->id);
                @endphp
                @foreach($ulpsOfUp3 as $ulp)
                    @php
                        $ulpTargetBulan = $matrixTargetBulanan[$lm->id][$ulp->id] ?? 0;
                        $ulpDefisitLalu = $matrixDefisitBulanLalu[$lm->id][$ulp->id] ?? 0;
                    @endphp
                    <tr bgcolor="#ffffff">
                        <td align="left" style="color: #64748b;">{{ $lm->wig->judul ?? '-' }}</td>
                        <td align="left" style="color: #64748b;">{{ $lm->judul_lm }}</td>
                        <td align="center" style="color: #64748b;">{{ $lm->satuan->name ?? '' }}</td>
                        <td align="center" style="color: #64748b;">{{ ucfirst($lm->polaritas ?? 'positif') }}</td>
                        <td align="left" style="color: #334155;">&nbsp;&nbsp;&bull; {{ $ulp->name }}</td>
                        <td align="right" style="color: #334155;">{{ number_format($ulpTargetBulan, 2) }}</td>
                        <td align="right" style="color: #334155;">{{ number_format($ulpDefisitLalu, 2) }}</td>
                        @foreach($sesiWigs as $swIdx => $sw)
                            @php
                                $t = $lmUnitData[$ulp->id][$sw->id]['target'] ?? 0;
                                $tPlusCO = $lmUnitData[$ulp->id][$sw->id]['targetPlusCO'] ?? 0;
                                $coThisWeek = $lmUnitData[$ulp->id][$sw->id]['carryOver'] ?? 0;
                                $r = $lmUnitData[$ulp->id][$sw->id]['realisasi'] ?? 0;
                                $k = $matrixKomitmen[$lm->id][$ulp->id][$sw->id]['komitmen'] ?? null;
                                $pct = $calcCapaian($tPlusCO, $r, $lm->polaritas ?? 'positif');
                            @endphp
                            <td align="right">{{ number_format($t, 2) }}</td>
                            <td align="right" bgcolor="#faf5ff" style="color: #4338ca;">{{ number_format($tPlusCO, 2) }}</td>
                            <td align="right">{{ $k !== null ? number_format((float)$k, 2) : '-' }}</td>
                            <td align="right">{{ number_format($r, 2) }}</td>
                            <td align="center" style="color: {{ $pct !== null ? ($pct >= 100 ? '#15803d' : '#b91c1c') : '#64748b' }};">{{ $pct !== null ? number_format($pct, 2) . '%' : '-' }}</td>
                            <td align="center" style="color: {{ $coThisWeek < 0 ? '#15803d' : ($coThisWeek > 0 ? '#4338ca' : '#64748b') }};">{{ number_format($coThisWeek, 2) }}</td>
                        @endforeach
                        @php
                            $totalUlpReal = $sesiWigs->isNotEmpty() ? ($lmUnitData[$ulp->id][$sesiWigs->last()->id]['kumulatifReal'] ?? 0) : 0;
                            $finalUlpReal = $isPercent ? (count($sesiWigs) > 0 ? $totalUlpReal / count($sesiWigs) : 0) : $totalUlpReal;
                            $ulpCapaianBulan = $calcCapaian($ulpTargetBulan, $finalUlpReal, $lm->polaritas ?? 'positif');
                        @endphp
                        <td align="right">{{ number_format($finalUlpReal, 2) }}</td>
                        <td align="center" style="color: {{ $ulpCapaianBulan !== null ? ($ulpCapaianBulan >= 100 ? '#15803d' : '#b91c1c') : '#64748b' }}; font-weight: bold;">{{ $ulpCapaianBulan !== null ? number_format($ulpCapaianBulan, 2) . '%' : '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>

<!-- BAGIAN 3: REKAP DETAIL KOMITMEN, KENDALA & AKSI KONKRIT -->
<table border="0" cellpadding="2" style="margin-top: 15px; margin-bottom: 5px;">
    <tr>
        <td style="font-size: 13px; font-weight: bold; color: #1e3a8a;">
            III. REKAP FORMULIR KOMITMEN, HAMBATAN &amp; AKSI KONKRIT UNIT
        </td>
    </tr>
</table>

<table border="1" cellpadding="5" class="sesi-wig-table" style="margin-bottom: 25px;">
    <thead>
        <tr bgcolor="#f8fafc">
            <th align="center" width="40">No</th>
            <th align="left" width="140">Sesi WIG</th>
            <th align="left" width="180">Unit</th>
            <th align="left" width="220">Lead Measure</th>
            <th align="right" width="100">Angka Komitmen</th>
            <th align="left">Hambatan / Kendala</th>
            <th align="left">Aksi Konkrit / Rencana Kerja</th>
        </tr>
    </thead>
    <tbody>
        @php $noKom = 1; @endphp
        @forelse($detailKomitmens as $kom)
            @php
                $hambatanItems = [];
                if (is_array($kom->hambatans)) {
                    foreach ($kom->hambatans as $h) {
                        if (is_array($h)) {
                            $hPart = trim($h['hambatan'] ?? '');
                            $dPart = trim($h['dukungan'] ?? '');
                            $txt = $hPart . ($dPart !== '' ? " (Dukungan: {$dPart})" : '');
                            if ($txt !== '') $hambatanItems[] = $txt;
                        } elseif (is_string($h) && trim($h) !== '') {
                            $hambatanItems[] = trim($h);
                        }
                    }
                }
                $hambatanStr = !empty($hambatanItems) ? implode('; ', $hambatanItems) : '-';
                $hambatanStr = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $hambatanStr);

                $aksiItems = [];
                if (is_array($kom->aksi_konkrits)) {
                    foreach ($kom->aksi_konkrits as $a) {
                        if (is_array($a)) {
                            $aksi = trim($a['aksi'] ?? '');
                            $detail = trim($a['detail_komitmen'] ?? '');
                            $tgt = trim($a['target'] ?? '');
                            $dl = trim($a['deadline'] ?? '');
                            $txt = $aksi . ($detail !== '' ? ": {$detail}" : '') . ($tgt !== '' ? " (Target: {$tgt})" : '') . ($dl !== '' ? " [DL: {$dl}]" : '');
                            if ($txt !== '') $aksiItems[] = $txt;
                        } elseif (is_string($a) && trim($a) !== '') {
                            $aksiItems[] = trim($a);
                        }
                    }
                }
                $aksiStr = !empty($aksiItems) ? implode('; ', $aksiItems) : '-';
                $aksiStr = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $aksiStr);
            @endphp
            <tr bgcolor="{{ $noKom % 2 == 0 ? '#f8fafc' : '#ffffff' }}">
                <td align="center">{{ $noKom++ }}</td>
                <td align="left">{{ $kom->sesiWig->nama_sesi ?? '-' }}</td>
                <td align="left" style="font-weight: bold; color: #0369a1;">{{ $kom->masterUnit->name ?? '-' }}</td>
                <td align="left">{{ $kom->masterLm->judul_lm ?? '-' }}</td>
                <td align="right" style="font-weight: bold; color: #4338ca;">{{ number_format((float)$kom->komitmen, 2) }}</td>
                <td align="left" style="color: #dc2626;">{{ $hambatanStr ?: '-' }}</td>
                <td align="left" style="color: #15803d;">{{ $aksiStr ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" align="center" style="color: #94a3b8; padding: 12px;">
                    Belum ada data formulir komitmen yang diisi untuk sesi pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
