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

        return response()->json([
            'status' => 'success',
            'data' => $komitmen
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
