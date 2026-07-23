<?php
namespace App\Http\Controllers;

use App\Models\MasterUnit;
use Illuminate\Http\Request;

class MasterUnitController extends Controller
{
    public function index()
    {
        $units = MasterUnit::with('parent')->get();
        $parentUnits = MasterUnit::whereIn('type', ['UID', 'UP3'])->get();
        return view('master-units.index', compact('units', 'parentUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:UID,UP3,ULP',
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
            'type' => 'required|in:UID,UP3,ULP',
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
}
