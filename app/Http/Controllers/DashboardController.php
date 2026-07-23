<?php
namespace App\Http\Controllers;

use App\Models\MasterWig;
use App\Models\MasterLm;
use App\Models\Realisasi;
use App\Models\MasterUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedDivisi = $request->query('divisi');
        $selectedUp3    = $request->query('up3_id');
        $selectedUlp    = $request->query('ulp_id');
        $bulan          = (int) $request->query('bulan', date('n'));
        $tahun          = (int) $request->query('tahun', date('Y'));

        $periodeStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $periodeEnd   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // ── Filter UI Data ──────────────────────────────────────────────────────
        $divisions = MasterWig::select('divisi')->distinct()->pluck('divisi');
        $up3s      = MasterUnit::where('type', 'UP3')->get();
        $ulpsQuery = MasterUnit::where('type', 'ULP');
        if ($selectedUp3) $ulpsQuery->where('parent_id', $selectedUp3);
        $ulps    = $ulpsQuery->get();
        $allUlps = MasterUnit::where('type', 'ULP')->get();

        // ── Quick Stats ─────────────────────────────────────────────────────────
        $totalWigs = MasterWig::where('is_approved', true)->count();
        $totalLms  = MasterLm::where('is_approved', true)->count();

        $realCountQ = Realisasi::whereMonth('tanggal_input', $bulan)->whereYear('tanggal_input', $tahun);
        if ($selectedUlp) {
            $realCountQ->where('unit_id', $selectedUlp);
        } elseif ($selectedUp3) {
            $realCountQ->whereIn('unit_id', function ($q) use ($selectedUp3) {
                $q->select('id')->from('master_units')->where('id', $selectedUp3)->orWhere('parent_id', $selectedUp3);
            });
        }
        $totalRealisasis = $realCountQ->count();

        // ── Build unit_id filter list for current scope ─────────────────────────
        if ($selectedUlp) {
            $scopedUnitIds = [$selectedUlp];
        } elseif ($selectedUp3) {
            $scopedUnitIds = MasterUnit::where('id', $selectedUp3)
                ->orWhere('parent_id', $selectedUp3)
                ->pluck('id')->toArray();
        } else {
            $scopedUnitIds = null; // null = no unit filter
        }

        // ── Pre-aggregate Targets (breakdowns) ─────────────────────────────────
        $bdQuery = DB::table('breakdown_lms')
            ->where('periode_start', '<=', $periodeEnd)
            ->where('periode_end', '>=', $periodeStart)
            ->select('lm_id', 'unit_id', DB::raw('SUM(angka_target) as target'))
            ->groupBy('lm_id', 'unit_id')
            ->get();

        // ── Pre-aggregate Realisasi ────────────────────────────────────────────
        $realBaseQuery = DB::table('realisasis')
            ->whereMonth('tanggal_input', $bulan)
            ->whereYear('tanggal_input', $tahun);
        if ($scopedUnitIds !== null) {
            $realBaseQuery->whereIn('unit_id', $scopedUnitIds);
        }
        $realQuery = (clone $realBaseQuery)
            ->select('lm_id', 'unit_id', DB::raw('SUM(angka_realisasi) as realisasi'))
            ->groupBy('lm_id', 'unit_id')
            ->get();

        // Build fast lookup maps  [unit_id][lm_id] => value
        $bdMap   = [];
        foreach ($bdQuery as $row) {
            $bdMap[$row->unit_id][$row->lm_id] = (float) $row->target;
        }
        $realMap = [];
        foreach ($realQuery as $row) {
            $realMap[$row->unit_id][$row->lm_id] = (float) $row->realisasi;
        }

        // ── Build scoped target map for current scope ──────────────────────────
        // Merged: aggregate target & realisasi per lm_id for current scope
        $scopedTarget   = [];   // [lm_id] => target
        $scopedRealisasi = [];  // [lm_id] => realisasi

        if ($scopedUnitIds !== null) {
            foreach ($scopedUnitIds as $uid) {
                foreach ($bdMap[$uid] ?? [] as $lmId => $t) {
                    $scopedTarget[$lmId] = ($scopedTarget[$lmId] ?? 0) + $t;
                }
                foreach ($realMap[$uid] ?? [] as $lmId => $r) {
                    $scopedRealisasi[$lmId] = ($scopedRealisasi[$lmId] ?? 0) + $r;
                }
            }
        } else {
            // All units – aggregate across everything
            foreach ($bdMap as $uid => $lms) {
                foreach ($lms as $lmId => $t) {
                    $scopedTarget[$lmId] = ($scopedTarget[$lmId] ?? 0) + $t;
                }
            }
            foreach ($realMap as $uid => $lms) {
                foreach ($lms as $lmId => $r) {
                    $scopedRealisasi[$lmId] = ($scopedRealisasi[$lmId] ?? 0) + $r;
                }
            }
        }

        // ── WIG Progresses ─────────────────────────────────────────────────────
        $wigQuery = MasterWig::where('is_approved', true)->with(['satuan', 'masterLms' => function ($q) {
            $q->where('is_approved', true)->with('satuan');
        }]);
        if ($selectedDivisi) $wigQuery->where('divisi', $selectedDivisi);
        $wigs = $wigQuery->get();

        $wigProgresses = [];
        foreach ($wigs as $wig) {
            $totalPct = 0;
            $count    = 0;
            $lmDetails = [];

            foreach ($wig->masterLms as $lm) {
                $target      = $scopedTarget[$lm->id] ?? 0;
                $realisasi   = $scopedRealisasi[$lm->id] ?? 0;

                if ($target > 0) {
                    if ($wig->polaritas === 'negatif') {
                        $pct = max(0, 100 + (($target - $realisasi) / $target) * 100);
                    } else {
                        $pct = ($realisasi / $target) * 100;
                    }
                    $pct = min($pct, 100);
                    $totalPct += $pct;
                    $count++;

                    $lmDetails[] = [
                        'judul'     => $lm->judul_lm,
                        'target'    => $target,
                        'realisasi' => $realisasi,
                        'progress'  => round($pct, 1),
                        'satuan'    => $lm->satuan->name ?? '',
                        'polaritas' => $wig->polaritas,
                    ];
                }
            }

            $wigProgresses[] = [
                'id'          => $wig->id,
                'judul'       => $wig->judul,
                'angka_target' => $wig->angka_target,
                'satuan'      => $wig->satuan->name ?? '',
                'progress'    => $count > 0 ? round($totalPct / $count, 1) : 0,
                'lm_count'    => $count,
                'lms'         => $lmDetails,
            ];
        }

        // ── Leaderboard (Top 10 ULP by total realisasi this month) ───────────
        $leaderboardScores = DB::table('realisasis')
            ->whereMonth('tanggal_input', $bulan)
            ->whereYear('tanggal_input', $tahun)
            ->select('unit_id', DB::raw('SUM(angka_realisasi) as score'))
            ->groupBy('unit_id')
            ->pluck('score', 'unit_id')->toArray();

        $leaderboard = [];
        foreach ($allUlps as $ulp) {
            $leaderboard[] = ['unit' => $ulp->name, 'score' => $leaderboardScores[$ulp->id] ?? 0];
        }
        usort($leaderboard, fn($a, $b) => $b['score'] <=> $a['score']);
        $leaderboard = array_slice($leaderboard, 0, 10);

        // ── Map Data ───────────────────────────────────────────────────────────
        $mapData = $allUlps->filter(fn($u) => $u->latitude && $u->longitude)
            ->map(fn($u) => ['name' => $u->name, 'lat' => $u->latitude, 'lng' => $u->longitude, 'progress' => rand(40, 100)])
            ->values();

        // ── Menang / Kalah ─────────────────────────────────────────────────────
        $menangKalah = ['divisi' => ['menang' => [], 'kalah' => []], 'up3' => ['menang' => [], 'kalah' => []], 'ulp' => ['menang' => [], 'kalah' => []]];

        // Divisi – reuse already-loaded $wigs + scoped maps
        $divisiData = [];
        foreach ($wigs as $wig) {
            $name = $wig->divisi ?? 'Lainnya';
            $totalPct = 0; $count = 0;
            foreach ($wig->masterLms as $lm) {
                $target = (float) ($scopedTarget[$lm->id] ?? 0);
                if ($target > 0) {
                    $pct = min((float) ($scopedRealisasi[$lm->id] ?? 0) / $target * 100, 100);
                    $totalPct += $pct; $count++;
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

        // UP3 – use bdMap (per-UP3 targets) + combined realMap for UP3+children
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

        // ULP
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

        // Sort all scoreboard levels
        foreach (['divisi', 'up3', 'ulp'] as $level) {
            usort($menangKalah[$level]['menang'], fn($a, $b) => $b['score'] <=> $a['score']);
            usort($menangKalah[$level]['kalah'],  fn($a, $b) => $b['score'] <=> $a['score']);
        }

        return view('dashboard.index', compact(
            'totalWigs', 'totalLms', 'totalRealisasis',
            'wigProgresses', 'mapData', 'divisions', 'selectedDivisi',
            'up3s', 'ulps', 'selectedUp3', 'selectedUlp',
            'leaderboard', 'menangKalah', 'bulan', 'tahun'
        ));
    }
}
