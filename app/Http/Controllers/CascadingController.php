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
        
        $wigs = MasterWig::where('is_approved', true)
            ->with(['breakdowns.unit', 'breakdowns.satuan'])
            ->get();
        
        $satuans = MasterSatuan::all();
        $uidUnits = MasterUnit::where('type', 'UID')->get();
        $up3Units = MasterUnit::where('type', 'UP3')->get();

        return view('cascading.wig', compact('wigs', 'satuans', 'uidUnits', 'up3Units'));
    }

    public function lmIndex()
    {
        $user = Auth::user();
        
        $wigs = MasterWig::where('is_approved', true)
            ->with(['masterLms' => function($q) {
                $q->where('is_approved', true);
            }, 'masterLms.breakdowns.unit', 'masterLms.breakdowns.satuan'])
            ->get();
        
        $satuans = MasterSatuan::all();
        
        $availableUnits = collect();
        if ($user->hasRole('Super Admin')) {
            $availableUnits = MasterUnit::orderBy('type')->get();
        } elseif ($user->unit) {
            if ($user->unit->type === 'UID') {
                $availableUnits = MasterUnit::where('type', 'UP3')->get();
            } elseif ($user->unit->type === 'UP3') {
                $availableUnits = MasterUnit::where('parent_id', $user->unit->id)->where('type', 'ULP')->get();
            }
        }

        return view('cascading.lm', compact('wigs', 'satuans', 'availableUnits'));
    }

    public function storeBreakdown(Request $request)
    {
        $request->validate([
            'lm_id' => 'required|exists:master_lms,id',
            'unit_id' => 'required|exists:master_units,id',
            'bidang' => 'nullable|string|max:255',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
        ]);

        BreakdownLm::create($request->all());

        return redirect()->back()->with('success', 'Breakdown LM berhasil ditambahkan ke Unit.');
    }

    public function updateBreakdown(Request $request, $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:master_units,id',
            'bidang' => 'nullable|string|max:255',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
        ]);

        $breakdown = BreakdownLm::findOrFail($id);
        $breakdown->update($request->all());

        return redirect()->back()->with('success', 'Breakdown LM berhasil diperbarui.');
    }

    public function destroyBreakdown($id)
    {
        $breakdown = BreakdownLm::findOrFail($id);
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
}
