<?php

namespace App\Http\Controllers;

use App\Models\RealisasiWig;
use App\Models\MasterWig;
use App\Models\BreakdownWig;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class RealisasiWigController extends Controller
{
    public function index(Request $request)
    {
        $bulanFilter = $request->input('bulan', date('n'));
        $tahunFilter = $request->input('tahun', date('Y'));

        $query = RealisasiWig::with(['wig.satuan', 'unit', 'user']);
        
        // Filter by user's unit if not superadmin/pusat (assuming UP3 only sees their own)
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin']) && auth()->user()->unit_id) {
            $query->where('unit_id', auth()->user()->unit_id);
        }

        $realisasis = $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        
        // We only show WIGs that have breakdown for this user's unit (for the input modal)
        $unitId = auth()->user()->unit_id;
        $wigs = MasterWig::whereHas('breakdowns', function($q) use ($unitId, $tahunFilter) {
            if ($unitId) {
                $q->where('unit_id', $unitId);
            }
            $q->where('tahun', $tahunFilter);
        })->get();

        $availableUnits = \App\Models\MasterUnit::where('type', 'up3')->get();

        return view('realisasis.wig', compact('realisasis', 'wigs', 'bulanFilter', 'tahunFilter', 'availableUnits'));
    }

    public function getTargetBulanan(Request $request)
    {
        $wigId = $request->wig_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unitId = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID', 'Admin Unit']) 
                  ? $request->unit_id 
                  : auth()->user()->unit_id;

        if (!$unitId) {
            return response()->json(['error' => 'Unit belum dipilih atau tidak valid'], 400);
        }

        $monthMap = [
            1 => 'target_jan', 2 => 'target_feb', 3 => 'target_mar', 4 => 'target_apr',
            5 => 'target_mei', 6 => 'target_jun', 7 => 'target_jul', 8 => 'target_agu',
            9 => 'target_sep', 10 => 'target_okt', 11 => 'target_nov', 12 => 'target_des'
        ];

        if (!isset($monthMap[$bulan])) {
            return response()->json(['error' => 'Bulan tidak valid'], 400);
        }

        $column = $monthMap[$bulan];

        $breakdown = BreakdownWig::where('wig_id', $wigId)
                                 ->where('unit_id', $unitId)
                                 ->where('tahun', $tahun)
                                 ->first();

        if ($breakdown) {
            return response()->json([
                'target' => $breakdown->$column,
                'satuan' => $breakdown->satuan->name ?? ''
            ]);
        }

        return response()->json(['target' => null, 'message' => 'Belum ada target bulanan untuk WIG ini pada bulan/tahun yang dipilih. Hubungi admin.'], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'wig_id' => 'required|exists:master_wigs,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
            'angka_realisasi' => 'required|numeric',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan_tambahan' => 'nullable|string|max:1000',
        ]);

        // UP3 restriction: only current month
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin'])) {
            $currentMonth = (int)date('n');
            $currentYear = (int)date('Y');
            if ((int)$request->bulan !== $currentMonth || (int)$request->tahun !== $currentYear) {
                return redirect()->back()->with('error', 'Anda hanya dapat menginput realisasi untuk bulan berjalan.');
            }
        }

        $unitId = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Admin UID']) 
                  ? $request->unit_id 
                  : auth()->user()->unit_id;
        
        if (!$unitId) {
            return redirect()->back()->with('error', 'Unit belum dipilih atau tidak valid.');
        }

        // Ensure breakdown target exists
        $breakdown = BreakdownWig::where('wig_id', $request->wig_id)
                                 ->where('unit_id', $unitId)
                                 ->where('tahun', $request->tahun)
                                 ->first();

        if (!$breakdown) {
            return redirect()->back()->with('error', 'Tidak dapat menyimpan realisasi. Breakdown target untuk unit Anda belum dibuat oleh Admin.');
        }

        // Check if already inputted for this month
        $existing = RealisasiWig::where('wig_id', $request->wig_id)
                                ->where('unit_id', $unitId)
                                ->where('bulan', $request->bulan)
                                ->where('tahun', $request->tahun)
                                ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Realisasi untuk WIG ini pada bulan tersebut sudah ada. Anda bisa mengeditnya.');
        }

        $data = $request->except('bukti_file');
        $data['user_id'] = auth()->id();
        $data['unit_id'] = $unitId;

        if ($request->hasFile('bukti_file')) {
            $data['bukti_file'] = $request->file('bukti_file')->store('bukti_realisasi_wig', 'public');
        }

        RealisasiWig::create($data);

        return redirect()->back()->with('success', 'Realisasi WIG berhasil ditambahkan.');
    }

    public function update(Request $request, RealisasiWig $realisasi_wig)
    {
        $this->authorizeSuperadmin();

        $request->validate([
            'angka_realisasi' => 'required|numeric',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan_tambahan' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        
        if ($request->hasFile('bukti_file')) {
            if ($realisasi_wig->bukti_file) {
                Storage::disk('public')->delete($realisasi_wig->bukti_file);
            }
            $data['bukti_file'] = $request->file('bukti_file')->store('bukti_realisasi_wig', 'public');
        }

        $realisasi_wig->update($data);

        return redirect()->back()->with('success', 'Realisasi WIG berhasil diperbarui.');
    }

    public function destroy(RealisasiWig $realisasi_wig)
    {
        $this->authorizeSuperadmin();

        if ($realisasi_wig->bukti_file) {
            Storage::disk('public')->delete($realisasi_wig->bukti_file);
        }
        
        $realisasi_wig->delete();
        return redirect()->back()->with('success', 'Realisasi WIG berhasil dihapus.');
    }

    private function authorizeSuperadmin()
    {
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak: Hanya Superadmin yang dapat mengedit/menghapus Realisasi WIG.');
        }
    }
}
