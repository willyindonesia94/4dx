<?php

namespace App\Http\Controllers;

use App\Models\MasterPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterPeriodeController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        
        $periodes = MasterPeriode::where('tahun', $tahun)
            ->orderBy('bulan', 'asc')
            ->get();
            
        return view('master-periodes.index', compact('tahun', 'periodes'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2040'
        ]);

        $tahun = $request->tahun;

        DB::beginTransaction();
        try {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $weeks = MasterPeriode::calculateDefaultWeeks($tahun, $bulan);
                
                MasterPeriode::updateOrCreate(
                    ['tahun' => $tahun, 'bulan' => $bulan],
                    [
                        'start_m1' => $weeks['target_m1'] ? $weeks['target_m1']['start'] : null,
                        'end_m1'   => $weeks['target_m1'] ? $weeks['target_m1']['end'] : null,
                        'start_m2' => $weeks['target_m2'] ? $weeks['target_m2']['start'] : null,
                        'end_m2'   => $weeks['target_m2'] ? $weeks['target_m2']['end'] : null,
                        'start_m3' => $weeks['target_m3'] ? $weeks['target_m3']['start'] : null,
                        'end_m3'   => $weeks['target_m3'] ? $weeks['target_m3']['end'] : null,
                        'start_m4' => $weeks['target_m4'] ? $weeks['target_m4']['start'] : null,
                        'end_m4'   => $weeks['target_m4'] ? $weeks['target_m4']['end'] : null,
                        'start_m5' => $weeks['target_m5'] ? $weeks['target_m5']['start'] : null,
                        'end_m5'   => $weeks['target_m5'] ? $weeks['target_m5']['end'] : null,
                    ]
                );
            }
            DB::commit();
            return redirect()->back()->with('success', "Kalender Master Periode 4DX untuk tahun {$tahun} berhasil di-generate secara otomatis berlandaskan jadwal Senin-Minggu!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Terjadi kesalahan saat menggenerate kalender: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $periode = MasterPeriode::findOrFail($id);

        $request->validate([
            'start_m1' => 'nullable|date', 'end_m1' => 'nullable|date|after_or_equal:start_m1',
            'start_m2' => 'nullable|date', 'end_m2' => 'nullable|date|after_or_equal:start_m2',
            'start_m3' => 'nullable|date', 'end_m3' => 'nullable|date|after_or_equal:start_m3',
            'start_m4' => 'nullable|date', 'end_m4' => 'nullable|date|after_or_equal:start_m4',
            'start_m5' => 'nullable|date', 'end_m5' => 'nullable|date|after_or_equal:start_m5',
        ]);

        // Simpan referensi tanggal lawas sebelum diperbarui
        $oldDates = [
            'm1' => ['start' => $periode->start_m1, 'end' => $periode->end_m1],
            'm2' => ['start' => $periode->start_m2, 'end' => $periode->end_m2],
            'm3' => ['start' => $periode->start_m3, 'end' => $periode->end_m3],
            'm4' => ['start' => $periode->start_m4, 'end' => $periode->end_m4],
            'm5' => ['start' => $periode->start_m5, 'end' => $periode->end_m5],
        ];

        $periode->update($request->only([
            'start_m1', 'end_m1',
            'start_m2', 'end_m2',
            'start_m3', 'end_m3',
            'start_m4', 'end_m4',
            'start_m5', 'end_m5',
        ]));

        // Cascade/Rambatkan perubahan ke tabel BreakdownLm
        foreach (['m1', 'm2', 'm3', 'm4', 'm5'] as $m) {
            $oldStart = $oldDates[$m]['start'];
            $oldEnd = $oldDates[$m]['end'];
            $newStart = $periode->{"start_$m"};
            $newEnd = $periode->{"end_$m"};

            if ($oldStart && $oldEnd && $newStart && $newEnd) {
                if ($oldStart !== $newStart || $oldEnd !== $newEnd) {
                    \App\Models\BreakdownLm::where('periode_start', $oldStart)
                        ->where('periode_end', $oldEnd)
                        ->update([
                            'periode_start' => $newStart,
                            'periode_end' => $newEnd
                        ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Master Periode Bulan ' . \Carbon\Carbon::createFromDate(null, $periode->bulan, 1)->locale('id')->translatedFormat('F') . ' Tahun ' . $periode->tahun . ' berhasil diperbarui! Perubahan tanggal juga telah otomatis disinkronkan ke seluruh data Cascading LM terkait.');
    }
}
