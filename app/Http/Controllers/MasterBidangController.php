<?php

namespace App\Http\Controllers;

use App\Models\MasterBidang;
use Illuminate\Http\Request;

class MasterBidangController extends Controller
{
    public function index()
    {
        $bidangs = MasterBidang::with('parent')->orderBy('name', 'asc')->get();
        // Semua bidang bisa berpotensi menjadi parent untuk level di bawahnya
        $parentBidangs = MasterBidang::whereIn('level', ['UID_BIDANG', 'UID_SUBBIDANG', 'UP3_BIDANG'])->orderBy('name', 'asc')->get(['id', 'name', 'level']);
        return view('master-bidangs.index', compact('bidangs', 'parentBidangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:UID_BIDANG,UID_SUBBIDANG,UP3_BIDANG,ULP_BIDANG',
            'parent_id' => 'nullable|exists:master_bidangs,id',
            'description' => 'nullable|string'
        ]);

        MasterBidang::create($request->all());

        return redirect()->route('master-bidangs.index')
            ->with('success', 'Bidang berhasil ditambahkan.')
            ->with('active_tab', $request->level);
    }

    public function update(Request $request, MasterBidang $masterBidang)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:UID_BIDANG,UID_SUBBIDANG,UP3_BIDANG,ULP_BIDANG',
            'parent_id' => 'nullable|exists:master_bidangs,id',
            'description' => 'nullable|string'
        ]);

        if ($request->parent_id == $masterBidang->id) {
            return redirect()->back()->withErrors(['parent_id' => 'Bidang tidak dapat menjadikan dirinya sendiri sebagai induk.']);
        }

        $masterBidang->update($request->all());

        return redirect()->route('master-bidangs.index')
            ->with('success', 'Bidang berhasil diperbarui.')
            ->with('active_tab', $request->level);
    }

    public function destroy(MasterBidang $masterBidang)
    {
        $level = $masterBidang->level;
        $masterBidang->delete();
        return redirect()->route('master-bidangs.index')
            ->with('success', 'Bidang berhasil dihapus.')
            ->with('active_tab', $level);
    }
}
