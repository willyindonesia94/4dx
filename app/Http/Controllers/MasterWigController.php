<?php
namespace App\Http\Controllers;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use Illuminate\Http\Request;

class MasterWigController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // 'all' or 'draft'
        
        $query = MasterWig::with(['unitPemilik', 'satuan']);
        
        if ($status === 'draft') {
            $query->where('is_approved', false);
        }
        
        $wigs = $query->get();
        $units = MasterUnit::all();
        $satuans = MasterSatuan::all();
        return view('master-wigs.index', compact('wigs', 'status', 'units', 'satuans'));
    }

    public function create()
    {
        $units = MasterUnit::all();
        $satuans = MasterSatuan::all();
        return view('master-wigs.create', compact('units', 'satuans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'unit_pemilik_id' => 'required|exists:master_units,id',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'divisi' => 'required|string|max:255',
            'polaritas' => 'required|in:positif,negatif',
        ]);

        $data = $request->all();
        $data['is_approved'] = true; // Created via UI is approved by default
        MasterWig::create($data);

        return redirect()->route('master-wigs.index')->with('success', 'Master WIG berhasil dibuat.');
    }

    public function update(Request $request, MasterWig $masterWig)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'unit_pemilik_id' => 'required|exists:master_units,id',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'divisi' => 'required|string|max:255',
            'polaritas' => 'required|in:positif,negatif',
        ]);

        $masterWig->update($request->all());

        return redirect()->route('master-wigs.index')->with('success', 'Master WIG berhasil diperbarui.');
    }

    public function destroy(MasterWig $masterWig)
    {
        $masterWig->delete();
        return redirect()->route('master-wigs.index')->with('success', 'Master WIG berhasil dihapus.');
    }

    public function approve($id)
    {
        $wig = MasterWig::findOrFail($id);
        $wig->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'WIG berhasil disetujui.');
    }
}
