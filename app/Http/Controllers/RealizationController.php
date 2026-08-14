<?php
namespace App\Http\Controllers;

use App\Models\Realisasi;
use App\Models\MasterLm;
use App\Models\MasterUnit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RealisasiLmMassImport;
use App\Imports\RealisasiLmFormatBidangImport;

class RealizationController extends Controller
{
    public function index(Request $request)
    {
        $bulan      = $request->input('bulan', date('n'));
        $tahun      = $request->input('tahun', date('Y'));
        $wigId      = $request->input('wig_id');
        $lmIdFilter = $request->input('lm_id_filter');

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);
        $isUlpLevel = !$isSuperAdmin && $user && $user->unit && strtoupper(trim((string)$user->unit->type)) === 'ULP';

        $query = Realisasi::with(['lm.wig', 'lm.satuan', 'user', 'unit'])
            ->whereMonth('tanggal_input', $bulan)
            ->whereYear('tanggal_input', $tahun)
            ->latest('tanggal_input');

        if ($wigId) {
            $query->whereHas('lm', fn($q) => $q->where('wig_id', $wigId));
        }

        if ($lmIdFilter) {
            $query->where('lm_id', $lmIdFilter);
        }

        // 1. Pembatasan Sesuai Matrix Bidang User
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $query->whereHas('lm.wig', function($q) use ($allowedDivisis) {
                $q->whereIn('divisi', $allowedDivisis);
            });
        }

        // 2. Pembatasan Sesuai Hierarki Unit Kerja
        if (!$isSuperAdmin && $user && $user->unit) {
            $unitType = strtoupper(trim((string)$user->unit->type));
            if ($unitType === 'ULP') {
                $query->where('unit_id', $user->unit_id);
            } elseif ($unitType === 'UP3') {
                $query->whereIn('unit_id', function($q) use ($user) {
                    $q->select('id')->from('master_units')
                      ->where('id', $user->unit_id)
                      ->orWhere('parent_id', $user->unit_id);
                });
            }
        }

        $realisasis = $query->paginate(20)->appends($request->query());
        
        // Filter dropdown WIG & LM pada Modal Input agar sesuai Matrix Bidang User
        $wigsQuery = \App\Models\MasterWig::with('masterLms.satuan');
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->whereIn('divisi', $allowedDivisis);
        }
        $wigs = $wigsQuery->get();
        $availableLms = $wigs->pluck('masterLms')->flatten()->unique('id');

        // Available months that have data (for tabs)
        $availableMonths = Realisasi::selectRaw('MONTH(tanggal_input) as bulan, YEAR(tanggal_input) as tahun')
            ->groupByRaw('YEAR(tanggal_input), MONTH(tanggal_input)')
            ->orderByRaw('YEAR(tanggal_input) DESC, MONTH(tanggal_input) ASC')
            ->get();

        return view('realisasis.index', compact('realisasis', 'wigs', 'bulan', 'tahun', 'wigId', 'lmIdFilter', 'availableMonths', 'isUlpLevel', 'availableLms'));
    }

    public function create()
    {
        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);

        $lmQuery = MasterLm::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $lmQuery->whereHas('wig', fn($q) => $q->where('divisi', $userMatrixGroup));
        }
        $lms = $lmQuery->get();

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
        
        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && in_array($user->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID']);

        $lmQuery = MasterLm::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $lmQuery->whereHas('wig', fn($q) => $q->where('divisi', $userMatrixGroup));
        }
        $lms = $lmQuery->get();

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
        if (in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID'])) {
            return; // Superadmin has full access
        }

        if (!Carbon::parse($realisasi->tanggal_input)->isSameDay(now())) {
            abort(403, 'Akses Ditolak: Data realisasi LM hanya dapat diedit pada hari yang sama dengan tanggal pelaksanaannya.');
        }
    }

    private function checkDeleteRule(Realisasi $realisasi)
    {
        if (!in_array(auth()->user()->role_name, ['Super Admin', 'superadmin', 'Perencanaan UID'])) {
            abort(403, 'Akses Ditolak: Hanya Superadmin yang dapat menghapus data realisasi LM.');
        }
    }

    /**
     * Import realisasi LM dari file Excel
     */
    public function import(Request $request)
    {
        $fileKey = $request->hasFile('file_excel') ? 'file_excel' : 'file';
        
        $request->validate([
            $fileKey => 'required|mimes:xlsx,xls',
            'is_prorata' => 'nullable|boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        try {
            $isProrata = $request->input('is_prorata', false);
            $tanggalMulai = $request->input('tanggal_mulai');
            $tanggalSelesai = $request->input('tanggal_selesai');
            $bulanImport = (int) $request->input('bulan_import', date('n'));
            $tahunImport = (int) $request->input('tahun_import', date('Y'));
            $formatImport = $request->input('format_import', 'standar');

            if ($formatImport === 'bidang') {
                Excel::import(new RealisasiLmFormatBidangImport($bulanImport, $tahunImport), $request->file($fileKey));
            } else {
                Excel::import(new RealisasiLmMassImport($isProrata, $tanggalMulai, $tanggalSelesai, $bulanImport, $tahunImport), $request->file($fileKey));
            }
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
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Realisasi LM');

        // Style untuk Header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3730A3'], // Indigo / Blue 800
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];

        // Header Row - Persis Sesuai Urutan Form UI Realisasi Harian
        $headers = [
            'A1' => 'judul_wig',
            'B1' => 'judul_lm',
            'C1' => 'angka_realisasi',
            'D1' => 'tanggal_input',
            'E1' => 'bukti_keterangan',
            'F1' => 'email_penginput',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Fetch all LMs to populate the template
        $lms = \App\Models\MasterLm::with('wig')->orderBy('wig_id')->orderBy('id')->get();
        $rowNum = 2;
        $userEmail = auth()->user() ? (auth()->user()->email ?? '') : '';
        $today = date('Y-m-d');

        if ($lms->count() > 0) {
            foreach ($lms as $lm) {
                $sheet->setCellValue('A' . $rowNum, $lm->wig ? $lm->wig->judul : '');
                $sheet->setCellValue('B' . $rowNum, $lm->judul_lm);
                $sheet->setCellValue('C' . $rowNum, '');
                $sheet->setCellValue('D' . $rowNum, $today);
                $sheet->setCellValue('E' . $rowNum, '');
                $sheet->setCellValue('F' . $rowNum, $userEmail);

                // Style Contoh Data
                $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
                $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                $sheet->getRowDimension($rowNum)->setRowHeight(22);
                
                $rowNum++;
            }
        } else {
            // Fallback jika belum ada LM
            $sheet->setCellValue('A2', 'WIG 01: Contoh WIG');
            $sheet->setCellValue('B2', 'LM-1 Contoh Lead Measure');
            $sheet->setCellValue('C2', 15.50);
            $sheet->setCellValue('D2', $today);
            $sheet->setCellValue('E2', 'https://link-bukti.com / Laporan Kunjungan Pelanggan');
            $sheet->setCellValue('F2', $userEmail);
            $sheet->getStyle('A2:F2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
            $sheet->getStyle('C2')->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('D2')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            $sheet->getRowDimension(2)->setRowHeight(22);
        }

        // Auto Fit Lebar Kolom
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $responseHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_upload_realisasi_lm.xlsx"',
        ];

        return response($content, 200, $responseHeaders);
    }
}
