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
        
        $query = MasterWig::with(['unitPemilik', 'satuan']);
        
        if ($status === 'draft') {
            $query->where('is_approved', false);
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
        $bidangs = MasterBidang::where('level', 'UID_SUBBIDANG')->orderBy('name', 'asc')->get();
        return view('master-wigs.index', compact('wigs', 'status', 'search', 'units', 'satuans', 'bidangs'));
    }

    public function create()
    {
        $units = MasterUnit::all();
        $satuans = MasterSatuan::all();
        $bidangs = MasterBidang::where('level', 'UID_SUBBIDANG')->orderBy('name', 'asc')->get();
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
        $data['is_approved'] = false; // Created via UI requires approval by MSB
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

        return redirect()->route('master-wigs.index')->with('success', 'Master WIG berhasil dibuat beserta breakdown otomatis untuk UID.');
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
        $user = Auth::user();
        $isSuperAdmin = $user && (in_array(strtolower(trim($user->role_name ?? '')), ['super admin', 'superadmin']) || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Super Admin'])));
        $isMsb = $user && (strtolower(trim($user->role_name ?? '')) === 'sub bidang uid');

        $data['is_approved'] = ($isSuperAdmin || $isMsb) ? true : false;
        $masterWig->update($data);

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
