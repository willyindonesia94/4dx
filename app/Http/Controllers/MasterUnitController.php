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

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            // Read Excel to array
            $data = Excel::toArray(new MasterUnitImport, $request->file('file'));
            
            if (empty($data) || empty($data[0])) {
                return redirect()->back()->with('error', 'File Excel kosong atau format tidak sesuai.');
            }
            
            $rows = $data[0];
            $previewData = [];
            
            foreach ($rows as $row) {
                $name = trim($row['nama_unit'] ?? '');
                $type = strtoupper(trim($row['tipe_uidup3up2dup2kulp'] ?? ''));
                
                if (empty($name) || empty($type)) {
                    continue;
                }
                
                $parentName = trim($row['nama_induk_unit'] ?? '');
                $parentStatus = 'valid';
                $parentId = null;
                
                if (!empty($parentName)) {
                    $parentUnit = MasterUnit::where('name', $parentName)->first();
                    if ($parentUnit) {
                        $parentId = $parentUnit->id;
                    } else {
                        $parentStatus = 'not_found';
                    }
                }
                
                $previewData[] = [
                    'name' => $name,
                    'type' => $type,
                    'parent_name' => $parentName,
                    'parent_id' => $parentId,
                    'parent_status' => $parentStatus,
                    'latitude' => $row['latitude'] ?? null,
                    'longitude' => $row['longitude'] ?? null,
                ];
            }
            
            // Store preview data in session
            session(['master_unit_import_preview' => $previewData]);
            
            return view('master-units.preview', compact('previewData'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    public function confirmImport(Request $request)
    {
        $previewData = session('master_unit_import_preview');
        
        if (!$previewData) {
            return redirect()->route('master-units.index')->with('error', 'Sesi import telah habis, silakan upload ulang.');
        }
        
        try {
            $count = 0;
            foreach ($previewData as $data) {
                MasterUnit::updateOrCreate(
                    [
                        'name' => $data['name'],
                        'type' => $data['type']
                    ],
                    [
                        'parent_id' => $data['parent_id'],
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                    ]
                );
                $count++;
            }
            
            // Clear session
            session()->forget('master_unit_import_preview');
            
            return redirect()->route('master-units.index')->with('success', $count . ' Data Master Unit berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('master-units.index')->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}
