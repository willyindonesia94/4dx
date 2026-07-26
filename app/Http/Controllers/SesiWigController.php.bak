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
            'hari_mingguan' => 'required|integer|min:0|max:6', // 0=Sunday, 1=Monday...
            'tanggal_bulanan' => 'required|integer|min:1|max:31',
            'level_terlibat' => 'required|array',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $hari = $request->hari_mingguan;
        $tglBulanan = $request->tanggal_bulanan;
        $levels = $request->level_terlibat;

        // Create Mingguan sessions
        $startOfMonth = Carbon::create($tahun, $bulan, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $currentDate = $startOfMonth->copy();
        // Advance to the first occurrence of the selected day
        while ($currentDate->dayOfWeek != $hari) {
            $currentDate->addDay();
        }

        $mingguKe = 1;
        while ($currentDate->lte($endOfMonth)) {
            SesiWig::create([
                'nama_sesi' => "Sesi WIG Mingguan $mingguKe - " . $currentDate->translatedFormat('F Y'),
                'tahun' => $tahun,
                'bulan' => $bulan,
                'minggu_ke' => $mingguKe,
                'tipe_sesi' => 'Mingguan',
                'tanggal_pelaksanaan' => $currentDate->format('Y-m-d'),
                'level_terlibat' => $levels,
            ]);
            $currentDate->addWeek();
            $mingguKe++;
        }

        // Create Bulanan session
        $bulananDate = Carbon::create($tahun, $bulan, min($tglBulanan, $endOfMonth->day));
        SesiWig::create([
            'nama_sesi' => "Sesi WIG Bulanan - " . $bulananDate->translatedFormat('F Y'),
            'tahun' => $tahun,
            'bulan' => $bulan,
            'minggu_ke' => null,
            'tipe_sesi' => 'Bulanan',
            'tanggal_pelaksanaan' => $bulananDate->format('Y-m-d'),
            'level_terlibat' => $levels,
        ]);

        return redirect()->route('sesi-wigs.index')->with('success', 'Sesi WIG untuk bulan tersebut berhasil di-generate.');
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
        $lms = MasterLm::with('wig', 'satuan')->get();

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
            $wig->capaian = $target > 0 ? min(100, round(($realisasi / $target) * 100, 2)) : 0;
        }

        // Calculate LM Realization
        foreach ($lms as $lm) {
            $realisasiQuery = \App\Models\Realisasi::where('lm_id', $lm->id)
                ->where('tanggal_input', '<=', $endDate);
                
            $targetQuery = BreakdownLm::where('lm_id', $lm->id)
                ->where('periode_start', '<=', $endDate)
                ->where('periode_end', '>=', Carbon::parse($sesi_wig->tanggal_pelaksanaan)->startOfMonth());

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
            $lm->capaian = $target > 0 ? min(100, round(($realisasi / $target) * 100, 2)) : 0;
        }
        
        $allUlps = MasterUnit::where('type', 'ULP')->get();
        $up3s    = MasterUnit::where('type', 'UP3')->get();
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
            ->where('periode_start', '<=', $endDate)
            ->where('periode_end', '>=', Carbon::parse($sesi_wig->tanggal_pelaksanaan)->startOfMonth())
            ->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_target) as target'))
            ->groupBy('lm_id', 'unit_id')
            ->get();
            
        $realQuery = \Illuminate\Support\Facades\DB::table('realisasis')
            ->where('tanggal_input', '<=', $endDate)
            ->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_realisasi) as realisasi'))
            ->groupBy('lm_id', 'unit_id')
            ->get();

        $bdMap = [];
        foreach ($bdQuery as $row) { $bdMap[$row->unit_id][$row->lm_id] = (float) $row->target; }
        $realMap = [];
        foreach ($realQuery as $row) { $realMap[$row->unit_id][$row->lm_id] = (float) $row->realisasi; }

        $menangKalah = ['divisi' => ['menang' => [], 'kalah' => []], 'up3' => ['menang' => [], 'kalah' => []], 'ulp' => ['menang' => [], 'kalah' => []]];

        $divisiData = [];
        foreach ($wigs as $wig) {
            $name = $wig->divisi ?? 'Lainnya';
            $totalPct = 0; $count = 0;
            foreach ($lms->where('wig_id', $wig->id) as $lm) {
                $target = 0; foreach ($bdMap as $uid => $lmsBd) { $target += $lmsBd[$lm->id] ?? 0; }
                $realisasi = 0; foreach ($realMap as $uid => $lmsReal) { $realisasi += $lmsReal[$lm->id] ?? 0; }
                if ($target > 0) {
                    $totalPct += min($realisasi / $target * 100, 100); $count++;
                }
            }
            $avg = $count > 0 ? round($totalPct / $count, 1) : 0;
            $divisiData[$name]['total'] = ($divisiData[$name]['total'] ?? 0) + $avg;
            $divisiData[$name]['count'] = ($divisiData[$name]['count'] ?? 0) + 1;
        }
        foreach ($divisiData as $name => $d) {
            $avg = round($d['total'] / $d['count'], 1);
            $menangKalah['divisi'][$avg >= 100 ? 'menang' : 'kalah'][] = ['name' => $name, 'score' => $avg];
        }

        foreach ($up3s as $up3) {
            $up3Targets = $bdMap[$up3->id] ?? [];
            if (empty($up3Targets)) continue;
            $childIds = $allUlps->where('parent_id', $up3->id)->pluck('id')->toArray();
            $totalPct = 0; $count = 0;
            foreach ($up3Targets as $lmId => $target) {
                if ($target <= 0) continue;
                $sum = $realMap[$up3->id][$lmId] ?? 0;
                foreach ($childIds as $cId) $sum += $realMap[$cId][$lmId] ?? 0;
                $totalPct += min($sum / $target * 100, 100); $count++;
            }
            $avg = $count > 0 ? round($totalPct / $count, 1) : 0;
            $menangKalah['up3'][$avg >= 100 ? 'menang' : 'kalah'][] = ['name' => $up3->name, 'score' => $avg];
        }

        foreach ($allUlps as $ulp) {
            $ulpTargets = $bdMap[$ulp->id] ?? [];
            if (empty($ulpTargets)) continue;
            $totalPct = 0; $count = 0;
            foreach ($ulpTargets as $lmId => $target) {
                if ($target <= 0) continue;
                $sum = $realMap[$ulp->id][$lmId] ?? 0;
                $totalPct += min($sum / $target * 100, 100); $count++;
            }
            $avg = $count > 0 ? round($totalPct / $count, 1) : 0;
            $menangKalah['ulp'][$avg >= 100 ? 'menang' : 'kalah'][] = ['name' => $ulp->name, 'score' => $avg];
        }

        foreach (['divisi', 'up3', 'ulp'] as $level) {
            usort($menangKalah[$level]['menang'], fn($a, $b) => $b['score'] <=> $a['score']);
            usort($menangKalah[$level]['kalah'],  fn($a, $b) => $b['score'] <=> $a['score']);
        }

        $previousSesi = SesiWig::where('tanggal_pelaksanaan', '<', $sesi_wig->tanggal_pelaksanaan)
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->first();

        // Get the chosen presenters
        $presenters = $sesi_wig->presenters;

        return view('sesi-wigs.show', compact('sesi_wig', 'previousSesi', 'wigs', 'lms', 'units', 'wig_bulan', 'lm_unit', 'leaderboard', 'menangKalah', 'presenters'));
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
