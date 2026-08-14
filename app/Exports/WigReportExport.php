<?php

namespace App\Exports;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WigReportExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function view(): View
    {
        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP3'));

        $wigsQuery = MasterWig::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->whereIn('divisi', $allowedDivisis);
        }
        $wigs = $wigsQuery->get();

        $unitsQuery = MasterUnit::query();
        if (!$isSuperAdmin && $user && $user->unit) {
            $unitType = strtoupper(trim((string)$user->unit->type));
            if ($unitType === 'ULP') {
                $unitsQuery->where('type', 'ULP')->where('id', $user->unit_id);
            } elseif ($unitType === 'UP3') {
                $unitsQuery->where('type', 'ULP')->where('parent_id', $user->unit_id);
            } else {
                $unitsQuery->where('type', 'up3');
            }
        } else {
            $unitsQuery->where('type', 'up3');
        }
        $units = $unitsQuery->orderBy('name')->get();

        $isAllBulan = $this->month === 'all';
        $bulanT = $isAllBulan ? 12 : (int)$this->month;
        $tahunT = (int)$this->year;

        $reportData = [];
        $nonSummableSatuans = [1, 2, 6, 14];
        $targetBulan = $isAllBulan ? 12 : $bulanT;
        $colBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$targetBulan];

        foreach ($wigs as $wig) {
            $polaritasWig = strtolower(trim($wig->polaritas ?? 'positif'));
            $isNonSummableWig = in_array($wig->satuan_id, $nonSummableSatuans);

            $wigTargetQuery = DB::table('breakdown_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT);
            $wigRealQuery = DB::table('realisasi_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT);
                
            $wigTargetTot = 0;
            $wigRealTot = 0;

            if (!$isSuperAdmin && $user && $user->unit_id) {
                $wigTargetQuery->where('unit_id', $user->unit_id);
                $wigRealQuery->where('unit_id', $user->unit_id);
                
                $wigTargetTot = $wigTargetQuery->sum($colBln);
                $wigRealTot = $wigRealQuery->where('bulan', $targetBulan)->sum('angka_realisasi') ?? 0;
            } else {
                $wigTargetQuery->where('unit_id', 1);
                $wigTargetTot = $wigTargetQuery->sum($colBln);
                
                $wigRealQuery->where('bulan', $targetBulan)->where('unit_id', '!=', 1);
                if ($isNonSummableWig) {
                    $wigRealTot = $wigRealQuery->avg('angka_realisasi') ?? 0;
                } else {
                    $wigRealTot = $wigRealQuery->sum('angka_realisasi') ?? 0;
                }
            }

            $pctUid = 0;
            if ($wigTargetTot > 0) {
                if ($polaritasWig === 'negatif' || $polaritasWig === '3') {
                    $pctUid = ($wigTargetTot / max(0.0001, $wigRealTot)) * 100;
                } else {
                    $pctUid = ($wigRealTot / $wigTargetTot) * 100;
                }
            }

            $wigData = [
                'target' => $wigTargetTot,
                'realisasi' => $wigRealTot,
                'pct' => $pctUid,
                'units' => [],
            ];

            foreach ($units as $unit) {
                $uT = DB::table('breakdown_wigs')
                    ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('unit_id', $unit->id)
                    ->sum($colBln);
                $uR = DB::table('realisasi_wigs')
                    ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('bulan', $targetBulan)->where('unit_id', $unit->id)
                    ->sum('angka_realisasi') ?? 0;

                $uPct = 0;
                if ($uT > 0 || $uR > 0) {
                    if ($polaritasWig === 'negatif' || $polaritasWig === '3') {
                        $uPct = $uT > 0 ? ($uT / max(0.0001, $uR)) * 100 : 0;
                    } else {
                        $uPct = $uT > 0 ? ($uR / $uT) * 100 : 0;
                    }
                }
                
                $wigData['units'][$unit->id] = [
                    't' => $uT,
                    'r' => $uR,
                    'pct' => $uPct
                ];
            }

            $reportData[$wig->id] = $wigData;
        }

        $bulanName = $isAllBulan ? 'Sepanjang Tahun' : Carbon::createFromDate($this->year, $bulanT, 1)->translatedFormat('F');

        return view('exports.wig_report_table', [
            'reportData' => $reportData,
            'wigs' => $wigs,
            'units' => $units,
            'bulan' => $bulanName,
            'tahun' => $this->year,
            'isUlpLevel' => $isUlpLevel,
            'isUp3Level' => $isUp3Level
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'size' => 14]],
            3    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]],
            4    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]],
        ];
    }
}
