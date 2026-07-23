<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LmImportTemplateExport;
use App\Imports\HistoricalDataImport;
use App\Models\MasterLm;
use App\Models\BreakdownLm;
use App\Models\Realisasi;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBulananController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        
        $export = new \App\Exports\MonthlyReportExport($tahun, $bulan);
        $previewView = $export->view();
        $previewData = $previewView->getData();

        return view('laporan-bulanan.index', compact('bulan', 'tahun', 'previewData'));
    }

    public function downloadTemplate(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));
        
        $filename = "Template_Import_Historis_{$tahun}_{$bulan}.xlsx";
        return Excel::download(new LmImportTemplateExport($tahun, $bulan), $filename);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));

        try {
            Excel::import(new HistoricalDataImport($tahun, $bulan), $request->file('file_excel'));
            return redirect()->back()->with('success', 'Data historis berhasil diimpor!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function exportLaporan(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $format = $request->input('format', 'excel');
        
        $filename = "Laporan_LM_{$tahun}_{$bulan}";
        $export = new \App\Exports\MonthlyReportExport($tahun, $bulan);

        if ($format === 'pdf') {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', '300');
            
            $view = $export->view();
            $pdf = Pdf::loadView($view->name(), $view->getData())
                      ->setPaper('legal', 'landscape');
            return $pdf->download("{$filename}.pdf");
        }

        return Excel::download($export, "{$filename}.xlsx");
    }
}
