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
        $periodeWig     = $request->query('periode_wig', 'bulanan');

        $periodeStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $periodeEnd   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        // ── Filter UI Data ──────────────────────────────────────────────────────
        $rawDivisions = MasterWig::select('divisi')->whereNotNull('divisi')->pluck('divisi');
        $divisions = collect();
        foreach ($rawDivisions as $raw) {
            $divs = is_array($raw) ? $raw : json_decode($raw, true);
            $divs = is_array($divs) ? $divs : [$raw];
            foreach ($divs as $d) {
                if (!empty($d) && !$divisions->contains($d)) {
                    $divisions->push($d);
                }
            }
        }
        $divisions = $divisions->sort()->values();
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
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereRaw('DATEDIFF(periode_end, periode_start) >= 20')
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
            // Target: Get target for the explicitly selected unit only (prevent double counting with children)
            $targetUnitForScope = $selectedUlp ? $selectedUlp : $selectedUp3;
            if (isset($bdMap[$targetUnitForScope])) {
                foreach ($bdMap[$targetUnitForScope] as $lmId => $t) {
                    $scopedTarget[$lmId] = ($scopedTarget[$lmId] ?? 0) + $t;
                }
            }
        } else {
            // All units - For Target: Only take the UID target (Top-Down distribution)
            $uidUnits = \App\Models\MasterUnit::where('type', 'UID')->pluck('id')->toArray();
            foreach ($uidUnits as $uid) {
                if (isset($bdMap[$uid])) {
                    foreach ($bdMap[$uid] as $lmId => $t) {
                        $scopedTarget[$lmId] = ($scopedTarget[$lmId] ?? 0) + $t;
                    }
                }
            }
        }
        
        // For Realisasi: Aggregate across all relevant units (Bottom-Up)
        if ($scopedUnitIds !== null) {
            foreach ($scopedUnitIds as $uid) {
                foreach ($realMap[$uid] ?? [] as $lmId => $r) {
                    $scopedRealisasi[$lmId] = ($scopedRealisasi[$lmId] ?? 0) + $r;
                }
            }
        } else {
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
        if ($selectedDivisi) $wigQuery->whereJsonContains('divisi', $selectedDivisi);
        $wigs = $wigQuery->get()->each(function($wig) {
            $wig->setRelation('masterLms', $wig->masterLms->sortBy(function($lm) {
                preg_match('/LM-?(\d+)/i', $lm->judul_lm, $m);
                return (int)($m[1] ?? 999);
            })->values());
        });

        $wigProgresses = [];
        foreach ($wigs as $wig) {
            $totalPct = 0;
            $count    = 0;
            $lmDetails = [];

            foreach ($wig->masterLms as $lm) {
                $target      = $scopedTarget[$lm->id] ?? 0;
                $realisasi   = $scopedRealisasi[$lm->id] ?? 0;
                $pct = 0;
                $lmPolaritas = strtolower(trim($lm->polaritas ?? 'positif'));

                if ($target >= 0) {
                    if ($lmPolaritas === 'negatif' || $lmPolaritas === '3') {
                        if ($target == 0) {
                            $pct = ($realisasi == 0) ? 100 : 0;
                        } else {
                            $pct = ($realisasi == 0) ? 100 : ($target / $realisasi) * 100;
                        }
                    } else {
                        if ($target == 0) {
                            $pct = ($realisasi > 0) ? 100 : 0;
                        } else {
                            $pct = ($realisasi / $target) * 100;
                        }
                    }
                    $totalPct += $pct;
                    $count++;
                }

                $lmDetails[] = [
                    'judul'     => $lm->judul_lm,
                    'target'    => $target,
                    'realisasi' => $realisasi,
                    'progress'  => round($pct, 1),
                    'satuan'    => $lm->satuan->name ?? '',
                    'polaritas' => $lm->polaritas ?? 'positif',
                ];
            }

            // Calculate WIG Progress based on Target (Bulanan or Tahunan) and Realisasi
            $wigTargetQuery = DB::table('breakdown_wigs')->where('wig_id', $wig->id)->where('tahun', $tahun);
            if ($scopedUnitIds !== null) {
                $wigTargetQuery->whereIn('unit_id', $scopedUnitIds);
            } else {
                $uidUnits = \App\Models\MasterUnit::where('type', 'UID')->pluck('id')->toArray();
                if (!empty($uidUnits)) {
                    $wigTargetQuery->whereIn('unit_id', $uidUnits);
                }
            }
            
            if ($periodeWig === 'bulanan') {
                $bulanNamesFull = [1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'mei', 6 => 'jun', 7 => 'jul', 8 => 'agu', 9 => 'sep', 10 => 'okt', 11 => 'nov', 12 => 'des'];
                $colTarget = 'target_' . $bulanNamesFull[$bulan];
                $wigTarget = (float) $wigTargetQuery->sum($colTarget);
            } else {
                $wigTarget = (float) $wigTargetQuery->sum('target_tahunan');
            }
            
            // Fallback to global WIG target if no breakdown target is found (only for tahunan)
            if ($wigTarget == 0 && $periodeWig !== 'bulanan') {
                $wigTarget = $wig->angka_target;
            }

            $wigRealQuery = DB::table('realisasi_wigs')
                ->where('wig_id', $wig->id)
                ->where('tahun', $tahun);
                
            if ($periodeWig === 'bulanan') {
                $wigRealQuery->where('bulan', $bulan);
            } else {
                $wigRealQuery->where('bulan', '<=', $bulan);
            }
            
            if ($scopedUnitIds !== null) {
                $wigRealQuery->whereIn('unit_id', $scopedUnitIds);
            }
            $satuanAvgIds = [1, 2, 14]; // %, Menit, Menit/plg
            $isAvg = in_array($wig->satuan_id, $satuanAvgIds);
            
            if ($isAvg) {
                $wigRealisasi = (float) $wigRealQuery->avg('angka_realisasi');
            } else {
                $wigRealisasi = (float) $wigRealQuery->sum('angka_realisasi');
            }

            $wigProgress = 0;
            $wigPolaritas = strtolower(trim($wig->polaritas ?? 'positif'));
            
            if ($wigTarget >= 0) {
                if ($wigPolaritas === 'negatif' || $wigPolaritas === '3') {
                    if ($wigTarget == 0) {
                        $wigProgress = ($wigRealisasi == 0) ? 100 : 0;
                    } else {
                        $wigProgress = ($wigRealisasi == 0) ? 100 : ($wigTarget / $wigRealisasi) * 100;
                    }
                } else {
                    if ($wigTarget == 0) {
                        $wigProgress = ($wigRealisasi > 0) ? 100 : 0;
                    } else {
                        $wigProgress = ($wigRealisasi / $wigTarget) * 100;
                    }
                }
            }

            $wigProgresses[] = [
                'id'          => $wig->id,
                'judul'       => $wig->judul,
                'deskripsi'   => $wig->deskripsi,
                'angka_target' => $wigTarget, // Updated to show scoped target if applicable
                'angka_realisasi' => $wigRealisasi,
                'satuan'      => $wig->satuan->name ?? '',
                'polaritas'   => $wig->polaritas,
                'progress'    => round($wigProgress, 1),
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
        foreach ($leaderboard as $idx => &$item) {
            $item['rank'] = $idx + 1;
        }
        if (count($leaderboard) > 10) {
            $leaderboard = array_merge(array_slice($leaderboard, 0, 5), array_slice($leaderboard, -5));
        }

        $leaderboardUp3 = [];
        foreach ($up3s as $up3) {
            $childIds = $allUlps->where('parent_id', $up3->id)->pluck('id')->toArray();
            $up3Score = 0;
            foreach ($childIds as $cId) {
                $up3Score += $leaderboardScores[$cId] ?? 0;
            }
            $up3Score += $leaderboardScores[$up3->id] ?? 0;
            $leaderboardUp3[] = ['unit' => $up3->name, 'score' => $up3Score];
        }
        usort($leaderboardUp3, fn($a, $b) => $b['score'] <=> $a['score']);
        foreach ($leaderboardUp3 as $idx => &$item) {
            $item['rank'] = $idx + 1;
        }
        if (count($leaderboardUp3) > 10) {
            $leaderboardUp3 = array_merge(array_slice($leaderboardUp3, 0, 5), array_slice($leaderboardUp3, -5));
        }

        // ── Map Data (moved to latestSesiWig block) ───────────────────────────
        $mapData = [];

        // ── Menang / Kalah ─────────────────────────────────────────────────────
        $menangKalah = ['divisi' => ['menang' => [], 'kalah' => []], 'up3' => ['menang' => [], 'kalah' => []], 'ulp' => ['menang' => [], 'kalah' => []]];

        // Divisi – reuse already-loaded $wigs + scoped maps
        $divisiData = [];
        foreach ($wigs as $wig) {
            $divs = is_array($wig->divisi) ? $wig->divisi : json_decode($wig->divisi, true);
            $divs = is_array($divs) ? $divs : [$wig->divisi ?? 'Lainnya'];
            
            $totalPct = 0; $count = 0;
            foreach ($wig->masterLms as $lm) {
                $target = (float) ($scopedTarget[$lm->id] ?? 0);
                if ($target > 0) {
                    $pct = min((float) ($scopedRealisasi[$lm->id] ?? 0) / $target * 100, 100);
                    $totalPct += $pct; $count++;
                }
            }
            $avg = $count > 0 ? round($totalPct / $count, 1) : 0;
            
            foreach ($divs as $name) {
                $divisiData[$name]['total'] = ($divisiData[$name]['total'] ?? 0) + $avg;
                $divisiData[$name]['count'] = ($divisiData[$name]['count'] ?? 0) + 1;
            }
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

        // ── Trend Data Calculation (Jan to Current Month) ──
        $trendData = [
            'labels' => [],
            'wig_progress' => [],
        ];
        
        foreach ($wigs as $wig) {
            $trendData['wig_progress'][$wig->id] = ['name' => $wig->judul, 'data' => []];
        }

        $bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

        $maxBulan = max(1, $bulan);
        for ($m = 1; $m <= $maxBulan; $m++) {
            $trendData['labels'][] = $bulanNames[$m - 1];
            $tPeriodeStart = sprintf('%04d-%02d-01', $tahun, $m);
            $tPeriodeEnd = date('Y-m-t', strtotime($tPeriodeStart));
            
            $tBdQuery = DB::table('breakdown_lms')
                ->where('bulan', $m)
                ->where('tahun', $tahun)
                ->whereRaw('DATEDIFF(periode_end, periode_start) >= 20')
                ->select('lm_id', 'unit_id', DB::raw('SUM(angka_target) as target'))
                ->groupBy('lm_id', 'unit_id')
                ->get();
            $tBdMap = [];
            foreach ($tBdQuery as $row) {
                $tBdMap[$row->unit_id][$row->lm_id] = (float) $row->target;
            }
            
            $tRealBaseQuery = DB::table('realisasis')->whereMonth('tanggal_input', $m)->whereYear('tanggal_input', $tahun);
            if ($scopedUnitIds !== null) {
                $tRealBaseQuery->whereIn('unit_id', $scopedUnitIds);
            }
            $tRealQuery = $tRealBaseQuery->select('lm_id', 'unit_id', DB::raw('SUM(angka_realisasi) as realisasi'))
                ->groupBy('lm_id', 'unit_id')
                ->get();
            $tRealMap = [];
            foreach ($tRealQuery as $row) {
                $tRealMap[$row->unit_id][$row->lm_id] = (float) $row->realisasi;
            }
            
            $tScopedTarget = [];
            $tScopedRealisasi = [];
            if ($scopedUnitIds !== null) {
                $targetUnitForScope = $selectedUlp ? $selectedUlp : $selectedUp3;
                foreach ($tBdMap[$targetUnitForScope] ?? [] as $lmId => $t) {
                    $tScopedTarget[$lmId] = ($tScopedTarget[$lmId] ?? 0) + $t;
                }
                foreach ($scopedUnitIds as $uid) {
                    foreach ($tRealMap[$uid] ?? [] as $lmId => $r) {
                        $tScopedRealisasi[$lmId] = ($tScopedRealisasi[$lmId] ?? 0) + $r;
                    }
                }
            } else {
                // All units - For Target: Only take the UID target (Top-Down distribution)
                $uidUnits = \App\Models\MasterUnit::where('type', 'UID')->pluck('id')->toArray();
                foreach ($uidUnits as $uid) {
                    foreach ($tBdMap[$uid] ?? [] as $lmId => $t) {
                        $tScopedTarget[$lmId] = ($tScopedTarget[$lmId] ?? 0) + $t;
                    }
                }
                // For Realisasi: Aggregate across all units
                foreach ($tRealMap as $uid => $lms) {
                    foreach ($lms as $lmId => $r) {
                        $tScopedRealisasi[$lmId] = ($tScopedRealisasi[$lmId] ?? 0) + $r;
                    }
                }
            }
            
            $bulanNamesFull = [1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'mei', 6 => 'jun', 7 => 'jul', 8 => 'agu', 9 => 'sep', 10 => 'okt', 11 => 'nov', 12 => 'des'];
            $colTarget = 'target_' . $bulanNamesFull[$m];
            
            // Get WIG target for month $m
            $tWigTargetQuery = DB::table('breakdown_wigs')->where('tahun', $tahun);
            if ($scopedUnitIds !== null) {
                $tWigTargetQuery->whereIn('unit_id', $scopedUnitIds);
            } else {
                $uidUnits = \App\Models\MasterUnit::where('type', 'UID')->pluck('id')->toArray();
                if (!empty($uidUnits)) {
                    $tWigTargetQuery->whereIn('unit_id', $uidUnits);
                }
            }
            $tWigTargets = $tWigTargetQuery->select('wig_id', DB::raw("SUM($colTarget) as total_target"))->groupBy('wig_id')->pluck('total_target', 'wig_id')->toArray();
            
            // Get WIG realisasi for month $m
            $tWigRealQuery = DB::table('realisasi_wigs')->where('tahun', $tahun)->where('bulan', $m);
            if ($scopedUnitIds !== null) {
                $tWigRealQuery->whereIn('unit_id', $scopedUnitIds);
            }
            $tWigRealisasisSum = $tWigRealQuery->select('wig_id', DB::raw('SUM(angka_realisasi) as total_realisasi'))->groupBy('wig_id')->pluck('total_realisasi', 'wig_id')->toArray();
            $tWigRealisasisAvg = $tWigRealQuery->select('wig_id', DB::raw('AVG(angka_realisasi) as total_realisasi'))->groupBy('wig_id')->pluck('total_realisasi', 'wig_id')->toArray();

            foreach ($wigs as $wig) {
                $target = (float) ($tWigTargets[$wig->id] ?? 0);
                $realisasi = 0;
                $satuanAvgIds = [1, 2, 14]; // %, Menit, Menit/plg
                $isAvg = in_array($wig->satuan_id, $satuanAvgIds);
                if ($isAvg) {
                    $realisasi = (float) ($tWigRealisasisAvg[$wig->id] ?? 0);
                } else {
                    $realisasi = (float) ($tWigRealisasisSum[$wig->id] ?? 0);
                }
                $wigPolaritas = strtolower(trim($wig->polaritas ?? 'positif'));
                $pct = 0;
                
                if ($target >= 0) {
                    if ($wigPolaritas === 'negatif' || $wigPolaritas === '3') {
                        if ($target == 0) {
                            $pct = ($realisasi == 0) ? 100 : 0;
                        } else {
                            $pct = ($realisasi == 0) ? 100 : ($target / $realisasi) * 100;
                        }
                    } else {
                        if ($target == 0) {
                            $pct = ($realisasi > 0) ? 100 : 0;
                        } else {
                            $pct = ($realisasi / $target) * 100;
                        }
                    }
                }
                
                $trendData['wig_progress'][$wig->id]['data'][] = round($pct, 1);
            }
        }

        // Sort all scoreboard levels
        foreach (['divisi', 'up3', 'ulp'] as $level) {
            usort($menangKalah[$level]['menang'], fn($a, $b) => $b['score'] <=> $a['score']);
            usort($menangKalah[$level]['kalah'],  fn($a, $b) => $b['score'] <=> $a['score']);
        }

        // ── Sesi WIG Matrix Calculation (Latest Sesi in Month) ──
        $latestSesiWig = \App\Models\SesiWig::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('minggu_ke', 'desc')
            ->first();
            
        $sesi_wigs_matrix = collect();
        $matrixTargets = [];
        $matrixRealisasi = [];
        $matrixKomitmen = [];
        $rtMenangKalah = [];
        $dynamicMapData = [];
        $lms = \App\Models\MasterLm::all();
        $sesi_wigs_month = collect();

        if ($latestSesiWig) {
            $sesi_wigs_month = \App\Models\SesiWig::where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->orderBy('minggu_ke')
                ->get();
                
            $sesi_wigs_matrix = $sesi_wigs_month;
            if ($latestSesiWig->tipe_sesi === 'Mingguan') {
                $sesi_wigs_matrix = $sesi_wigs_month->filter(function($sw) use ($latestSesiWig) {
                    return $sw->tipe_sesi === 'Mingguan' && $sw->minggu_ke > 0 && $sw->minggu_ke <= $latestSesiWig->minggu_ke;
                });
            }

            $matrixWeeklyTargets = [];
            foreach ($sesi_wigs_month as $index => $sw) {
                $swDate = \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->endOfDay();
                
                // Determine the start date for the current week's interval
                if ($index === 0) {
                    // For the first week, use the start of the month (or a bit earlier to catch first week data)
                    $prevSwDate = \Carbon\Carbon::create($tahun, $bulan, 1)->subDays(7)->startOfDay();
                } else {
                    $prevSw = $sesi_wigs_month[$index - 1];
                    $prevSwDate = \Carbon\Carbon::parse($prevSw->tanggal_pelaksanaan)->endOfDay();
                }

                $sums = \Illuminate\Support\Facades\DB::table('realisasis')
                    ->where('tanggal_input', '>', $prevSwDate)
                    ->where('tanggal_input', '<=', $swDate)
                    ->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_realisasi) as total'))
                    ->groupBy('lm_id', 'unit_id')
                    ->get();
                foreach ($sums as $s) {
                    $matrixRealisasi[$s->lm_id][$s->unit_id][$sw->id] = $s->total;
                }
                
                $wTgt = \Illuminate\Support\Facades\DB::table('breakdown_lms')
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->where('periode_end', '>', $prevSwDate)
                    ->where('periode_end', '<=', $swDate)
                    ->whereRaw('DATEDIFF(periode_end, periode_start) < 20')
                    ->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_target) as total'))
                    ->groupBy('lm_id', 'unit_id')
                    ->get();
                foreach ($wTgt as $wt) {
                    $matrixWeeklyTargets[$wt->lm_id][$wt->unit_id][$sw->id] = (float) $wt->total;
                }
            }

            // Fetch Komitmen
            $komitmens = \App\Models\SesiWigKomitmen::whereIn('sesi_wig_id', $sesi_wigs_month->pluck('id'))->get();
            foreach ($komitmens as $k) {
                $matrixKomitmen[$k->lm_id][$k->unit_id][$k->sesi_wig_id] = [
                    'komitmen' => $k->komitmen,
                    'carry_over' => $k->carry_over
                ];
            }
        }

        $targetQuery = \Illuminate\Support\Facades\DB::table('breakdown_lms')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereRaw('DATEDIFF(periode_end, periode_start) >= 20')
            ->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_target) as target'))
            ->groupBy('lm_id', 'unit_id')
            ->get();
        $rtBdMap = [];
        foreach ($targetQuery as $t) {
            $matrixTargets[$t->lm_id][$t->unit_id] = $t->target;
            $rtBdMap[$t->unit_id][$t->lm_id] = (float) $t->target;
        }

        // Calculate Menang Kalah for latest sesi (or end of month if no sesi)
        $rtRealQuery = \Illuminate\Support\Facades\DB::table('realisasis')
            ->whereMonth('tanggal_input', $bulan)
            ->whereYear('tanggal_input', $tahun);
            
        if ($latestSesiWig) {
            $rtRealQuery->where('tanggal_input', '<=', \Carbon\Carbon::parse($latestSesiWig->tanggal_pelaksanaan)->endOfDay());
        }
        
        $rtRealQuery = $rtRealQuery->select('lm_id', 'unit_id', \Illuminate\Support\Facades\DB::raw('SUM(angka_realisasi) as realisasi'))
            ->groupBy('lm_id', 'unit_id')
            ->get();
            
        $rtRealMap = [];
        foreach ($rtRealQuery as $row) { $rtRealMap[$row->unit_id][$row->lm_id] = (float) $row->realisasi; }

        if (!$latestSesiWig) {
            $dummySw = new \stdClass();
            $dummySw->id = 'dummy';
            $dummySw->minggu_ke = '0';
            $sesi_wigs_matrix->push($dummySw);

            $matrixWeeklyTargets = [];
            foreach ($rtRealMap as $uid => $lmsReal) {
                foreach ($lmsReal as $lmId => $real) {
                    $matrixRealisasi[$lmId][$uid]['dummy'] = $real;
                }
            }
            foreach ($rtBdMap as $uid => $lmsBd) {
                foreach ($lmsBd as $lmId => $bd) {
                    $matrixWeeklyTargets[$lmId][$uid]['dummy'] = $bd;
                }
            }
        }

        foreach ($lms as $lm) {
            $rtMenangKalah[$lm->id] = ['divisi' => ['menang' => [], 'kalah' => []], 'up3' => ['menang' => [], 'kalah' => []], 'ulp' => ['menang' => [], 'kalah' => []]];

            $wig = $wigs->where('id', $lm->wig_id)->first();
            if ($wig) {
                $divs = is_array($wig->divisi) ? $wig->divisi : json_decode($wig->divisi, true);
                $divs = is_array($divs) ? $divs : [$wig->divisi ?? 'Lainnya'];
                
                $target = 0; 
                $uidUnits = \App\Models\MasterUnit::where('type', 'UID')->pluck('id')->toArray();
                foreach ($uidUnits as $uid) { $target += $rtBdMap[$uid][$lm->id] ?? 0; }
                $realisasi = 0; foreach ($rtRealMap as $uid => $lmsReal) { $realisasi += $lmsReal[$lm->id] ?? 0; }
                if ($target > 0) {
                    $pct = min($realisasi / $target * 100, 100);
                    foreach ($divs as $name) {
                        $rtMenangKalah[$lm->id]['divisi'][$pct >= 100 ? 'menang' : 'kalah'][] = ['name' => $name, 'score' => round($pct, 1)];
                    }
                }
            }

            foreach ($up3s as $up3) {
                $target = $rtBdMap[$up3->id][$lm->id] ?? 0;
                if ($target <= 0) continue;
                $sum = $rtRealMap[$up3->id][$lm->id] ?? 0;
                $childIds = $ulps->where('parent_id', $up3->id)->pluck('id')->toArray();
                foreach ($childIds as $cId) {
                    $sum += $rtRealMap[$cId][$lm->id] ?? 0;
                }
                $pct = min($sum / $target * 100, 100);
                $rtMenangKalah[$lm->id]['up3'][$pct >= 100 ? 'menang' : 'kalah'][] = ['name' => $up3->name, 'score' => round($pct, 1)];
            }

            foreach ($ulps as $ulp) {
                $target = $rtBdMap[$ulp->id][$lm->id] ?? 0;
                if ($target <= 0) continue;
                $sum = $rtRealMap[$ulp->id][$lm->id] ?? 0;
                $pct = min($sum / $target * 100, 100);
                $rtMenangKalah[$lm->id]['ulp'][$pct >= 100 ? 'menang' : 'kalah'][] = ['name' => $ulp->name, 'score' => round($pct, 1)];
            }

            foreach (['divisi', 'up3', 'ulp'] as $level) {
                usort($rtMenangKalah[$lm->id][$level]['menang'], fn($a, $b) => $b['score'] <=> $a['score']);
                usort($rtMenangKalah[$lm->id][$level]['kalah'],  fn($a, $b) => $b['score'] <=> $a['score']);
            }
        }
        
        // Generate Map Data specifically by LM for the dashboard
        $dynamicMapData = [];
        foreach ($lms as $lm) {
            $dynamicMapData[$lm->id] = ['up3' => [], 'ulp' => []];
            
            // ULP Map Data
            foreach ($allUlps->filter(fn($u) => $u->latitude && $u->longitude) as $ulp) {
                $target = $rtBdMap[$ulp->id][$lm->id] ?? 0;
                $sum = $rtRealMap[$ulp->id][$lm->id] ?? 0;
                $pct = $target > 0 ? min(($sum / $target) * 100, 100) : 0;
                
                $komitmenVal = '';
                if ($latestSesiWig) {
                    $komitmenData = $matrixKomitmen[$lm->id][$ulp->id][$latestSesiWig->id] ?? ['komitmen' => ''];
                    $komitmenVal = $komitmenData['komitmen'];
                }
                
                $dynamicMapData[$lm->id]['ulp'][] = [
                    'id' => $ulp->id,
                    'name' => $ulp->name,
                    'lat' => $ulp->latitude,
                    'lng' => $ulp->longitude,
                    'progress' => round($pct, 1),
                    'komitmen' => $komitmenVal,
                    'realisasi' => $sum
                ];
            }
            
            // UP3 Map Data
            foreach ($up3s->filter(fn($u) => $u->latitude && $u->longitude) as $up3) {
                $target = $rtBdMap[$up3->id][$lm->id] ?? 0;
                $sum = $rtRealMap[$up3->id][$lm->id] ?? 0;
                $childIds = $allUlps->where('parent_id', $up3->id)->pluck('id')->toArray();
                foreach ($childIds as $cId) {
                    $sum += $rtRealMap[$cId][$lm->id] ?? 0;
                }
                $pct = $target > 0 ? min(($sum / $target) * 100, 100) : 0;
                
                $komitmenVal = '';
                if ($latestSesiWig) {
                    $komitmenData = $matrixKomitmen[$lm->id][$up3->id][$latestSesiWig->id] ?? ['komitmen' => ''];
                    $komitmenVal = $komitmenData['komitmen'];
                }
                
                $dynamicMapData[$lm->id]['up3'][] = [
                    'id' => $up3->id,
                    'name' => $up3->name,
                    'lat' => $up3->latitude,
                    'lng' => $up3->longitude,
                    'progress' => round($pct, 1),
                    'komitmen' => $komitmenVal,
                    'realisasi' => $sum
                ];
            }
        }

        return view('dashboard.index', compact(
            'totalWigs', 'totalLms', 'totalRealisasis',
            'wigProgresses', 'mapData', 'dynamicMapData', 'divisions', 'selectedDivisi',
            'up3s', 'ulps', 'selectedUp3', 'selectedUlp',
            'leaderboard', 'leaderboardUp3', 'menangKalah', 'bulan', 'tahun', 'trendData',
            'wigs', 'latestSesiWig', 'sesi_wigs_month', 'sesi_wigs_matrix', 'matrixTargets', 'matrixRealisasi', 'matrixKomitmen', 'rtMenangKalah', 'periodeWig',
            'rtBdMap', 'rtRealMap', 'matrixWeeklyTargets'
        ));
    }
}
