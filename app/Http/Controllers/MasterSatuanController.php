<?php
namespace App\Http\Controllers;

use App\Models\MasterSatuan;
use Illuminate\Http\Request;

class MasterSatuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = MasterSatuan::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $satuans = $query->get();
        return view('master-satuans.index', compact('satuans', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:master_satuans,name',
        ]);

        MasterSatuan::create($request->all());

        return redirect()->route('master-satuans.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, MasterSatuan $masterSatuan)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:master_satuans,name,' . $masterSatuan->id,
        ]);

        $masterSatuan->update($request->all());

        return redirect()->route('master-satuans.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(MasterSatuan $masterSatuan)
    {
        // Simple protection against deleting used satuan could be added here
        $masterSatuan->delete();
        return redirect()->route('master-satuans.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
