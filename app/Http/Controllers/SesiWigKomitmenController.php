<?php

namespace App\Http\Controllers;

use App\Models\SesiWigKomitmen;
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
            if ($sesiWig && $sesiWig->tanggal_pelaksanaan) {
                $breakdownLm = \App\Models\BreakdownLm::where('lm_id', $lm_id)
                    ->where('unit_id', $unit_id)
                    ->where('periode_start', '<=', $sesiWig->tanggal_pelaksanaan->format('Y-m-d'))
                    ->where('periode_end', '>=', $sesiWig->tanggal_pelaksanaan->format('Y-m-d'))
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
