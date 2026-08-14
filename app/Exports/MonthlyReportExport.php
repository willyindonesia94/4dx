<?php

namespace App\Exports;

use App\Models\MasterLm;
use App\Models\MasterUnit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyReportExport implements FromView, ShouldAutoSize, WithStyles
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

        // Filter LM by Matrix Bidang
        $lmsQuery = MasterLm::with(['wig', 'satuan']);
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $lmsQuery->whereHas('wig', fn($q) => $q->whereIn('divisi', $allowedDivisis));
        }
        $lms = $lmsQuery->get();

        // Filter Units by user hierarchy level
        $unitsQuery = MasterUnit::query();
        if (!$isSuperAdmin && $user && $user->unit) {
            $unitType = strtoupper(trim((string)$user->unit->type));
            if ($unitType === 'ULP') {
                $unitsQuery->where('id', $user->unit_id);
            } elseif ($unitType === 'UP3') {
                $unitsQuery->where(function($q) use ($user) {
                    $q->where('id', $user->unit_id)
                      ->orWhere('parent_id', $user->unit_id);
                });
            }
        }
        $units = $unitsQuery->get();
        $reportData = collect([]);

        // Get the end of the month day (28, 29, 30, 31)
        $periodeStart = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $periodeEnd = $periodeStart->copy()->endOfMonth();

        foreach ($lms as $lm) {
            $targetUnitIds = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                ->where('periode_start', '<=', $periodeEnd->format('Y-m-d'))
                ->where('periode_end', '>=', $periodeStart->format('Y-m-d'))
                ->pluck('unit_id');
                
            $realisasiUnitIds = \App\Models\Realisasi::where('lm_id', $lm->id)
                ->whereBetween('tanggal_input', [$periodeStart->format('Y-m-d 00:00:00'), $periodeEnd->format('Y-m-d 23:59:59')])
                ->pluck('unit_id');
                
            $applicableUnitIds = $targetUnitIds->concat($realisasiUnitIds)->unique();
            if ($isUlpLevel || $isUp3Level || $applicableUnitIds->isEmpty()) {
                $applicableUnits = $units;
            } else {
                $applicableUnits = $units->whereIn('id', $applicableUnitIds);
                if ($applicableUnits->isEmpty()) {
                    $applicableUnits = $units;
                }
            }

            $lmRows = [];
            $uidTotal = [
                'target' => 0,
                'r1' => 0, 'r2' => 0, 'r3' => 0, 'r4' => 0, 'r5' => 0,
                'total' => 0
            ];
            $hasUp3s = false;

            foreach ($applicableUnits as $unit) {
                $allTargets = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                    ->where('unit_id', $unit->id)
                    ->where('bulan', $this->month)
                    ->where('tahun', $this->year)
                    ->get();
                
                $target = $allTargets->sortByDesc(function ($t) {
                    return \Carbon\Carbon::parse($t->periode_start)->diffInDays(\Carbon\Carbon::parse($t->periode_end));
                })->first();
                
                $angkaTarget = $target ? $target->angka_target : 0;

                // Fetch Realisasis for this month
                $realisasis = \App\Models\Realisasi::where('lm_id', $lm->id)
                    ->where('unit_id', $unit->id)
                    ->whereBetween('tanggal_input', [$periodeStart->format('Y-m-d 00:00:00'), $periodeEnd->format('Y-m-d 23:59:59')])
                    ->orderBy('tanggal_input')
                    ->get();
                
                // Group by week (roughly)
                $r1 = $r2 = $r3 = $r4 = $r5 = 0;
                foreach ($realisasis as $r) {
                    $day = Carbon::parse($r->tanggal_input)->day;
                    if ($day <= 7) $r1 += $r->angka_realisasi;
                    elseif ($day <= 14) $r2 += $r->angka_realisasi;
                    elseif ($day <= 21) $r3 += $r->angka_realisasi;
                    elseif ($day <= 28) $r4 += $r->angka_realisasi;
                    else $r5 += $r->angka_realisasi;
                }

                $totalRealisasi = $r1 + $r2 + $r3 + $r4 + $r5;
                $capaian = 0;
                if ($angkaTarget > 0) {
                    if (($lm->polaritas ?? 'positif') === 'negatif') {
                        $diff = $angkaTarget - $totalRealisasi;
                        $percentage = 100 + (($diff / $angkaTarget) * 100);
                        $capaian = max(0, $percentage);
                    } else {
                        $capaian = ($totalRealisasi / $angkaTarget) * 100;
                    }
                }

                $rowData = [
                    'wig' => $lm->wig->judul ?? '-',
                    'lm' => $lm->judul_lm,
                    'satuan' => $lm->satuan->name ?? '',
                    'polaritas' => $lm->polaritas ?? 'positif',
                    'unit' => $unit->name,
                    'target' => $angkaTarget,
                    'r1' => $r1, 'r2' => $r2, 'r3' => $r3, 'r4' => $r4, 'r5' => $r5,
                    'total' => $totalRealisasi,
                    'capaian' => round($capaian, 2),
                    'is_uid' => ($unit->type === 'UID' || str_contains(strtoupper($unit->name), 'UID'))
                ];

                if (!$rowData['is_uid']) {
                    $lmRows[] = $rowData;
                    // Sum for UID if it's a UP3 (or if we just sum all non-UIDs)
                    // The user requested "total dari UP3 lainnya"
                    if ($unit->type === 'UP3' || str_contains(strtoupper($unit->name), 'UP3')) {
                        $uidTotal['target'] += $angkaTarget;
                        $uidTotal['r1'] += $r1;
                        $uidTotal['r2'] += $r2;
                        $uidTotal['r3'] += $r3;
                        $uidTotal['r4'] += $r4;
                        $uidTotal['r5'] += $r5;
                        $uidTotal['total'] += $totalRealisasi;
                        $hasUp3s = true;
                    }
                }
            }

            // Only push rows if there are applicable units for this LM in the current month
            if ($applicableUnits->isNotEmpty()) {
                // Calculate UID Capaian
                $uidCapaian = 0;
                if ($uidTotal['target'] > 0) {
                    if (($lm->polaritas ?? 'positif') === 'negatif') {
                        $diff = $uidTotal['target'] - $uidTotal['total'];
                        $percentage = 100 + (($diff / $uidTotal['target']) * 100);
                        $uidCapaian = max(0, $percentage);
                    } else {
                        $uidCapaian = ($uidTotal['total'] / $uidTotal['target']) * 100;
                    }
                }

                // Push UID Row First only if not ULP/UP3 level restricted
                if (!$isUlpLevel && !$isUp3Level) {
                    $reportData->push([
                        'wig' => $lm->wig->judul ?? '-',
                        'lm' => $lm->judul_lm,
                        'satuan' => $lm->satuan->name ?? '',
                        'polaritas' => $lm->polaritas ?? 'positif',
                        'unit' => 'UID Jawa Barat',
                        'target' => $uidTotal['target'],
                        'r1' => $uidTotal['r1'], 'r2' => $uidTotal['r2'], 'r3' => $uidTotal['r3'], 'r4' => $uidTotal['r4'], 'r5' => $uidTotal['r5'],
                        'total' => $uidTotal['total'],
                        'capaian' => round($uidCapaian, 2),
                        'is_uid' => true
                    ]);
                }

                // Then push the other units
                foreach ($lmRows as $row) {
                    $reportData->push($row);
                }
            }
        }

        return view('exports.lm_report', [
            'data' => $reportData,
            'bulan' => $periodeStart->translatedFormat('F'),
            'tahun' => $this->year
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
