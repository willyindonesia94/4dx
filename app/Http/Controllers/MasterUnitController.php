<?php
namespace App\Http\Controllers;

use App\Models\MasterUnit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MasterUnitImport;
use App\Exports\MasterUnitTemplateExport;
use App\Exports\MasterUnitExport;

class MasterUnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        $query = MasterUnit::with('parent');
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
        }
        
        $units = $query->get();
        $parentUnits = MasterUnit::whereIn('type', ['UID', 'UP3'])->get();
        return view('master-units.index', compact('units', 'parentUnits', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:UID,UP3,UP2D,UP2K,ULP',
            'parent_id' => 'nullable|exists:master_units,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        MasterUnit::create($request->all());

        return redirect()->route('master-units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function update(Request $request, MasterUnit $masterUnit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:UID,UP3,UP2D,UP2K,ULP',
            'parent_id' => 'nullable|exists:master_units,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        $masterUnit->update($request->all());

        return redirect()->route('master-units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(MasterUnit $masterUnit)
    {
        $masterUnit->delete();
        return redirect()->route('master-units.index')->with('success', 'Unit berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new MasterUnitTemplateExport, 'Template_Master_Unit.xlsx');
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'UID');
        if (!in_array($type, ['UID', 'UP3', 'UP2D', 'UP2K', 'ULP'])) {
            $type = 'UID';
        }
        return Excel::download(new MasterUnitExport($type), 'Data_Master_Unit_' . $type . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new MasterUnitImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Master Unit berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
