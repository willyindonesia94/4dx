<?php
namespace App\Http\Controllers;

use App\Models\MasterWig;
use App\Models\MasterUnit;
use App\Models\MasterSatuan;
use App\Models\MasterBidang;
use App\Models\User;
use App\Notifications\RequiresApprovalNotification;
use Illuminate\Http\Request;

class MasterWigController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // 'all' or 'draft'
        $search = $request->query('search', '');
        
        $query = MasterWig::with(['unitPemilik', 'satuan', 'deleteRequestedBy']);
        
        if ($status === 'draft') {
            $query->where('is_approved', false);
        } elseif ($status === 'pending_delete') {
            $query->where('is_delete_pending', true);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('divisi', 'like', "%{$search}%");
            });
        }
        
        $wigs = $query->get();
        $units = MasterUnit::all();
        $satuans = MasterSatuan::all();
        $bidangs = MasterBidang::whereIn('level', ['UID_SUBBIDANG', 'UID_BIDANG'])->orderBy('name', 'asc')->get();
        return view('master-wigs.index', compact('wigs', 'status', 'search', 'units', 'satuans', 'bidangs'));
    }

    public function create()
    {
        $units = MasterUnit::all();
        $satuans = MasterSatuan::all();
        $bidangs = MasterBidang::whereIn('level', ['UID_SUBBIDANG', 'UID_BIDANG'])->orderBy('name', 'asc')->get();
        return view('master-wigs.create', compact('units', 'satuans', 'bidangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'unit_pemilik_id' => 'required|exists:master_units,id',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'divisi' => 'required|array',
            'divisi.*' => 'string|max:255',
            'polaritas' => 'required|in:positif,negatif',
        ]);

        $data = $request->all();
        $data['is_approved'] = true; // Langsung aktif tanpa persetujuan
        $data['is_delete_pending'] = false;
        $wig = MasterWig::create($data);

        // Auto-create cascading to UID Jabar
        $uidJabar = MasterUnit::where('type', 'UID')->first();
        if ($uidJabar) {
            $targetTahunan = $data['angka_target'];
            $targetBulanan = round($targetTahunan / 12, 2);
            
            \App\Models\BreakdownWig::create([
                'wig_id' => $wig->id,
                'unit_id' => $uidJabar->id,
                'satuan_id' => $data['satuan_id'],
                'tahun' => date('Y'),
                'target_tahunan' => $targetTahunan,
                'target_jan' => $targetBulanan,
                'target_feb' => $targetBulanan,
                'target_mar' => $targetBulanan,
                'target_apr' => $targetBulanan,
                'target_mei' => $targetBulanan,
                'target_jun' => $targetBulanan,
                'target_jul' => $targetBulanan,
                'target_agu' => $targetBulanan,
                'target_sep' => $targetBulanan,
                'target_okt' => $targetBulanan,
                'target_nov' => $targetBulanan,
                'target_des' => $targetBulanan,
            ]);
        }

        return redirect()->route('master-wigs.index')->with('success', 'Master WIG berhasil dibuat langsung tanpa persetujuan beserta breakdown otomatis untuk UID.');
    }

    public function update(Request $request, MasterWig $masterWig)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'unit_pemilik_id' => 'required|exists:master_units,id',
            'angka_target' => 'required|numeric',
            'satuan_id' => 'required|exists:master_satuans,id',
            'divisi' => 'required|array',
            'divisi.*' => 'string|max:255',
            'polaritas' => 'required|in:positif,negatif',
        ]);

        $data = $request->all();
        $data['is_approved'] = true; // Langsung aktif tanpa persetujuan
        $masterWig->update($data);

        return redirect()->route('master-wigs.index')->with('success', 'Master WIG berhasil diperbarui langsung tanpa persetujuan.');
    }

    public function requestDelete(Request $request, $id)
    {
        $wig = MasterWig::findOrFail($id);
        $wig->update([
            'is_delete_pending' => true,
            'delete_requested_by' => auth()->id(),
            'delete_reason' => $request->input('reason', 'Permintaan penghapusan Master WIG')
        ]);

        return redirect()->route('master-wigs.index')->with('success', 'Permintaan penghapusan Master WIG berhasil diajukan dan menunggu persetujuan (dapat disetujui sendiri).');
    }

    public function cancelDelete($id)
    {
        $wig = MasterWig::findOrFail($id);
        $wig->update([
            'is_delete_pending' => false,
            'delete_requested_by' => null,
            'delete_reason' => null
        ]);

        return redirect()->route('master-wigs.index')->with('success', 'Permintaan penghapusan Master WIG berhasil dibatalkan.');
    }

    public function destroy(MasterWig $masterWig)
    {
        $masterWig->delete();

        return redirect()->route('master-wigs.index')->with('success', 'Persetujuan penghapusan berhasil. Master WIG telah dihapus.');
    }

    public function approve($id)
    {
        $wig = MasterWig::findOrFail($id);
        $wig->update(['is_approved' => true, 'is_delete_pending' => false]);
        return redirect()->back()->with('success', 'WIG berhasil disetujui.');
    }
}
