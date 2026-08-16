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
        
        $previewData = [];

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && in_array(strtoupper(trim((string)$user->unit->type)), ['UP3', 'UP2D', 'UP2K'])) || str_contains(strtoupper($user->role_name ?? ''), 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP2D') || str_contains(strtoupper($user->role_name ?? ''), 'UP2K'));

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
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        
        $filename = "Laporan_Capaian_WIG_{$tahun}_{$bulan}.xlsx";
        return Excel::download(new \App\Exports\WigReportExport($tahun, $bulan), $filename);
    }


    public function previewReport(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $jenis = $request->input('jenis', 'lm');

        if ($jenis === 'wig') {
            $export = new \App\Exports\WigReportExport($tahun, $bulan);
            return response()->json(['html' => $export->view()->render()]);
        }
        
        if ($jenis === 'lengkap') {
            $data = $this->getLengkapData($request);
            if (!$data) {
                return response()->json(['html' => '<div class="p-4 text-red-500 font-bold">Pilih WIG Induk terlebih dahulu.</div>']);
            }
            $html = view('exports.lengkap_html', $data)->render();
            return response()->json(['html' => $html]);
        }

        if ($jenis === 'dashboard_heatmap') {
            $data = $this->getLengkapData($request);
            if (!$data) {
                return response()->json(['html' => '<div class="p-4 text-gray-500">Pilih WIG terlebih dahulu.</div>']);
            }
            $html = view('dashboard.partials.heatmap', $data)->render();
            return response()->json(['html' => $html]);
        }

        $export = new \App\Exports\MonthlyReportExport($tahun, $bulan);
        return response()->json(['html' => $export->view()->render()]);
    }

    public function exportLengkap(Request $request)
    {
        $data = $this->getLengkapData($request);
        
        if (!$data) {
            return back()->with('error', 'Pilih WIG terlebih dahulu.');
        }

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        
        $pdf = Pdf::loadView('exports.lengkap_html', $data)
                  ->setPaper('a3', 'landscape');
                  
        $filename = "Laporan_Lengkap_WIG_{$data['tahunT']}_{$data['bulanT']}.pdf";
        return $pdf->download($filename);
    }

    public function getLengkapData(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $wigId = $request->input('wig_id');
        
        if (!$wigId) {
            return false;
        }

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && in_array(strtoupper(trim((string)$user->unit->type)), ['UP3', 'UP2D', 'UP2K'])) || str_contains(strtoupper($user->role_name ?? ''), 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP2D') || str_contains(strtoupper($user->role_name ?? ''), 'UP2K'));

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
            if (in_array($unitType, ['ULP', 'UP2D', 'UP2K'])) {
                $unitsQuery->where('id', $user->unit_id);
            } elseif ($unitType === 'UP3') {
                $unitsQuery->where('type', 'ULP')->where('parent_id', $user->unit_id);
            } else {
                $unitsQuery->whereIn('type', ['UP3', 'UP2D', 'UP2K']);
            }
        } else {
            $unitsQuery->whereIn('type', ['UP3', 'UP2D', 'UP2K']);
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
            
            $nonSummableSatuans = [1, 2, 6, 14];
            $isNonSummableWig = in_array($wig->satuan_id, $nonSummableSatuans);
            $targetBulan = $isAllBulan ? 12 : $bulanT;
            $colBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$targetBulan];

            $wigTargetQuery = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT);
            $wigRealQuery = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                ->where('wig_id', $wig->id)->where('tahun', $tahunT);
                
            if (!$isSuperAdmin && $user && $user->unit_id) {
                // If it's a specific user (UP3), they only see their own unit
                $wigTargetQuery->where('unit_id', $user->unit_id);
                $wigRealQuery->where('unit_id', $user->unit_id);
                
                $wigTargetTot = $wigTargetQuery->sum($colBln);
                $wigRealTot = $wigRealQuery->where('bulan', $targetBulan)->sum('angka_realisasi') ?? 0;
            } else {
                // UID Target Level (unit_id = 1)
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

            $prevBulan = $targetBulan > 1 ? $targetBulan - 1 : 12;
            $prevTahun = $targetBulan > 1 ? $tahunT : $tahunT - 1;
            $colPrevBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$prevBulan];
            
            // Previous month overall
            $wigPrevTargetQuery = \Illuminate\Support\Facades\DB::table('breakdown_wigs')->where('wig_id', $wig->id);
            $wigPrevRealQuery = \Illuminate\Support\Facades\DB::table('realisasi_wigs')->where('wig_id', $wig->id);
            
            $wigPrevTargetTot = 0;
            $wigPrevRealTot = 0;
            
            if (!$isSuperAdmin && $user && $user->unit_id) {
                $wigPrevTargetQuery->where('unit_id', $user->unit_id);
                $wigPrevRealQuery->where('unit_id', $user->unit_id);
                
                $wigPrevTargetTot = $wigPrevTargetQuery->where('tahun', $prevTahun)->sum($colPrevBln);
                $wigPrevRealTot = $wigPrevRealQuery->where('tahun', $prevTahun)->where('bulan', $prevBulan)->sum('angka_realisasi') ?? 0;
            } else {
                $wigPrevTargetQuery->where('unit_id', 1);
                $wigPrevTargetTot = $wigPrevTargetQuery->where('tahun', $prevTahun)->sum($colPrevBln);
                
                $wigPrevRealQuery->where('tahun', $prevTahun)->where('bulan', $prevBulan)->where('unit_id', '!=', 1);
                if ($isNonSummableWig) {
                    $wigPrevRealTot = $wigPrevRealQuery->avg('angka_realisasi') ?? 0;
                } else {
                    $wigPrevRealTot = $wigPrevRealQuery->sum('angka_realisasi') ?? 0;
                }
            }

            $prevPctUid = 0;
            if ($wigPrevTargetTot > 0) {
                if ($polaritasWig === 'negatif' || $polaritasWig === '3') {
                    $prevPctUid = ($wigPrevTargetTot / max(0.0001, $wigPrevRealTot)) * 100;
                } else {
                    $prevPctUid = ($wigPrevRealTot / $wigPrevTargetTot) * 100;
                }
            }

            $wigData = [
                'target' => $wigTargetTot,
                'realisasi' => $wigRealTot,
                'pct' => $pctUid,
                'prev_target' => $wigPrevTargetTot,
                'prev_realisasi' => $wigPrevRealTot,
                'prev_pct' => $prevPctUid,
                'lms' => [],
                'units' => [],
                'status_menang' => 0,
                'status_kalah' => 0,
            ];

            // Unit Data
            foreach ($units as $unit) {
                // Unit WIG Target & Realisasi
                $uT = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                    ->where('wig_id', $wig->id)->where('tahun', $tahunT)->where('unit_id', $unit->id)
                    ->sum($colBln);
                $uR = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
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
                
                // Prev Month
                $pT = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                    ->where('wig_id', $wig->id)->where('tahun', $prevTahun)->where('unit_id', $unit->id)
                    ->sum($colPrevBln);
                $pR = \Illuminate\Support\Facades\DB::table('realisasi_wigs')
                    ->where('wig_id', $wig->id)->where('tahun', $prevTahun)->where('bulan', $prevBulan)->where('unit_id', $unit->id)
                    ->sum('angka_realisasi') ?? 0;
                    
                $pPct = 0;
                if ($pT > 0 || $pR > 0) {
                    if ($polaritasWig === 'negatif' || $polaritasWig === '3') {
                        $pPct = $pT > 0 ? ($pT / max(0.0001, $pR)) * 100 : 0;
                    } else {
                        $pPct = $pT > 0 ? ($pR / $pT) * 100 : 0;
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
                    'pt' => $pT,
                    'pr' => $pR,
                    'ppct' => $pPct,
                    'lms' => []
                ];
            }

            // LM Data
            foreach ($wig->masterLms as $lm) {
                $lmPolaritas = strtolower(trim($lm->polaritas ?? 'positif'));
                $isNonSummableLm = in_array($lm->satuan_id, $nonSummableSatuans);
                
                $lmTotalTarget = 0;
                $lmTotalReal = 0;
                $lmMenang = 0;
                $lmKalah = 0;
                $unitCount = count($units);

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
                                ->where('unit_id', $unit->id)
                                ->where('tanggal_input', '>=', $wStart . ' 00:00:00')
                                ->where('tanggal_input', '<=', $wEnd . ' 23:59:59')
                                ->sum('angka_realisasi') ?? 0;
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
                        ->where('bulan', $targetBulan)
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

                    $lmTotalReal += $unitLmTotalR;
                    
                    if (!$isSuperAdmin && $user && $user->unit_id && $user->unit_id == $unit->id) {
                        $lmTotalTarget = $unitLmTotalT;
                        $lmTotalReal = $unitLmTotalR;
                    }

                    $wigData['units'][$unit->id]['lms'][$lm->id] = $unitLmWeeks;
                }

                if (!$isSuperAdmin && $user && $user->unit_id) {
                    // Already set in loop
                } else {
                    // UID Target Level (unit_id = 1)
                    $allUidTargets = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                        ->where('unit_id', 1)
                        ->where('bulan', $targetBulan)
                        ->where('tahun', $tahunT)
                        ->get();
                        
                    $monthlyUidTargetRecord = $allUidTargets->sortByDesc(function ($t) {
                        return \Carbon\Carbon::parse($t->periode_start)->diffInDays(\Carbon\Carbon::parse($t->periode_end));
                    })->first();
                    
                    $lmTotalTarget = $monthlyUidTargetRecord ? $monthlyUidTargetRecord->angka_target : 0;
                    
                    if ($isNonSummableLm && $unitCount > 0) {
                        $lmTotalReal = $lmTotalReal / $unitCount;
                    }
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
                    'target' => $lmTotalTarget,
                    'real' => $lmTotalReal,
                    'pct' => $lmOverallPct,
                    'menang' => $lmMenang,
                    'kalah' => $lmKalah
                ];
            }

            $reportData[$wig->id] = $wigData;
        }
        
        return compact('bulan', 'tahun', 'wigs', 'units', 'isUlpLevel', 'isUp3Level', 'user', 'isAllBulan', 'bulanT', 'tahunT', 'reportData');
    }

}
