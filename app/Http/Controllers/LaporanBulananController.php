<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LmImportTemplateExport;
use App\Imports\HistoricalDataImport;
use App\Models\MasterLm;
use App\Models\BreakdownLm;
use App\Models\Realisasi;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBulananController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        
        $export = new \App\Exports\MonthlyReportExport($tahun, $bulan);
        $previewView = $export->view();
        $previewData = $previewView->getData();

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Admin UID']);
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP3'));

        $wigsQuery = \App\Models\MasterWig::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->whereIn('divisi', $allowedDivisis);
        }
        $availableWigs = $wigsQuery->get();

        return view('laporan-bulanan.index', compact('bulan', 'tahun', 'previewData', 'availableWigs', 'isUlpLevel', 'isUp3Level', 'userMatrixGroup'));
    }

    public function downloadTemplate(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));
        
        $filename = "Template_Import_Historis_{$tahun}_{$bulan}.xlsx";
        return Excel::download(new LmImportTemplateExport($tahun, $bulan), $filename);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:10240',
            'tanggal_awal_periode' => 'required|date'
        ]);

        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $tanggalAwal = $request->input('tanggal_awal_periode');

        try {
            Excel::import(new HistoricalDataImport($tahun, $bulan, $tanggalAwal), $request->file('file_excel'));
            return redirect()->back()->with('success', 'Data historis berhasil diimpor!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function exportLaporan(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $format = $request->input('format', 'excel');
        
        $filename = "Laporan_LM_{$tahun}_{$bulan}";
        $export = new \App\Exports\MonthlyReportExport($tahun, $bulan);

        if ($format === 'pdf') {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', '300');
            
            $view = $export->view();
            $pdf = Pdf::loadView($view->name(), $view->getData())
                      ->setPaper('legal', 'landscape');
            return $pdf->download("{$filename}.pdf");
        }

        return Excel::download($export, "{$filename}.xlsx");
    }

    public function exportWig(Request $request)
    {
        // Placeholder for WIG Export (Excel)
        return back()->with('error', 'Export WIG format Excel belum diimplementasikan sepenuhnya.');
    }


    public function exportLengkap(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $wigId = $request->input('wig_id');
        
        if (!$wigId) {
            return back()->with('error', 'Pilih WIG terlebih dahulu.');
        }

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Admin UID']);
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP3'));

        if ($wigId === 'all') {
            $wigsQuery = \App\Models\MasterWig::with(['masterLms' => function($q) {
                $q->orderBy('judul_lm');
            }]);
            if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
                $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
                $wigsQuery->whereIn('divisi', $allowedDivisis);
            }
            $wigs = $wigsQuery->get();
        } else {
            $wig = \App\Models\MasterWig::with(['masterLms' => function($q) {
                $q->orderBy('judul_lm');
            }])->findOrFail($wigId);
            $wigs = collect([$wig]);
        }
        
        $unitsQuery = \App\Models\MasterUnit::query();
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
        $unitIds = $units->pluck('id')->toArray();

        $isAllBulan = $bulan === 'all';
        $bulanT = $isAllBulan ? 12 : (int)$bulan;
        $tahunT = (int)$tahun;

        // Get Periodes for M1-M5
        $masterPeriode = \App\Models\MasterPeriode::where('tahun', $tahunT)->where('bulan', $bulanT)->first();
        $weeklyCalendars = [];
        if ($masterPeriode) {
            if ($masterPeriode->start_m1 && $masterPeriode->end_m1) $weeklyCalendars[1] = ['start' => $masterPeriode->start_m1, 'end' => $masterPeriode->end_m1];
            if ($masterPeriode->start_m2 && $masterPeriode->end_m2) $weeklyCalendars[2] = ['start' => $masterPeriode->start_m2, 'end' => $masterPeriode->end_m2];
            if ($masterPeriode->start_m3 && $masterPeriode->end_m3) $weeklyCalendars[3] = ['start' => $masterPeriode->start_m3, 'end' => $masterPeriode->end_m3];
            if ($masterPeriode->start_m4 && $masterPeriode->end_m4) $weeklyCalendars[4] = ['start' => $masterPeriode->start_m4, 'end' => $masterPeriode->end_m4];
            if ($masterPeriode->start_m5 && $masterPeriode->end_m5) $weeklyCalendars[5] = ['start' => $masterPeriode->start_m5, 'end' => $masterPeriode->end_m5];
        }

        // Prepare Report Data Structure
        $reportData = [];

        foreach ($wigs as $wig) {
            $polaritasWig = strtolower(trim($wig->polaritas ?? 'positif'));
            
            // Overall WIG Target & Realisasi
            $wigTargetTot = 0;
            $wigRealTot = 0;
            
            $wigTargetQuery = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT);
            $wigRealQuery = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT);
                
            if (!$isSuperAdmin && $user && $user->unit_id) {
                $wigTargetQuery->where('unit_id', $user->unit_id);
                $wigRealQuery->where('unit_id', $user->unit_id);
            }

            if ($isAllBulan) {
                $wigTargetTot = $wigTargetQuery->sum(\Illuminate\Support\Facades\DB::raw('target_jan + target_feb + target_mar + target_apr + target_mei + target_jun + target_jul + target_agu + target_sep + target_okt + target_nov + target_des'));
                $wigRealTot = $wigRealQuery->sum('angka_realisasi');
            } else {
                $colBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$bulanT];
                $wigTargetTot = $wigTargetQuery->sum($colBln);
                $wigRealQuery->where('bulan', $bulanT);
                $wigRealTot = $wigRealQuery->sum('angka_realisasi');
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
                'lms' => [],
                'units' => [],
                'status_menang' => 0,
                'status_kalah' => 0,
            ];

            // Unit Data
            foreach ($units as $unit) {
                // Unit WIG Target & Realisasi
                $uT = 0; $uR = 0;
                if ($isAllBulan) {
                    $uT = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                        ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('unit_id', $unit->id)
                        ->sum(\Illuminate\Support\Facades\DB::raw('target_jan + target_feb + target_mar + target_apr + target_mei + target_jun + target_jul + target_agu + target_sep + target_okt + target_nov + target_des'));
                    $uR = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                        ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('unit_id', $unit->id)
                        ->sum('angka_realisasi');
                } else {
                    $colBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$bulanT];
                    $uT = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                        ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('unit_id', $unit->id)
                        ->sum($colBln);
                    $uR = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                        ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('bulan', $bulanT)->where('unit_id', $unit->id)
                        ->sum('angka_realisasi');
                }

                $uPct = 0;
                if ($uT > 0 || $uR > 0) {
                    if ($polaritasWig === 'negatif' || $polaritasWig === '3') {
                        $uPct = $uT > 0 ? ($uT / max(0.0001, $uR)) * 100 : 0;
                    } else {
                        $uPct = $uT > 0 ? ($uR / $uT) * 100 : 0;
                    }
                }
                
                if ($uPct >= 100) {
                    $wigData['status_menang']++;
                } else {
                    $wigData['status_kalah']++;
                }

                $wigData['units'][$unit->id] = [
                    't' => $uT,
                    'r' => $uR,
                    'pct' => $uPct,
                    'lms' => []
                ];
            }

            // LM Data
            foreach ($wig->masterLms->take(3) as $lm) {
                $lmPolaritas = strtolower(trim($lm->polaritas ?? 'positif'));
                
                $lmTotalTarget = 0;
                $lmTotalReal = 0;
                $lmMenang = 0;
                $lmKalah = 0;

                // Calculate LM per Unit per Week
                foreach ($units as $unit) {
                    $unitLmWeeks = [];
                    $unitLmTotalT = 0;
                    $unitLmTotalR = 0;

                    for ($w = 1; $w <= 5; $w++) {
                        $wT = 0; $wR = 0; $wPct = 0;
                        if (isset($weeklyCalendars[$w])) {
                            $wStart = $weeklyCalendars[$w]['start'];
                            $wEnd = $weeklyCalendars[$w]['end'];

                            $wT = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                                ->where('unit_id', $unit->id)
                                ->where('periode_start', $wStart)
                                ->where('periode_end', $wEnd)
                                ->sum('angka_target');
                            
                            $wR = \App\Models\Realisasi::where('lm_id', $lm->id)
                                ->whereHas('user', function($q) use ($unit) {
                                    $q->where('unit_id', $unit->id);
                                })
                                ->where('tanggal_input', '>=', $wStart . ' 00:00:00')
                                ->where('tanggal_input', '<=', $wEnd . ' 23:59:59')
                                ->sum('angka_realisasi');
                        }
                        
                        if ($wT > 0 || $wR > 0) {
                            if ($lmPolaritas === 'negatif' || $lmPolaritas === '3') {
                                $wPct = $wT > 0 ? ($wT / max(0.0001, $wR)) * 100 : 0;
                            } else {
                                $wPct = $wT > 0 ? ($wR / $wT) * 100 : 0;
                            }
                        }
                        
                        $unitLmWeeks[$w] = [
                            't' => $wT,
                            'r' => $wR,
                            'pct' => $wPct
                        ];

                        $unitLmTotalR += $wR;
                    }
                    
                    $allTargets = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                        ->where('unit_id', $unit->id)
                        ->where('bulan', $bulanT)
                        ->where('tahun', $tahunT)
                        ->get();
                    
                    $monthlyTargetRecord = $allTargets->sortByDesc(function ($t) {
                        return \Carbon\Carbon::parse($t->periode_start)->diffInDays(\Carbon\Carbon::parse($t->periode_end));
                    })->first();
                    
                    $unitLmTotalT = $monthlyTargetRecord ? $monthlyTargetRecord->angka_target : 0;

                    $unitLmPct = 0;
                    if ($unitLmTotalT > 0 || $unitLmTotalR > 0) {
                        if ($lmPolaritas === 'negatif' || $lmPolaritas === '3') {
                            $unitLmPct = $unitLmTotalT > 0 ? ($unitLmTotalT / max(0.0001, $unitLmTotalR)) * 100 : 0;
                        } else {
                            $unitLmPct = $unitLmTotalT > 0 ? ($unitLmTotalR / $unitLmTotalT) * 100 : 0;
                        }
                    }

                    if ($unitLmPct >= 100) {
                        $lmMenang++;
                    } else {
                        $lmKalah++;
                    }

                    $lmTotalTarget += $unitLmTotalT;
                    $lmTotalReal += $unitLmTotalR;

                    $wigData['units'][$unit->id]['lms'][$lm->id] = $unitLmWeeks;
                }

                $lmOverallPct = 0;
                if ($lmTotalTarget > 0 || $lmTotalReal > 0) {
                    if ($lmPolaritas === 'negatif' || $lmPolaritas === '3') {
                        $lmOverallPct = $lmTotalTarget > 0 ? ($lmTotalTarget / max(0.0001, $lmTotalReal)) * 100 : 0;
                    } else {
                        $lmOverallPct = $lmTotalTarget > 0 ? ($lmTotalReal / $lmTotalTarget) * 100 : 0;
                    }
                }

                $wigData['lms'][$lm->id] = [
                    'pct' => $lmOverallPct,
                    'menang' => $lmMenang,
                    'kalah' => $lmKalah
                ];
            }

            $reportData[$wig->id] = $wigData;
        }
        
        return view('exports.lengkap_html', compact('bulan', 'tahun', 'wigs', 'units', 'isUlpLevel', 'isUp3Level', 'user', 'isAllBulan', 'bulanT', 'tahunT', 'reportData'));
    }

}
