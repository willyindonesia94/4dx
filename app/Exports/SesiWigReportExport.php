<?php

namespace App\Exports;

use App\Models\MasterWig;
use App\Models\MasterLm;
use App\Models\MasterUnit;
use App\Models\SesiWig;
use App\Models\SesiWigKomitmen;
use App\Models\BreakdownLm;
use App\Models\Realisasi;
use App\Models\MasterPeriode;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SesiWigReportExport implements FromView, WithStyles
{
    protected $year;
    protected $month;
    protected $sesiWigId;
    protected $wigId;

    public function __construct($year, $month, $sesiWigId = 'all', $wigId = 'all')
    {
        $this->year = $year;
        $this->month = $month;
        $this->sesiWigId = $sesiWigId;
        $this->wigId = $wigId;
    }

    public function view(): View
    {
        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
        $isUlpLevel = $user && (($user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP') || str_contains(strtoupper($user->role_name ?? ''), 'ULP'));
        $isUp3Level = $user && (($user->unit && in_array(strtoupper(trim((string)$user->unit->type)), ['UP3', 'UP2D', 'UP2K'])) || str_contains(strtoupper($user->role_name ?? ''), 'UP3') || str_contains(strtoupper($user->role_name ?? ''), 'UP2D') || str_contains(strtoupper($user->role_name ?? ''), 'UP2K'));

        // 1. Ambil Sesi WIG
        $sesiWigsQuery = SesiWig::with('presenters')->where('tahun', $this->year);
        if ($this->month !== 'all') {
            $sesiWigsQuery->where('bulan', (int)$this->month);
        }
        if ($this->sesiWigId && $this->sesiWigId !== 'all') {
            $sesiWigsQuery->where('id', $this->sesiWigId);
        }
        $sesiWigs = $sesiWigsQuery->orderBy('bulan')->orderBy('minggu_ke')->get();

        // 2. Ambil WIG & LM sesuai Matrix Group User & Filter WIG
        $wigsQuery = MasterWig::query();
        if ($this->wigId && $this->wigId !== 'all') {
            $wigsQuery->where('id', $this->wigId);
        }
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        $wigs = $wigsQuery->orderBy('id')->get();
        $wigIds = $wigs->pluck('id')->toArray();

        $lms = MasterLm::with(['wig', 'satuan'])
            ->whereIn('wig_id', $wigIds)
            ->get()
            ->sort(function($a, $b) {
                if ($a->wig_id === $b->wig_id) {
                    preg_match('/LM-?(\d+)/i', $a->judul_lm, $mA);
                    preg_match('/LM-?(\d+)/i', $b->judul_lm, $mB);
                    return (int)($mA[1] ?? 999) <=> (int)($mB[1] ?? 999);
                }
                return $a->wig_id <=> $b->wig_id;
            });

        // 3. Ambil Unit yang relevan
        $allUlps = MasterUnit::where('type', 'ULP')->orderBy('name')->get();
        $up3sQuery = MasterUnit::whereIn('type', ['UP3', 'UP2D'])->orderBy('name');
        
        if (!$isSuperAdmin && $user && $user->unit) {
            $unitType = strtoupper(trim((string)$user->unit->type));
            if ($unitType === 'ULP') {
                $allUlps = MasterUnit::where('id', $user->unit_id)->get();
                $up3sQuery->where('id', $user->unit->parent_id);
            } elseif (in_array($unitType, ['UP3', 'UP2D', 'UP2K'])) {
                $allUlps = MasterUnit::where('parent_id', $user->unit_id)->orderBy('name')->get();
                $up3sQuery->where('id', $user->unit_id);
            }
        }
        $up3s = $up3sQuery->get();

        // 4. Kalender Mingguan
        $weeklyCalendars = [];
        $targetBulan = ($this->month !== 'all') ? (int)$this->month : date('n');
        $weeksData = MasterPeriode::getWeekDates($this->year, $targetBulan);
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($weeksData["target_m{$i}"])) {
                $weeklyCalendars[$i] = $weeksData["target_m{$i}"];
            }
        }

        // 5. Build Matriks Target & Realisasi
        $matrixTargets = [];
        $matrixRealisasi = [];
        $matrixRealisasiCount = [];
        $matrixKomitmen = [];

        foreach ($sesiWigs as $sw) {
            $isMingguan = strtolower(trim($sw->tipe_sesi)) === 'mingguan';
            $w = $sw->minggu_ke ?? 1;

            if ($isMingguan && isset($weeklyCalendars[$w])) {
                $swStart = $weeklyCalendars[$w]['start'];
                $swEnd = $weeklyCalendars[$w]['end'];
            } else {
                $swStart = Carbon::create($sw->tahun, $sw->bulan, 1)->addDays(($w-1)*7)->format('Y-m-d');
                $swEnd = Carbon::create($sw->tahun, $sw->bulan, 1)->addDays(($w-1)*7 + 6)->format('Y-m-d');
            }

            // Target Breakdown
            $targets = DB::table('breakdown_lms')
                ->where('periode_start', '=', $swStart)
                ->where('periode_end', '=', $swEnd)
                ->get();
            foreach ($targets as $t) {
                $matrixTargets[$t->lm_id][$t->unit_id][$sw->id] = (float)$t->angka_target;
            }

            // Realisasi
            $realisasis = DB::table('realisasis')
                ->where('tanggal_input', '>=', $swStart . ' 00:00:00')
                ->where('tanggal_input', '<=', $swEnd . ' 23:59:59')
                ->get();
            foreach ($realisasis as $r) {
                $matrixRealisasi[$r->lm_id][$r->unit_id][$sw->id] = ($matrixRealisasi[$r->lm_id][$r->unit_id][$sw->id] ?? 0) + (float)$r->angka_realisasi;
                $matrixRealisasiCount[$r->lm_id][$r->unit_id][$sw->id] = ($matrixRealisasiCount[$r->lm_id][$r->unit_id][$sw->id] ?? 0) + 1;
            }

            // Komitmen
            $komitmens = SesiWigKomitmen::where('sesi_wig_id', $sw->id)->get();
            foreach ($komitmens as $k) {
                $matrixKomitmen[$k->lm_id][$k->unit_id][$sw->id] = [
                    'komitmen' => (float)$k->komitmen,
                    'carry_over' => (float)$k->carry_over,
                    'hambatan' => $k->hambatans ?? [],
                    'aksi_konkrit' => $k->aksi_konkrits ?? []
                ];
            }
        }

        // 6. Rollup ULP -> UP3 -> UID
        $nonSummableSatuans = [1, 2, 14];
        foreach ($lms as $lm) {
            $isNonSummable = in_array($lm->satuan_id, $nonSummableSatuans);
            foreach ($sesiWigs as $sw) {
                // Average ULP if non-summable
                if ($isNonSummable) {
                    foreach ($allUlps as $ulp) {
                        if (isset($matrixRealisasiCount[$lm->id][$ulp->id][$sw->id]) && $matrixRealisasiCount[$lm->id][$ulp->id][$sw->id] > 0) {
                            $matrixRealisasi[$lm->id][$ulp->id][$sw->id] /= $matrixRealisasiCount[$lm->id][$ulp->id][$sw->id];
                        }
                    }
                }

                // Rollup to UP3
                foreach ($up3s as $up3) {
                    $ulpsOfUp3 = $allUlps->where('parent_id', $up3->id);
                    if ($ulpsOfUp3->isNotEmpty()) {
                        $sumT = 0; $sumR = 0; $sumK = 0;
                        $cT = 0; $cR = 0; $cK = 0;
                        foreach ($ulpsOfUp3 as $ulp) {
                            $t = $matrixTargets[$lm->id][$ulp->id][$sw->id] ?? 0;
                            $r = $matrixRealisasi[$lm->id][$ulp->id][$sw->id] ?? 0;
                            $k = $matrixKomitmen[$lm->id][$ulp->id][$sw->id]['komitmen'] ?? null;
                            if ($t > 0) { $sumT += $t; $cT++; }
                            if ($r > 0) { $sumR += $r; $cR++; }
                            if ($k !== null) { $sumK += (float)$k; $cK++; }
                        }
                        if ($isNonSummable) {
                            $matrixTargets[$lm->id][$up3->id][$sw->id] = $cT > 0 ? ($sumT / $cT) : $sumT;
                            $matrixRealisasi[$lm->id][$up3->id][$sw->id] = $cR > 0 ? ($sumR / $cR) : $sumR;
                            if ($sumK > 0) {
                                $matrixKomitmen[$lm->id][$up3->id][$sw->id] = ['komitmen' => $cK > 0 ? ($sumK / $cK) : $sumK];
                            }
                        } else {
                            if (!isset($matrixTargets[$lm->id][$up3->id][$sw->id]) || $matrixTargets[$lm->id][$up3->id][$sw->id] == 0) {
                                $matrixTargets[$lm->id][$up3->id][$sw->id] = $sumT;
                            }
                            if (!isset($matrixRealisasi[$lm->id][$up3->id][$sw->id]) || $matrixRealisasi[$lm->id][$up3->id][$sw->id] == 0) {
                                $matrixRealisasi[$lm->id][$up3->id][$sw->id] = $sumR;
                            }
                            if (!isset($matrixKomitmen[$lm->id][$up3->id][$sw->id]) && $sumK > 0) {
                                $matrixKomitmen[$lm->id][$up3->id][$sw->id] = ['komitmen' => $sumK];
                            }
                        }
                    }
                }

                // Rollup to UID (Unit 1)
                $sumTUid = 0; $sumRUid = 0; $sumKUid = 0;
                $cTUid = 0; $cRUid = 0; $cKUid = 0;
                foreach ($up3s as $up3) {
                    $t = $matrixTargets[$lm->id][$up3->id][$sw->id] ?? 0;
                    $r = $matrixRealisasi[$lm->id][$up3->id][$sw->id] ?? 0;
                    $k = $matrixKomitmen[$lm->id][$up3->id][$sw->id]['komitmen'] ?? null;
                    if ($t > 0) { $sumTUid += $t; $cTUid++; }
                    if ($r > 0) { $sumRUid += $r; $cRUid++; }
                    if ($k !== null) { $sumKUid += (float)$k; $cKUid++; }
                }
                if (!isset($matrixTargets[$lm->id][1][$sw->id]) || $matrixTargets[$lm->id][1][$sw->id] == 0) {
                    $matrixTargets[$lm->id][1][$sw->id] = $isNonSummable ? ($cTUid > 0 ? $sumTUid / $cTUid : 0) : $sumTUid;
                }
                if (!isset($matrixRealisasi[$lm->id][1][$sw->id]) || $matrixRealisasi[$lm->id][1][$sw->id] == 0) {
                    $matrixRealisasi[$lm->id][1][$sw->id] = $isNonSummable ? ($cRUid > 0 ? $sumRUid / $cRUid : 0) : $sumRUid;
                }
                if (!isset($matrixKomitmen[$lm->id][1][$sw->id]) && $sumKUid > 0) {
                    $matrixKomitmen[$lm->id][1][$sw->id] = ['komitmen' => $isNonSummable ? ($cKUid > 0 ? $sumKUid / $cKUid : 0) : $sumKUid];
                }
            }
        }

        // 6b. Calculate Target Bulanan & Defisit Bulan Lalu
        $matrixTargetBulanan = [];
        $matrixDefisitBulanLalu = [];

        $targetTahun = $this->year;
        $startCurMonth = Carbon::create($targetTahun, $targetBulan, 1)->startOfMonth()->format('Y-m-d');
        $endCurMonth = Carbon::create($targetTahun, $targetBulan, 1)->endOfMonth()->format('Y-m-d');
        $curMonthlyBdRecords = DB::table('breakdown_lms')
            ->where('periode_start', '>=', $startCurMonth)
            ->where('periode_start', '<=', $endCurMonth)
            ->get();
        $curMonthlyBdMap = [];
        foreach ($curMonthlyBdRecords as $b) {
            $diff = Carbon::parse($b->periode_start)->diffInDays(Carbon::parse($b->periode_end));
            if ($diff >= 20) {
                $curMonthlyBdMap[$b->lm_id][$b->unit_id] = (float)$b->angka_target;
            }
        }

        $prevBulanNum = $targetBulan > 1 ? $targetBulan - 1 : 12;
        $prevTahunNum = $targetBulan > 1 ? $targetTahun : $targetTahun - 1;
        $startPrev = Carbon::create($prevTahunNum, $prevBulanNum, 1)->startOfMonth()->format('Y-m-d');
        $endPrev = Carbon::create($prevTahunNum, $prevBulanNum, 1)->endOfMonth()->format('Y-m-d');

        $prevBdRecords = DB::table('breakdown_lms')
            ->where('periode_start', '>=', $startPrev)
            ->where('periode_start', '<=', $endPrev)
            ->get();
        $prevRealRecords = DB::table('realisasis')
            ->where('tanggal_input', '>=', $startPrev . ' 00:00:00')
            ->where('tanggal_input', '<=', $endPrev . ' 23:59:59')
            ->select('lm_id', 'unit_id', DB::raw('SUM(angka_realisasi) as total_realisasi'), DB::raw('AVG(angka_realisasi) as avg_realisasi'))
            ->groupBy('lm_id', 'unit_id')
            ->get();

        $prevRealMap = [];
        foreach ($prevRealRecords as $pr) {
            $prevRealMap[$pr->lm_id][$pr->unit_id] = [
                'sum' => (float)$pr->total_realisasi,
                'avg' => (float)$pr->avg_realisasi,
            ];
        }

        $prevBdMap = [];
        foreach ($prevBdRecords as $b) {
            $diff = Carbon::parse($b->periode_start)->diffInDays(Carbon::parse($b->periode_end));
            $prevBdMap[$b->lm_id][$b->unit_id][] = [
                'target' => (float)$b->angka_target,
                'is_monthly' => ($diff >= 20)
            ];
        }

        foreach ($lms as $lm) {
            $isPercent = (($lm->satuan_id ?? null) == 1) || (trim($lm->satuan->name ?? '') === '%');
            $isPolaritasNegatif = strtolower(trim($lm->polaritas ?? 'positif')) === 'negatif' || ($lm->polaritas === '3');
            $isEligibleCarryOver = !$isPercent && !$isPolaritasNegatif;
            $isNonSummable = in_array($lm->satuan_id, $nonSummableSatuans);

            // A. ULP
            $prevTargetUlp = [];
            $prevRealUlp = [];
            foreach ($allUlps as $ulp) {
                if (isset($curMonthlyBdMap[$lm->id][$ulp->id]) && $curMonthlyBdMap[$lm->id][$ulp->id] > 0) {
                    $matrixTargetBulanan[$lm->id][$ulp->id] = $curMonthlyBdMap[$lm->id][$ulp->id];
                } else {
                    $sumT = 0; $cntT = 0;
                    foreach ($sesiWigs as $sw) {
                        $tVal = $matrixTargets[$lm->id][$ulp->id][$sw->id] ?? 0;
                        if ($tVal > 0) { $sumT += $tVal; $cntT++; }
                    }
                    $matrixTargetBulanan[$lm->id][$ulp->id] = $isNonSummable ? ($cntT > 0 ? $sumT / $cntT : 0) : $sumT;
                }

                $bds = $prevBdMap[$lm->id][$ulp->id] ?? [];
                $tPrev = 0;
                if (!empty($bds)) {
                    $monthly = null; $sumT = 0; $countT = 0;
                    foreach ($bds as $bd) {
                        if ($bd['is_monthly']) { $monthly = $bd['target']; break; }
                        $sumT += $bd['target']; $countT++;
                    }
                    $tPrev = $monthly !== null ? $monthly : ($isNonSummable ? ($countT > 0 ? $sumT / $countT : 0) : $sumT);
                }
                $rPrev = $isNonSummable ? ($prevRealMap[$lm->id][$ulp->id]['avg'] ?? 0) : ($prevRealMap[$lm->id][$ulp->id]['sum'] ?? 0);
                $prevTargetUlp[$ulp->id] = $tPrev;
                $prevRealUlp[$ulp->id] = $rPrev;
                $matrixDefisitBulanLalu[$lm->id][$ulp->id] = $isEligibleCarryOver ? max(0, $tPrev - $rPrev) : 0;
            }

            // B. UP3
            foreach ($up3s as $up3) {
                $ulpsOfUp3 = $allUlps->where('parent_id', $up3->id);
                if ($ulpsOfUp3->isNotEmpty()) {
                    $sumTCur = 0; $cntTCur = 0;
                    $sumTPrev = 0; $sumRPrev = 0; $cntTPrev = 0; $cntRPrev = 0;
                    foreach ($ulpsOfUp3 as $u) {
                        $tCur = $matrixTargetBulanan[$lm->id][$u->id] ?? 0;
                        if ($tCur > 0) { $sumTCur += $tCur; $cntTCur++; }
                        $tP = $prevTargetUlp[$u->id] ?? 0;
                        $rP = $prevRealUlp[$u->id] ?? 0;
                        if ($tP > 0) { $sumTPrev += $tP; $cntTPrev++; }
                        if ($rP > 0) { $sumRPrev += $rP; $cntRPrev++; }
                    }
                    $matrixTargetBulanan[$lm->id][$up3->id] = $isNonSummable ? ($cntTCur > 0 ? $sumTCur / $cntTCur : 0) : $sumTCur;
                    $tPrev = $isNonSummable ? ($cntTPrev > 0 ? $sumTPrev / $cntTPrev : 0) : $sumTPrev;
                    $rPrev = $isNonSummable ? ($cntRPrev > 0 ? $sumRPrev / $cntRPrev : 0) : $sumRPrev;
                } else {
                    if (isset($curMonthlyBdMap[$lm->id][$up3->id]) && $curMonthlyBdMap[$lm->id][$up3->id] > 0) {
                        $matrixTargetBulanan[$lm->id][$up3->id] = $curMonthlyBdMap[$lm->id][$up3->id];
                    } else {
                        $sumT = 0; $cntT = 0;
                        foreach ($sesiWigs as $sw) {
                            $tVal = $matrixTargets[$lm->id][$up3->id][$sw->id] ?? 0;
                            if ($tVal > 0) { $sumT += $tVal; $cntT++; }
                        }
                        $matrixTargetBulanan[$lm->id][$up3->id] = $isNonSummable ? ($cntT > 0 ? $sumT / $cntT : 0) : $sumT;
                    }
                    $bds = $prevBdMap[$lm->id][$up3->id] ?? [];
                    $tPrev = 0;
                    if (!empty($bds)) {
                        $monthly = null; $sumT = 0; $countT = 0;
                        foreach ($bds as $bd) {
                            if ($bd['is_monthly']) { $monthly = $bd['target']; break; }
                            $sumT += $bd['target']; $countT++;
                        }
                        $tPrev = $monthly !== null ? $monthly : ($isNonSummable ? ($countT > 0 ? $sumT / $countT : 0) : $sumT);
                    }
                    $rPrev = $isNonSummable ? ($prevRealMap[$lm->id][$up3->id]['avg'] ?? 0) : ($prevRealMap[$lm->id][$up3->id]['sum'] ?? 0);
                }
                $matrixDefisitBulanLalu[$lm->id][$up3->id] = $isEligibleCarryOver ? max(0, $tPrev - $rPrev) : 0;
            }

            // C. UID
            if (isset($curMonthlyBdMap[$lm->id][1]) && $curMonthlyBdMap[$lm->id][1] > 0) {
                $matrixTargetBulanan[$lm->id][1] = $curMonthlyBdMap[$lm->id][1];
            } else {
                $sumTUid = 0; $cntTUid = 0;
                foreach ($up3s as $up3) {
                    $tVal = $matrixTargetBulanan[$lm->id][$up3->id] ?? 0;
                    if ($tVal > 0) { $sumTUid += $tVal; $cntTUid++; }
                }
                $matrixTargetBulanan[$lm->id][1] = $isNonSummable ? ($cntTUid > 0 ? $sumTUid / $cntTUid : 0) : $sumTUid;
            }

            $sumDefUid = 0; $cntDefUid = 0;
            foreach ($up3s as $up3) {
                $def = $matrixDefisitBulanLalu[$lm->id][$up3->id] ?? 0;
                $sumDefUid += $def;
                $cntDefUid++;
            }
            $matrixDefisitBulanLalu[$lm->id][1] = $isEligibleCarryOver ? ($isNonSummable ? ($cntDefUid > 0 ? $sumDefUid / $cntDefUid : 0) : $sumDefUid) : 0;
        }

        // 7. Ambil Detail Komitmen & Aksi Konkrit (Hanya yang memiliki data komitmen / kendala / rencana aksi)
        $sesiIds = $sesiWigs->pluck('id')->toArray();
        $detailKomitmens = SesiWigKomitmen::with(['sesiWig', 'masterLm.wig', 'masterUnit'])
            ->whereIn('sesi_wig_id', $sesiIds)
            ->whereIn('lm_id', $lms->pluck('id')->toArray())
            ->where(function($q) {
                $q->where('komitmen', '>', 0)
                  ->orWhereNotNull('hambatans')
                  ->orWhereNotNull('aksi_konkrits');
            })
            ->get()
            ->filter(function($k) {
                $hasKom = (float)$k->komitmen > 0;
                $hasHambatan = is_array($k->hambatans) && !empty(array_filter($k->hambatans));
                $hasAksi = is_array($k->aksi_konkrits) && !empty(array_filter($k->aksi_konkrits));
                return $hasKom || $hasHambatan || $hasAksi;
            });

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = ($this->month !== 'all') ? ($bulanNames[(int)$this->month] ?? $this->month) : 'Semua Bulan';

        $calcCapaian = function($target, $realisasi, $polaritas) {
            if ($target == 0 && $realisasi == 0) return 100;
            if ($target == 0) return 0;
            if (strtolower(trim($polaritas)) === 'negatif' || $polaritas === '3') {
                if ($realisasi == 0) return 100;
                return ($target / $realisasi) * 100;
            }
            return ($realisasi / $target) * 100;
        };

        return view('exports.sesi_wig_report_table', [
            'tahun' => $this->year,
            'bulan' => $this->month,
            'namaBulan' => $namaBulan,
            'sesiWigs' => $sesiWigs,
            'wigs' => $wigs,
            'lms' => $lms,
            'up3s' => $up3s,
            'allUlps' => $allUlps,
            'matrixTargets' => $matrixTargets,
            'matrixRealisasi' => $matrixRealisasi,
            'matrixKomitmen' => $matrixKomitmen,
            'matrixTargetBulanan' => $matrixTargetBulanan,
            'matrixDefisitBulanLalu' => $matrixDefisitBulanLalu,
            'detailKomitmens' => $detailKomitmens,
            'calcCapaian' => $calcCapaian,
            'isUlpLevel' => $isUlpLevel,
            'isUp3Level' => $isUp3Level,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
