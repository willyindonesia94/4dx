<?php
namespace App\Http\Controllers;

use App\Models\MasterLm;
use App\Models\MasterWig;
use App\Models\MasterSatuan;
use Illuminate\Http\Request;

class LeadMeasureController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status', 'all'); // 'all' or 'draft'
        $search = $request->query('search', '');
        
        // Matrix filtering logic:
        $query = MasterLm::with(['wig', 'satuan']);
        
        if ($status === 'draft') {
            $query->where('is_approved', false);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul_lm', 'like', "%{$search}%")
                  ->orWhereHas('wig', function($w) use ($search) {
                      $w->where('judul', 'like', "%{$search}%")
                        ->orWhere('divisi', 'like', "%{$search}%");
                  });
            });
        }
        
        // If not superadmin and matrix_group_id is not ALL, optionally filter based on matrix_group_id
        if ($user && !$user->hasAnyRole(['Super Admin', 'Perencanaan UID']) && $user->matrix_group_id !== 'ALL') {
            $query->whereHas('wig', function($q) use ($user) {
                // Assuming we show LMs where the user's matrix_group_id matches the WIG's divisi
                $q->where('divisi', $user->matrix_group_id);
            });
        }
        
        $lms = $query->orderBy('wig_id')->orderBy('judul_lm')->get();
        $wigs = MasterWig::all();
        $satuans = MasterSatuan::all();
        return view('master-lms.index', compact('lms', 'status', 'search', 'wigs', 'satuans'));
    }

    public function create()
    {
        $wigs = MasterWig::all();
        $satuans = MasterSatuan::all();
        return view('master-lms.create', compact('wigs', 'satuans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'wig_id' => 'required|exists:master_wigs,id',
            'judul_lm' => 'required|string|max:255',
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
            'satuan_id' => 'required|exists:master_satuans,id',
            'polaritas' => 'required|in:positif,negatif',
        ]);

        $data = $request->all();
        
        $wig = MasterWig::with('unitPemilik')->findOrFail($data['wig_id']);
        $data['tujuan_unit_role'] = $wig->unitPemilik ? $wig->unitPemilik->name : '';

        $data['is_approved'] = true; // Created via UI is approved by default
        $data['angka_target'] = $data['angka_target'] ?? 0;
        MasterLm::create($data);

        return redirect()->route('master-lms.index')->with('success', 'Lead Measure berhasil dibuat.');
    }

    public function edit(MasterLm $masterLm)
    {
        $wigs = MasterWig::all();
        $satuans = MasterSatuan::all();
        return view('master-lms.edit', compact('masterLm', 'wigs', 'satuans'));
    }

    public function update(Request $request, MasterLm $masterLm)
    {
        $request->validate([
            'wig_id' => 'required|exists:master_wigs,id',
            'judul_lm' => 'required|string|max:255',
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
            'satuan_id' => 'required|exists:master_satuans,id',
            'polaritas' => 'required|in:positif,negatif',
        ]);

        $data = $request->all();

        $wig = MasterWig::with('unitPemilik')->findOrFail($data['wig_id']);
        $data['tujuan_unit_role'] = $wig->unitPemilik ? $wig->unitPemilik->name : '';

        $data['angka_target'] = $data['angka_target'] ?? 0;
        $masterLm->update($data);

        return redirect()->route('master-lms.index')->with('success', 'Lead Measure berhasil diperbarui.');
    }

    public function destroy(MasterLm $masterLm)
    {
        $masterLm->delete();
        return redirect()->route('master-lms.index')->with('success', 'Lead Measure berhasil dihapus.');
    }

    public function approve($id)
    {
        $lm = MasterLm::findOrFail($id);
        $lm->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Lead Measure berhasil disetujui.');
    }
}
