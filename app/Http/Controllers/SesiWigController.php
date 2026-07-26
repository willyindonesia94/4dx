<?php
namespace App\Http\Controllers;

use App\Models\MasterUnit;
use App\Models\MasterWig;
use App\Models\SesiWig;
use App\Models\MasterLm;
use App\Models\BreakdownLm;
use App\Models\BreakdownWig;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SesiWigController extends Controller
{
    public function index()
    {
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
        
        // Find representative Breakdown LM to get the calendar (Bulletproof method)
        // Find the "Bulanan" target that covers the 15th of the requested month
        $midMonth = \Carbon\Carbon::create($tahun, $bulan, 15)->format('Y-m-d');
        $bulananBd = \Illuminate\Support\Facades\DB::table('breakdown_lms')
            ->whereRaw('DATEDIFF(periode_end, periode_start) > 20')
            ->where('periode_start', '<=', $midMonth)
            ->where('periode_end', '>=', $midMonth)
            ->first();
            
        if (!$bulananBd) {
            return redirect()->back()->with('error', 'Tidak dapat men-generate Sesi WIG. Anda harus membuat minimal 1 Cascading LM (Breakdown Target) terlebih dahulu pada bulan tersebut sebagai patokan kalender.');
        }

        // Fetch M1-M5 based on the boundaries of the bulanan target
        $weeklyBds = \Illuminate\Support\Facades\DB::table('breakdown_lms')
            ->where('lm_id', $bulananBd->lm_id)
            ->where('unit_id', $bulananBd->unit_id)
            ->whereRaw('DATEDIFF(periode_end, periode_start) <= 20')
            ->where('periode_start', '>=', $bulananBd->periode_start)
            ->where('periode_end', '<=', $bulananBd->periode_end)
            ->orderBy('periode_start', 'asc')
            ->get();

        $weeklyCalendars = [];
        $wIndex = 1;
        foreach ($weeklyBds as $bd) {
            $weeklyCalendars[$wIndex] = $bd;
            $wIndex++;
        }

        // Create Mingguan sessions
        foreach ($weeklyCalendars as $w => $bd) {
            // Tgl Pelaksanaan = 1 hari setelah periode berakhir
            $execDate = \Carbon\Carbon::parse($bd->periode_end)->addDay();
            
            SesiWig::create([
                'nama_sesi' => "Sesi WIG Mingguan $w - " . \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y'),
                'tahun' => $tahun,
                'bulan' => $bulan,
                'minggu_ke' => $w,
                'tipe_sesi' => 'Mingguan',
                'tanggal_pelaksanaan' => $execDate->format('Y-m-d'),
                'level_terlibat' => $levels,
            ]);
        }

        return redirect()->route('sesi-wigs.index')->with('success', 'Sesi WIG Mingguan berhasil digenerate berdasarkan kalender Cascading LM.');
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

        // Fetch all WIGs
        $wigs = MasterWig::all();
        // Fetch all LMs
        $lms = MasterLm::with('wig', 'satuan')->get()->sortBy(function($lm) {
            preg_match('/LM-?(\d+)/i', $lm->judul_lm, $m);
            return (int)($m[1] ?? 999);
        });

        // Determine dynamic calendars from BreakdownLm (Bulletproof method)
        $sampleLm = collect($lms)->first();
        $weeklyCalendars = [];
        $monthlyCalendar = null;
        
        $targetStartDate = Carbon::parse($sesi_wig->tanggal_pelaksanaan)->startOfMonth()->format('Y-m-d');
        $targetEndDate = Carbon::parse($sesi_wig->tanggal_pelaksanaan)->endOfMonth()->format('Y-m-d');

        if ($sampleLm) {
            $midMonth = \Carbon\Carbon::create($sesi_wig->tahun, $sesi_wig->bulan, 15)->format('Y-m-d');
            $bulananBd = \Illuminate\Support\Facades\DB::table('breakdown_lms')
                ->where('lm_id', $sampleLm->id)
                ->whereRaw('DATEDIFF(periode_end, periode_start) > 20')
                ->where('periode_start', '<=', $midMonth)
                ->where('periode_end', '>=', $midMonth)
                ->first();

            if ($bulananBd) {
                $monthlyCalendar = ['start' => $bulananBd->periode_start, 'end' => $bulananBd->periode_end];
                
                $weeklyBds = \Illuminate\Support\Facades\DB::table('breakdown_lms')
                    ->where('lm_id', $bulananBd->lm_id)
                    ->where('unit_id', $bulananBd->unit_id)
                    ->whereRaw('DATEDIFF(periode_end, periode_start) <= 20')
                    ->where('periode_start', '>=', $bulananBd->periode_start)
                    ->where('periode_end', '<=', $bulananBd->periode_end)
                    ->orderBy('periode_start', 'asc')
                    ->get();
                    
                $wIndex = 1;
                foreach ($weeklyBds as $bd) {
                    $weeklyCalendars[$wIndex] = ['start' => $bd->periode_start, 'end' => $bd->periode_end];
                    $wIndex++;
                }
            }
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

        // Calculate WIG Realization
        foreach ($wigs as $wig) {
            $realisasiQuery = \App\Models\RealisasiWig::where('wig_id', $wig->id);
            
            if ($wig_bulan) {
                // If filtered by specific month
                $realisasiQuery->where('tahun', $endDate->year)->where('bulan', $wig_bulan);
            } else {
                // Up to session date
                $realisasiQuery->where(function($query) use ($endDate) {
                    $query->where('tahun', '<', $endDate->year)
                          ->orWhere(function($q) use ($endDate) {
                              $q->where('tahun', $endDate->year)
                                ->where('bulan', '<=', $endDate->month);
                          });
                });
            }
            $realisasi = $realisasiQuery->sum('angka_realisasi');
            
            $target = $wig->angka_target;

            $wig->total_target = $target;
            $wig->total_realisasi = $realisasi;
            $wig->capaian = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;
        }

        // Calculate LM Realization
        foreach ($lms as $lm) {
            $realisasiQuery = \App\Models\Realisasi::where('lm_id', $lm->id)
                ->where('tanggal_input', '>=', $targetStartDate . ' 00:00:00')
                ->where('tanggal_input', '<=', $targetEndDate . ' 23:59:59');
                
            $targetQuery = BreakdownLm::where('lm_id', $lm->id)
                ->where('periode_start', '=', $targetStartDate)
                ->where('periode_end', '=', $targetEndDate);

            if ($lm_unit) {
                $realisasiQuery->whereHas('user', function($q) use ($lm_unit) {
                    $q->where('unit_id', $lm_unit);
                });
                $targetQuery->where('unit_id', $lm_unit);
            }

            $realisasi = $realisasiQuery->sum('angka_realisasi');
            $target = $targetQuery->sum('angka_target');

            $lm->total_target = $target;
            $lm->total_realisasi = $realisasi;
            $lm->capaian = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;
        }
        
        $allUlps = MasterUnit::where('type', 'ULP')->orderBy('name')->get();
        $up3s    = MasterUnit::where('type', 'UP3')->orderBy('name')->get();
        $units = MasterUnit::whereIn('type', ['UP3', 'ULP'])->orderBy('type')->orderBy('name')->get();

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
                        'carry_over' => $k->carry_over
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

        return view('sesi-wigs.show', compact('sesi_wig', 'previousSesi', 'wigs', 'lms', 'units', 'wig_bulan', 'lm_unit', 'leaderboard', 'lmMenangKalah', 'presenters', 'up3s', 'allUlps', 'sesi_wigs_matrix', 'sesi_wigs_month', 'matrixTargets', 'matrixRealisasi', 'matrixKomitmen'));
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
}
