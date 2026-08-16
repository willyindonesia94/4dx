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
        $wigFilter = $request->input('wig_id', '');

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);

        $query = RealisasiWig::with(['wig.satuan', 'unit', 'user'])
                             ->where('bulan', $bulanFilter)
                             ->where('tahun', $tahunFilter);
        
        if (!empty($wigFilter)) {
            $query->where('wig_id', $wigFilter);
        }
        
        // Filter by Matrix Bidang
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $query->whereHas('wig', function($q) use ($allowedDivisis) {
                $q->where(function($query) use ($allowedDivisis) {
                    foreach ($allowedDivisis as $div) {
                        $query->orWhereJsonContains('divisi', $div);
                    }
                });
            });
        }
        
        // Filter by user's unit if not superadmin/pusat (assuming UP3 only sees their own)
        if (!$isSuperAdmin && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }

        $realisasis = $query->orderBy('unit_id', 'asc')->paginate(10)->withQueryString();
        
        // We only show WIGs that have breakdown for this user's unit (for the input modal)
        $unitId = $user->unit_id;
        $wigsQuery = MasterWig::whereHas('breakdowns', function($q) use ($unitId, $tahunFilter) {
            if ($unitId) {
                $q->where('unit_id', $unitId);
            }
            $q->where('tahun', $tahunFilter);
        });

        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        $wigs = $wigsQuery->get();

        $availableUnits = \App\Models\MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->get();

        return view('realisasis.wig', compact('realisasis', 'wigs', 'bulanFilter', 'tahunFilter', 'wigFilter', 'availableUnits'));
    }

    public function getTargetBulanan(Request $request)
    {
        $wigId = $request->wig_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unitId = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID', 'Admin Unit']) 
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
            $wig = MasterWig::find($wigId);
        
            return response()->json([
                'target' => $breakdown->$column,
                'satuan' => $wig->satuan->name ?? '',
                'polaritas' => $wig->polaritas ?? '1'
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
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID'])) {
            $currentMonth = (int)date('n');
            $currentYear = (int)date('Y');
            if ((int)$request->bulan !== $currentMonth || (int)$request->tahun !== $currentYear) {
                return redirect()->back()->with('error', 'Anda hanya dapat menginput realisasi untuk bulan berjalan.');
            }
        }

        $unitId = in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']) 
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
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID'])) {
            abort(403, 'Akses Ditolak: Hanya Superadmin yang dapat mengedit/menghapus Realisasi WIG.');
        }
    }
    public function downloadTemplate(Request $request)
    {
        $this->authorizeSuperadmin();
        
        $tahun = $request->query('tahun', date('Y'));
        
        $wigs = MasterWig::all();
        $up3s = \App\Models\MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Realisasi WIG ' . $tahun);

        $months = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
        $monthCols = ['target_jan', 'target_feb', 'target_mar', 'target_apr', 'target_mei', 'target_jun', 'target_jul', 'target_agu', 'target_sep', 'target_okt', 'target_nov', 'target_des'];

        $headers = ['NO', 'TYPE', 'INDIKATOR KINERJA ' . $tahun, 'POLARITAS', 'SATUAN', 'UNIT'];
        foreach ($months as $m) $headers[] = 'TARGET ' . $m;
        foreach ($months as $m) $headers[] = 'REALISASI ' . $m;

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;
        foreach ($up3s as $up3) {
            foreach ($wigs as $wig) {
                $breakdown = BreakdownWig::where('wig_id', $wig->id)
                                         ->where('unit_id', $up3->id)
                                         ->where('tahun', $tahun)
                                         ->first();
                
                $sheet->setCellValue('A' . $row, $row - 1); // NO
                $sheet->setCellValue('B' . $row, '4DX'); // TYPE
                $sheet->setCellValue('C' . $row, $wig->judul); // INDIKATOR
                $sheet->setCellValue('D' . $row, $wig->polaritas ?? '3'); // POLARITAS
                $sheet->setCellValue('E' . $row, $wig->satuan->name ?? ''); // SATUAN
                $sheet->setCellValue('F' . $row, $up3->name); // UNIT
                
                // Targets
                $c = 'G';
                foreach ($monthCols as $mCol) {
                    $sheet->setCellValue($c . $row, $breakdown ? $breakdown->$mCol : 0);
                    $c++;
                }

                // Realisasi (empty by default)
                foreach ($monthCols as $mCol) {
                    $sheet->setCellValue($c . $row, '');
                    $c++;
                }
                
                $row++;
            }
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "Template_Realisasi_WIG_Tahun_{$tahun}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $this->authorizeSuperadmin();
        
        $request->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv',
            'tahun' => 'required|integer',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\RealisasiWigImport($request->tahun), $request->file('file_import'));
            return redirect()->back()->with('success', 'Data Realisasi WIG berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat impor: ' . $e->getMessage());
        }
    }
}
