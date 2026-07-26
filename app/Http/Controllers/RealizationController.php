<?php
namespace App\Http\Controllers;

use App\Models\Realisasi;
use App\Models\MasterLm;
use App\Models\MasterUnit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RealisasiLmMassImport;

class RealizationController extends Controller
{
    public function index(Request $request)
    {
        $bulan   = $request->input('bulan', date('n'));
        $tahun   = $request->input('tahun', date('Y'));
        $wigId   = $request->input('wig_id');

        $query = Realisasi::with(['lm.wig', 'lm.satuan', 'user', 'unit'])
            ->whereMonth('tanggal_input', $bulan)
            ->whereYear('tanggal_input', $tahun)
            ->latest('tanggal_input');

        if ($wigId) {
            $query->whereHas('lm', fn($q) => $q->where('wig_id', $wigId));
        }

        $realisasis = $query->paginate(20)->appends($request->query());
        $wigs = \App\Models\MasterWig::with('masterLms.satuan')->get();

        // Available months that have data (for tabs)
        $availableMonths = Realisasi::selectRaw('MONTH(tanggal_input) as bulan, YEAR(tanggal_input) as tahun')
            ->groupByRaw('YEAR(tanggal_input), MONTH(tanggal_input)')
            ->orderByRaw('YEAR(tanggal_input) DESC, MONTH(tanggal_input) ASC')
            ->get();

        return view('realisasis.index', compact('realisasis', 'wigs', 'bulan', 'tahun', 'wigId', 'availableMonths'));
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

    /**
     * Import realisasi LM dari file Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'is_prorata' => 'nullable|boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $isProrata = $request->input('is_prorata', false);
            $tanggalMulai = $request->input('tanggal_mulai');
            $tanggalSelesai = $request->input('tanggal_selesai');

            Excel::import(new RealisasiLmMassImport($isProrata, $tanggalMulai, $tanggalSelesai), $request->file('file'));
            return redirect()->route('realisasis.index')->with('success', 'Data realisasi LM berhasil di-upload dari Excel.');
        } catch (\Exception $e) {
            return redirect()->route('realisasis.index')->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel untuk import realisasi LM
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_realisasi_lm.xlsx"',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Row
        $sheet->setCellValue('A1', 'judul_lm');
        $sheet->setCellValue('B1', 'nip');
        $sheet->setCellValue('C1', 'tanggal_input');
        $sheet->setCellValue('D1', 'angka_realisasi');
        $sheet->setCellValue('E1', 'link_bukti');

        // Example Row
        $sheet->setCellValue('A2', 'LM-1 Melaksanakan Penambahan Daya Tersambung');
        $sheet->setCellValue('B2', '1234567890');
        $sheet->setCellValue('C2', date('Y-m-d'));
        $sheet->setCellValue('D2', '10.5');
        $sheet->setCellValue('E2', 'https://link-bukti.com');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, $headers);
    }
}
