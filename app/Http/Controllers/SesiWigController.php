<?php
namespace App\Http\Controllers;

use App\Models\MasterUnit;
use App\Models\MasterWig;
use App\Models\SesiWig;
use App\Models\MasterLm;
use App\Models\BreakdownLm;
use App\Models\BreakdownWig;
use App\Models\SesiWigKomitmen;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SesiWigController extends Controller
{
    public function index()
    {
        $userRole = auth()->user()->role_name ?? (auth()->user()->roles->pluck('name')->first() ?? '');
        $isSrmPerencanaan = str_contains(strtoupper($userRole), 'SRM PERENCANAAN');
        
        if ($isSrmPerencanaan) {
            // Find current month and year
            $currentMonth = date('n');
            $currentYear = date('Y');
            
            // Find the closest session in the current month (or just the latest one)
            $currentSesi = SesiWig::where('tahun', $currentYear)
                                  ->where('bulan', $currentMonth)
                                  ->where('tanggal_pelaksanaan', '>=', now())
                                  ->orderBy('tanggal_pelaksanaan', 'asc')
                                  ->first();
                                  
            if (!$currentSesi) {
                // Fallback to the latest available session
                $currentSesi = SesiWig::orderBy('tanggal_pelaksanaan', 'desc')->first();
            }
            
            if ($currentSesi) {
                return redirect()->route('sesi-wigs.show', $currentSesi->id);
            }
        }

        $sesis = SesiWig::orderBy('tanggal_pelaksanaan', 'desc')->get();
        return view('sesi-wigs.index', compact('sesis'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'level_terlibat' => 'required|array',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $levels = $request->level_terlibat;
        
        $master = \App\Models\MasterPeriode::where('tahun', $tahun)->where('bulan', $bulan)->first();
        if (!$master) {
            return redirect()->back()->with('error', 'Tidak dapat men-generate Sesi WIG. Master Periode untuk bulan tersebut belum dikonfigurasi.');
        }

        $weeklyCalendars = [];
        if ($master->end_m1) $weeklyCalendars[1] = $master->end_m1;
        if ($master->end_m2) $weeklyCalendars[2] = $master->end_m2;
        if ($master->end_m3) $weeklyCalendars[3] = $master->end_m3;
        if ($master->end_m4) $weeklyCalendars[4] = $master->end_m4;
        if ($master->end_m5) $weeklyCalendars[5] = $master->end_m5;

        // Create Mingguan sessions
        $createdCount = 0;
        foreach ($weeklyCalendars as $w => $endDate) {
            // Tgl Pelaksanaan = 1 hari setelah periode berakhir
            $execDate = \Carbon\Carbon::parse($endDate)->addDay();
            
            $exists = SesiWig::where('tahun', $tahun)->where('bulan', $bulan)->where('minggu_ke', $w)->first();
            if (!$exists) {
                SesiWig::create([
                    'nama_sesi' => "Sesi WIG Mingguan $w - " . strtoupper(\Carbon\Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y')),
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'minggu_ke' => $w,
                    'tipe_sesi' => 'Mingguan',
                    'tanggal_pelaksanaan' => $execDate->format('Y-m-d'),
                    'level_terlibat' => $levels,
                ]);
                $createdCount++;
            }
        }

        if ($createdCount > 0) {
            return redirect()->route('sesi-wigs.index')->with('success', "$createdCount Sesi WIG Mingguan baru berhasil digenerate berdasarkan Master Periode.");
        } else {
            return redirect()->route('sesi-wigs.index')->with('info', "Semua Sesi WIG untuk bulan ini sudah ada. Tidak ada sesi baru yang ditambahkan.");
        }
    }

    public function destroy(SesiWig $sesi_wig)
    {
        $sesi_wig->delete();
        return redirect()->route('sesi-wigs.index')->with('success', 'Sesi WIG berhasil dihapus.');
    }

    public function updateNotes(Request $request, SesiWig $sesi_wig)
    {
        $request->validate([
            'komitmen' => 'nullable|string',
            'evaluasi' => 'nullable|string',
        ]);

        $sesi_wig->update($request->only('komitmen', 'evaluasi'));
        return redirect()->back()->with('success', 'Catatan Komitmen dan Evaluasi berhasil disimpan.');
    }

    public function show(Request $request, SesiWig $sesi_wig)
    {
        $endDate = Carbon::parse($sesi_wig->tanggal_pelaksanaan)->endOfDay();
        
        $wig_bulan = $request->get('wig_bulan');
        $lm_unit = $request->get('lm_unit');

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
        $canEditSesiWig = $isSuperAdmin;
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && in_array(strtoupper(trim((string)$user->unit->type)), ['UP3', 'UP2D', 'UP2K'])) || str_contains(strtoupper($user->role_name ?? ''), 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP2D') || str_contains(strtoupper($user->role_name ?? ''), 'UP2K'));

        // Filter WIG & LM sesuai Matrix Bidang User
        $wigsQuery = MasterWig::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        $wigs = $wigsQuery->get();

        $lmsQuery = MasterLm::with('wig', 'satuan');
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $lmsQuery->whereHas('wig', function($q) use ($allowedDivisis) {
                $q->where(function($query) use ($allowedDivisis) {
                    foreach ($allowedDivisis as $div) {
                        $query->orWhereJsonContains('divisi', $div);
                    }
                });
            });
        }
        $lms = $lmsQuery->get()->sortBy(function($lm) {
            preg_match('/LM-?(\d+)/i', $lm->judul_lm, $m);
            return (int)($m[1] ?? 999);
        });

        $weeklyCalendars = [];
        $monthlyCalendar = null;
        
        $targetStartDate = Carbon::parse($sesi_wig->tanggal_pelaksanaan)->startOfMonth()->format('Y-m-d');
        $targetEndDate = Carbon::parse($sesi_wig->tanggal_pelaksanaan)->endOfMonth()->format('Y-m-d');

        $masterPeriode = \App\Models\MasterPeriode::where('tahun', $sesi_wig->tahun)->where('bulan', $sesi_wig->bulan)->first();
        if ($masterPeriode) {
            $monthlyCalendar = [
                'start' => $masterPeriode->start_m1, 
                'end' => $masterPeriode->end_m5 ?: ($masterPeriode->end_m4 ?: $masterPeriode->end_m1)
            ];
            if ($masterPeriode->start_m1 && $masterPeriode->end_m1) $weeklyCalendars[1] = ['start' => $masterPeriode->start_m1, 'end' => $masterPeriode->end_m1];
            if ($masterPeriode->start_m2 && $masterPeriode->end_m2) $weeklyCalendars[2] = ['start' => $masterPeriode->start_m2, 'end' => $masterPeriode->end_m2];
            if ($masterPeriode->start_m3 && $masterPeriode->end_m3) $weeklyCalendars[3] = ['start' => $masterPeriode->start_m3, 'end' => $masterPeriode->end_m3];
            if ($masterPeriode->start_m4 && $masterPeriode->end_m4) $weeklyCalendars[4] = ['start' => $masterPeriode->start_m4, 'end' => $masterPeriode->end_m4];
            if ($masterPeriode->start_m5 && $masterPeriode->end_m5) $weeklyCalendars[5] = ['start' => $masterPeriode->start_m5, 'end' => $masterPeriode->end_m5];
        }

        if (strtolower(trim($sesi_wig->tipe_sesi)) === 'mingguan') {
            $w = $sesi_wig->minggu_ke ?? 1;
            if (isset($weeklyCalendars[$w])) {
                $targetStartDate = $weeklyCalendars[$w]['start'];
                $targetEndDate = $weeklyCalendars[$w]['end'];
            } else {
                // Fallback to old behavior
                $targetStartDate = Carbon::create($sesi_wig->tahun, $sesi_wig->bulan, 1)->addDays(($w-1)*7)->format('Y-m-d');
                $targetEndDate = Carbon::create($sesi_wig->tahun, $sesi_wig->bulan, 1)->addDays(($w-1)*7 + 6)->format('Y-m-d');
            }
        } else {
            if ($monthlyCalendar) {
                $targetStartDate = $monthlyCalendar['start'];
                $targetEndDate = $monthlyCalendar['end'];
            }
        }

        $nonSummableSatuans = [1, 2, 6, 14];

        $wigUnitData = []; // Store Unit-level data for WIGs
        $up3List = \App\Models\MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->orderBy('name')->get();

        $targetBulan = $wig_bulan ? (int)$wig_bulan : $endDate->month;
        $prevBulan = $targetBulan > 1 ? $targetBulan - 1 : 12;
        $prevTahun = $targetBulan > 1 ? $endDate->year : $endDate->year - 1;

        // Calculate WIG Realization
        foreach ($wigs as $wig) {
            $isNonSummable = in_array($wig->satuan_id, $nonSummableSatuans);
            
            $colBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$targetBulan];
            $colPrevBln = 'target_' . [1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'agu',9=>'sep',10=>'okt',11=>'nov',12=>'des'][$prevBulan];
            
            $targetQuery = \Illuminate\Support\Facades\DB::table('breakdown_wigs')->where('wig_id', $wig->id);
            $realisasiQuery = \App\Models\RealisasiWig::where('wig_id', $wig->id);
            
            // UID Target: Ambil dari unit_id = 1 untuk bulan yang sesuai
            $targetQuery->where('tahun', $endDate->year)->where('unit_id', 1);
            $target = $targetQuery->sum($colBln);
            
            // Realisasi UID: Tarik data bulan berjalan
            $realisasiQuery->where('tahun', $endDate->year)->where('bulan', $targetBulan)->where('unit_id', '!=', 1);
            
            if ($isNonSummable) {
                // Untuk metrik seperti % atau Menit, gunakan Average antar UP3
                $realisasi = $realisasiQuery->avg('angka_realisasi') ?? 0;
            } else {
                // Untuk metrik seperti GWh, gunakan Sum antar UP3
                $realisasi = $realisasiQuery->sum('angka_realisasi') ?? 0;
            }
            
            $wig->total_target = $target;
            $wig->total_realisasi = $realisasi;
            
            $capaian = 0;
            if ($target > 0) {
                if (strtolower($wig->polaritas) === 'negatif' || $wig->polaritas === '3') {
                    $capaian = ($target / max(0.0001, $realisasi)) * 100;
                } else {
                    $capaian = ($realisasi / $target) * 100;
                }
            }
            $wig->capaian = round($capaian, 2);
            
            // --- UNIT LEVEL (UP3) FOR WIG TABLE ---
            $wigUnitData[$wig->id] = [];
            foreach ($up3List as $up3) {
                // Current Month
                $curT = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                            ->where('wig_id', $wig->id)->where('tahun', $endDate->year)->where('unit_id', $up3->id)->sum($colBln);
                $curR = \App\Models\RealisasiWig::where('wig_id', $wig->id)
                            ->where('tahun', $endDate->year)->where('bulan', $targetBulan)->where('unit_id', $up3->id)->sum('angka_realisasi') ?? 0;
                
                $curPct = 0;
                if ($curT > 0 || $curR > 0) {
                    if (strtolower($wig->polaritas) === 'negatif' || $wig->polaritas === '3') {
                        $curPct = $curT > 0 ? ($curT / max(0.0001, $curR)) * 100 : 0;
                    } else {
                        $curPct = $curT > 0 ? ($curR / $curT) * 100 : 0;
                    }
                }
                
                // Previous Month
                $prevT = \Illuminate\Support\Facades\DB::table('breakdown_wigs')
                            ->where('wig_id', $wig->id)->where('tahun', $prevTahun)->where('unit_id', $up3->id)->sum($colPrevBln);
                $prevR = \App\Models\RealisasiWig::where('wig_id', $wig->id)
                            ->where('tahun', $prevTahun)->where('bulan', $prevBulan)->where('unit_id', $up3->id)->sum('angka_realisasi') ?? 0;
                
                $prevPct = 0;
                if ($prevT > 0 || $prevR > 0) {
                    if (strtolower($wig->polaritas) === 'negatif' || $wig->polaritas === '3') {
                        $prevPct = $prevT > 0 ? ($prevT / max(0.0001, $prevR)) * 100 : 0;
                    } else {
                        $prevPct = $prevT > 0 ? ($prevR / $prevT) * 100 : 0;
                    }
                }
                
                $wigUnitData[$wig->id][$up3->id] = [
                    'cur' => ['t' => $curT, 'r' => $curR, 'pct' => round($curPct, 2)],
                    'prev' => ['t' => $prevT, 'r' => $prevR, 'pct' => round($prevPct, 2)]
                ];
            }
        }

        // Calculate LM Realization
        foreach ($lms as $lm) {
            $isNonSummable = in_array($lm->satuan_id, $nonSummableSatuans);
            
            $realisasiQuery = \App\Models\Realisasi::where('lm_id', $lm->id)
                ->where('tanggal_input', '>=', $targetStartDate . ' 00:00:00')
                ->where('tanggal_input', '<=', $targetEndDate . ' 23:59:59');
                
            $targetQuery = BreakdownLm::where('lm_id', $lm->id)
                ->where('periode_start', '=', $targetStartDate)
                ->where('periode_end', '=', $targetEndDate);

            if ($lm_unit) {
                $realisasiQuery->where('unit_id', $lm_unit);
                $targetQuery->where('unit_id', $lm_unit);
                
                $realisasi = $realisasiQuery->sum('angka_realisasi') ?? 0;
            } else {
                // UID Level
                $targetQuery->where('unit_id', 1);
                $realisasiQuery->where('unit_id', '!=', 1);
                
                if ($isNonSummable) {
                    $realisasi = $realisasiQuery->avg('angka_realisasi') ?? 0;
                } else {
                    $realisasi = $realisasiQuery->sum('angka_realisasi') ?? 0;
                }
            }

            $target = $targetQuery->sum('angka_target') ?? 0;

            $lm->total_target = $target;
            $lm->total_realisasi = $realisasi;
            
            $capaian = 0;
            if ($target > 0) {
                if (strtolower($lm->polaritas) === 'negatif' || $lm->polaritas === '3') {
                    $capaian = ($target / max(0.0001, $realisasi)) * 100;
                } else {
                    $capaian = ($realisasi / $target) * 100;
                }
            }
            $lm->capaian = round($capaian, 2);
        }
        
        // Filter Unit sesuai tingkatan akses User (ULP/UP3/UID)
        $ulpsQuery = MasterUnit::whereIn('type', ['ULP', 'UP2D', 'UP2K'])->orderBy('name');
        $up3sQuery = MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->orderBy('name');
        $unitsQuery = MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K', 'ULP'])->orderBy('type')->orderBy('name');

        if (!$isSuperAdmin && $user && $user->unit) {
            $unitType = strtoupper(trim((string)$user->unit->type));
            if ($unitType === 'ULP') {
                $ulpsQuery->where('id', $user->unit_id);
                $up3sQuery->where('id', $user->unit->parent_id);
                $unitsQuery->whereIn('id', [$user->unit_id, $user->unit->parent_id]);
            } elseif (in_array($unitType, ['UP3', 'UP2D', 'UP2K'])) {
                if (in_array($unitType, ['UP2D', 'UP2K'])) {
                    $ulpsQuery->where('id', $user->unit_id);
                } else {
                    $ulpsQuery->where('parent_id', $user->unit_id);
                }
                $up3sQuery->where('id', $user->unit_id);
                $unitsQuery->whereIn('id', function($q) use ($user) {
                    $q->select('id')->from('master_units')
                      ->where('id', $user->unit_id)
                      ->orWhere('parent_id', $user->unit_id);
                });
            }
        }
        $allUlps = $ulpsQuery->get();
        $up3s    = $up3sQuery->get();
        $units   = $unitsQuery->get();

        $filteredUp3sByWig = [];
        foreach ($wigs as $wig) {
            $isWig4 = str_contains(strtolower($wig->judul), 'wig 4') || str_contains(strtolower($wig->judul), 'peningkatan kepuasan pelanggan');
            $filtered = collect($up3s)->filter(function($u) use ($isWig4) {
                $type = strtoupper(trim($u->type ?? ''));
                if (in_array($type, ['UP2D', 'UP2K'])) {
                    return $isWig4;
                }
                return true;
            })->sortBy(function($u) {
                $type = strtoupper(trim($u->type ?? ''));
                if (in_array($type, ['UP2D', 'UP2K'])) {
                    return 'Z_' . $u->name;
                }
                return 'A_' . $u->name;
            })->values();
            $filteredUp3sByWig[$wig->id] = $filtered;
        }

        $leaderboardScores = \Illuminate\Support\Facades\DB::table('realisasis')
            ->where('tanggal_input', '<=', $endDate)
            ->select('unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_realisasi) as score'))
            ->groupBy('unit_id')
            ->pluck('score', 'unit_id')->toArray();

        $leaderboard = [];
        foreach ($allUlps as $ulp) {
            $leaderboard[] = ['unit' => $ulp->name, 'score' => $leaderboardScores[$ulp->id] ?? 0];
        }
        usort($leaderboard, fn($a, $b) => $b['score'] <=> $a['score']);
        $leaderboard = array_slice($leaderboard, 0, 10);

        $bdQuery = \Illuminate\Support\Facades\DB::table('breakdown_lms')
            ->where('periode_start', '=', $targetStartDate)
            ->where('periode_end', '=', $targetEndDate)
            ->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_target) as target'))
            ->groupBy('lm_id', 'unit_id')
            ->get();
            


        $previousSesi = SesiWig::where('tanggal_pelaksanaan', '<', $sesi_wig->tanggal_pelaksanaan)
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->first();

        // Get the chosen presenters
        $presenters = $sesi_wig->presenters;

        // --- Fetch Data for LM Matrix Table ---
        $sesi_wigs_matrix = \App\Models\SesiWig::where('tahun', $sesi_wig->tahun)
            ->where('bulan', $sesi_wig->bulan)
            ->orderByRaw('CASE WHEN minggu_ke IS NULL THEN 1 ELSE 0 END, minggu_ke ASC')
            ->get();

        $matrixTargets = [];
        $matrixRealisasi = [];
        $matrixKomitmen = [];


        foreach ($sesi_wigs_matrix as $sw) {
            $isMingguan = strtolower(trim($sw->tipe_sesi)) === 'mingguan';
            $w = $sw->minggu_ke ?? 1;

            if ($isMingguan) {
                if (isset($weeklyCalendars[$w])) {
                    $swStart = $weeklyCalendars[$w]['start'];
                    $swEnd = $weeklyCalendars[$w]['end'];
                } else {
                    // Fallback to old behavior if no breakdown LM found
                    $swStart = \Carbon\Carbon::create($sw->tahun, $sw->bulan, 1)->addDays(($w-1)*7)->format('Y-m-d');
                    $swEnd = \Carbon\Carbon::create($sw->tahun, $sw->bulan, 1)->addDays(($w-1)*7 + 6)->format('Y-m-d');
                }
            } else {
                if ($monthlyCalendar) {
                    $swStart = $monthlyCalendar['start'];
                    $swEnd = $monthlyCalendar['end'];
                } else {
                    // Fallback
                    $swStart = \Carbon\Carbon::create($sw->tahun, $sw->bulan, 1)->format('Y-m-d');
                    $swEnd = \Carbon\Carbon::create($sw->tahun, $sw->bulan, 1)->endOfMonth()->format('Y-m-d');
                }
            }
            
            $targets = \Illuminate\Support\Facades\DB::table('breakdown_lms')
                ->where('periode_start', '=', $swStart)
                ->where('periode_end', '=', $swEnd)
                ->get();
            foreach ($targets as $t) {
                $matrixTargets[$t->lm_id][$t->unit_id][$sw->id] = $t->angka_target;
            }

            $realisasis = \Illuminate\Support\Facades\DB::table('realisasis')
                ->where('tanggal_input', '>=', $swStart . ' 00:00:00')
                ->where('tanggal_input', '<=', $swEnd . ' 23:59:59')
                ->get();
            foreach ($realisasis as $r) {
                $matrixRealisasi[$r->lm_id][$r->unit_id][$sw->id] = ($matrixRealisasi[$r->lm_id][$r->unit_id][$sw->id] ?? 0) + $r->angka_realisasi;
            }

            if (class_exists(\App\Models\SesiWigKomitmen::class)) {
                $komitmens = \App\Models\SesiWigKomitmen::where('sesi_wig_id', $sw->id)->get();
                foreach ($komitmens as $k) {
                    $matrixKomitmen[$k->lm_id][$k->unit_id][$sw->id] = [
                        'komitmen' => $k->komitmen,
                        'carry_over' => $k->carry_over,
                        'has_form' => true
                    ];
                }
            }
        }

        $sesi_wigs_month = $sesi_wigs_matrix;

        // Calculate MenangKalah dynamically from Matrix data for THIS SESSION ($sesi_wig->id)
        $lmMenangKalah = [];
        foreach ($lms as $lm) {
            $lmMenangKalah[$lm->id] = ['up3' => ['menang' => [], 'kalah' => []], 'ulp' => ['menang' => [], 'kalah' => []]];
        }
        $sid = $sesi_wig->id;
        foreach ($up3s as $up3) {
            foreach ($lms as $lm) {
                $target = $matrixTargets[$lm->id][$up3->id][$sid] ?? 0;
                if ($target <= 0) continue;
                $realisasi = $matrixRealisasi[$lm->id][$up3->id][$sid] ?? 0;
                $pct = round(($realisasi / $target) * 100, 2);
                $lmMenangKalah[$lm->id]['up3'][$pct >= 100 ? 'menang' : 'kalah'][] = ['name' => $up3->name, 'score' => $pct];
            }
        }
        foreach ($allUlps as $ulp) {
            foreach ($lms as $lm) {
                $target = $matrixTargets[$lm->id][$ulp->id][$sid] ?? 0;
                if ($target <= 0) continue;
                $realisasi = $matrixRealisasi[$lm->id][$ulp->id][$sid] ?? 0;
                $pct = round(($realisasi / $target) * 100, 2);
                $lmMenangKalah[$lm->id]['ulp'][$pct >= 100 ? 'menang' : 'kalah'][] = ['name' => $ulp->name, 'score' => $pct];
            }
        }
        foreach ($lms as $lm) {
            foreach (['up3', 'ulp'] as $level) {
                usort($lmMenangKalah[$lm->id][$level]['menang'], fn($a, $b) => $b['score'] <=> $a['score']);
                usort($lmMenangKalah[$lm->id][$level]['kalah'],  fn($a, $b) => $b['score'] <=> $a['score']);
            }
        }

        return view('sesi-wigs.show', compact('sesi_wig', 'previousSesi', 'wigs', 'lms', 'units', 'wig_bulan', 'lm_unit', 'leaderboard', 'lmMenangKalah', 'presenters', 'up3s', 'filteredUp3sByWig', 'allUlps', 'sesi_wigs_matrix', 'sesi_wigs_month', 'matrixTargets', 'matrixRealisasi', 'matrixKomitmen', 'isUlpLevel', 'isUp3Level', 'userMatrixGroup', 'canEditSesiWig', 'wigUnitData', 'targetBulan', 'prevBulan'));
    }

    public function drawPresenter(Request $request, SesiWig $sesi_wig)
    {
        $request->validate([
            'type' => 'required|in:ULP,UP3',
        ]);

        $type = $request->type;
        $tahun = $sesi_wig->tahun;
        $bulan = $sesi_wig->bulan;

        // 1. Get all units of the requested type
        $allUnits = \App\Models\MasterUnit::where('type', $type)->get();

        // 2. Get unit IDs that have already presented this month
        $presentedUnitIds = \Illuminate\Support\Facades\DB::table('sesi_wig_presenters')
            ->join('sesi_wigs', 'sesi_wigs.id', '=', 'sesi_wig_presenters.sesi_wig_id')
            ->where('sesi_wigs.tahun', $tahun)
            ->where('sesi_wigs.bulan', $bulan)
            ->pluck('sesi_wig_presenters.unit_id')
            ->toArray();

        // Detach existing presenters of this type for this session (so we can redraw)
        $currentPresenters = $sesi_wig->presenters()->where('type', $type)->get();
        if ($currentPresenters->isNotEmpty()) {
            $sesi_wig->presenters()->detach($currentPresenters->pluck('id'));
        }

        // 3. Filter eligible units
        $eligibleUnits = $allUnits->whereNotIn('id', $presentedUnitIds)->values();

        if ($eligibleUnits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Semua unit {$type} sudah presentasi bulan ini!"
            ], 400);
        }

        // 4. Randomly pick one
        $winner = $eligibleUnits->random();

        // 5. Save to database
        $sesi_wig->presenters()->attach($winner->id);

        return response()->json([
            'success' => true,
            'winner' => $winner,
            'candidates' => $eligibleUnits->map(fn($u) => $u->name)->toArray()
        ]);
    }

    public function saveKomitmen(Request $request, SesiWig $sesi_wig)
    {
        $request->validate([
            'lm_id' => 'required|exists:master_lms,id',
            'unit_id' => 'required|exists:master_units,id',
            'komitmen' => 'nullable',
            'carry_over' => 'nullable',
        ]);

        SesiWigKomitmen::updateOrCreate(
            [
                'sesi_wig_id' => $sesi_wig->id,
                'lm_id' => $request->lm_id,
                'unit_id' => $request->unit_id,
            ],
            [
                'komitmen' => $request->komitmen !== '' ? $request->komitmen : null,
                'carry_over' => $request->carry_over !== '' ? $request->carry_over : null,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function setPresenter(Request $request, SesiWig $sesi_wig)
    {
        $request->validate([
            'up3_id' => 'nullable|exists:master_units,id',
            'ulp_id' => 'nullable|exists:master_units,id',
        ]);

        $presenterIds = array_filter([$request->up3_id, $request->ulp_id]);
        $sesi_wig->presenters()->sync($presenterIds);

        return redirect()->back()->with('success', 'Presenter sesi berhasil disimpan.');
    }
}
