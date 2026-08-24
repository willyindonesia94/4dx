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
        $up3IdFilter = $request->input('up3_id');

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && $user->hasRole('Super Admin');
        $isUlpLevel = !$isSuperAdmin && $user && $user->unit && in_array(strtoupper(trim((string)$user->unit->type)), ['ULP', 'UP2D', 'UP2K']);
        
        $skipMatrixFilter = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'SRM Perencanaan UID', 'Asman Perencanaan UP3', 'Manager UP3', 'Manager ULP', 'General Manager UID']);

        // Base Query for WIGs to display (only approved WIGs and LMs)
        $displayWigsQuery = \App\Models\MasterWig::where('is_approved', true)->with(['masterLms' => function($q) {
            $q->where('is_approved', true);
        }]);

        if ($wigId) {
            $displayWigsQuery->where('id', $wigId);
        }

        // Apply Matrix Bidang
        if (!$skipMatrixFilter && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $displayWigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }

        $displayWigs = $displayWigsQuery->get();

        // Eager load realisasis and apply filters
        $displayWigs->load(['masterLms.realisasis' => function($q) use ($bulan, $tahun, $up3IdFilter, $lmIdFilter, $isSuperAdmin, $user) {
            $q->whereMonth('tanggal_input', $bulan)
              ->whereYear('tanggal_input', $tahun)
              ->with('unit', 'user');

            if ($lmIdFilter) {
                $q->where('lm_id', $lmIdFilter);
            }

            // Hierarki Unit Kerja Filter
            if (!$isSuperAdmin && $user && $user->unit) {
                $unitType = strtoupper(trim((string)$user->unit->type));
                if (in_array($unitType, ['ULP', 'UP2D', 'UP2K'])) {
                    $q->where('unit_id', $user->unit_id);
                } elseif ($unitType === 'UP3') {
                    $q->whereIn('unit_id', function($subq) use ($user) {
                        $subq->select('id')->from('master_units')
                          ->where('id', $user->unit_id)
                          ->orWhere('parent_id', $user->unit_id);
                    });
                }
            }

            // UP3 Filter from request
            if ($up3IdFilter) {
                $q->whereIn('unit_id', function($subq) use ($up3IdFilter) {
                    $subq->select('id')->from('master_units')
                      ->where('id', $up3IdFilter)
                      ->orWhere('parent_id', $up3IdFilter);
                });
            }

            $q->orderBy('tanggal_input', 'desc');
        }]);

        // Sort LMs but do NOT filter out empty ones
        $displayWigs->each(function($wig) {
            $wig->setRelation('masterLms', $wig->masterLms->sortBy(function($lm) {
                preg_match('/LM-?(\d+)/i', $lm->judul_lm, $m);
                return (int)($m[1] ?? 999);
            })->values());
        });

        // Filter dropdown WIG & LM pada Modal Input agar sesuai Matrix Bidang User
        $wigsQuery = \App\Models\MasterWig::with('masterLms.satuan');
        if (!$skipMatrixFilter && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        $wigs = $wigsQuery->get();
        $availableLms = $wigs->pluck('masterLms')->flatten()->unique('id')->sort(function($a, $b) {
            if ($a->wig_id === $b->wig_id) {
                preg_match('/LM-?(\d+)/i', $a->judul_lm, $mA);
                preg_match('/LM-?(\d+)/i', $b->judul_lm, $mB);
                return (int)($mA[1] ?? 999) <=> (int)($mB[1] ?? 999);
            }
            return $a->wig_id <=> $b->wig_id;
        })->values();

        $isUid = !$isSuperAdmin && $user && $user->hasAnyRole(['Perencanaan UID', 'SRM Perencanaan UID', 'SRM Bidang UID', 'MSB UID', 'Admin Sub Bidang UID', 'General Manager UID']);
        $isUp3 = !$isSuperAdmin && $user && $user->hasAnyRole(['Asman Perencanaan UP3', 'Asman Bidang UP3', 'Manager UP3', 'UP2D', 'UP2K']);

        $up3Units = collect();
        if ($isSuperAdmin || $isUid) {
            $up3Units = MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->orderBy('name')->get();
        } elseif ($isUp3 && $user->unit_id) {
            $userUnitType = strtoupper(trim((string)$user->unit->type));
            if (in_array($userUnitType, ['UP2D', 'UP2K'])) {
                $up3Units = MasterUnit::where('id', $user->unit_id)->get();
            } else {
                $up3Units = MasterUnit::where('id', $user->unit_id)
                    ->orWhere(function($q) use ($user) {
                        $q->where('type', 'ULP')->where('parent_id', $user->unit_id);
                    })->orderBy('type')->orderBy('name')->get();
            }
        }
        return view('realisasis.index', compact('displayWigs', 'wigs', 'bulan', 'tahun', 'wigId', 'lmIdFilter', 'up3IdFilter', 'up3Units', 'isUlpLevel', 'availableLms', 'isSuperAdmin'));
    }

    public function create()
    {
        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);

        $lmQuery = MasterLm::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $lmQuery->whereHas('wig', fn($q) => $q->where('divisi', $userMatrixGroup));
        }
        $lms = $lmQuery->get()->sort(function($a, $b) {
            if ($a->wig_id === $b->wig_id) {
                preg_match('/LM-?(\d+)/i', $a->judul_lm, $mA);
                preg_match('/LM-?(\d+)/i', $b->judul_lm, $mB);
                return (int)($mA[1] ?? 999) <=> (int)($mB[1] ?? 999);
            }
            return $a->wig_id <=> $b->wig_id;
        });

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

        $user = auth()->user();
        if (!$user->hasAnyRole(['Team Leader ULP', 'Super Admin', 'Admin Unit', 'Perencanaan UID'])) {
            return redirect()->back()->with('error', 'Hanya Pelaksana / Team Leader ULP yang dapat menginput realisasi LM harian.');
        }

        $data = $request->except('bukti_file');
        $data['user_id'] = auth()->id();
        $data['unit_id'] = auth()->user()->unit_id;
        $data['tanggal_input'] = now();

        if ($request->hasFile('bukti_file')) {
            $data['bukti_file'] = $request->file('bukti_file')->store('bukti_realisasi', 'public');
        }

        Realisasi::create($data);
        $lm = MasterLm::find($request->lm_id);

        $redirect = redirect()->back()->with('success', 'Realisasi berhasil ditambahkan.');
        if ($lm && $lm->wig_id) {
            $redirect->with('active_wig', $lm->wig_id)->with('expanded_lm', $lm->id);
        }
        return $redirect;
    }

    public function edit(Realisasi $realisasi)
    {
        $this->checkEditRule($realisasi);
        
        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);

        $lmQuery = MasterLm::query();
        if (!$isSuperAdmin && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $lmQuery->whereHas('wig', fn($q) => $q->where('divisi', $userMatrixGroup));
        }
        $lms = $lmQuery->get()->sort(function($a, $b) {
            if ($a->wig_id === $b->wig_id) {
                preg_match('/LM-?(\d+)/i', $a->judul_lm, $mA);
                preg_match('/LM-?(\d+)/i', $b->judul_lm, $mB);
                return (int)($mA[1] ?? 999) <=> (int)($mB[1] ?? 999);
            }
            return $a->wig_id <=> $b->wig_id;
        });

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

        return redirect()->back()->with('success', 'Realisasi berhasil diperbarui.')->with('active_wig', $realisasi->lm->wig_id ?? null)->with('expanded_lm', $realisasi->lm_id);
    }

    public function destroy(Realisasi $realisasi)
    {
        $this->checkDeleteRule($realisasi);
        
        $wig_id = $realisasi->lm->wig_id ?? null;
        $lm_id = $realisasi->lm_id;
        $realisasi->delete();
        return redirect()->back()->with('success', 'Realisasi berhasil dihapus.')->with('active_wig', $wig_id)->with('expanded_lm', $lm_id);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $user = auth()->user();
        $isSuperAdmin = $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);

        $deletedCount = 0;
        foreach ($ids as $id) {
            $realisasi = Realisasi::find($id);
            if ($realisasi) {
                // If not superadmin, ensure it is the same day as tanggal_input
                if (!$isSuperAdmin && !\Carbon\Carbon::parse($realisasi->tanggal_input)->isSameDay(now())) {
                    continue; // skip if cannot delete
                }
                $realisasi->delete();
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            return redirect()->back()->with('error', 'Tidak ada data yang berhasil dihapus (mungkin Anda tidak memiliki izin untuk menghapus data di luar hari ini).');
        }

        $first = Realisasi::with('lm')->whereIn('id', $ids)->first();
        $wig_id = $first ? ($first->lm->wig_id ?? null) : null;
        $lm_id = $first ? $first->lm_id : null;
        $redirect = redirect()->back()->with('success', "Berhasil menghapus {$deletedCount} data realisasi LM.");
        if ($wig_id) {
            $redirect->with('active_wig', $wig_id)->with('expanded_lm', $lm_id);
        }
        return $redirect;
    }

    private function checkEditRule(Realisasi $realisasi)
    {
        if (auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID'])) {
            return; // Superadmin has full access
        }

        if (!Carbon::parse($realisasi->tanggal_input)->isSameDay(now())) {
            abort(403, 'Akses Ditolak: Data realisasi LM hanya dapat diedit pada hari yang sama dengan tanggal pelaksanaannya.');
        }
    }

    private function checkDeleteRule(Realisasi $realisasi)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID'])) {
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
        $lms = \App\Models\MasterLm::with('wig')->get()->sort(function($a, $b) {
            if ($a->wig_id === $b->wig_id) {
                preg_match('/LM-?(\d+)/i', $a->judul_lm, $mA);
                preg_match('/LM-?(\d+)/i', $b->judul_lm, $mB);
                return (int)($mA[1] ?? 999) <=> (int)($mB[1] ?? 999);
            }
            return $a->wig_id <=> $b->wig_id;
        });
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
