<?php

namespace App\Http\Controllers;

use App\Models\SesiWigKomitmen;
use App\Models\SesiWig;
use Illuminate\Http\Request;

class SesiWigKomitmenController extends Controller
{
    public function show($sesi_wig_id, $lm_id, $unit_id)
    {
        $komitmen = SesiWigKomitmen::where('sesi_wig_id', $sesi_wig_id)
            ->where('lm_id', $lm_id)
            ->where('unit_id', $unit_id)
            ->first();

        $defaultKomitmen = null;
        if (!$komitmen || is_null($komitmen->komitmen)) {
            $sesiWig = \App\Models\SesiWig::find($sesi_wig_id);
            if ($sesiWig) {
                $masterPeriode = \App\Models\MasterPeriode::where('tahun', $sesiWig->tahun)->where('bulan', $sesiWig->bulan)->first();
                $targetStart = null;
                if ($masterPeriode) {
                    $col = 'start_m' . $sesiWig->minggu_ke;
                    $targetStart = $masterPeriode->$col;
                }
                if ($targetStart) {
                    $breakdownLm = \App\Models\BreakdownLm::where('lm_id', $lm_id)
                        ->where('unit_id', $unit_id)
                        ->where('periode_start', '<=', $targetStart)
                        ->where('periode_end', '>=', $targetStart)
                        ->orderByRaw('DATEDIFF(periode_end, periode_start) ASC')
                        ->first();
                if ($breakdownLm) {
                    $defaultKomitmen = $breakdownLm->angka_target;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $komitmen,
            'default_komitmen' => $defaultKomitmen
        ]);
    }

    public function store(Request $request, $sesi_wig_id, $lm_id, $unit_id)
    {
        $sesi = SesiWig::findOrFail($sesi_wig_id);
        
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->hasRole('Super Admin') || strtolower($user->role_name) === 'super admin');
        
        if (!$isSuperAdmin) {
            $masterPeriode = \App\Models\MasterPeriode::where('tahun', $sesi->tahun)->where('bulan', $sesi->bulan)->first();
            $startDate = null;
            if ($masterPeriode) {
                $col = 'start_m' . $sesi->minggu_ke;
                $startDate = $masterPeriode->$col;
            }
            if (!$startDate) {
                $startDate = \Carbon\Carbon::create($sesi->tahun, $sesi->bulan, 1)->addDays(($sesi->minggu_ke - 1) * 7)->format('Y-m-d');
            }
            $targetStart = \Carbon\Carbon::parse($startDate);
            $openDate = $targetStart->copy()->subDays(6)->startOfDay(); // Tuesday 00:00:00 before target week
            $deadline = $targetStart->copy()->endOfDay(); // Monday 23:59:59 of target week

            if (now()->isAfter($deadline) || now()->isBefore($openDate)) {
                return response()->json(['success' => false, 'message' => 'Batas waktu pengisian komitmen (Hari Selasa s/d Senin ' . $deadline->format('d M') . ' pukul 23:59) tidak sesuai. Saat ini Anda hanya bisa melihat komitmen (Read-Only).'], 403);
            }
        }
        
        $request->validate([
            'pic_lm' => 'nullable|string|max:255',
            'komitmen' => 'nullable|numeric',
            'carry_over' => 'nullable|numeric',
            'hambatans' => 'nullable|array',
            'aksi_konkrits' => 'nullable|array',
        ]);

        $komitmen = SesiWigKomitmen::updateOrCreate(
            [
                'sesi_wig_id' => $sesi_wig_id,
                'lm_id' => $lm_id,
                'unit_id' => $unit_id,
            ],
            [
                'pic_lm' => $request->pic_lm,
                'komitmen' => $request->komitmen,
                'carry_over' => $request->carry_over,
                'hambatans' => $request->hambatans,
                'aksi_konkrits' => $request->aksi_konkrits,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Komitmen berhasil disimpan!',
            'data' => $komitmen
        ]);
    }
}
