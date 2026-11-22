<?php
namespace App\Http\Controllers;

use App\Models\MasterLm;
use App\Models\MasterWig;
use App\Models\MasterSatuan;
use App\Models\User;
use App\Notifications\RequiresApprovalNotification;
use Illuminate\Http\Request;

class LeadMeasureController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status', 'all'); // 'all' or 'draft'
        $search = $request->query('search', '');
        
        // Matrix filtering logic:
        $query = MasterLm::with(['wig', 'satuan', 'deleteRequestedBy']);
        
        if ($status === 'draft') {
            $query->where('is_approved', false);
        } elseif ($status === 'pending_delete') {
            $query->where('is_delete_pending', true);
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
                $q->whereJsonContains('divisi', $user->matrix_group_id);
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
            'periode_start' => 'nullable|date',
            'periode_end' => 'nullable|date',
            'satuan_id' => 'required|exists:master_satuans,id',
            'polaritas' => 'required|in:positif,negatif',
            'bulan_mulai' => 'nullable|integer|min:1|max:12',
            'is_active' => 'nullable',
            'bulan_nonaktif' => 'nullable|integer|min:1|max:12',
        ]);

        $data = $request->all();
        
        $wig = MasterWig::with('unitPemilik')->findOrFail($data['wig_id']);
        $data['tujuan_unit_role'] = $wig->unitPemilik ? $wig->unitPemilik->name : '';

        $tahun = (int) date('Y');
        $bulanMulai = $request->filled('bulan_mulai') ? (int)$request->bulan_mulai : 1;
        $data['bulan_mulai'] = $bulanMulai;
        $data['periode_start'] = $request->input('periode_start') ?: sprintf('%04d-%02d-01', $tahun, $bulanMulai);
        $data['periode_end'] = $request->input('periode_end') ?: sprintf('%04d-12-31', $tahun);

        $isActive = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : true;
        $data['is_active'] = $isActive;
        $data['bulan_nonaktif'] = !$isActive ? ($request->filled('bulan_nonaktif') ? (int)$request->bulan_nonaktif : (int)date('n')) : null;

        $data['is_approved'] = true; // Langsung aktif tanpa persetujuan
        $data['is_delete_pending'] = false;
        $data['angka_target'] = $data['angka_target'] ?? 0;
        $lm = MasterLm::create($data);

        return redirect()->route('master-lms.index')->with('success', 'Lead Measure berhasil dibuat langsung tanpa persetujuan.');
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
            'periode_start' => 'nullable|date',
            'periode_end' => 'nullable|date',
            'satuan_id' => 'required|exists:master_satuans,id',
            'polaritas' => 'required|in:positif,negatif',
            'bulan_mulai' => 'nullable|integer|min:1|max:12',
            'is_active' => 'nullable',
            'bulan_nonaktif' => 'nullable|integer|min:1|max:12',
        ]);

        $data = $request->all();

        $wig = MasterWig::with('unitPemilik')->findOrFail($data['wig_id']);
        $data['tujuan_unit_role'] = $wig->unitPemilik ? $wig->unitPemilik->name : '';

        $tahun = (int) date('Y');
        $bulanMulai = $request->filled('bulan_mulai') ? (int)$request->bulan_mulai : ($masterLm->bulan_mulai ?? 1);
        $data['bulan_mulai'] = $bulanMulai;
        $data['periode_start'] = $request->input('periode_start') ?: sprintf('%04d-%02d-01', $tahun, $bulanMulai);
        $data['periode_end'] = $request->input('periode_end') ?: sprintf('%04d-12-31', $tahun);

        $isActive = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : (bool)$masterLm->is_active;
        $data['is_active'] = $isActive;
        $data['bulan_nonaktif'] = !$isActive ? ($request->filled('bulan_nonaktif') ? (int)$request->bulan_nonaktif : ($masterLm->bulan_nonaktif ?? (int)date('n'))) : null;

        $data['angka_target'] = $data['angka_target'] ?? 0;
        $data['is_approved'] = true; // Langsung aktif tanpa persetujuan
        
        $masterLm->update($data);

        return redirect()->route('master-lms.index')->with('success', 'Lead Measure berhasil diperbarui langsung tanpa persetujuan.');
    }

    public function toggleActive(Request $request, $id)
    {
        $lm = MasterLm::findOrFail($id);

        $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        $bulanNonaktif = $request->filled('bulan_nonaktif') ? (int)$request->input('bulan_nonaktif') : null;
        $bulanMulai = $request->filled('bulan_mulai') ? (int)$request->input('bulan_mulai') : ($lm->bulan_mulai ?? 1);

        if (!$isActive && !$bulanNonaktif) {
            $bulanNonaktif = (int)date('n');
        }

        $lm->update([
            'is_active' => $isActive,
            'bulan_nonaktif' => !$isActive ? $bulanNonaktif : null,
            'bulan_mulai' => $bulanMulai,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $lm->is_active,
                'bulan_mulai' => $lm->bulan_mulai,
                'bulan_nonaktif' => $lm->bulan_nonaktif,
                'message' => 'Status aktif Lead Measure berhasil diperbarui.'
            ]);
        }

        return redirect()->back()->with('success', 'Status aktif Lead Measure berhasil diperbarui.');
    }

    public function requestDelete(Request $request, $id)
    {
        $lm = MasterLm::findOrFail($id);
        $lm->update([
            'is_delete_pending' => true,
            'delete_requested_by' => auth()->id(),
            'delete_reason' => $request->input('reason', 'Permintaan penghapusan Master LM')
        ]);

        return redirect()->route('master-lms.index')->with('success', 'Permintaan penghapusan Lead Measure berhasil diajukan dan menunggu persetujuan (dapat disetujui sendiri).');
    }

    public function cancelDelete($id)
    {
        $lm = MasterLm::findOrFail($id);
        $lm->update([
            'is_delete_pending' => false,
            'delete_requested_by' => null,
            'delete_reason' => null
        ]);

        return redirect()->route('master-lms.index')->with('success', 'Permintaan penghapusan Lead Measure berhasil dibatalkan.');
    }

    public function destroy(MasterLm $masterLm)
    {
        $masterLm->delete();

        return redirect()->route('master-lms.index')->with('success', 'Persetujuan penghapusan berhasil. Master LM telah dihapus.');
    }

    public function approve($id)
    {
        $lm = MasterLm::findOrFail($id);
        $lm->update(['is_approved' => true, 'is_delete_pending' => false]);
        return redirect()->back()->with('success', 'Lead Measure berhasil disetujui.');
    }
}
