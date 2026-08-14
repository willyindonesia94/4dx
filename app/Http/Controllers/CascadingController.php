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
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin', 'perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Perencanaan UID'])));
        
        $wigsQuery = MasterWig::where('is_approved', true)
            ->with(['breakdowns.unit', 'breakdowns.satuan']);
            
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->whereIn('divisi', $allowedDivisis);
        }
        
        $wigs = $wigsQuery->get()->each(function ($wig) {
            $wig->setRelation('breakdowns', $wig->breakdowns->sortBy(function ($bd) {
                return $bd->unit ? strtolower($bd->unit->name) : '';
            })->values());
        });

        $satuans = MasterSatuan::all();
        $uidUnits = MasterUnit::where('type', 'UID')->get();
        $up3Units = MasterUnit::where('type', 'UP3')->get();

        return view('cascading.wig', compact('wigs', 'satuans', 'uidUnits', 'up3Units'));
    }

    public function lmIndex()
    {
        $user = Auth::user();
        $userRole = strtolower(trim($user->role_name ?? ''));
        $unitType = $user->unit ? strtoupper(trim((string)$user->unit->type)) : '';
        $isSuperAdmin = $user && (in_array($userRole, ['super admin', 'superadmin', 'perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Perencanaan UID'])));
        
        $isUid = !$isSuperAdmin && ($unitType === 'UID' || str_contains($userRole, 'uid') || (str_contains($userRole, 'bidang') && !str_contains($userRole, 'up3')));
        $isUp3 = !$isSuperAdmin && ($unitType === 'UP3' || str_contains($userRole, 'up3'));
        
        $canBreakdownToUid = $isSuperAdmin || str_contains($userRole, 'perencanaan uid');
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
            $wigsQuery->whereIn('divisi', $allowedDivisis);
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
            // Level Bidang UID breakdown ke Level UP3
            $availableUnits = MasterUnit::where('type', 'UP3')->orderBy('name')->get();
        } elseif ($isUp3) {
            // Level UP3 breakdown ke ULP di bawah jangkauan UP3-nya
            if ($user->unit_id) {
                $availableUnits = MasterUnit::where('type', 'ULP')->where('parent_id', $user->unit_id)->orderBy('name')->get();
            } else {
                $availableUnits = MasterUnit::where('type', 'ULP')->orderBy('name')->get();
            }
        }

        return view('cascading.lm', compact(
            'wigs', 'satuans', 'availableUnits', 
            'canBreakdownToUid', 'canBreakdownToUp3', 'canBreakdownToUlp',
            'isSuperAdmin', 'isUid', 'isUp3', 'user'
        ));
    }

    private function checkUp3Permission($targetUnitId = null, $existingBreakdown = null)
    {
        $user = Auth::user();
        $userRole = strtolower(trim($user->role_name ?? ''));
        $unitType = $user->unit ? strtoupper(trim((string)$user->unit->type)) : '';
        $isSuperAdmin = $user && (in_array($userRole, ['super admin', 'superadmin', 'perencanaan uid']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin', 'Perencanaan UID'])));
        $isUp3 = !$isSuperAdmin && ($unitType === 'UP3' || str_contains($userRole, 'up3'));

        if ($isUp3 && $user->unit_id) {
            if ($targetUnitId) {
                $unit = MasterUnit::find($targetUnitId);
                if (!$unit || strtoupper(trim($unit->type)) !== 'ULP' || (int)$unit->parent_id !== (int)$user->unit_id) {
                    return false;
                }
            }
            if ($existingBreakdown && $existingBreakdown->unit) {
                $unit = $existingBreakdown->unit;
                if (strtoupper(trim($unit->type)) !== 'ULP' || (int)$unit->parent_id !== (int)$user->unit_id) {
                    return false;
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
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Breakdown LM berhasil ditambahkan ke Unit sesuai kalender Master Periode.');
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
        $breakdown->save();

        return redirect()->back()->with('success', 'Breakdown LM berhasil diperbarui.');
    }

    public function destroyBreakdown($id)
    {
        $breakdown = BreakdownLm::with('unit')->findOrFail($id);
        if (!$this->checkUp3Permission(null, $breakdown)) {
            return redirect()->back()->with('error', 'Akses ditolak. UP3 hanya berwenang menghapus target untuk unit ULP di bawah naungannya.');
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

        \App\Models\BreakdownWig::create($request->all());

        return redirect()->back()->with('success', 'Breakdown WIG berhasil ditambahkan ke UID.');
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
        $breakdown->update($request->all());

        return redirect()->back()->with('success', 'Breakdown WIG berhasil diperbarui.');
    }

    public function destroyWigBreakdown($id)
    {
        $breakdown = \App\Models\BreakdownWig::findOrFail($id);
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
}
