<?php
namespace App\Http\Controllers;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use App\Models\BreakdownLm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CascadingController extends Controller
{
    public function wigIndex()
    {
        $user = Auth::user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isPerencanaanUid = $user && $user->hasRole('Perencanaan UID');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanPerencanaanUp3 = $user && $user->hasRole('Asman Perencanaan UP3');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');
        
        $skipMatrixFilter = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'SRM Perencanaan UID', 'Manager UP3', 'Manager ULP', 'General Manager UID']);
        // MSB UID atau Asman Perencanaan UP3 dengan matrix_group_id = 'ALL' juga skip filter
        $skipMatrixFilter = $skipMatrixFilter
            || ($isMsb && strtoupper($userMatrixGroup) === 'ALL')
            || ($isAsmanPerencanaanUp3 && strtoupper($userMatrixGroup) === 'ALL');
        $canApproveWig = $isSuperAdmin || $isMsb;
        
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['satuan', 'breakdowns', 'breakdowns.unit', 'breakdowns.satuan']);
            
        if (!$skipMatrixFilter && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        
        $wigs = $wigsQuery->get()->each(function ($wig) {
            $wig->setRelation('breakdowns', $wig->breakdowns->sortBy(function ($bd) {
                if (!$bd->unit) return '';
                $type = strtoupper(trim($bd->unit->type));
                $prefix = in_array($type, ['UP2D', 'UP2K']) ? 'z_' : 'a_';
                return $prefix . strtolower($bd->unit->name);
            })->values());
        });

        $satuans = MasterSatuan::all();
        $uidUnits = MasterUnit::where('type', 'UID')->get();
        $up3Units = MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->get();

        return view('cascading.wig', compact('wigs', 'satuans', 'uidUnits', 'up3Units', 'canApproveWig'));
    }

    public function lmIndex()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        
        $user = Auth::user();
        $unitType = $user->unit ? strtoupper(trim((string)$user->unit->type)) : '';
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isPerencanaanUid = $user && $user->hasRole('Perencanaan UID');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanPerencanaanUp3 = $user && $user->hasRole('Asman Perencanaan UP3');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');
        
        $isK3L = strtoupper($userMatrixGroup) === 'K3L';
        
        $isUid = !$isSuperAdmin && ($unitType === 'UID' || $isK3L || $user->hasAnyRole(['Perencanaan UID', 'SRM Perencanaan UID', 'SRM Bidang UID', 'MSB UID', 'Admin Sub Bidang UID', 'General Manager UID']));
        $isUp3 = !$isSuperAdmin && !$isK3L && (in_array($unitType, ['UP3', 'UP2D', 'UP2K']) || $user->hasAnyRole(['Asman Perencanaan UP3', 'Asman Bidang UP3', 'Manager UP3', 'UP2D', 'UP2K']));
        
        $canApproveLm = $isSuperAdmin || $isMsb || $isAsmanBidangUp3 || $isK3L;
        
        $canBreakdownToUid = $isSuperAdmin || $isPerencanaanUid;
        $canBreakdownToUp3 = $isSuperAdmin || $isPerencanaanUid;
        $canBreakdownToUlp = $isSuperAdmin || $isAsmanPerencanaanUp3;
        
        $skipMatrixFilter = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'SRM Perencanaan UID', 'Asman Perencanaan UP3', 'Manager UP3', 'Manager ULP', 'General Manager UID']);
        // MSB UID dengan matrix_group_id = 'ALL' juga skip filter (bisa lihat semua LM)
        $skipMatrixFilter = $skipMatrixFilter || ($isMsb && strtoupper($userMatrixGroup) === 'ALL');
        
        $tahun = request('tahun', date('Y'));
        $up3IdFilter = request('up3_id');
        
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['masterLms' => function($q) use ($tahun, $isUp3, $user, $up3IdFilter) {
                $q->where('is_approved', true)
                  ->withCount([
                      'breakdowns as uid_breakdowns_count' => function($query) use ($tahun) {
                          $query->where('tahun', $tahun)->whereHas('unit', function($subq) {
                              $subq->where('type', 'UID');
                          });
                      },
                      'breakdowns as up3_breakdowns_count' => function($query) use ($tahun, $isUp3, $user, $up3IdFilter) {
                          $query->where('tahun', $tahun)->whereHas('unit', function($subq) use ($isUp3, $user, $up3IdFilter) {
                              $subq->whereIn('type', ['UP3', 'UP2D', 'UP2K']);
                              if (!empty($isUp3) && !empty($user->unit_id)) {
                                  $subq->where(function($q) use ($user) {
                                      $q->where('id', $user->unit_id)
                                        ->orWhere('parent_id', $user->unit_id);
                                  });
                              } else if ($up3IdFilter) {
                                  $subq->where(function($q) use ($up3IdFilter) {
                                      $q->where('id', $up3IdFilter)
                                        ->orWhere('parent_id', $up3IdFilter);
                                  });
                              }
                          });
                      },
                      'breakdowns as ulp_breakdowns_count' => function($query) use ($tahun, $isUp3, $user, $up3IdFilter) {
                          $query->where('tahun', $tahun)->whereHas('unit', function($subq) use ($isUp3, $user, $up3IdFilter) {
                              $subq->where('type', 'ULP');
                              if (!empty($isUp3) && !empty($user->unit_id)) {
                                  $subq->where(function($q) use ($user) {
                                      $q->where('id', $user->unit_id)
                                        ->orWhere('parent_id', $user->unit_id);
                                  });
                              } else if ($up3IdFilter) {
                                  $subq->where(function($q) use ($up3IdFilter) {
                                      $q->where('id', $up3IdFilter)
                                        ->orWhere('parent_id', $up3IdFilter);
                                  });
                              }
                          });
                      }
                  ])
                  ->with('satuan'); // Safely eager load nested relation without array conflict
            }]);

        if (!$skipMatrixFilter && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }

        $wigs = $wigsQuery->get()
            ->each(function($wig) {
                $wig->setRelation('masterLms', $wig->masterLms->sortBy(function($lm) {
                    preg_match('/LM-?(\d+)/i', $lm->judul_lm, $m);
                    return (int)($m[1] ?? 999);
                })->values());
            });

        // Preload myUp3Targets string for ULP Modal
        $allMyUp3Targets = collect();
        if (!empty($isUp3) && !empty($user->unit_id)) {
            $allMyUp3Targets = BreakdownLm::with('unit', 'satuan')
                ->where('tahun', $tahun)
                ->where('unit_id', $user->unit_id)
                ->get()
                ->groupBy('lm_id');
        }

        $formatLmValue = function($value, $satuan) {
            if ($value === null || $value === '') return '-';
            if (trim($satuan) === '%') {
                $formatted = number_format((float)$value, 2, ",", ".");
                $formatted = rtrim(rtrim($formatted, '0'), ',');
                return $formatted . ' %';
            }
            return number_format((float)$value, 2, ",", ".") . ' ' . $satuan;
        };

        $wigs->each(function($wig) use ($allMyUp3Targets, $formatLmValue, $isUp3) {
            $wig->masterLms->each(function($lm) use ($allMyUp3Targets, $formatLmValue, $isUp3) {
                $lm->myUp3TargetText = 'Belum ada target LM bulanan yang diturunkan ke UP3 Anda pada LM ini';
                if (!empty($isUp3) && $allMyUp3Targets->has($lm->id)) {
                    $myUp3Targets = $allMyUp3Targets->get($lm->id);
                    $monthlyTargets = $myUp3Targets->filter(function($t) {
                        return \Carbon\Carbon::parse($t->periode_start)->diffInDays(\Carbon\Carbon::parse($t->periode_end)) >= 20;
                    });
                    if ($monthlyTargets->isEmpty()) {
                        $monthlyTargets = $myUp3Targets;
                    }
                    if ($monthlyTargets->isNotEmpty()) {
                        $unitName = $monthlyTargets->first()->unit->name ?? 'UP3';
                        $targetItems = $monthlyTargets->sortBy('periode_start')->map(function($t) use ($formatLmValue) {
                            return \Carbon\Carbon::parse($t->periode_start)->locale('id')->translatedFormat('M Y') . ': ' . $formatLmValue($t->angka_target, $t->satuan->name ?? '');
                        })->unique()->implode('  •  ');
                        
                        $lm->myUp3TargetText = $unitName . ' => ' . $targetItems;
                    }
                }
            });
        });
        
        $satuans = MasterSatuan::all();
        
        $availableUnits = collect();
        if ($isSuperAdmin || $isPerencanaanUid) {
            $availableUnits = MasterUnit::orderBy('type')->get();
        } elseif ($isUp3) {
            if ($user->unit_id) {
                $userUnit = MasterUnit::find($user->unit_id);
                if ($userUnit && in_array(strtoupper(trim($userUnit->type)), ['UP2D', 'UP2K'])) {
                    $availableUnits = MasterUnit::where('id', $user->unit_id)->get();
                } else {
                    $availableUnits = MasterUnit::where('type', 'ULP')->where('parent_id', $user->unit_id)->orderBy('name')->get();
                }
            } else {
                $availableUnits = MasterUnit::whereIn('type', ['ULP', 'UP2D', 'UP2K'])->orderBy('name')->get();
            }
        }

        return view('cascading.lm', compact(
            'wigs', 'satuans', 'availableUnits', 
            'canBreakdownToUid', 'canBreakdownToUp3', 'canBreakdownToUlp',
            'isSuperAdmin', 'isUid', 'isUp3', 'user', 'canApproveLm', 'tahun'
        ));
    }

    public function getBreakdownsPartial(Request $request, $lm_id, $type)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isPerencanaanUid = $user && $user->hasRole('Perencanaan UID');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');
        $isAsmanPerencanaanUp3 = $user && $user->hasRole('Asman Perencanaan UP3');
        $isK3L = strtoupper(trim((string)($user->matrix_group_id ?? ''))) === 'K3L';
        $isUp3 = !$isSuperAdmin && !$isK3L && (in_array(strtoupper(trim((string)$user->unit->type ?? '')), ['UP3', 'UP2D', 'UP2K']) || $user->hasAnyRole(['Asman Perencanaan UP3', 'Asman Bidang UP3', 'Manager UP3', 'UP2D', 'UP2K']));
        
        $canApproveLm = $isSuperAdmin || $isMsb || $isAsmanBidangUp3 || $isK3L;
        $canEditDelete = $user && ($user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Asman Perencanaan UP3']) || ($isMsb && $isK3L));

        $canBreakdownToUid = $isSuperAdmin || $isPerencanaanUid;
        $canBreakdownToUp3 = $isSuperAdmin || $isPerencanaanUid;
        $canBreakdownToUlp = $isSuperAdmin || $isAsmanPerencanaanUp3;

        $tahun = $request->input('tahun', date('Y'));
        $up3IdFilter = $request->input('up3_id');

        $lm = \App\Models\MasterLm::with('satuan')->findOrFail($lm_id);

        $query = \App\Models\BreakdownLm::with('unit')
            ->where('lm_id', $lm_id)
            ->where('tahun', $tahun);
            
        $query->whereHas('unit', function($q) use ($type, $isUp3, $user, $up3IdFilter) {
            if ($type === 'uid') {
                $q->where('type', 'UID');
            } elseif ($type === 'up3') {
                $q->whereIn('type', ['UP3', 'UP2D', 'UP2K']);
            } elseif ($type === 'ulp') {
                $q->where('type', 'ULP');
            }

            if ($type === 'up3' || $type === 'ulp') {
                if (!empty($isUp3) && !empty($user->unit_id)) {
                    $q->where(function($subq) use ($user) {
                        $subq->where('id', $user->unit_id)
                             ->orWhere('parent_id', $user->unit_id);
                    });
                } else if ($up3IdFilter) {
                    $q->where(function($subq) use ($up3IdFilter) {
                        $subq->where('id', $up3IdFilter)
                             ->orWhere('parent_id', $up3IdFilter);
                    });
                }
            }
        });

        // Fetch all data for client-side per-month pagination
        $breakdowns = $query->orderBy('periode_start')->get();

        return view('cascading.partials.breakdowns_table', compact(
            'breakdowns', 'type', 'lm', 'canApproveLm', 'canEditDelete', 
            'canBreakdownToUid', 'canBreakdownToUp3', 'canBreakdownToUlp'
        ));
    }

    private function checkUp3Permission($targetUnitId = null, $existingBreakdown = null)
    {
        $user = Auth::user();
        if (!$user) return false;
        $isSuperAdmin = $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);
        $isK3L = strtoupper(trim((string)($user->matrix_group_id ?? ''))) === 'K3L';
        $isUp3 = !$isSuperAdmin && !$isK3L && $user->hasAnyRole(['Asman Perencanaan UP3', 'Asman Bidang UP3', 'Manager UP3', 'UP2D', 'UP2K']);

        if ($isUp3 && $user->unit_id) {
            $userUnit = MasterUnit::find($user->unit_id);
            $isTechnicalUp3 = $userUnit && in_array(strtoupper(trim($userUnit->type)), ['UP2D', 'UP2K']);
            
            if ($targetUnitId) {
                $unit = MasterUnit::find($targetUnitId);
                if ($isTechnicalUp3) {
                    if ((int)$unit->id !== (int)$user->unit_id) return false;
                } else {
                    if (!$unit || strtoupper(trim($unit->type)) !== 'ULP' || (int)$unit->parent_id !== (int)$user->unit_id) {
                        return false;
                    }
                }
            }
            if ($existingBreakdown && $existingBreakdown->unit) {
                $unit = $existingBreakdown->unit;
                if ($isTechnicalUp3) {
                    if ((int)$unit->id !== (int)$user->unit_id) return false;
                } else {
                    if (strtoupper(trim($unit->type)) !== 'ULP' || (int)$unit->parent_id !== (int)$user->unit_id) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function storeBreakdown(Request $request)
    {
        $request->validate([
            'lm_id' => 'required|exists:master_lms,id',
            'unit_id' => 'required|exists:master_units,id',
            'bidang' => 'nullable|string|max:255',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2040',
        ]);

        if (!$this->checkUp3Permission($request->unit_id)) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak berwenang menurunkan target ke unit ini.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');

        $is_approved = ($isSuperAdmin || $isMsb || $isAsmanBidangUp3) ? true : false;

        $carbonStart = \Carbon\Carbon::create($request->tahun, $request->bulan, 1);
        $carbonEnd = $carbonStart->copy()->endOfMonth();
        $weeks = \App\Models\MasterPeriode::getWeekDates($request->tahun, $request->bulan);

        $data = $request->except(['bulan', 'tahun', 'target_m1', 'target_m2', 'target_m3', 'target_m4', 'target_m5']);
        $data['bulan'] = $request->bulan;
        $data['tahun'] = $request->tahun;
        $data['periode_start'] = $weeks['target_m1']['start'] ?? $carbonStart->format('Y-m-d');
        $endWeek = isset($weeks['target_m5']) && $weeks['target_m5'] ? 'target_m5' : 'target_m4';
        $data['periode_end'] = $weeks[$endWeek]['end'] ?? $carbonEnd->format('Y-m-d');
        $data['is_approved'] = $is_approved;
        BreakdownLm::create($data);
        
        $weeklyKeys = ['target_m1', 'target_m2', 'target_m3', 'target_m4', 'target_m5'];
        $hasWeekly = false;
        foreach($weeklyKeys as $wk) {
            if ($request->filled($wk)) $hasWeekly = true;
        }
        
        if ($hasWeekly) {
            foreach ($weeks as $key => $dates) {
                if ($dates && $request->filled($key) && $dates['start'] <= $dates['end']) {
                    BreakdownLm::create([
                        'lm_id' => $request->lm_id,
                        'unit_id' => $request->unit_id,
                        'bidang' => $request->bidang,
                        'satuan_id' => $request->satuan_id,
                        'angka_target' => $request->input($key),
                        'periode_start' => $dates['start'],
                        'periode_end' => $dates['end'],
                        'bulan' => $request->bulan,
                        'tahun' => $request->tahun,
                        'is_approved' => $is_approved,
                    ]);
                }
            }
        }

        $lm = \App\Models\MasterLm::find($request->lm_id);
        $wig_id = $lm ? $lm->wig_id : null;
        $unit = \App\Models\MasterUnit::find($request->unit_id);
        $unit_type = $unit ? strtolower($unit->type) : '';
        if (in_array($unit_type, ['up2d', 'up2k'])) $unit_type = 'up3';

        // Kirim notifikasi ke MSB yang sesuai bidang jika perlu approval
        if (!$is_approved) {
            $wig = $wig_id ? \App\Models\MasterWig::find($wig_id) : null;
            $wigDivisis = $wig ? (is_array($wig->divisi) ? $wig->divisi : [$wig->divisi]) : [];

            $breakdownUnit = $unit;
            $parentUp3Id = null;
            if ($breakdownUnit && strtoupper(trim($breakdownUnit->type)) === 'ULP') {
                $parentUp3Id = $breakdownUnit->parent_id;
            } elseif ($breakdownUnit && in_array(strtoupper(trim($breakdownUnit->type)), ['UP3', 'UP2D', 'UP2K'])) {
                $parentUp3Id = $breakdownUnit->id;
            }

            $msbApprovers = \App\Models\User::role(['MSB UID'])
                ->get()
                ->filter(function($u) use ($wigDivisis) {
                    if (strtoupper(trim($u->username)) === 'MSB.PERENCANAAN' || strtoupper(trim($u->name)) === 'MSB PERENCANAAN') return false;
                    $matrixGroup = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($matrixGroup) || strtoupper($matrixGroup) === 'ALL') return true;
                    $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($matrixGroup);
                    return !empty(array_intersect($wigDivisis, $allowedDivisis));
                });

            $asmanApprovers = \App\Models\User::role(['Asman Bidang UP3'])
                ->when($parentUp3Id, fn($q) => $q->where('unit_id', $parentUp3Id))
                ->get()
                ->filter(function($u) use ($wigDivisis) {
                    $matrixGroup = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($matrixGroup) || strtoupper($matrixGroup) === 'ALL') return true;
                    $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($matrixGroup);
                    return !empty(array_intersect($wigDivisis, $allowedDivisis));
                });

            $approvers = $msbApprovers->merge($asmanApprovers)->unique('id');
            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::role(['Super Admin'])->get();
            }

            if ($approvers->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                    'Persetujuan Cascading LM Baru',
                    'Cascading LM Baru',
                    "Cascading LM \"" . ($lm->judul_lm ?? '-') . "\" untuk unit " . ($unit->name ?? '-') . " telah dibuat oleh " . $user->name . " dan membutuhkan persetujuan."
                ));
            }
        }

        $redirect = redirect()->back()->with('success', 'Breakdown LM berhasil ditambahkan ke Unit. ' . (!$is_approved ? 'Menunggu persetujuan.' : ''));
        if ($wig_id) {
            $redirect->with('active_wig', $wig_id)
                     ->with('expanded_lm', $request->lm_id)
                     ->with('expanded_unit_type', $unit_type);
        }
        return $redirect;
    }

    public function updateBreakdown(Request $request, $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:master_units,id',
            'bidang' => 'nullable|string|max:255',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:2040',
        ]);

        $breakdown = BreakdownLm::with('unit')->findOrFail($id);
        $oldTarget = $breakdown->angka_target;

        if (!$this->checkUp3Permission($request->unit_id, $breakdown)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $isMonthly = \Carbon\Carbon::parse($breakdown->periode_start)->diffInDays(\Carbon\Carbon::parse($breakdown->periode_end)) >= 20;
            if ($isMonthly) {
                $weeks = \App\Models\MasterPeriode::getWeekDates($request->tahun, $request->bulan);
                $breakdown->periode_start = $weeks['target_m1']['start'] ?? \Carbon\Carbon::create($request->tahun, $request->bulan, 1)->format('Y-m-d');
                $endWeek = isset($weeks['target_m5']) && $weeks['target_m5'] ? 'target_m5' : 'target_m4';
                $breakdown->periode_end = $weeks[$endWeek]['end'] ?? \Carbon\Carbon::create($request->tahun, $request->bulan, 1)->endOfMonth()->format('Y-m-d');
                $breakdown->bulan = $request->bulan;
                $breakdown->tahun = $request->tahun;
            }
        }

        $breakdown->unit_id = $request->unit_id;
        $breakdown->bidang = $request->bidang;
        $breakdown->angka_target = $request->angka_target;
        $breakdown->satuan_id = $request->satuan_id;

        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');

        $breakdown->save();

        if (!$isSuperAdmin && !$isMsb && !$isAsmanBidangUp3) {
            // Ambil divisi WIG terkait untuk filter approver
            $lm = \App\Models\MasterLm::find($breakdown->lm_id);
            $wig = $lm ? \App\Models\MasterWig::find($lm->wig_id) : null;
            $wigDivisis = $wig ? (is_array($wig->divisi) ? $wig->divisi : [$wig->divisi]) : [];
            $breakdownUnitId = $breakdown->unit_id;
            $breakdownUnit = $breakdown->unit;
            // Cari unit UP3 induk jika unit adalah ULP
            $parentUp3Id = null;
            if ($breakdownUnit && strtoupper(trim($breakdownUnit->type)) === 'ULP') {
                $parentUp3Id = $breakdownUnit->parent_id;
            } elseif ($breakdownUnit && in_array(strtoupper(trim($breakdownUnit->type)), ['UP3', 'UP2D', 'UP2K'])) {
                $parentUp3Id = $breakdownUnit->id;
            }

            // MSB sesuai bidang WIG
            $msbApprovers = \App\Models\User::role(['MSB UID'])->get()
                ->filter(function($u) use ($wigDivisis) {
                    if (strtoupper(trim($u->username)) === 'MSB.PERENCANAAN' || strtoupper(trim($u->name)) === 'MSB PERENCANAAN') return false;
                    $mg = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($mg) || strtoupper($mg) === 'ALL') return true;
                    $allowed = \App\Models\MasterBidang::getRelatedDivisions($mg);
                    return !empty(array_intersect($wigDivisis, $allowed));
                });

            // Asman Bidang UP3 di UP3 yang sama dan sesuai bidang
            $asmanApprovers = \App\Models\User::role(['Asman Bidang UP3'])
                ->when($parentUp3Id, fn($q) => $q->where('unit_id', $parentUp3Id))
                ->get()
                ->filter(function($u) use ($wigDivisis) {
                    $mg = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($mg) || strtoupper($mg) === 'ALL') return true;
                    $allowed = \App\Models\MasterBidang::getRelatedDivisions($mg);
                    return !empty(array_intersect($wigDivisis, $allowed));
                });

            $approvers = $msbApprovers->merge($asmanApprovers)->unique('id');
            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::role(['Super Admin'])->get();
            }

            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Perubahan Cascading LM', 
                'Cascading LM Diubah', 
                "Data Cascading LM unit " . ($breakdown->unit->name ?? '-') . " telah diubah oleh " . $user->name . " (Target lama: " . $oldTarget . " -> " . $breakdown->angka_target . ")"
            ));
        }

        $redirect = redirect()->back()->with('success', 'Breakdown LM berhasil diperbarui.');
        $wig_id = $breakdown->lm->wig_id ?? null;
        if ($wig_id) {
            $unit_type = strtolower($breakdown->unit->type ?? '');
            if (in_array($unit_type, ['up2d', 'up2k'])) $unit_type = 'up3';
            $redirect->with('active_wig', $wig_id)
                     ->with('expanded_lm', $breakdown->lm_id)
                     ->with('expanded_unit_type', $unit_type);
        }
        return $redirect;
    }

    public function destroyBreakdown($id)
    {
        $breakdown = BreakdownLm::with('unit')->findOrFail($id);
        if (!$this->checkUp3Permission(null, $breakdown)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');

        if (!$isSuperAdmin && !$isMsb && !$isAsmanBidangUp3) {
            // Ambil divisi WIG terkait untuk filter approver
            $lm = \App\Models\MasterLm::find($breakdown->lm_id);
            $wig = $lm ? \App\Models\MasterWig::find($lm->wig_id) : null;
            $wigDivisis = $wig ? (is_array($wig->divisi) ? $wig->divisi : [$wig->divisi]) : [];
            $breakdownUnit = $breakdown->unit;
            // Cari unit UP3 induk jika unit adalah ULP
            $parentUp3Id = null;
            if ($breakdownUnit && strtoupper(trim($breakdownUnit->type)) === 'ULP') {
                $parentUp3Id = $breakdownUnit->parent_id;
            } elseif ($breakdownUnit && in_array(strtoupper(trim($breakdownUnit->type)), ['UP3', 'UP2D', 'UP2K'])) {
                $parentUp3Id = $breakdownUnit->id;
            }

            // MSB sesuai bidang WIG
            $msbApprovers = \App\Models\User::role(['MSB UID'])->get()
                ->filter(function($u) use ($wigDivisis) {
                    if (strtoupper(trim($u->username)) === 'MSB.PERENCANAAN' || strtoupper(trim($u->name)) === 'MSB PERENCANAAN') return false;
                    $mg = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($mg) || strtoupper($mg) === 'ALL') return true;
                    $allowed = \App\Models\MasterBidang::getRelatedDivisions($mg);
                    return !empty(array_intersect($wigDivisis, $allowed));
                });

            // Asman Bidang UP3 di UP3 yang sama dan sesuai bidang
            $asmanApprovers = \App\Models\User::role(['Asman Bidang UP3'])
                ->when($parentUp3Id, fn($q) => $q->where('unit_id', $parentUp3Id))
                ->get()
                ->filter(function($u) use ($wigDivisis) {
                    $mg = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($mg) || strtoupper($mg) === 'ALL') return true;
                    $allowed = \App\Models\MasterBidang::getRelatedDivisions($mg);
                    return !empty(array_intersect($wigDivisis, $allowed));
                });

            $approvers = $msbApprovers->merge($asmanApprovers)->unique('id');
            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::role(['Super Admin'])->get();
            }

            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Penghapusan Cascading LM', 
                'Cascading LM Dihapus', 
                "Data Cascading LM unit " . ($breakdown->unit->name ?? '-') . " telah dihapus oleh " . $user->name . " (Target lama: " . $breakdown->angka_target . ")"
            ));
        }

        $wig_id = $breakdown->lm->wig_id ?? null;
        $lm_id = $breakdown->lm_id;
        $unit_type = strtolower($breakdown->unit->type ?? '');
        if (in_array($unit_type, ['up2d', 'up2k'])) $unit_type = 'up3';
        
        $breakdown->delete();

        $redirect = redirect()->back()->with('success', 'Breakdown LM berhasil dihapus.');
        if ($wig_id) {
            $redirect->with('active_wig', $wig_id)
                     ->with('expanded_lm', $lm_id)
                     ->with('expanded_unit_type', $unit_type);
        }
        return $redirect;
    }

    public function bulkDestroyLm(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $user = Auth::user();
        $isMsbK3L = $user && $user->hasRole('MSB UID') && strtoupper(trim((string)($user->matrix_group_id ?? ''))) === 'K3L';
        $canEditDelete = $user && ($user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Asman Perencanaan UP3']) || $isMsbK3L);
        
        if (!$canEditDelete) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus target.');
        }

        $first = \App\Models\BreakdownLm::with('lm')->whereIn('id', $ids)->first();
        $wig_id = $first ? ($first->lm->wig_id ?? null) : null;
        
        \App\Models\BreakdownLm::whereIn('id', $ids)->delete();

        $redirect = redirect()->back()->with('success', count($ids) . ' target berhasil dihapus.');
        if ($wig_id) $redirect->with('active_wig', $wig_id);
        return $redirect;
    }

    public function storeWigBreakdown(Request $request)
    {
        $request->validate([
            'wig_id' => 'required|exists:master_wigs,id',
            'unit_id' => 'required|exists:master_units,id',
            'satuan_id' => 'required|exists:master_satuans,id',
            'tahun' => 'required|integer',
            'target_tahunan' => 'required|numeric',
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isMsb = $user && $user->hasRole('MSB UID');

        $is_approved = ($isSuperAdmin || $isMsb) ? true : false;

        $data = $request->all();
        $data['is_approved'] = $is_approved;

        $breakdown = \App\Models\BreakdownWig::create($data);

        if (!$is_approved) {
            // Ambil divisi WIG yang terkait untuk menentukan MSB yang berwenang
            $wig = \App\Models\MasterWig::find($request->wig_id);
            $wigDivisis = $wig ? (is_array($wig->divisi) ? $wig->divisi : [$wig->divisi]) : [];

            // Cari MSB yang matrix_group_id-nya cocok dengan divisi WIG
            $approvers = \App\Models\User::role(['MSB UID'])
                ->get()
                ->filter(function($u) use ($wigDivisis) {
                    if (strtoupper(trim($u->username)) === 'MSB.PERENCANAAN' || strtoupper(trim($u->name)) === 'MSB PERENCANAAN') return false;
                    $matrixGroup = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($matrixGroup) || strtoupper($matrixGroup) === 'ALL') return true;
                    $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($matrixGroup);
                    return !empty(array_intersect($wigDivisis, $allowedDivisis));
                });

            // Jika tidak ada MSB yang cocok, fallback ke Super Admin
            if ($approvers->isEmpty()) {
                $approvers = \App\Models\User::role(['Super Admin'])->get();
            }

            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Persetujuan Cascading WIG Baru', 
                'Cascading WIG Baru', 
                "Cascading WIG untuk unit Anda telah dibuat oleh " . $user->name . " dan membutuhkan persetujuan."
            ));
        }

        return redirect()->back()->with('success', 'Breakdown WIG berhasil ditambahkan ke Unit. ' . (!$is_approved ? 'Menunggu persetujuan.' : ''))->with('active_wig', $request->wig_id);
    }

    public function updateWigBreakdown(Request $request, $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:master_units,id',
            'satuan_id' => 'required|exists:master_satuans,id',
            'tahun' => 'required|integer',
            'target_tahunan' => 'required|numeric',
        ]);

        $breakdown = \App\Models\BreakdownWig::findOrFail($id);
        $oldTarget = $breakdown->target_tahunan;

        $breakdown->unit_id = $request->unit_id;
        $breakdown->satuan_id = $request->satuan_id;
        $breakdown->tahun = $request->tahun;
        $breakdown->target_tahunan = $request->target_tahunan;
        
        $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
        foreach($months as $m) {
            if ($request->has("target_{$m}")) $breakdown->{"target_{$m}"} = $request->{"target_{$m}"};
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isMsb = $user && $user->hasRole('MSB UID');

        $breakdown->save();

        if (!$isSuperAdmin && !$isMsb) {
            $approvers = \App\Models\User::role(['Super Admin', 'MSB UID'])->get()->filter(function($u) {
                return strtoupper(trim($u->username)) !== 'MSB.PERENCANAAN' && strtoupper(trim($u->name)) !== 'MSB PERENCANAAN';
            });
            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                !$breakdown->is_approved ? 'Persetujuan Cascading WIG' : 'Perubahan Cascading WIG', 
                !$breakdown->is_approved ? 'Menunggu Persetujuan' : 'Cascading WIG Diubah', 
                !$breakdown->is_approved 
                    ? "Draft Cascading WIG unit " . ($breakdown->unit->name ?? '-') . " telah diperbarui oleh " . $user->name . " dan menunggu persetujuan Anda."
                    : "Data Cascading WIG unit " . ($breakdown->unit->name ?? '-') . " telah diubah oleh " . $user->name . " (Target lama: " . $oldTarget . " -> " . $breakdown->target_tahunan . ")"
            ));
        }

        return redirect()->back()->with('success', 'Breakdown WIG berhasil diperbarui.')->with('active_wig', $breakdown->wig_id)->with('expanded_breakdown', $breakdown->id);
    }

    public function destroyWigBreakdown($id)
    {
        $breakdown = \App\Models\BreakdownWig::with('unit')->findOrFail($id);
        
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isMsb = $user && $user->hasRole('MSB UID');

        if (!$isSuperAdmin && !$isMsb) {
            $approvers = \App\Models\User::role(['Super Admin', 'MSB UID'])->get()->filter(function($u) {
                return strtoupper(trim($u->username)) !== 'MSB.PERENCANAAN' && strtoupper(trim($u->name)) !== 'MSB PERENCANAAN';
            });
            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Penghapusan Cascading WIG', 
                'Cascading WIG Dihapus', 
                "Data Cascading WIG unit " . ($breakdown->unit->name ?? '-') . " telah dihapus oleh " . $user->name . " (Target lama: " . $breakdown->target_tahunan . ")"
            ));
        }

        $wig_id = $breakdown->wig_id;
        $breakdown->delete();

        return redirect()->back()->with('success', 'Breakdown WIG berhasil dihapus.')->with('active_wig', $wig_id);
    }

    public function wigTemplate() { return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WigMassTemplateExport, 'Template_Mass_Upload_WIG.xlsx'); }
    
    public function wigImport(Request $request)
    {
        $request->validate(['file_excel' => 'required|mimes:xlsx,xls']);
        try {
            $request->file('file_excel')->storeAs('logs', 'uploaded_wig.xlsx', 'local');
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\WigMassImport, $request->file('file_excel'));
            return redirect()->back()->with('success', 'WIG berhasil diimport secara massal!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function lmTemplate() { return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LmMassTemplateExport, 'Template_Mass_Upload_LM.xlsx'); }
    
    public function lmImport(Request $request)
    {
        $request->validate(['file_excel' => 'required|mimes:xlsx,xls']);
        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\LmMassImport, $request->file('file_excel'));
            return redirect()->back()->with('success', 'LM berhasil diimport secara massal!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function breakdownLmTemplate() { return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BreakdownLmTemplateExport, 'Template_Upload_Target_Unit.xlsx'); }
    
    public function breakdownLmTemplateK3L() { return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BreakdownLmTemplateK3LExport, 'Template_Upload_Target_K3L_Semua_ULP.xlsx'); }
    
    public function importBreakdownLm(\Illuminate\Http\Request $request)
    {
        $request->validate([
            "file_excel" => "required|mimes:xlsx,xls",
            "bulan" => "required|integer|min:1|max:12",
            "tahun" => "required|integer|min:2020",
        ]);
        try {
            $request->file("file_excel")->storeAs('logs', 'uploaded_lm.xlsx', 'local');
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\BreakdownLmMassImport($request->bulan, $request->tahun), $request->file("file_excel"));
            
            if (in_array(strtolower(auth()->user()->username), ['admin.k3l', 'msb.k3l'])) {
                $this->accumulateK3LBreakdown($request->bulan, $request->tahun);
            }

            return redirect()->back()->with("success", "Breakdown Target LM berhasil di-upload secara massal.");
        } catch (\Exception $e) {
            return redirect()->back()->with("error", "Terjadi kesalahan saat upload data: " . $e->getMessage());
        }
    }

    private function accumulateK3LBreakdown($bulan, $tahun)
    {
        $lms = \App\Models\MasterLm::whereHas('wig', function($q) {
            $q->where('divisi', 'LIKE', '%K3L%');
        })->with('satuan')->get();

        $uidUnit = \App\Models\MasterUnit::where('type', 'UID')->first();
        $up3Units = \App\Models\MasterUnit::where('type', 'UP3')->with('children')->get();
        $periodes = \App\Models\BreakdownLm::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->select('periode_start', 'periode_end')
            ->distinct()
            ->get();

        foreach ($lms as $lm) {
            $isNonSummable = in_array($lm->satuan_id, [1, 2, 14]);

            foreach ($periodes as $periode) {
                $periodeStart = $periode->periode_start;
                $periodeEnd = $periode->periode_end;
                
                foreach ($up3Units as $up3) {
                    $ulpIds = $up3->children->pluck('id')->toArray();
                    
                    $childTargets = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->where('periode_start', $periodeStart)
                        ->where('periode_end', $periodeEnd)
                        ->whereIn('unit_id', $ulpIds)
                        ->pluck('angka_target');

                    if ($childTargets->count() > 0) {
                        $up3Target = $isNonSummable ? $childTargets->avg() : $childTargets->sum();

                        \App\Models\BreakdownLm::updateOrCreate(
                            [
                                'lm_id' => $lm->id,
                                'unit_id' => $up3->id,
                                'bulan' => $bulan,
                                'tahun' => $tahun,
                                'periode_start' => $periodeStart,
                                'periode_end' => $periodeEnd
                            ],
                            [
                                'angka_target' => $up3Target,
                                'satuan_id' => $lm->satuan_id,
                                'is_approved' => true
                            ]
                        );
                    }
                }

                if ($uidUnit) {
                    $up3Ids = $up3Units->pluck('id')->toArray();
                    $up3Targets = \App\Models\BreakdownLm::where('lm_id', $lm->id)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->where('periode_start', $periodeStart)
                        ->where('periode_end', $periodeEnd)
                        ->whereIn('unit_id', $up3Ids)
                        ->pluck('angka_target');

                    if ($up3Targets->count() > 0) {
                        $uidTarget = $isNonSummable ? $up3Targets->avg() : $up3Targets->sum();

                        \App\Models\BreakdownLm::updateOrCreate(
                            [
                                'lm_id' => $lm->id,
                                'unit_id' => $uidUnit->id,
                                'bulan' => $bulan,
                                'tahun' => $tahun,
                                'periode_start' => $periodeStart,
                                'periode_end' => $periodeEnd
                            ],
                            [
                                'angka_target' => $uidTarget,
                                'satuan_id' => $lm->satuan_id,
                                'is_approved' => true
                            ]
                        );
                    }
                }
            }
        }
    }

    public function approveWigBreakdown($id)
    {
        $breakdown = \App\Models\BreakdownWig::findOrFail($id);
        $breakdown->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Cascading WIG berhasil disetujui.')->with('active_wig', $breakdown->wig_id);
    }

    public function approveLmBreakdown($id)
    {
        $breakdown = BreakdownLm::findOrFail($id);
        $breakdown->update(['is_approved' => true]);
        $wig_id = $breakdown->lm->wig_id ?? null;
        $redirect = redirect()->back()->with('success', 'Cascading LM berhasil disetujui.');
        if ($wig_id) $redirect->with('active_wig', $wig_id);
        return $redirect;
    }

    public function bulkApproveLm(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        $first = BreakdownLm::with('lm')->whereIn('id', $ids)->first();
        $wig_id = $first ? ($first->lm->wig_id ?? null) : null;
        BreakdownLm::whereIn('id', $ids)->update(['is_approved' => true]);
        $redirect = redirect()->back()->with('success', 'Cascading LM terpilih berhasil disetujui.');
        if ($wig_id) $redirect->with('active_wig', $wig_id);
        return $redirect;
    }

    public function bulkUpdateLm(Request $request)
    {
        $user = Auth::user();
        $isMsbK3L = $user && $user->hasRole('MSB UID') && strtoupper(trim((string)($user->matrix_group_id ?? ''))) === 'K3L';
        $canEditDelete = $user && ($user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Asman Perencanaan UP3']) || $isMsbK3L);
        if (!$canEditDelete) {
            return abort(403, 'Akses Ditolak');
        }

        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        
        $request->validate([
            'angka_target' => 'required|numeric'
        ]);

        BreakdownLm::whereIn('id', $ids)->update(['angka_target' => $request->angka_target]);
        
        $first = BreakdownLm::with('lm')->whereIn('id', $ids)->first();
        $wig_id = $first ? ($first->lm->wig_id ?? null) : null;
        
        $redirect = redirect()->back()->with('success', count($ids) . ' target Cascading LM berhasil diubah masal.');
        if ($wig_id) $redirect->with('active_wig', $wig_id);
        return $redirect;
    }

    public function bulkDestroyWigBreakdown(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        $user = Auth::user();
        $canEditDelete = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);
        if (!$canEditDelete) return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus target.');
        $first = \App\Models\BreakdownWig::whereIn('id', $ids)->first();
        $wig_id = $first ? $first->wig_id : null;
        \App\Models\BreakdownWig::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', count($ids) . ' target berhasil dihapus.')->with('active_wig', $wig_id);
    }

    public function bulkApproveWigBreakdown(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        $first = \App\Models\BreakdownWig::whereIn('id', $ids)->first();
        $wig_id = $first ? $first->wig_id : null;
        \App\Models\BreakdownWig::whereIn('id', $ids)->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Cascading WIG terpilih berhasil disetujui.')->with('active_wig', $wig_id);
    }
}
