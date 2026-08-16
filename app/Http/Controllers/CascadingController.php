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
        $userRole = strtolower(trim($user->role_name ?? ''));
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && (in_array($userRole, ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isPerencanaanUid = $user && (in_array($userRole, ['perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Perencanaan UID'])));
        $isMsb = $userRole === 'sub bidang uid';
        
        $canApproveWig = $isSuperAdmin || $isMsb;
        
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['breakdowns', 'breakdowns.unit', 'breakdowns.satuan']);
            
        if (!$isSuperAdmin && !$isPerencanaanUid && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        
        $wigs = $wigsQuery->get()->each(function ($wig) {
            $wig->setRelation('breakdowns', $wig->breakdowns->sortBy(function ($bd) {
                return $bd->unit ? strtolower($bd->unit->name) : '';
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
        $userRole = strtolower(trim($user->role_name ?? ''));
        $unitType = $user->unit ? strtoupper(trim((string)$user->unit->type)) : '';
        $isSuperAdmin = $user && (in_array($userRole, ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isPerencanaanUid = $user && (in_array($userRole, ['perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Perencanaan UID'])));
        
        $isUid = !$isSuperAdmin && ($unitType === 'UID' || str_contains($userRole, 'uid') || (str_contains($userRole, 'bidang') && !str_contains($userRole, 'up3') && !str_contains($userRole, 'up2')));
        $isUp3 = !$isSuperAdmin && (in_array($unitType, ['UP3', 'UP2D', 'UP2K']) || str_contains($userRole, 'up3') || str_contains($userRole, 'up2d') || str_contains($userRole, 'up2k'));
        $isMsb = $userRole === 'sub bidang uid';
        $isManagerUp3 = in_array($userRole, ['manager up3', 'up2k', 'up2d']);
        
        $canApproveLm = $isSuperAdmin || $isMsb || $isManagerUp3;
        
        $canBreakdownToUid = $isSuperAdmin || $isPerencanaanUid || str_contains($userRole, 'perencanaan uid');
        $canBreakdownToUp3 = $isSuperAdmin || $isUid;
        $canBreakdownToUlp = $isSuperAdmin || $isUp3;
        
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['masterLms' => function($q) {
                $q->where('is_approved', true);
            }, 'masterLms.breakdowns' => function($q) {
                $q->join('master_units', 'breakdown_lms.unit_id', '=', 'master_units.id')
                  ->orderBy('master_units.name', 'asc')
                  ->select('breakdown_lms.*');
            }, 'masterLms.breakdowns.unit', 'masterLms.breakdowns.satuan']);

        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
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
        if ($isSuperAdmin) {
            $availableUnits = MasterUnit::orderBy('type')->get();
        } elseif ($isUid) {
            // Level Bidang UID breakdown ke Level UP3/UP2D/UP2K
            $availableUnits = MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->orderBy('name')->get();
        } elseif ($isUp3) {
            // Level UP3 breakdown ke ULP, atau UP2D/UP2K ke dirinya sendiri
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
        $userRole = strtolower(trim($user->role_name ?? ''));
        $unitType = $user->unit ? strtoupper(trim((string)$user->unit->type)) : '';
        $isSuperAdmin = $user && (in_array($userRole, ['super admin', 'superadmin', 'perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Perencanaan UID'])));
        $isUp3 = !$isSuperAdmin && (in_array($unitType, ['UP3', 'UP2D', 'UP2K']) || str_contains($userRole, 'up3') || str_contains($userRole, 'up2d') || str_contains($userRole, 'up2k'));

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
            return redirect()->back()->with('error', 'Akses ditolak. UP3 hanya berwenang menurunkan target (breakdown) ke unit ULP di bawah naungannya.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');
        $isManagerUp3 = $user && in_array(strtolower(trim($user->role_name ?? '')), ['manager up3', 'up2k', 'up2d']);

        $is_approved = ($isSuperAdmin || $isMsb || $isManagerUp3) ? true : false;

        $carbonStart = \Carbon\Carbon::create($request->tahun, $request->bulan, 1);
        $carbonEnd = $carbonStart->copy()->endOfMonth();

        $weeks = \App\Models\MasterPeriode::getWeekDates($request->tahun, $request->bulan);

        // Create main (monthly) target
        $data = $request->except(['bulan', 'tahun', 'target_m1', 'target_m2', 'target_m3', 'target_m4', 'target_m5']);
        $data['bulan'] = $request->bulan;
        $data['tahun'] = $request->tahun;
        $data['periode_start'] = $weeks['target_m1']['start'] ?? $carbonStart->format('Y-m-d');
        $endWeek = isset($weeks['target_m5']) && $weeks['target_m5'] ? 'target_m5' : 'target_m4';
        $data['periode_end'] = $weeks[$endWeek]['end'] ?? $carbonEnd->format('Y-m-d');
        $data['is_approved'] = $is_approved;
        BreakdownLm::create($data);
        
        // Handle weekly targets if provided
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

        return redirect()->back()->with('success', 'Breakdown LM berhasil ditambahkan ke Unit. ' . (!$is_approved ? 'Menunggu persetujuan.' : ''));
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
            return redirect()->back()->with('error', 'Akses ditolak. UP3 hanya berwenang mengubah target untuk unit ULP di bawah naungannya.');
        }

        // Apply date changes only if it is a monthly target (safeguard weekly date ranges)
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
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');
        $isManagerUp3 = $user && in_array(strtolower(trim($user->role_name ?? '')), ['manager up3', 'up2k', 'up2d']);

        // Do not require re-approval on edit, just notify
        $breakdown->save();

        if (!$isSuperAdmin && !$isMsb && !$isManagerUp3) {
            $approvers = \App\Models\User::whereIn('role_name', ['Super Admin', 'Sub Bidang UID', 'Manager UP3', 'UP2K', 'UP2D'])->get();
            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Perubahan Cascading LM', 
                'Cascading LM Diubah', 
                "Data Cascading LM unit " . ($breakdown->unit->name ?? '-') . " telah diubah oleh " . $user->name . " (Target lama: " . $oldTarget . " -> " . $breakdown->angka_target . ")"
            ));
        }

        return redirect()->back()->with('success', 'Breakdown LM berhasil diperbarui.');
    }

    public function destroyBreakdown($id)
    {
        $breakdown = BreakdownLm::with('unit')->findOrFail($id);
        if (!$this->checkUp3Permission(null, $breakdown)) {
            return redirect()->back()->with('error', 'Akses ditolak. UP3 hanya berwenang menghapus target untuk unit ULP di bawah naungannya.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');
        $isManagerUp3 = $user && in_array(strtolower(trim($user->role_name ?? '')), ['manager up3', 'up2k', 'up2d']);

        if (!$isSuperAdmin && !$isMsb && !$isManagerUp3) {
            $approvers = \App\Models\User::whereIn('role_name', ['Super Admin', 'Sub Bidang UID', 'Manager UP3', 'UP2K', 'UP2D'])->get();
            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Penghapusan Cascading LM', 
                'Cascading LM Dihapus', 
                "Data Cascading LM unit " . ($breakdown->unit->name ?? '-') . " telah dihapus oleh " . $user->name . " (Target lama: " . $breakdown->angka_target . ")"
            ));
        }

        $breakdown->delete();

        return redirect()->back()->with('success', 'Breakdown LM berhasil dihapus.');
    }

    public function storeWigBreakdown(Request $request)
    {
        $request->validate([
            'wig_id' => 'required|exists:master_wigs,id',
            'unit_id' => 'required|exists:master_units,id',
            'satuan_id' => 'required|exists:master_satuans,id',
            'tahun' => 'required|integer',
            'target_tahunan' => 'required|numeric',
            'target_jan' => 'nullable|numeric',
            'target_feb' => 'nullable|numeric',
            'target_mar' => 'nullable|numeric',
            'target_apr' => 'nullable|numeric',
            'target_mei' => 'nullable|numeric',
            'target_jun' => 'nullable|numeric',
            'target_jul' => 'nullable|numeric',
            'target_agu' => 'nullable|numeric',
            'target_sep' => 'nullable|numeric',
            'target_okt' => 'nullable|numeric',
            'target_nov' => 'nullable|numeric',
            'target_des' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');

        $is_approved = ($isSuperAdmin || $isMsb) ? true : false;

        $data = $request->all();
        $data['is_approved'] = $is_approved;

        \App\Models\BreakdownWig::create($data);

        return redirect()->back()->with('success', 'Breakdown WIG berhasil ditambahkan ke UID. ' . (!$is_approved ? 'Menunggu persetujuan.' : ''));
    }

    public function updateWigBreakdown(Request $request, $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:master_units,id',
            'satuan_id' => 'required|exists:master_satuans,id',
            'tahun' => 'required|integer',
            'target_tahunan' => 'required|numeric',
            'target_jan' => 'nullable|numeric',
            'target_feb' => 'nullable|numeric',
            'target_mar' => 'nullable|numeric',
            'target_apr' => 'nullable|numeric',
            'target_mei' => 'nullable|numeric',
            'target_jun' => 'nullable|numeric',
            'target_jul' => 'nullable|numeric',
            'target_agu' => 'nullable|numeric',
            'target_sep' => 'nullable|numeric',
            'target_okt' => 'nullable|numeric',
            'target_nov' => 'nullable|numeric',
            'target_des' => 'nullable|numeric',
        ]);

        $breakdown = \App\Models\BreakdownWig::findOrFail($id);
        
        $oldTarget = $breakdown->target_tahunan;

        $breakdown->unit_id = $request->unit_id;
        $breakdown->satuan_id = $request->satuan_id;
        $breakdown->tahun = $request->tahun;
        $breakdown->target_tahunan = $request->target_tahunan;
        $breakdown->target_jan = $request->target_jan;
        $breakdown->target_feb = $request->target_feb;
        $breakdown->target_mar = $request->target_mar;
        $breakdown->target_apr = $request->target_apr;
        $breakdown->target_mei = $request->target_mei;
        $breakdown->target_jun = $request->target_jun;
        $breakdown->target_jul = $request->target_jul;
        $breakdown->target_agu = $request->target_agu;
        $breakdown->target_sep = $request->target_sep;
        $breakdown->target_okt = $request->target_okt;
        $breakdown->target_nov = $request->target_nov;
        $breakdown->target_des = $request->target_des;

        $user = Auth::user();
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');

        // Do not require re-approval on edit, just notify
        $breakdown->save();

        if (!$isSuperAdmin && !$isMsb) {
            $approvers = \App\Models\User::whereIn('role_name', ['Super Admin', 'Sub Bidang UID'])->get();
            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Perubahan Cascading WIG', 
                'Cascading WIG Diubah', 
                "Data Cascading WIG unit " . ($breakdown->unit->name ?? '-') . " telah diubah oleh " . $user->name . " (Target lama: " . $oldTarget . " -> " . $breakdown->target_tahunan . ")"
            ));
        }

        return redirect()->back()->with('success', 'Breakdown WIG berhasil diperbarui.');
    }

    public function destroyWigBreakdown($id)
    {
        $breakdown = \App\Models\BreakdownWig::with('unit')->findOrFail($id);
        
        $user = Auth::user();
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');

        if (!$isSuperAdmin && !$isMsb) {
            $approvers = \App\Models\User::whereIn('role_name', ['Super Admin', 'Sub Bidang UID'])->get();
            \Illuminate\Support\Facades\Notification::send($approvers, new \App\Notifications\RequiresApprovalNotification(
                'Penghapusan Cascading WIG', 
                'Cascading WIG Dihapus', 
                "Data Cascading WIG unit " . ($breakdown->unit->name ?? '-') . " telah dihapus oleh " . $user->name . " (Target lama: " . $breakdown->target_tahunan . ")"
            ));
        }

        $breakdown->delete();

        return redirect()->back()->with('success', 'Breakdown WIG berhasil dihapus.');
    }

    public function wigTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WigMassTemplateExport, 'Template_Mass_Upload_WIG.xlsx');
    }

    public function wigImport(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        try {
            \Illuminate\Support\Facades\Log::info("Starting WIG Import...");
            $request->file('file_excel')->storeAs('logs', 'uploaded_wig.xlsx', 'local');
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\WigMassImport, $request->file('file_excel'));
            return redirect()->back()->with('success', 'WIG berhasil diimport secara massal!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function lmTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LmMassTemplateExport, 'Template_Mass_Upload_LM.xlsx');
    }

    public function lmImport(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\LmMassImport, $request->file('file_excel'));
            return redirect()->back()->with('success', 'LM berhasil diimport secara massal!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function breakdownLmTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BreakdownLmTemplateExport, 'Template_Upload_Target_Unit.xlsx');
    }

    public function importBreakdownLm(\Illuminate\Http\Request $request)
    {
        $request->validate([
            "file_excel" => "required|mimes:xlsx,xls",
            "bulan" => "required|integer|min:1|max:12",
            "tahun" => "required|integer|min:2020",
        ]);

        try {
            \Illuminate\Support\Facades\Log::info("Starting LM Breakdown Import...");
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

        // Delete from notifications table dynamically logic? Handled via dynamic UI.
        return redirect()->back()->with('success', 'Cascading WIG berhasil disetujui.');
    }

    public function approveLmBreakdown($id)
    {
        $breakdown = BreakdownLm::findOrFail($id);
        $breakdown->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Cascading LM berhasil disetujui.');
    }


}
