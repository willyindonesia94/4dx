<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterWig;
use App\Models\SesiWig;
use App\Models\SesiWigKomitmen;
use App\Models\MasterUnit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanKomitmenController extends Controller
{
    public function index(Request $request)
    {
        $wigs = MasterWig::orderBy('id')->get();
        
        $years = SesiWig::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $weeks = [1, 2, 3, 4, 5];

        $filterWig = $request->query('wig_id', $wigs->first()->id ?? null);
        $filterYear = $request->query('tahun', date('Y'));
        $filterMonth = $request->query('bulan', date('n'));
        $filterWeek = $request->query('minggu_ke', 1);

        $previewData = null;
        if ($filterWig && $filterYear && $filterMonth && $filterWeek) {
            $previewData = $this->getLaporanData($filterWig, $filterYear, $filterMonth, $filterWeek);
        }

        return view('laporan-komitmen.index', compact(
            'wigs', 'years', 'months', 'weeks', 
            'filterWig', 'filterYear', 'filterMonth', 'filterWeek', 'previewData'
        ));
    }

    private function getLaporanData($wigId, $tahun, $bulan, $mingguKe)
    {
        $sesi = SesiWig::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('minggu_ke', $mingguKe)
            ->first();

        if (!$sesi) {
            return null;
        }

        $wig = MasterWig::find($wigId);
        
        // Get all komitmens for this session and WIG
        $komitmens = SesiWigKomitmen::with(['masterLm', 'masterUnit.parent'])
            ->where('sesi_wig_id', $sesi->id)
            ->whereHas('masterLm', function($q) use ($wigId) {
                $q->where('wig_id', $wigId);
            })
            ->get();

        // Group by UP3 (Parent Unit) -> ULP (Child Unit)
        $grouped = [];

        foreach ($komitmens as $k) {
            $unit = $k->masterUnit;
            if (!$unit) continue;
            
            // If unit has parent (ULP), use parent as UP3. If it has no parent, it might be UP3 itself.
            $up3 = $unit->parent ? $unit->parent : $unit;
            $ulp = $unit->parent ? $unit : null;

            $up3Name = $up3->name;
            if (!isset($grouped[$up3Name])) {
                $grouped[$up3Name] = [];
            }

            if ($ulp) {
                $ulpName = $ulp->name;
                if (!isset($grouped[$up3Name][$ulpName])) {
                    $grouped[$up3Name][$ulpName] = [];
                }
                $grouped[$up3Name][$ulpName][] = $k;
            } else {
                // If it's a UP3 making direct commitment
                if (!isset($grouped[$up3Name]['(Tanpa ULP)'])) {
                    $grouped[$up3Name]['(Tanpa ULP)'] = [];
                }
                $grouped[$up3Name]['(Tanpa ULP)'][] = $k;
            }
        }

        // Sort keys
        ksort($grouped);
        foreach ($grouped as &$ulps) {
            ksort($ulps);
        }

        return [
            'sesi' => $sesi,
            'wig' => $wig,
            'data' => $grouped
        ];
    }

    public function preview(Request $request)
    {
        $wigId = $request->get('wig_id');
        $tahun = $request->get('tahun');
        $bulan = $request->get('bulan');
        $mingguKe = $request->get('minggu_ke');

        $data = $this->getLaporanData($wigId, $tahun, $bulan, $mingguKe);
        if (!$data) {
            return response()->json(['html' => '<div class="p-4 text-center text-gray-500">Tidak ada sesi WIG pada minggu ini.</div>']);
        }

        $html = view('laporan-komitmen.pdf_view', $data)->render();
        return response()->json(['html' => $html]);
    }

    public function exportPdf(Request $request)
    {
        $wigId = $request->input('wig_id');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $mingguKe = $request->input('minggu_ke');

        $data = $this->getLaporanData($wigId, $tahun, $bulan, $mingguKe);
        if (!$data) {
            return back()->with('error', 'Tidak ada data komitmen pada periode tersebut.');
        }

        $pdf = Pdf::loadView('laporan-komitmen.pdf_view', $data)
            ->setPaper('legal', 'landscape');
            
        $filename = "Laporan_Komitmen_Minggu_{$mingguKe}_Bulan_{$bulan}_Tahun_{$tahun}.pdf";

        return $pdf->download($filename);
    }
    public function exportWord(Request $request)
    {
        $wigId = $request->input('wig_id');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $mingguKe = $request->input('minggu_ke');

        $data = $this->getLaporanData($wigId, $tahun, $bulan, $mingguKe);
        if (!$data) {
            return back()->with('error', 'Tidak ada data komitmen pada periode tersebut.');
        }

        $filename = "Laporan_Komitmen_Minggu_{$mingguKe}_Bulan_{$bulan}_Tahun_{$tahun}.doc";
        $html = view('laporan-komitmen.excel_view', $data)->render();
        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}


