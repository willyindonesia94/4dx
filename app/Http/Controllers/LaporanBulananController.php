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
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:10240',
            'tanggal_awal_periode' => 'required|date'
        ]);

        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $tanggalAwal = $request->input('tanggal_awal_periode');

        try {
            Excel::import(new HistoricalDataImport($tahun, $bulan, $tanggalAwal), $request->file('file_excel'));
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

    public function exportWig(Request $request)
    {
        // Placeholder for WIG Export (Excel)
        return back()->with('error', 'Export WIG format Excel belum diimplementasikan sepenuhnya.');
    }

    public function exportLengkap(Request $request)
    {
        $bulan = $request->input('bulan', date('n'));
        $tahun = $request->input('tahun', date('Y'));
        $wigId = $request->input('wig_id');
        
        if (!$wigId) {
            return back()->with('error', 'Pilih WIG terlebih dahulu.');
        }

        if ($wigId === 'all') {
            $wigs = \App\Models\MasterWig::with(['masterLms' => function($q) {
                $q->orderBy('judul_lm');
            }])->get();
        } else {
            $wig = \App\Models\MasterWig::with(['masterLms' => function($q) {
                $q->orderBy('judul_lm');
            }])->findOrFail($wigId);
            $wigs = collect([$wig]);
        }
        
        $units = \App\Models\MasterUnit::where('type', 'up3')->orderBy('name')->get();
        
        // Generate Dashboard Print View Data
        return view('exports.lengkap_html', compact('bulan', 'tahun', 'wigs', 'units'));
    }
}
