<?php
namespace App\Http\Controllers;

use App\Models\Realisasi;
use App\Models\MasterLm;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RealizationController extends Controller
{
    public function index()
    {
        $realisasis = Realisasi::with(['lm.wig', 'user'])->latest('tanggal_input')->get();
        $wigs = \App\Models\MasterWig::with('masterLms.satuan')->get();
        return view('realisasis.index', compact('realisasis', 'wigs'));
    }

    public function create()
    {
        $lms = MasterLm::all(); // Optionally filter by user's matrix_group_id
        return view('realisasis.create', compact('lms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lm_id' => 'required|exists:master_lms,id',
            'angka_realisasi' => 'required|numeric',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'keterangan_tambahan' => 'nullable|string|max:1000',
        ]);

        $data = $request->except('bukti_file');
        $data['user_id'] = auth()->id();
        $data['unit_id'] = auth()->user()->unit_id;
        $data['tanggal_input'] = now();

        if ($request->hasFile('bukti_file')) {
            $data['bukti_file'] = $request->file('bukti_file')->store('bukti_realisasi', 'public');
        }

        Realisasi::create($data);

        return redirect()->route('realisasis.index')->with('success', 'Realisasi berhasil ditambahkan.');
    }

    public function edit(Realisasi $realisasi)
    {
        $this->checkEditRule($realisasi);
        
        $lms = MasterLm::all();
        return view('realisasis.edit', compact('realisasi', 'lms'));
    }

    public function update(Request $request, Realisasi $realisasi)
    {
        $this->checkEditRule($realisasi);

        $request->validate([
            'angka_realisasi' => 'required|numeric',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan_tambahan' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('bukti_file')) {
            $data['bukti_file'] = $request->file('bukti_file')->store('bukti_realisasi', 'public');
        }

        $realisasi->update($data);

        return redirect()->route('realisasis.index')->with('success', 'Realisasi berhasil diperbarui.');
    }

    public function destroy(Realisasi $realisasi)
    {
        $this->checkDeleteRule($realisasi);
        
        $realisasi->delete();
        return redirect()->route('realisasis.index')->with('success', 'Realisasi berhasil dihapus.');
    }

    /**
     * CRITICAL RULE: Same Day Rule
     * Users can only edit a record if today is the same day as tanggal_input.
     * Superadmin can edit or delete anytime.
     */
    private function checkEditRule(Realisasi $realisasi)
    {
        if (in_array(auth()->user()->role_name, ['Super Admin', 'superadmin'])) {
            return; // Superadmin has full access
        }

        if (!Carbon::parse($realisasi->tanggal_input)->isSameDay(now())) {
            abort(403, 'Akses Ditolak: Data realisasi LM hanya dapat diedit pada hari yang sama dengan tanggal pelaksanaannya.');
        }
    }

    private function checkDeleteRule(Realisasi $realisasi)
    {
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak: Hanya Superadmin yang dapat menghapus data realisasi LM.');
        }
    }
}
