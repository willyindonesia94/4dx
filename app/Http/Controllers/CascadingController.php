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
        
        $skipMatrixFilter = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'SRM Perencanaan UID', 'Manager UP3', 'Manager ULP', 'General Manager UID']);
        // MSB UID dengan matrix_group_id = 'ALL' juga skip filter (bisa lihat semua WIG)
        $skipMatrixFilter = $skipMatrixFilter || ($isMsb && strtoupper($userMatrixGroup) === 'ALL');
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
        $user = Auth::user();
        $unitType = $user->unit ? strtoupper(trim((string)$user->unit->type)) : '';
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isPerencanaanUid = $user && $user->hasRole('Perencanaan UID');
        $isMsb = $user && $user->hasRole('MSB UID');
        $isAsmanPerencanaanUp3 = $user && $user->hasRole('Asman Perencanaan UP3');
        $isAsmanBidangUp3 = $user && $user->hasRole('Asman Bidang UP3');
        
        $isUid = !$isSuperAdmin && ($unitType === 'UID' || $user->hasAnyRole(['Perencanaan UID', 'SRM Perencanaan UID', 'SRM Bidang UID', 'MSB UID', 'Admin Sub Bidang UID', 'General Manager UID']));
        $isUp3 = !$isSuperAdmin && (in_array($unitType, ['UP3', 'UP2D', 'UP2K']) || $user->hasAnyRole(['Asman Perencanaan UP3', 'Asman Bidang UP3', 'Manager UP3', 'UP2D', 'UP2K']));
        
        $canApproveLm = $isSuperAdmin || $isMsb || $isAsmanBidangUp3;
        
        $canBreakdownToUid = $isSuperAdmin || $isPerencanaanUid;
        $canBreakdownToUp3 = $isSuperAdmin || $isPerencanaanUid;
        $canBreakdownToUlp = $isSuperAdmin || $isAsmanPerencanaanUp3;
        
        $skipMatrixFilter = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'SRM Perencanaan UID', 'Asman Perencanaan UP3', 'Manager UP3', 'Manager ULP', 'General Manager UID']);
        // MSB UID dengan matrix_group_id = 'ALL' juga skip filter (bisa lihat semua LM)
        $skipMatrixFilter = $skipMatrixFilter || ($isMsb && strtoupper($userMatrixGroup) === 'ALL');
        
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['masterLms' => function($q) {
                $q->where('is_approved', true);
            }, 'masterLms.breakdowns' => function($q) {
                $q->join('master_units', 'breakdown_lms.unit_id', '=', 'master_units.id')
                  ->orderByRaw("CASE WHEN UPPER(TRIM(master_units.type)) IN ('UP2D', 'UP2K') THEN 2 ELSE 1 END")
                  ->orderBy('master_units.name', 'asc')
                  ->select('breakdown_lms.*');
            }, 'masterLms.breakdowns.unit', 'masterLms.breakdowns.satuan', 'masterLms.satuan']);

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
        
        $satuans = MasterSatuan::all();
        
        $availableUnits = collect();
        if ($isSuperAdmin || $isPerencanaanUid) {
            $availableUnits = MasterUnit::orderBy('type')->get();
        } elseif ($isAsmanPerencanaanUp3) {
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
            'isSuperAdmin', 'isUid', 'isUp3', 'user', 'canApproveLm'
        ));
    }

    private function checkUp3Permission($targetUnitId = null, $existingBreakdown = null)
    {
        $user = Auth::user();
        if (!$user) return false;
        $isSuperAdmin = $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);
        $isUp3 = !$isSuperAdmin && $user->hasAnyRole(['Asman Perencanaan UP3', 'Asman Bidang UP3', 'Manager UP3', 'UP2D', 'UP2K']);

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

            $approvers = \App\Models\User::role(['MSB UID'])
                ->get()
                ->filter(function($u) use ($wigDivisis) {
                    $matrixGroup = trim((string)($u->matrix_group_id ?? ''));
                    if (empty($matrixGroup) || strtoupper($matrixGroup) === 'ALL') return true;
                    $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($matrixGroup);
                    return !empty(array_intersect($wigDivisis, $allowedDivisis));
                });

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
            $approvers = \App\Models\User::role(['Super Admin', 'MSB UID', 'Asman Bidang UP3', 'UP2K', 'UP2D'])->get();
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
            $approvers = \App\Models\User::role(['Super Admin', 'MSB UID', 'Asman Bidang UP3', 'UP2K', 'UP2D'])->get();
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
        $canEditDelete = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Asman Perencanaan UP3']);
        
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
            $approvers = \App\Models\User::role(['Super Admin', 'MSB UID'])->get();
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
            $approvers = \App\Models\User::role(['Super Admin', 'MSB UID'])->get();
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
            return redirect()->back()->with("success", "Breakdown Target LM berhasil di-upload secara massal.");
        } catch (\Exception $e) {
            return redirect()->back()->with("error", "Terjadi kesalahan saat upload data: " . $e->getMessage());
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
