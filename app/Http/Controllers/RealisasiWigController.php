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
        $up3Filter = $request->input('up3_id', '');

        $user = auth()->user();
        $userMatrixGroup = $user ? trim((string)($user->matrix_group_id ?? 'ALL')) : 'ALL';
        $isSuperAdmin = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID']);

        $skipMatrixFilter = $user && $user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'SRM Perencanaan UID', 'Asman Perencanaan UP3', 'Manager UP3', 'Manager ULP', 'General Manager UID']);

        // Query MasterWig with realisasis
        $wigsQuery = MasterWig::with(['satuan', 'realisasis' => function($q) use ($bulanFilter, $tahunFilter, $up3Filter, $isSuperAdmin, $user) {
            $q->where('bulan', $bulanFilter)
              ->where('tahun', $tahunFilter)
              ->with(['unit', 'user']);

            // Hierarki Unit Kerja Filter (match Realisasi LM)
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

            // Filter UP3 dari Request
            if ($up3Filter) {
                $q->whereIn('unit_id', function($subq) use ($up3Filter) {
                    $subq->select('id')->from('master_units')
                      ->where('id', $up3Filter)
                      ->orWhere('parent_id', $up3Filter);
                });
            }

            $q->orderBy('unit_id', 'asc');
        }]);

        if (!empty($wigFilter)) {
            $wigsQuery->where('id', $wigFilter);
        }

        // Filter by Matrix Bidang
        if (!$skipMatrixFilter && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsQuery->where(function($query) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $query->orWhereJsonContains('divisi', $div);
                }
            });
        }

        $displayWigs = $wigsQuery->get();

        // Only keep wigs that have matching realisasis (or just show them all and display "No Data" inside)
        // For standard cascading feel, we can show the WIG even if empty, but maybe sort them or just show all allowed WIGs.

        // We only show WIGs that have breakdown for this user's unit (for the input modal)
        $unitId = $user->unit_id;
        $wigsInputQuery = MasterWig::whereHas('breakdowns', function($q) use ($unitId, $tahunFilter) {
            if ($unitId) {
                $q->where('unit_id', $unitId);
            }
            $q->where('tahun', $tahunFilter);
        });

        if (!$skipMatrixFilter && $userMatrixGroup !== '' && strtoupper($userMatrixGroup) !== 'ALL') {
            $allowedDivisis = \App\Models\MasterBidang::getRelatedDivisions($userMatrixGroup);
            $wigsInputQuery->where(function($q) use ($allowedDivisis) {
                foreach ($allowedDivisis as $div) {
                    $q->orWhereJsonContains('divisi', $div);
                }
            });
        }
        $wigs = $wigsInputQuery->get();

        $availableUnits = \App\Models\MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->get();

        return view('realisasis.wig', compact('displayWigs', 'wigs', 'bulanFilter', 'tahunFilter', 'wigFilter', 'up3Filter', 'availableUnits', 'isSuperAdmin'));
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorizeSuperadmin();
        
        $ids = json_decode($request->input('ids', '[]'), true);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data realisasi yang dipilih untuk dihapus.');
        }

        $realisasis = RealisasiWig::whereIn('id', $ids)->get();

        foreach ($realisasis as $r) {
            if ($r->bukti_file) {
                Storage::disk('public')->delete($r->bukti_file);
            }
            $r->delete();
        }

        $redirect = redirect()->back()->with('success', count($realisasis) . ' data realisasi WIG berhasil dihapus.');
        if ($realisasis->first()) $redirect->with('active_wig', $realisasis->first()->wig_id);
        return $redirect;
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

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Admin Sub Bidang UID'])) {
            return redirect()->back()->with('error', 'Hanya Admin Sub Bidang UID yang dapat menginput Realisasi WIG.');
        }

        if (!$user->hasAnyRole(['Super Admin', 'Perencanaan UID'])) {
            $currentMonth = (int)date('n');
            $currentYear = (int)date('Y');
            if ((int)$request->bulan !== $currentMonth || (int)$request->tahun !== $currentYear) {
                return redirect()->back()->with('error', 'Anda hanya dapat menginput realisasi untuk bulan berjalan.');
            }
        }

        $unitId = $user->hasAnyRole(['Super Admin', 'Perencanaan UID']) 
                  ? $request->unit_id 
                  : $user->unit_id;
        
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

        return redirect()->back()->with('success', 'Realisasi WIG berhasil ditambahkan.')->with('active_wig', $request->wig_id);
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

        return redirect()->back()->with('success', 'Realisasi WIG berhasil diperbarui.')->with('active_wig', $realisasi_wig->wig_id);
    }

    public function destroy(RealisasiWig $realisasi_wig)
    {
        $this->authorizeSuperadmin();

        if ($realisasi_wig->bukti_file) {
            Storage::disk('public')->delete($realisasi_wig->bukti_file);
        }
        
        $wig_id = $realisasi_wig->wig_id;
        $realisasi_wig->delete();
        return redirect()->back()->with('success', 'Realisasi WIG berhasil dihapus.')->with('active_wig', $wig_id);
    }

    private function authorizeSuperadmin()
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Admin Sub Bidang UID'])) {
            abort(403, 'Akses Ditolak: Hanya Admin Sub Bidang UID atau Superadmin yang dapat mengedit/menghapus Realisasi WIG.');
        }
    }
    public function downloadTemplate(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Asman Perencanaan UP3'])) {
            abort(403, 'Akses Ditolak');
        }
        
        $tahun = $request->query('tahun', date('Y'));
        
        $wigs = MasterWig::all();
        if ($user->hasRole('Asman Perencanaan UP3') && $user->unit_id) {
            $up3s = \App\Models\MasterUnit::where('id', $user->unit_id)->get();
        } else {
            $up3s = \App\Models\MasterUnit::whereIn('type', ['UP3', 'UP2D', 'UP2K'])->get();
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Realisasi WIG ' . $tahun);

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
        
        $sheet->getStyle('A1:AD1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

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
                
                // Style Data Row
                $sheet->getStyle('A' . $row . ':AD' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
                $sheet->getRowDimension($row)->setRowHeight(22);
                
                $row++;
            }
        }
        
        // Auto Fit Lebar Kolom
        $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD'];
        foreach ($columns as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
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
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Perencanaan UID', 'Asman Perencanaan UP3'])) {
            abort(403, 'Akses Ditolak');
        }
        
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
