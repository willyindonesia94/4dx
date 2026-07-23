<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $activeYears = SystemSetting::get('active_years', ['2026']);
        return view('settings.index', compact('activeYears'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'active_years' => 'required|string',
        ]);

        // Process comma-separated years into an array
        $yearsArray = array_map('trim', explode(',', $request->active_years));
        $yearsArray = array_filter($yearsArray); // Remove empty values

        SystemSetting::set('active_years', $yearsArray);

        return redirect()->route('settings.index')->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
